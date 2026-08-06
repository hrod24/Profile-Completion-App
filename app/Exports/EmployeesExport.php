<?php

namespace App\Exports;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Support\DashboardExportWorkbook;
use Maatwebsite\Excel\Events\BeforeWriting;

final class EmployeesExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnFormatting,
    WithColumnWidths,
    WithEvents,
    WithTitle
{
    private array $columns;

    public function __construct(private readonly Builder $employeeQuery)
    {
        $configuredColumns = config('employee_export.columns', []);
        $excludedColumns = config('employee_export.excluded_columns', []);

        $this->columns = collect($configuredColumns)
            ->reject(
                fn(string $header, string $column): bool => in_array(
                    $column,
                    $excludedColumns,
                    true
                )
            )
            ->all();
    }

    public function query(): Builder
    {
        return $this->employeeQuery
            ->select(array_keys($this->columns))
            ->orderBy('employee_id');
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($employee): array
    {
        return collect(array_keys($this->columns))
            ->map(function (string $column) use ($employee) {
                $value = data_get($employee, $column);

                if ($value instanceof DateTimeInterface) {
                    return $value->format('Y-m-d');
                }

                if ($value === null) {
                    return null;
                }

                /*
                 * Export IDs and long numbers as strings so Excel does not
                 * remove leading zeroes or round values after 15 digits.
                 */
                return (string) $value;
            })
            ->all();
    }

    

    public function title(): string
    {
        return 'Employee Details';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF97316'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFFED7AA'],
                    ],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach (array_keys($this->columns) as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            $formats[$letter] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }

    public function columnWidths(): array
    {
        $longTextColumns = [
            'current_address',
            'ktp_address',
            'tax_object_code_monthly_code',
        ];

        $wideColumns = [
            'display_name',
            'emergency_full_name',
            'mother_full_name',
            'company',
            'institution_name',
            'business_unit_org_element_1',
            'department_org_element_2',
            'current_kotamadya_kabupaten',
            'ktp_kotamadya_kabupaten',
        ];

        $widths = [];

        foreach (array_keys($this->columns) as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);

            $widths[$letter] = match (true) {
                in_array($column, $longTextColumns, true) => 42,
                in_array($column, $wideColumns, true) => 28,
                default => 18,
            };
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (
                BeforeWriting $event
            ): void {
                DashboardExportWorkbook::mark(
                    $event->writer->getDelegate()
                );
            },

            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(
                    count($this->columns)
                );
                $highestRow = max($sheet->getHighestRow(), 1);

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}{$highestRow}");
                $sheet->getRowDimension(1)->setRowHeight(36);

                if ($highestRow >= 2) {
                    $sheet
                        ->getStyle("A2:{$lastColumn}{$highestRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_TOP)
                        ->setWrapText(true);
                }
            },
        ];
    }
}

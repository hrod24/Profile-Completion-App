<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class SetPicExport implements
  FromCollection,
  WithHeadings,
  ShouldAutoSize,
  WithEvents
{
  protected Collection $rows;

  protected Collection $picNames;

  public function __construct(
    Collection $rows,
    Collection $picNames
  ) {
    $this->rows = $rows;
    $this->picNames = $picNames;
  }

  public function collection(): Collection
  {
    return $this->rows;
  }

  public function headings(): array
  {
    return [
      'employee_Id',
      'display_name',
      'pic',
      'company',
      'source',
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (
        AfterSheet $event
      ) {
        $sheet =
          $event->sheet->getDelegate();

        /*
                 * =================================================
                 * HEADER
                 * =================================================
                 */

        $sheet
          ->getStyle('A1:E1')
          ->getFont()
          ->setBold(true);

        /*
                 * Header tetap terlihat saat scroll.
                 */
        $sheet->freezePane('A2');

        /*
                 * =================================================
                 * PIC HELPER LIST
                 * =================================================
                 *
                 * Daftar PIC ditaruh di kolom G.
                 *
                 * Kolom G nantinya disembunyikan,
                 * sehingga user hanya melihat:
                 *
                 * A = employee_Id
                 * B = display_name
                 * C = pic
                 * D = company
                 * E = source
                 */

        $sheet->setCellValue(
          'G1',
          'PIC_LIST'
        );

        foreach (
          $this->picNames as
          $index => $picName
        ) {
          /*
                     * Nama PIC dimulai dari G2.
                     */
          $rowNumber = $index + 2;

          $sheet->setCellValue(
            "G{$rowNumber}",
            $picName
          );
        }

        /*
                 * Hide kolom helper PIC.
                 */
        $sheet
          ->getColumnDimension('G')
          ->setVisible(false);

        /*
                 * =================================================
                 * PIC DROPDOWN
                 * =================================================
                 */

        $employeeCount =
          $this->rows->count();

        $picCount =
          $this->picNames->count();

        /*
                 * Tidak perlu membuat dropdown
                 * apabila tidak ada employee
                 * atau tidak ada PIC.
                 */
        if (
          $employeeCount === 0 ||
          $picCount === 0
        ) {
          return;
        }

        /*
                 * Heading berada di row 1.
                 *
                 * Employee pertama = row 2.
                 */
        $lastEmployeeRow =
          $employeeCount + 1;

        /*
                 * PIC pertama = G2.
                 */
        $lastPicRow =
          $picCount + 1;

        /*
                 * Dropdown mengambil data dari
                 * hidden column G.
                 */
        $picListFormula =
          '$G$2:$G$' .
          $lastPicRow;

        /*
                 * Terapkan dropdown ke seluruh
                 * cell PIC employee.
                 *
                 * C2
                 * C3
                 * C4
                 * ...
                 */
        for (
          $row = 2;
          $row <= $lastEmployeeRow;
          $row++
        ) {
          $validation =
            new DataValidation();

          $validation->setType(
            DataValidation::TYPE_LIST
          );

          $validation->setErrorStyle(
            DataValidation::STYLE_STOP
          );

          /*
                     * PIC boleh dikosongkan.
                     *
                     * Employee yang PIC-nya kosong
                     * tidak akan di-update saat upload.
                     */
          $validation->setAllowBlank(
            true
          );

          $validation->setShowDropDown(
            true
          );

          $validation
            ->setShowInputMessage(
              true
            );

          $validation
            ->setShowErrorMessage(
              true
            );

          $validation->setErrorTitle(
            'Invalid PIC'
          );

          $validation->setError(
            'PIC harus dipilih dari dropdown yang tersedia.'
          );

          $validation->setFormula1(
            $picListFormula
          );

          $sheet
            ->getCell("C{$row}")
            ->setDataValidation(
              $validation
            );
        }

        /*
                 * =================================================
                 * FILTER EXCEL
                 * =================================================
                 */

        $sheet
          ->setAutoFilter(
            "A1:E{$lastEmployeeRow}"
          );
      },
    ];
  }
}

<?php

namespace App\Services;

use App\Imports\EmployeeDetailsSheetImport;
use App\Models\employee_details;
use App\Models\EmployeeImportBatch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use RuntimeException;

class EmployeeExcelChunkService
{
  public function process(
    EmployeeImportBatch $batch,
    int $startRow,
    int $limit = 200
  ): array {
    /*
         * total_rows tidak termasuk header.
         * Karena header berada pada baris pertama,
         * baris data terakhir adalah total_rows + 1.
         */
    $lastDataRow = ((int) $batch->total_rows) + 1;

    if ($startRow > $lastDataRow) {
      return [
        'processed' => 0,
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'next_row' => $startRow,
      ];
    }

    $endRow = min(
      $startRow + $limit - 1,
      $lastDataRow
    );

    $fullPath = Storage::path(
      $batch->file_path
    );

    if (!is_file($fullPath)) {
      throw new RuntimeException(
        'File Excel sementara tidak ditemukan.'
      );
    }

    $reader = IOFactory::createReaderForFile(
      $fullPath
    );

    $reader->setReadDataOnly(true);

    $reader->setLoadSheetsOnly([
      'Employee Details',
    ]);

    /*
         * Hanya membaca:
         * - baris pertama sebagai header;
         * - rentang data chunk saat ini.
         */
    $reader->setReadFilter(
      new class(
        $startRow,
        $endRow
      ) implements IReadFilter {
        public function __construct(
          private int $startRow,
          private int $endRow
        ) {}

        public function readCell(
          $columnAddress,
          $row,
          $worksheetName = ''
        ) {
          $row = (int) $row;

          return $row === 1
            || (
              $row >= $this->startRow
              && $row <= $this->endRow
            );
        }
      }
    );

    $spreadsheet = $reader->load(
      $fullPath
    );

    try {
      $sheet = $spreadsheet->getSheetByName(
        'Employee Details'
      );

      if (!$sheet) {
        throw new RuntimeException(
          'Sheet Employee Details tidak ditemukan.'
        );
      }

      $highestColumn =
        $sheet->getHighestDataColumn(1);

      $headerRows = $sheet->rangeToArray(
        "A1:{$highestColumn}1",
        null,
        true,
        true,
        false
      );

      $rawHeaders = $headerRows[0] ?? [];

      $headers = array_map(
        fn($header): string =>
        $this->normalizeHeader($header),
        $rawHeaders
      );

      if (
        !in_array(
          'employee_id',
          $headers,
          true
        )
      ) {
        throw new RuntimeException(
          'Header Employee ID tidak ditemukan.'
        );
      }

      $rawRows = $sheet->rangeToArray(
        "A{$startRow}:{$highestColumn}{$endRow}",
        null,
        true,
        true,
        false
      );

      $rows = collect();
      $employeeIds = [];

      foreach ($rawRows as $rawRow) {
        $rowData = [];

        foreach (
          $headers as $columnIndex => $header
        ) {
          if ($header === '') {
            continue;
          }

          $rowData[$header] =
            $rawRow[$columnIndex] ?? null;
        }

        $rows->push(
          collect($rowData)
        );

        $employeeId =
          $this->normalizeEmployeeId(
            $rowData['employee_id'] ?? null
          );

        if ($employeeId !== null) {
          $employeeIds[] = $employeeId;
        }
      }

      /*
             * Mendeteksi Employee ID yang sebelumnya
             * sudah diproses pada chunk lain dalam batch sama.
             */
      $uniqueEmployeeIds = array_values(
        array_unique($employeeIds)
      );

      if ($uniqueEmployeeIds !== []) {
        $duplicateFromPreviousChunk =
          employee_details::query()
          ->whereIn(
            'employee_id',
            $uniqueEmployeeIds
          )
          ->where(
            'last_seen_import_batch',
            $batch->id
          )
          ->value('employee_id');

        if (
          $duplicateFromPreviousChunk !== null
        ) {
          throw new RuntimeException(
            "Employee ID {$duplicateFromPreviousChunk} muncul lebih dari satu kali dalam file Excel."
          );
        }
      }

      /*
             * Menggunakan kembali logika importer lama:
             * - mapping header;
             * - menjaga nilai lama ketika Excel kosong;
             * - menentukan insert/update;
             * - mengaktifkan employee;
             * - mengisi last_seen_import_batch.
             */
      $import = new EmployeeDetailsSheetImport(
        $batch->id
      );

      $import->collection($rows);

      return [
        /*
                 * processed adalah jumlah baris Excel
                 * yang sudah dilewati, termasuk baris kosong.
                 */
        'processed' =>
        $endRow - $startRow + 1,

        'inserted' =>
        $import->getInserted(),

        'updated' =>
        $import->getUpdated(),

        'skipped' =>
        $import->getSkipped(),

        'next_row' =>
        $endRow + 1,
      ];
    } finally {
      $spreadsheet->disconnectWorksheets();

      unset($spreadsheet);
    }
  }

  private function normalizeHeader(
    mixed $value
  ): string {
    $value = trim(
      (string) $value
    );

    if ($value === '') {
      return '';
    }

    /*
         * Menyamakan format header dengan
         * WithHeadingRow Laravel Excel.
         */
    return Str::slug(
      $value,
      '_'
    );
  }

  private function normalizeEmployeeId(
    mixed $value
  ): ?string {
    if ($value === null) {
      return null;
    }

    $normalized = trim(
      (string) $value
    );

    if (
      in_array(
        strtoupper($normalized),
        ['', '-', '--', 'N/A'],
        true
      )
    ) {
      return null;
    }

    if (
      is_int($value)
      || is_float($value)
    ) {
      return number_format(
        (float) $value,
        0,
        '',
        ''
      );
    }

    return $normalized;
  }
}

<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

final class DashboardExportWorkbook
{
  public const META_SHEET =
  '_KANMO_DASHBOARD_EXPORT';

  public const MARKER =
  'KANMO_DASHBOARD_EMPLOYEE_EXPORT_V1';

  /**
   * Tambahkan penanda ke file hasil export dashboard.
   */
  public static function mark(
    Spreadsheet $spreadsheet
  ): void {
    $activeSheetIndex =
      $spreadsheet->getActiveSheetIndex();

    /*
         * Hindari sheet marker ganda.
         */
    $existingSheet = $spreadsheet->getSheetByName(
      self::META_SHEET
    );

    if ($existingSheet !== null) {
      $spreadsheet->removeSheetByIndex(
        $spreadsheet->getIndex(
          $existingSheet
        )
      );
    }

    $metaSheet = $spreadsheet->createSheet();

    $metaSheet->setTitle(
      self::META_SHEET
    );

    $metaSheet->setCellValue(
      'A1',
      'marker'
    );

    $metaSheet->setCellValue(
      'B1',
      self::MARKER
    );

    $metaSheet->setCellValue(
      'A2',
      'purpose'
    );

    $metaSheet->setCellValue(
      'B2',
      'REPORT_ONLY'
    );

    $metaSheet->setCellValue(
      'A3',
      'importable'
    );

    $metaSheet->setCellValue(
      'B3',
      'NO'
    );

    $metaSheet->setCellValue(
      'A4',
      'generated_at'
    );

    $metaSheet->setCellValue(
      'B4',
      now()->toIso8601String()
    );

    /*
         * Tidak dapat di-unhide melalui UI Excel biasa.
         */
    $metaSheet->setSheetState(
      Worksheet::SHEETSTATE_VERYHIDDEN
    );

    /*
         * Tambahkan juga custom document property.
         */
    $properties =
      $spreadsheet->getProperties();

    $properties->setCustomProperty(
      'kanmo_file_type',
      self::MARKER
    );

    $properties->setCustomProperty(
      'kanmo_importable',
      'NO'
    );

    /*
         * Sheet utama tetap aktif ketika file dibuka.
         */
    $spreadsheet->setActiveSheetIndex(
      $activeSheetIndex
    );
  }

  /**
   * Periksa apakah file berasal dari dashboard export.
   */
  public static function isDashboardExport(
    UploadedFile $file
  ): bool {
    $path = $file->getRealPath();

    if (!is_string($path) || $path === '') {
      throw new RuntimeException(
        'Uploaded Excel file cannot be read.'
      );
    }

    $reader = IOFactory::createReaderForFile(
      $path
    );

    /*
         * Hanya baca daftar nama sheet terlebih dahulu,
         * sehingga pemeriksaan tetap ringan.
         */
    $sheetNames = $reader->listWorksheetNames(
      $path
    );

    if (
      !in_array(
        self::META_SHEET,
        $sheetNames,
        true
      )
    ) {
      return false;
    }

    /*
         * Hanya load metadata sheet.
         */
    $reader->setReadDataOnly(true);

    $reader->setLoadSheetsOnly(
      self::META_SHEET
    );

    $spreadsheet = $reader->load(
      $path
    );

    try {
      $metaSheet =
        $spreadsheet->getSheetByName(
          self::META_SHEET
        );

      if ($metaSheet === null) {
        return false;
      }

      $marker = trim(
        (string) $metaSheet
          ->getCell('B1')
          ->getValue()
      );

      $purpose = strtoupper(
        trim(
          (string) $metaSheet
            ->getCell('B2')
            ->getValue()
        )
      );

      return hash_equals(
        self::MARKER,
        $marker
      ) && $purpose === 'REPORT_ONLY';
    } finally {
      $spreadsheet->disconnectWorksheets();

      unset($spreadsheet);
    }
  }
}

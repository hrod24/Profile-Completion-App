<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeDetailsWorkbookImport implements WithMultipleSheets
{
    private EmployeeDetailsSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new EmployeeDetailsSheetImport(
            (string) Str::uuid()
        );
    }

    public function sheets(): array
    {
        return [
            'Employee Details' => $this->sheetImport,
        ];
    }

    /**
     * Harus dipanggil setelah seluruh baris Excel selesai diproses.
     */
    public function finalizeStatuses(): void
    {
        $this->sheetImport->finalizeStatuses();
    }

    public function getInserted(): int
    {
        return $this->sheetImport->getInserted();
    }

    public function getUpdated(): int
    {
        return $this->sheetImport->getUpdated();
    }

    public function getSkipped(): int
    {
        return $this->sheetImport->getSkipped();
    }

    public function getDeactivated(): int
    {
        return $this->sheetImport->getDeactivated();
    }

    public function getImportedEmployeeCount(): int
    {
        return $this->sheetImport->getImportedEmployeeCount();
    }

    public function summary(): array
    {
        return [
            'inserted' => $this->getInserted(),
            'updated' => $this->getUpdated(),
            'skipped' => $this->getSkipped(),
            'deactivated' => $this->getDeactivated(),
            'active_in_file' => $this->getImportedEmployeeCount(),
        ];
    }
}

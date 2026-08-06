<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Models\employee_details;
use App\Support\EmployeeDashboardFilters;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class EmployeeExportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $employeeQuery = employee_details::query()
            /* Only active employees are exportable. */
            ->where('active', 1);

        /* Apply search, company, BU, department, source, and PIC filters. */
        EmployeeDashboardFilters::apply($employeeQuery, $request);

        $filename = 'employee-details-'
            . now()->format('Ymd-His')
            . '.xlsx';

        return Excel::download(
            new EmployeesExport($employeeQuery),
            $filename,
            ExcelFormat::XLSX
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\SetPicExport;
use App\Imports\SetPicImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\employee_details;
use App\Models\Pic;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SetPicController extends Controller
{
  /**
   * Menampilkan employee yang belum memiliki PIC.
   * Method yang sama juga digunakan oleh live search AJAX.
   */
  public function show(Request $request)
  {
    $search = trim(
      (string) $request->query('search', '')
    );

    $selectedCompanies = collect(
      Arr::wrap(
        $request->query('companies', [])
      )
    )
      ->map(
        fn($company) => trim(
          (string) $company
        )
      )
      ->filter()
      ->unique()
      ->values()
      ->all();

    $selectedSources = collect(
      Arr::wrap(
        $request->query('sources', [])
      )
    )
      ->map(
        fn($source) => trim(
          (string) $source
        )
      )
      ->filter()
      ->unique()
      ->values()
      ->all();

    $employeeQuery =
      $this->filteredEmployeeQuery($request);

    $employees = $employeeQuery
      ->orderBy('display_name')
      ->orderBy('employee_id')
      ->paginate(1000)
      ->withQueryString();

    if ($request->ajax()) {
      return response()->json([
        'html' => view(
          'components.setPic.set-pic-table',
          compact('employees')
        )->render(),

        'total' => $employees->total(),
      ]);
    }

    /*
     * PIC list.
     */
    $pics = Pic::query()
      ->orderBy('name')
      ->orderBy('nip')
      ->get([
        'nip',
        'name',
      ]);

    /*
     * Company yang tersedia untuk employee
     * aktif yang belum mempunyai PIC.
     */
    $companies = employee_details::query()
      ->whereNull('pic_nip')
      ->where('active', 1)
      ->whereNotNull('company')
      ->where('company', '!=', '')
      ->distinct()
      ->orderBy('company')
      ->pluck('company');

    /*
     * Source yang tersedia untuk employee
     * aktif yang belum mempunyai PIC.
     */
    $sources = Source::query()
      ->whereNotNull('source')
      ->where('source', '!=', '')
      ->whereHas(
        'employees',
        function ($query) {
          $query
            ->whereNull('pic_nip')
            ->where('active', 1);
        }
      )
      ->distinct()
      ->orderBy('source')
      ->pluck('source');

    return view('pages.set-pic', [
      'title' => 'Set PIC',
      'employees' => $employees,
      'pics' => $pics,
      'companies' => $companies,
      'sources' => $sources,
      'search' => $search,
      'selectedCompanies' => $selectedCompanies,
      'selectedSources' => $selectedSources,
    ]);
  }

  /**
   * Menetapkan satu PIC ke banyak employee sekaligus.
   */
  public function assign(Request $request)
  {
    $validated = $request->validate(
      [
        'pic_nip' => [
          'required',
          'string',
          'exists:pics,nip',
        ],

        'employee_ids' => [
          'required',
          'array',
          'min:1',
          'max:1000',
        ],

        'employee_ids.*' => [
          'required',
          'integer',
          'distinct',
          'exists:employee_details,id',
        ],
      ],
      [
        'pic_nip.required' =>
        'Silakan pilih PIC terlebih dahulu.',

        'pic_nip.exists' =>
        'PIC yang dipilih tidak ditemukan.',

        'employee_ids.required' =>
        'Pilih minimal satu employee.',

        'employee_ids.min' =>
        'Pilih minimal satu employee.',
      ]
    );

    $pic = Pic::where(
      'nip',
      $validated['pic_nip']
    )->firstOrFail();

    $updatedEmployees = DB::transaction(
      function () use ($validated) {
        /*
                 * whereNull mencegah employee yang sudah memiliki PIC
                 * tertimpa jika terjadi perubahan data bersamaan.
                 */
        return employee_details::query()
          ->whereIn(
            'id',
            $validated['employee_ids']
          )
          ->whereNull('pic_nip')
          ->update([
            'pic_nip' => $validated['pic_nip'],
            'updated_at' => now(),
          ]);
      }
    );

    return redirect()
      ->route('set-pic.index')
      ->with(
        'success',
        "{$updatedEmployees} employee berhasil ditetapkan kepada {$pic->name}."
      );
  }

  public function download(Request $request)
  {
    /*
     * Employee mengikuti filter aktif:
     *
     * - search
     * - company
     * - source
     * - employee tanpa PIC
     */
    $query =
      $this->filteredEmployeeQuery(
        $request
      );

    $employees =
      $query
      ->orderBy('display_name')
      ->orderBy('employee_id')
      ->get();

    /*
     * ============================================================
     * PIC MAPPING
     * ============================================================
     *
     * NIP => Name
     *
     * Digunakan apabila suatu saat employee
     * yang sudah mempunyai PIC ikut di-export.
     */
    $picMap = Pic::query()
      ->pluck(
        'name',
        'nip'
      );

    /*
     * ============================================================
     * PIC DROPDOWN OPTIONS
     * ============================================================
     *
     * Hanya nama PIC.
     *
     * Contoh:
     *
     * Ahmad Fauzan
     * Hikam Maulana
     * Rina Amelia
     */
    $picOptions = Pic::query()
      ->whereNotNull('name')
      ->where('name', '!=', '')
      ->orderBy('name')
      ->pluck('name')
      ->map(
        fn($name) => trim(
          (string) $name
        )
      )
      ->filter()
      ->unique()
      ->values();

    /*
     * ============================================================
     * EMPLOYEE ROWS
     * ============================================================
     */

    $rows = $employees->map(
      function ($employee) use (
        $picMap
      ) {
        $picName = '';

        /*
             * Pada Set PIC biasanya kosong karena
             * query hanya mengambil employee tanpa PIC.
             */
        if (!empty($employee->pic_nip)) {
          $picName =
            $picMap->get(
              $employee->pic_nip,
              ''
            );
        }

        return [
          'employee_Id' =>
          (string)
          $employee->employee_id,

          'display_name' =>
          $employee->display_name
            ?? '',

          'pic' =>
          $picName,

          'company' =>
          $employee->company
            ?? '',

          'source' =>
          $employee
            ->sourceData
            ?->source
            ?? '',
        ];
      }
    );

    /*
     * ============================================================
     * FILE NAME
     * ============================================================
     */

    $filename =
      'set-pic-employee-' .
      now()->format(
        'Y-m-d_H-i-s'
      ) .
      '.xlsx';

    /*
     * rows       = employee
     * picOptions = nama PIC untuk dropdown
     */
    return Excel::download(
      new SetPicExport(
        $rows,
        $picOptions
      ),
      $filename
    );
  }

  public function upload(Request $request)
  {
    $request->validate([
      'file' => [
        'required',
        'file',
        'mimes:xlsx,xls',
        'max:10240',
      ],
    ]);

    $import = new SetPicImport();

    Excel::import(
      $import,
      $request->file('file')
    );

    $rows = $import->rows ?? collect();

    if ($rows->isEmpty()) {
      return back()->withErrors([
        'file' => 'File Excel tidak memiliki data.',
      ]);
    }

    /*
     * Ambil semua PIC.
     *
     * Kemudian kelompokkan berdasarkan nama dalam
     * lowercase agar pencarian tidak case-sensitive.
     */
    $pics = Pic::query()
      ->get([
        'nip',
        'name',
      ])
      ->groupBy(
        fn($pic) => mb_strtolower(
          trim($pic->name)
        )
      );

    /*
     * Ambil employee ID yang terdapat pada Excel.
     */
    $employeeIds = $rows
      ->map(
        fn($row) => trim(
          (string) ($row['employee_id'] ?? '')
        )
      )
      ->filter()
      ->unique()
      ->values();

    /*
     * Load employee sekali saja.
     *
     * Lebih efisien daripada query database pada
     * setiap baris Excel.
     */
    $employees = employee_details::query()
      ->whereIn(
        'employee_id',
        $employeeIds
      )
      ->get()
      ->keyBy(
        fn($employee) =>
        (string) $employee->employee_id
      );

    $updates = [];

    $errors = [];

    foreach ($rows as $index => $row) {

      /*
         * +2 karena:
         *
         * index collection dimulai 0
         * row 1 Excel adalah heading.
         */
      $excelRow = $index + 2;

      $employeeId = trim(
        (string) ($row['employee_id'] ?? '')
      );

      $picName = trim(
        (string) ($row['pic'] ?? '')
      );

      /*
         * Ignore completely empty row.
         */
      if (
        $employeeId === '' &&
        $picName === ''
      ) {
        continue;
      }

      if ($employeeId === '') {
        $errors[] =
          "Baris {$excelRow}: Employee ID kosong.";

        continue;
      }

      /*
         * PIC belum diisi.
         *
         * Jangan update employee tersebut.
         */
      if ($picName === '') {
        continue;
      }

      /*
         * Employee harus ada.
         */
      $employee = $employees->get(
        $employeeId
      );

      if (!$employee) {
        $errors[] =
          "Baris {$excelRow}: Employee ID {$employeeId} tidak ditemukan.";

        continue;
      }

      /*
         * Jangan menimpa PIC yang sudah ada.
         */
      if (!empty($employee->pic_nip)) {
        $errors[] =
          "Baris {$excelRow}: Employee {$employeeId} sudah mempunyai PIC.";

        continue;
      }

      /*
         * Normalize PIC name.
         */
      $normalizedPicName =
        mb_strtolower($picName);

      $matchedPics = $pics->get(
        $normalizedPicName,
        collect()
      );

      /*
         * PIC tidak ditemukan.
         */
      if ($matchedPics->isEmpty()) {
        $errors[] =
          "Baris {$excelRow}: PIC \"{$picName}\" tidak ditemukan.";

        continue;
      }

      /*
         * Sangat penting.
         *
         * Kalau ada dua PIC dengan nama sama,
         * jangan memilih NIP secara sembarangan.
         */
      if ($matchedPics->count() > 1) {
        $errors[] =
          "Baris {$excelRow}: Nama PIC \"{$picName}\" digunakan oleh lebih dari satu PIC.";

        continue;
      }

      $pic = $matchedPics->first();

      $updates[] = [
        'employee_id' => $employeeId,
        'pic_nip' => $pic->nip,
      ];
    }

    /*
     * Kalau ada kesalahan, jangan update database.
     *
     * Dengan demikian upload bersifat all-or-nothing.
     */
    if (!empty($errors)) {
      return back()->withErrors([
        'file' => implode(
          "\n",
          $errors
        ),
      ]);
    }

    if (empty($updates)) {
      return back()->withErrors([
        'file' =>
        'Tidak ada employee yang dapat di-update. Pastikan kolom PIC sudah diisi.',
      ]);
    }

    DB::transaction(
      function () use ($updates) {

        foreach ($updates as $update) {

          employee_details::query()
            ->where(
              'employee_id',
              $update['employee_id']
            )
            ->whereNull('pic_nip')
            ->update([
              'pic_nip' =>
              $update['pic_nip'],
            ]);
        }
      }
    );

    return redirect()
      ->route('set-pic.index')
      ->with(
        'success',
        count($updates) .
          ' employee berhasil diberikan PIC melalui Excel.'
      );
  }

  private function filteredEmployeeQuery(
    Request $request
  ) {
    $search = trim(
      (string) $request->query('search', '')
    );

    $selectedCompanies = collect(
      Arr::wrap(
        $request->query('companies', [])
      )
    )
      ->map(
        fn($company) => trim(
          (string) $company
        )
      )
      ->filter()
      ->unique()
      ->values()
      ->all();

    $selectedSources = collect(
      Arr::wrap(
        $request->query('sources', [])
      )
    )
      ->map(
        fn($source) => trim(
          (string) $source
        )
      )
      ->filter()
      ->unique()
      ->values()
      ->all();

    $employeeQuery = employee_details::query()
      ->select([
        'id',
        'employee_id',
        'employee_level_code',
        'display_name',
        'company',
        'pic_nip',
      ])

      /*
         * Dibutuhkan saat export agar kita bisa
         * mengambil nama source.
         */
      ->with('sourceData')

      ->whereNull('pic_nip')
      ->where('active', 1);

    /*
     * Search Employee ID / Name
     */
    if ($search !== '') {
      $employeeQuery->where(
        function ($query) use ($search) {
          $query
            ->where(
              'employee_id',
              'like',
              "%{$search}%"
            )
            ->orWhere(
              'display_name',
              'like',
              "%{$search}%"
            );
        }
      );
    }

    /*
     * Company filter
     */
    if (!empty($selectedCompanies)) {
      $employeeQuery->whereIn(
        'company',
        $selectedCompanies
      );
    }

    /*
     * Source filter
     */
    if (!empty($selectedSources)) {
      $employeeQuery->whereHas(
        'sourceData',
        function ($query) use (
          $selectedSources
        ) {
          $query->whereIn(
            'source',
            $selectedSources
          );
        }
      );
    }

    return $employeeQuery;
  }
}

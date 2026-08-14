<?php

namespace App\Http\Controllers;

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

        $employeeQuery = employee_details::query()
            ->select([
                'id',
                'employee_id',
                'employee_level_code',
                'display_name',
                'company',
                'pic_nip',
            ])
            ->whereNull('pic_nip')
            ->where('active', 1);

        /*
     * Live search berdasarkan NIP atau nama.
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
     * Filter company.
     */
        if (!empty($selectedCompanies)) {
            $employeeQuery->whereIn(
                'company',
                $selectedCompanies
            );
        }

        /*
     * Filter source.
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
}

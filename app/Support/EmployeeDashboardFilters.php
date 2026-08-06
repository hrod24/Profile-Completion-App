<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class EmployeeDashboardFilters
{
    /**
     * Apply the same dashboard filters to any employee_details query.
     *
     * Supported query parameter aliases:
     * - search
     * - company / companies
     * - business_unit / business_units / division / divisions
     * - department / departments
     * - source / sources
     * - pic / pics / pic_nip
     */
    public static function apply(Builder $query, Request $request): Builder
    {
        self::applySearch($query, $request);

        self::applyWhereIn(
            $query,
            'company',
            self::values($request, ['companies', 'company'])
        );

        self::applyWhereIn(
            $query,
            'business_unit_org_element_1',
            self::values(
                $request,
                ['business_units', 'business_unit', 'divisions', 'division']
            )
        );

        self::applyWhereIn(
            $query,
            'department_org_element_2',
            self::values($request, ['departments', 'department'])
        );

        self::applySourceFilter(
            $query,
            self::values($request, ['sources', 'source'])
        );

        self::applyWhereIn(
            $query,
            'pic_nip',
            self::values($request, ['pics', 'pic', 'pic_nip'])
        );

        return $query;
    }

    private static function applySearch(
        Builder $query,
        Request $request
    ): void {
        $search = trim((string) $request->query('search', ''));

        if ($search === '') {
            return;
        }

        $escapedSearch = addcslashes($search, '\\%_');
        $keyword = "%{$escapedSearch}%";

        $query->where(function (Builder $searchQuery) use ($keyword): void {
            $searchQuery
                ->where('employee_id', 'like', $keyword)
                ->orWhere('display_name', 'like', $keyword);
        });
    }

    private static function applyWhereIn(
        Builder $query,
        string $column,
        array $values
    ): void {
        if ($values === []) {
            return;
        }

        $query->whereIn($column, $values);
    }

    private static function applySourceFilter(
        Builder $query,
        array $sources
    ): void {
        if ($sources === []) {
            return;
        }

        /*
         * The filter may store either employee_level_code or the source label.
         * Supporting both forms keeps the export compatible with either UI.
         */
        $query->where(function (Builder $sourceQuery) use ($sources): void {
            $sourceQuery
                ->whereIn('employee_level_code', $sources)
                ->orWhereHas(
                    'sourceData',
                    fn(Builder $relationQuery) => $relationQuery
                        ->whereIn('source', $sources)
                );
        });
    }

    private static function values(
        Request $request,
        array $keys
    ): array {
        foreach ($keys as $key) {
            if (!$request->query->has($key)) {
                continue;
            }

            $value = $request->query($key);
            $values = is_array($value)
                ? $value
                : preg_split('/\s*,\s*/', (string) $value);

            return collect($values)
                ->flatten()
                ->map(fn($item) => trim((string) $item))
                ->filter(fn(string $item) => $item !== '')
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }
}

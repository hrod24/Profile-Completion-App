<x-layout :title="$title">
    <x-app-shell
        title="Employee Completion Dashboard"
        subtitle="Monitor employee profile completion, OD data readiness, and completion trends from one workspace."
    >

        <section class="minimal-card mb-5 overflow-visible">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-900">Employee Filters</h2>
                <p class="mt-1 text-xs text-slate-500">Filter dashboard by company, division, and department.</p>
            </div>

            <x-dashboard.company-filter
                :companies="$companies"
                :business-units="$businessUnits"
                :departments="$departments"
                :pics="$pics"
                :sources="$sources"

                :selected-companies="$selectedCompanies"
                :selected-business-units="$selectedBusinessUnits"
                :selected-departments="$selectedDepartments"
                :selected-pics="$selectedPics"
                :selected-sources="$selectedSources"
            />
        </section>

        <div data-dashboard-statistics>
            @include('components.dashboard.statistics')
        </div>

        <x-dashboard.table :employees="$employees" />
    </x-app-shell>
</x-layout>

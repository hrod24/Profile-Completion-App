<section class="minimal-card mt-5 overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Employee Profile Completion</h2>
            <p class="mt-1 text-xs text-slate-500">Search and review employee, PIC, OD, and overall completion.</p>
        </div>

        <form
            action="{{ route('dashboard') }}"
            method="GET"
            class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-xl"
            data-employee-search-form
        >
            <label for="employee-search" class="sr-only">Search employee</label>

            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3a6 6 0 104.472 10.003l3.262 3.263a1 1 0 001.414-1.414l-3.263-3.262A6 6 0 009 3zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                    </svg>
                </div>

                <input
                    type="search"
                    id="employee-search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search NIP or employee name"
                    autocomplete="off"
                    class="kanmo-input py-2.5 pl-10"
                    data-employee-search-input
                >

                <div class="pointer-events-none absolute inset-y-0 right-0 items-center pr-3.5" data-employee-search-loading hidden>
                    <svg class="h-4.5 w-4.5 animate-spin text-kanmo-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <button type="submit" class="kanmo-btn-primary">Search</button>

            <a
                href="{{ route('dashboard') }}"
                class="kanmo-btn-secondary {{ request()->filled('search') ? '' : 'hidden' }}"
                data-employee-search-reset
            >
                Reset
            </a>
        </form>
    </div>

    <p class="sr-only" aria-live="polite" aria-atomic="true" data-employee-search-status></p>

    <div data-employee-search-results aria-busy="false">
        @include('components.dashboard.table-results', [
            'employees' => $employees,
        ])
    </div>
</section>

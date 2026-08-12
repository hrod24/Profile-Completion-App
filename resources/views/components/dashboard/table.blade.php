<section class="minimal-card mt-5 overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Employee Profile Completion</h2>
            <p class="mt-1 text-xs text-slate-500">Search and review employee, PIC, OD, and overall completion.</p>
        </div>

        <form action="{{ route('dashboard') }}" method="GET" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-xl"
            data-employee-search-form>
            <label for="employee-search" class="sr-only">Search employee</label>

            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M9 3a6 6 0 104.472 10.003l3.262 3.263a1 1 0 001.414-1.414l-3.263-3.262A6 6 0 009 3zM5 9a4 4 0 118 0 4 4 0 01-8 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <input type="search" id="employee-search" name="search" value="{{ request('search') }}"
                    placeholder="Search NIP or employee name" autocomplete="off" class="kanmo-input py-2.5 pl-10"
                    data-employee-search-input>

                <div class=" pointer-events-none absolute inset-y-0 right-0 items-center pr-3.5"
                    data-employee-search-loading hidden>
                    <svg class="h-4.5 w-4.5 animate-spin text-kanmo-500" viewBox="0 0 24 24" fill="none"
                        aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>

            <button type="submit" class="kanmo-btn-primary">Search</button>

            <a href="{{ route('dashboard.employee-export') }}"
                class="kanmo-btn-secondary rounded-lg"
                data-employee-export-link
                data-employee-export-url="{{ route('dashboard.employee-export') }}"
                title="Download filtered employee data"
                aria-label="Download filtered employee data as Excel">
                <svg xmlns="http://www.w3.org/2000/svg" width="23px" viewBox="0 0 24 24"
                    fill="none">
                    <path
                        d="M8 22.0002H16C18.8284 22.0002 20.2426 22.0002 21.1213 21.1215C22 20.2429 22 18.8286 22 16.0002V15.0002C22 12.1718 22 10.7576 21.1213 9.8789C20.3529 9.11051 19.175 9.01406 17 9.00195M7 9.00195C4.82497 9.01406 3.64706 9.11051 2.87868 9.87889C2 10.7576 2 12.1718 2 15.0002L2 16.0002C2 18.8286 2 20.2429 2.87868 21.1215C3.17848 21.4213 3.54062 21.6188 4 21.749"
                        stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M12 2L12 15M12 15L9 11.5M12 15L15 11.5" stroke="#1C274C" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </form>
    </div>

    <p class="sr-only" aria-live="polite" aria-atomic="true" data-employee-search-status></p>

    <div data-employee-search-results aria-busy="false">
        @include('components.dashboard.table-results', [
            'employees' => $employees,
        ])
    </div>

    {{-- Replace the existing employee details modal with this markup. --}}
    <div class="fixed inset-0 z-[100] hidden items-center
           justify-center p-3 sm:p-5"
        data-employee-details-modal role="dialog" aria-modal="true" aria-labelledby="employee-details-title">
        <button type="button" class="absolute inset-0 cursor-default
               bg-slate-950/55 backdrop-blur-sm"
            data-employee-details-close aria-label="Close employee details"></button>

        <div
            class="relative flex max-h-[94vh] w-full
               max-w-4xl flex-col overflow-hidden
               rounded-2xl border border-slate-200
               bg-white shadow-2xl">
            <header
                class="flex shrink-0 items-center justify-between
                   gap-4 border-b border-slate-200
                   bg-white px-5 py-4">
                <div>
                    <p
                        class="text-[10px] font-bold uppercase
                           tracking-[0.16em] text-orange-600">
                        Employee Profile
                    </p>

                    <h2 id="employee-details-title" class="mt-1 text-lg font-bold text-slate-900">
                        Employee Details
                    </h2>
                </div>

                <button type="button"
                    class="inline-flex h-10 w-10 items-center
                       justify-center rounded-xl text-slate-400
                       transition hover:bg-slate-100
                       hover:text-slate-700"
                    data-employee-details-close aria-label="Close">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <div class="overflow-y-auto bg-slate-50/60
                   p-4 sm:p-5" data-employee-details-content>
            </div>
        </div>
    </div>

</section>

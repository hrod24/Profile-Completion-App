<x-layout :title="$title">
    <div class="kanmo-page">
        <header class="border-b border-stone-200/80 bg-white/90 backdrop-blur">
            <div class="mx-auto max-w-[1500px] px-4 sm:px-6 lg:px-8">
                <div class="flex min-h-20 flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <img width="40" src="{{ asset('img/kanmo-logo.jpeg') }}" alt="">
                        <div>
                            <p class="text-base font-extrabold tracking-[0.12em] text-kanmo-600">KANMO <span
                                    class="font-medium text-slate-500">GROUP</span></p>
                            <p class="text-xs font-medium text-slate-500">People Profile Portal</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('employee.form') }}" class="kanmo-btn-primary">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536M16.732 3.732a2.5 2.5 0 113.536 3.536L7.5 20.036 3 21l.964-4.5L16.732 3.732z" />
                            </svg>
                            Buka Employee Form
                        </a>
                        <a href="{{ route('employee.import.create') }}"
                            class="kanmo-btn-secondary text-white bg-[#21a366]">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4" />
                            </svg>
                            Upload Excel
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <section class="border-b border-kanmo-100 bg-white">
            <div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-kanmo-600 via-kanmo-500 to-orange-400 px-6 py-8 text-white shadow-[0_24px_60px_rgba(241,90,36,0.20)] sm:px-8 lg:px-10">
                    <div aria-hidden="true"
                        class="absolute -right-16 -top-24 h-64 w-64 rounded-full border-[40px] border-white/10"></div>
                    <div aria-hidden="true"
                        class="absolute -bottom-24 right-24 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="relative max-w-3xl">
                        <span
                            class="inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-white/90 backdrop-blur">EMPLOYEE
                            DATA COMPLETION</span>
                        <h1 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">Employee
                            Completion Dashboard</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/85 sm:text-base">Pantau kelengkapan profil
                            secara cepat, bantu employee menyelesaikan data dengan nyaman, dan jaga kualitas data HR
                            dalam satu tampilan.</p>
                    </div>
                </div>
            </div>
        </section>


        <main class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
            <x-dashboard.company-filter :companies="$companies" :business-units="$businessUnits" :departments="$departments" :selected-companies="$selectedCompanies"
                :selected-business-units="$selectedBusinessUnits" :selected-departments="$selectedDepartments" />
            <div data-dashboard-statistics>
                @include('components.dashboard.statistics')
            </div>
            <x-dashboard.table :employees="$employees" />
        </main>

        <footer class="border-t border-stone-200 bg-white">
            <div
                class="mx-auto flex max-w-[1500px] flex-col gap-1 px-4 py-5 text-xs text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <span>Kanmo Group · People Profile Portal</span>
                <span>Inspire and enrich life’s journeys.</span>
            </div>
        </footer>
    </div>

</x-layout>

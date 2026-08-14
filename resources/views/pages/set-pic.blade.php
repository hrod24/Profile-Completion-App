<x-layout :title="$title">
    <div id="set-pic-page" data-endpoint="{{ route('set-pic.index') }}">
        <x-app-shell title="Set PIC Employee" subtitle="Assign PIC to one or more employees in batches.">
            {{-- Success alert --}}
            @if (session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200
                           bg-emerald-50 px-5 py-4 text-sm
                           font-medium text-emerald-700"
                    role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200
                           bg-rose-50 px-5 py-4 text-sm text-rose-700"
                    role="alert">
                    <p class="font-bold">
                        PIC belum berhasil ditetapkan.
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Hidden batch form --}}
            <form id="assign-pic-form" action="{{ route('set-pic.assign') }}" method="POST">
                @csrf

                <div id="selected-employee-inputs"></div>
            </form>

            {{-- Filters --}}
            <section class="minimal-card mb-5 overflow-visible">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">
                        Employee Filters
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Search employee base on ID or name,
                        and then u can filter it by company.
                    </p>
                </div>

                <div class="p-5">
                    {{-- Search --}}
                    <div class="max-w-xl">
                        <label for="pic-employee-search" class="mb-2 block text-xs font-bold text-slate-600">
                            Search Employee
                        </label>

                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0
                                       left-0 flex items-center pl-3.5">
                                <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3a6 6 0 104.472 10.003l3.262
                                           3.263a1 1 0 001.414-1.414l-3.263-3.262A6
                                           6 0 009 3zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <input type="search" id="pic-employee-search" value="{{ $search }}"
                                placeholder="Search NIP or employee name" autocomplete="off"
                                class="kanmo-input py-2.5 pl-10 pr-10">

                            <div id="employee-table-loading"
                                class="pointer-events-none absolute inset-y-0
                                       right-0 hidden items-center pr-3.5">
                                <svg class="h-4.5 w-4.5 animate-spin text-kanmo-500" viewBox="0 0 24 24" fill="none"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>

                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0
                                           0 5.373 0 12h4zm2 5.291A7.962
                                           7.962 0 014 12H0c0 3.042 1.135
                                           5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Company filters --}}
                    <div class="mt-5 border-t border-slate-200 pt-5">
                        <div
                            class="mb-3 flex flex-col gap-2
                                   sm:flex-row sm:items-center
                                   sm:justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-600">
                                    Company
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Select one or more company.
                                </p>
                            </div>

                            <button type="button" id="clear-company-filters"
                                class="text-left text-xs font-bold
                                       text-kanmo-600 hover:text-kanmo-700 cursor-pointer border-[1px] border-gray-200 rounded p-1">
                                Clear company filters
                            </button>
                        </div>

                        <div id="company-filter-list"
                            class="grid grid-cols-1 gap-2
                                   sm:grid-cols-2 md:grid-cols-3
                                   xl:grid-cols-4">
                            @forelse ($companies as $company)
                                <label
                                    class="group flex cursor-pointer
                                           items-center gap-3 rounded-xl
                                           border border-stone-200
                                           bg-white px-3 py-2.5
                                           transition-colors
                                           hover:border-kanmo-200
                                           hover:bg-kanmo-50/50">
                                    <input type="checkbox" name="companies[]" value="{{ $company }}"
                                        class="company-filter-checkbox
           h-4 w-4 rounded
           border-stone-300
           text-kanmo-600
           focus:ring-kanmo-500"
                                        @checked(in_array($company, $selectedCompanies, true))>

                                    <span
                                        class="min-w-0 truncate text-sm
                                               font-semibold text-slate-600
                                               group-hover:text-kanmo-700"
                                        title="{{ $company }}">
                                        {{ $company }}
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">
                                    There is no company available
                                </p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Source filters --}}
                    <div class="mt-5 border-t border-slate-200 pt-5">
                        <div
                            class="mb-3 flex flex-col gap-2
               sm:flex-row sm:items-center
               sm:justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-600">
                                    Source
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Select one or more sources.
                                </p>
                            </div>

                            <button type="button" id="clear-source-filters"
                                class="cursor-pointer rounded border
                   border-gray-200 p-1 text-left
                   text-xs font-bold text-kanmo-600
                   hover:text-kanmo-700">
                                Clear source filters
                            </button>
                        </div>

                        <div id="source-filter-list"
                            class="grid grid-cols-1 gap-2
               sm:grid-cols-2 md:grid-cols-3
               xl:grid-cols-4">
                            @forelse ($sources as $source)
                                <label
                                    class="group flex cursor-pointer
                       items-center gap-3 rounded-xl
                       border border-stone-200
                       bg-white px-3 py-2.5
                       transition-colors
                       hover:border-kanmo-200
                       hover:bg-kanmo-50/50">
                                    <input type="checkbox" name="sources[]" value="{{ $source }}"
                                        class="source-filter-checkbox
           h-4 w-4 rounded
           border-stone-300
           text-kanmo-600
           focus:ring-kanmo-500"
                                        @checked(in_array($source, $selectedSources, true))>

                                    <span
                                        class="min-w-0 truncate
                           text-sm font-semibold
                           text-slate-600
                           group-hover:text-kanmo-700"
                                        title="{{ $source }}">
                                        {{ $source }}
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">
                                    There is no source available
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            {{-- Employee table --}}
            <section class="minimal-card overflow-hidden">
                <div
                    class="flex flex-col gap-4 border-b border-slate-200
                           px-5 py-4 lg:flex-row lg:items-center
                           lg:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Employee Without PIC
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            <span id="employee-result-count">
                                {{ number_format($employees->total(), 0, ',', '.') }}
                            </span>
                            employee does not have a PIC yet
                        </p>
                    </div>

                    {{-- Batch assignment controls --}}
                    <div class="flex w-full flex-col gap-2
                               sm:flex-row lg:max-w-2xl">
                        <label for="pic-nip" class="sr-only">
                            Select PIC
                        </label>

                        <select id="pic-nip" name="pic_nip" form="assign-pic-form" required
                            class="kanmo-input py-2.5 sm:min-w-[250px]">
                            <option value="">
                                Select PIC
                            </option>

                            @foreach ($pics as $pic)
                                <option value="{{ $pic->nip }}" @selected(old('pic_nip') === $pic->nip)>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" id="assign-pic-button" form="assign-pic-form" disabled
                            class="kanmo-btn-primary whitespace-nowrap
                                   disabled:cursor-not-allowed
                                   disabled:bg-slate-300">
                            Set PIC

                            <span id="selected-employee-count"
                                class="ml-1.5 inline-flex min-w-5
                                       items-center justify-center
                                       rounded-full bg-white/20
                                       px-1.5 py-0.5 text-xs">
                                0
                            </span>
                        </button>

                        <button type="button" id="clear-employee-selection"
                            class="kanmo-btn-secondary whitespace-nowrap">
                            Clear Selection
                        </button>
                    </div>
                </div>

                <p class="sr-only" aria-live="polite" aria-atomic="true" id="set-pic-search-status"></p>

                <div id="set-pic-table-container" aria-busy="false">
                    @include('components.setPic.set-pic-table', [
                        'employees' => $employees,
                    ])
                </div>
            </section>
        </x-app-shell>
    </div>
</x-layout>

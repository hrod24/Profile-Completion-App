@props([
    'companies' => collect(),
    'businessUnits' => collect(),
    'departments' => collect(),
    'pics' => collect(),
    'sources' => collect(),

    'selectedCompanies' => [],
    'selectedBusinessUnits' => [],
    'selectedDepartments' => [],
    'selectedPics' => [],
    'selectedSources' => [],
])

<div class="flex flex-wrap items-center ml-7 gap-4 p-4" data-dashboard-filters>
    {{-- SOURCE --}}
    <div>
        <button id="source-filter-button" data-dropdown-toggle="source-filter-dropdown" class="kanmo-btn-primary"
            type="button">
            <span data-source-filter-label>
                @if (count($selectedSources) > 0)
                    Source ({{ count($selectedSources) }})
                @else
                    Filter Source
                @endif
            </span>
    
            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    
        <div id="source-filter-dropdown"
            class="z-20 hidden min-w-72 rounded-xl
               border border-kanmo-200 bg-white
               p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-bold text-gray-900">
                        Source
                    </h6>
    
                    <p class="mt-0.5 text-xs text-slate-500">
                        Filter berdasarkan employee band.
                    </p>
                </div>
    
                <button type="button" data-filter-clear="source"
                    class="cursor-pointer text-xs font-semibold
                       text-kanmo-600 hover:text-kanmo-700">
                    Reset
                </button>
            </div>
    
            <ul class="max-h-72 space-y-3 overflow-y-auto">
                @forelse ($sources as $index => $source)
                    <li class="flex items-center">
                        <input id="source-filter-{{ $index }}" type="checkbox" value="{{ $source }}"
                            data-source-filter-checkbox @checked(in_array($source, $selectedSources, true))
                            class="h-4 w-4 cursor-pointer rounded
                               border-gray-300 text-kanmo-600
                               focus:ring-kanmo-300">
    
                        <label for="source-filter-{{ $index }}" class="ml-2 cursor-pointer text-sm">
                            {{ $source }}
                        </label>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">
                        Data Source belum tersedia.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
    {{-- COMPANY --}}
    <div>
        <button id="company-filter-button" data-dropdown-toggle="company-filter-dropdown" class="kanmo-btn-primary"
            type="button">
            <span data-company-filter-label>
                Filter Company
            </span>

            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="company-filter-dropdown"
            class="z-20 hidden min-w-72 rounded-xl border
                   border-kanmo-200 bg-white p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <h6 class="text-sm font-bold text-gray-900">
                    Company
                </h6>

                <button type="button" data-filter-clear="company"
                    class="cursor-pointer text-xs font-semibold text-kanmo-600">
                    Reset
                </button>
            </div>

            <ul class="max-h-72 space-y-3 overflow-y-auto">
                @foreach ($companies as $index => $company)
                    <li class="flex items-center">
                        <input id="company-filter-{{ $index }}" type="checkbox" value="{{ $company['value'] }}"
                            data-company-filter-checkbox @checked(in_array($company['value'], $selectedCompanies, true))
                            class="h-4 w-4 cursor-pointer rounded">

                        <label for="company-filter-{{ $index }}" class="ml-2 cursor-pointer text-sm">
                            {{ $company['label'] }}
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- BUSINESS UNIT / DIVISION --}}
    <div>
        <button id="business-unit-filter-button" data-dropdown-toggle="business-unit-filter-dropdown"
            class="kanmo-btn-primary" type="button">
            <span data-business-unit-filter-label>
                Filter Division
            </span>

            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="business-unit-filter-dropdown"
            class="z-20 hidden min-w-80 rounded-xl border
                   border-kanmo-200 bg-white p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <h6 class="text-sm font-bold text-gray-900">
                    Division / Business Unit
                </h6>

                <button type="button" data-filter-clear="business-unit"
                    class="cursor-pointer text-xs font-semibold text-kanmo-600">
                    Reset
                </button>
            </div>

            <ul class="max-h-72 space-y-3 overflow-y-auto">
                @foreach ($businessUnits as $index => $businessUnit)
                    <li class="flex items-start gap-2">
                        <input id="business-unit-filter-{{ $index }}" type="checkbox"
                            value="{{ $businessUnit['value'] }}" data-business-unit-filter-checkbox
                            @checked(in_array($businessUnit['value'], $selectedBusinessUnits, true)) class="mt-0.5 h-4 w-4 cursor-pointer rounded">

                        <label for="business-unit-filter-{{ $index }}" class="cursor-pointer text-sm">
                            <span class="block font-medium">
                                {{ $businessUnit['label'] }}
                            </span>

                            <span class="block text-xs text-slate-400">
                                {{ $businessUnit['code'] }}
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- DEPARTMENT --}}
    <div>
        <button id="department-filter-button" data-dropdown-toggle="department-filter-dropdown"
            class="kanmo-btn-primary" type="button">
            <span data-department-filter-label>
                Filter Department
            </span>

            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="department-filter-dropdown"
            class="z-20 hidden min-w-80 rounded-xl border
                   border-kanmo-200 bg-white p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h6 class="text-sm font-bold text-gray-900">
                        Department
                    </h6>

                    <p class="mt-0.5 text-xs text-slate-500">
                        Menyesuaikan division yang dipilih
                    </p>
                </div>

                <button type="button" data-filter-clear="department"
                    class="cursor-pointer text-xs font-semibold text-kanmo-600">
                    Reset
                </button>
            </div>

            <ul class="max-h-72 space-y-3 overflow-y-auto" data-department-filter-options>
                @include('components.dashboard.department-filter-options', [
                    'departments' => $departments,
                    'selectedDepartments' => $selectedDepartments,
                ])
            </ul>
        </div>
    </div>

    <div>
        <button id="pic-filter-button" data-dropdown-toggle="pic-filter-dropdown" class="kanmo-btn-primary"
            type="button">
            <span data-pic-filter-label>
                Filter PIC
            </span>

            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div id="pic-filter-dropdown"
            class="z-20 hidden min-w-72 rounded-xl border
                   border-kanmo-200 bg-white p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
                <h6 class="text-sm font-bold text-gray-900">
                    PIC
                </h6>

                <button type="button" data-filter-clear="pic"
                    class="cursor-pointer text-xs font-semibold text-kanmo-600">
                    Reset
                </button>
            </div>

            <ul class="max-h-72 space-y-3 overflow-y-auto">
                @foreach ($pics as $index => $pic)
                    <li class="flex items-start gap-2">
                        <input id="pic-filter-{{ $index }}" type="checkbox" value="{{ $pic->nip }}"
                            data-pic-filter-checkbox @checked(in_array($pic->nip, $selectedPics, true))
                            class="mt-0.5 h-4 w-4 cursor-pointer rounded
                       border-gray-300 text-kanmo-600
                       focus:ring-kanmo-300">

                        <label for="pic-filter-{{ $index }}" class="cursor-pointer text-sm">
                            <span class="block font-medium">
                                {{ $pic->name }}
                            </span>

                            <span class="block text-xs text-slate-400">
                                {{ $pic->nip }}
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

</div>

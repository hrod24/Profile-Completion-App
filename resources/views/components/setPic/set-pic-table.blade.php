@if (request()->filled('search') || request()->filled('companies'))
    <div
        class="flex flex-col gap-1 border-b border-kanmo-100
               bg-kanmo-50/70 px-5 py-3 text-sm
               sm:flex-row sm:items-center sm:justify-between lg:px-6">
        <div class="text-kanmo-800">
            @if (request()->filled('search'))
                Search results for

                <span class="font-bold">
                    “{{ request('search') }}”
                </span>
            @else
                Company filter is active
            @endif
        </div>

        <p class="text-xs font-semibold text-kanmo-600">
            {{ number_format($employees->total(), 0, ',', '.') }}
            results found
        </p>
    </div>
@endif

<div class="overflow-x-auto">
    <table class="w-full min-w-[850px] table-auto text-left">
        <thead class="border-b text-center border-stone-200 bg-stone-50/90">
            <tr>
                <th class="w-16 px-4 py-2.5 text-center
                           text-xs font-bold text-slate-500">
                    No.
                </th>

                <th class="w-14 px-4 py-2.5 text-center
                           text-xs font-bold text-slate-500">
                    <input type="checkbox" id="select-all-employees"
                        class="h-5 w-5 borer-2 cursor-pointer rounded border-stone-300
                               text-kanmo-600 focus:ring-kanmo-500"
                        aria-label="Select all employee on this page">
                </th>

                <th class="min-w-[300px] px-4 py-2.5
                           text-xs font-bold text-slate-500">
                    Employee
                </th>
                <th class="min-w-[220px] px-4 py-2.5
                           text-xs font-bold text-slate-500">
                    Company
                </th>

                <th class="min-w-[220px] px-4 py-2.5
                           text-xs font-bold text-slate-500">
                    Source
                </th>


            </tr>
        </thead>

        <tbody class="divide-y divide-stone-100 bg-white">
            @forelse ($employees as $employee)
                @php
                    $employeeName = $employee->display_name ?: 'Tanpa Nama';

                    $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1));
                @endphp

                <tr class="group transition-colors
                           hover:bg-kanmo-50/35" data-employee-row>
                    <td class="px-4 py-2 text-center">
                        <span
                            class="inline-flex h-8 min-w-8
                                   items-center justify-center
                                   rounded-lg bg-stone-100 px-2
                                   text-xs font-bold text-slate-600">
                            {{ $employees->firstItem() + $loop->index }}
                        </span>
                    </td>

                    <td class="px-4 py-2 text-center">
                        <input type="checkbox" value="{{ $employee->id }}" data-employee-id="{{ $employee->id }}"
                            class="employee-checkbox h-5 w-5
                                   rounded border-stone-300
                                   text-kanmo-600
                                   focus:ring-kanmo-500 cursor-pointer border-2"
                            aria-label="Select {{ $employeeName }}">
                    </td>

                    <td class="px-4 py-2">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0
                                       items-center justify-center
                                       rounded-xl bg-kanmo-50
                                       text-sm font-extrabold
                                       text-kanmo-600 ring-1
                                       ring-inset ring-kanmo-100">
                                {{ $employeeInitial }}
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold
                                           text-slate-900"
                                    title="{{ $employeeName }}">
                                    {{ $employeeName }}
                                </p>

                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs text-slate-400">
                                        NIP
                                    </span>

                                    <span
                                        class="rounded-md bg-stone-100
                                               px-2 py-0.5 font-mono
                                               text-xs font-semibold
                                               text-slate-600">
                                        {{ $employee->employee_id }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-2 text-center">
                        @if ($employee->company)
                            <span
                                class="inline-flex rounded-full
                                       bg-kanmo-50 px-3 py-1
                                       text-xs font-bold text-kanmo-700
                                       ring-1 ring-inset
                                       ring-kanmo-600/15">
                                {{ $employee->company }}
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full
                                       bg-stone-100 px-3 py-1
                                       text-xs font-semibold text-slate-500">
                                No Company
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-2 text-center">
                        @if ($employee->sourceData?->source)
                            <span
                                class="inline-flex rounded-full
                                       bg-kanmo-50 px-3 py-1
                                       text-xs font-bold text-kanmo-700
                                       ring-1 ring-inset
                                       ring-kanmo-600/15">
                                {{ $employee->sourceData?->source }}
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full
                                       bg-stone-100 px-3 py-1
                                       text-xs font-semibold text-slate-500">
                                No Source
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <div
                            class="mx-auto flex h-14 w-14
                                   items-center justify-center
                                   rounded-2xl bg-kanmo-50
                                   text-kanmo-500 ring-1 ring-kanmo-100">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75 21 21m-3-11.25
                                       a8.25 8.25 0 1 1-16.5 0
                                       8.25 8.25 0 0 1 16.5 0Z" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-bold text-slate-900">
                            Employee tidak ditemukan
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Tidak ada employee tanpa PIC yang sesuai
                            dengan pencarian atau filter.
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($employees->hasPages())
    <div class="border-t border-stone-200 bg-stone-50/70
               px-5 py-4 lg:px-6">
        {{ $employees->onEachSide(1)->links() }}
    </div>
@endif

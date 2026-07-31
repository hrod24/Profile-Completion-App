<x-layout title="Fill Employee Profile">
    <x-app-shell title="Fill Employee Profile"
        subtitle="Complete HR fields for active employees who already have an assigned PIC.">
        <x-slot:actions>
            <span
                class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 text-xs font-bold text-orange-700">
                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                <span data-employee-total>
                    {{ number_format($employees->total(), 0, ',', '.') }}
                </span>
                employees require completion
            </span>
        </x-slot:actions>

        @if (session('success'))
            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800"
                role="status">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="font-bold">Data saved successfully</p>
                    <p class="mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Employee Filter</h2>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Display employees based on the assigned PIC.</p>
                </div>

                <a href="{{ route('hr-form.index') }}" data-reset-employee-filter
                    class="{{ $selectedPic === '' && $search === '' ? 'hidden' : '' }} inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-bold text-slate-600 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700">
                    Reset Filter
                </a>
            </div>

            <form method="GET" action="{{ route('hr-form.index') }}"
                class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_260px_auto] md:items-end" data-live-employee-filter>
                <div class="w-full">
                    <label for="employee-search" class="mb-2 block text-sm font-bold text-slate-700">
                        Search Employee
                    </label>

                    <div class="relative">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </span>

                        <input id="employee-search" name="search" type="search" value="{{ $search }}"
                            placeholder="Search by name or Employee ID" autocomplete="off" data-employee-search
                            class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                    </div>
                </div>

                <div class="w-full">
                    <label for="pic" class="mb-2 block text-sm font-bold text-slate-700">
                        PIC
                    </label>

                    <select id="pic" name="pic" data-pic-filter
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm font-semibold text-slate-900 outline-none transition hover:border-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
                        <option value="">All PICs</option>

                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id }}" @selected((string) $selectedPic === (string) $pic->id)>
                                {{ $pic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-bold text-white shadow-[0_8px_20px_rgba(249,115,22,0.20)] transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4.5h18l-7.5 8.25v5.25l-3 1.5v-6.75L3 4.5z" />
                    </svg>

                    Apply Filter
                </button>
            </form>

            <div class="hidden border-t border-slate-100 px-5 py-3 text-xs font-semibold text-slate-500"
                data-live-search-status aria-live="polite"></div>
        </section>

        <div data-employee-results>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Employees Requiring Completion</h2>
                        <p class="mt-1 text-xs text-slate-500">Only active employees with an assigned PIC and incomplete
                            HR
                            fields are shown.</p>
                    </div>
                    <span
                        class="text-xs font-semibold text-slate-500">{{ number_format($employees->total(), 0, ',', '.') }}
                        records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead class="border-b border-slate-200 bg-slate-50/90">
                            <tr>
                                <th
                                    class="w-16 px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    No.</th>
                                <th
                                    class="min-w-[300px] px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Employee</th>
                                <th
                                    class="min-w-[220px] px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    PIC</th>
                                <th
                                    class="w-32 px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($employees as $employee)
                                @php
                                    $employeeName = $employee->display_name ?: 'Name not available';
                                    $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1));
                                    $picName = $employee->pic_name ?: 'PIC not available';
                                    $picInitial = strtoupper(substr(trim($picName), 0, 1));
                                @endphp

                                <tr class="transition-colors hover:bg-orange-50/35">
                                    <td class="px-5 py-3 text-center">
                                        <span
                                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-slate-100 px-2 text-xs font-bold text-slate-600">
                                            {{ $employees->firstItem() + $loop->index }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-extrabold text-orange-600 ring-1 ring-inset ring-orange-100">
                                                {{ $employeeInitial }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900"
                                                    title="{{ $employeeName }}">{{ $employeeName }}</p>
                                                <p class="mt-1 font-mono text-xs text-slate-500">Employee ID
                                                    {{ $employee->employee_id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                                                {{ $picInitial }}</div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-700">{{ $picName }}</p>
                                                <p class="mt-0.5 text-[11px] text-slate-400">Person in Charge</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('hr-form.edit', [
                                            'employeeId' => $employee->employee_id,
                                            'pic' => $selectedPic,
                                            'search' => $search,
                                        ]) }}"
                                            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-lg bg-orange-500 px-3.5 text-xs font-bold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 3.487a2.25 2.25 0 113.182 3.182L8.25 18.463 3.75 19.5l1.037-4.5L16.862 3.487z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <div
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <h3 class="mt-4 text-sm font-bold text-slate-900">All HR data is complete</h3>
                                        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">No active
                                            employees with an assigned PIC and incomplete HR fields were found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4" data-employee-pagination>
                        {{ $employees->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        </div>
    </x-app-shell>
</x-layout>

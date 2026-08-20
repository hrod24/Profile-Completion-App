@if (request()->filled('search'))
    <div
        class="flex flex-col gap-1 border-b border-kanmo-100
               bg-kanmo-50/70 px-5 py-3 text-sm
               sm:flex-row sm:items-center sm:justify-between lg:px-6">
        <p class="text-kanmo-800">
            Search results for

            <span class="font-bold">
                “{{ request('search') }}”
            </span>
        </p>

        <p class="text-xs font-semibold text-kanmo-600">
            {{ number_format($employees->total(), 0, ',', '.') }}
            results found
        </p>
    </div>
@endif

@php
    $progressMeta = static function (float $percentage): array {
        return match (true) {
            $percentage >= 100 => [
                'label' => 'Complete',
                'text' => 'text-emerald-600',
                'bar' => 'bg-emerald-500',
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            ],

            $percentage >= 75 => [
                'label' => 'Almost complete',
                'text' => 'text-kanmo-600',
                'bar' => 'bg-kanmo-500',
                'badge' => 'bg-kanmo-50 text-kanmo-700 ring-kanmo-600/20',
            ],

            $percentage >= 40 => [
                'label' => 'In progress',
                'text' => 'text-amber-600',
                'bar' => 'bg-amber-500',
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            ],

            default => [
                'label' => 'Needs completion',
                'text' => 'text-rose-600',
                'bar' => 'bg-rose-500',
                'badge' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            ],
        };
    };
@endphp

<div class="overflow-x-auto">
    <table class="w-full min-w-[1900px] table-auto text-left">
        <thead class="border-b border-stone-200 bg-stone-50/90">
            <tr>
                <th class="w-16 px-4 py-2.5 text-center text-xs font-bold text-slate-500">No.</th>
                <th class="min-w-[260px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Employee</th>
                <th class="min-w-[260px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">PIC</th>
                <th class="min-w-[260px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Source</th>
                <th class="min-w-[230px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Data Employee</th>
                <th class="min-w-[230px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Data HR</th>
                <th class="min-w-[275px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Overall Completion
                </th>
                <th class="min-w-[275px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Details</th>
                <th class="min-w-[275px] px-4 py-2.5 text-center text-xs font-bold text-slate-500">Employment Status
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-stone-100 bg-white">
            @forelse ($employees as $employee)
                @php
                    $employeeCompletion = min(max((float) $employee->profile_completion, 0), 100);
                    $hrCompletion = min(max((float) $employee->profile_od_completion, 0), 100);
                    $overallCompletion = min(max((float) $employee->overall_profile_completion, 0), 100);
                    $employeeMeta = $progressMeta($employeeCompletion);
                    $hrMeta = $progressMeta($hrCompletion);
                    $overallMeta = $progressMeta($overallCompletion);
                    $employeeName = $employee->display_name ?: 'Tanpa Nama';
                    $employeePic = $employee->pic->name ?? '---';
                    $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1));
                @endphp

                <tr class="group transition-colors hover:bg-kanmo-50/35">
                    <td class="px-4 py-1 text-center">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-stone-100 px-2 text-xs font-bold text-slate-600">
                            {{ $employees->firstItem() + $loop->index }}
                        </span>
                    </td>

                    <td class="px-4 py-1">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kanmo-50 text-sm font-extrabold text-kanmo-600 ring-1 ring-inset ring-kanmo-100">
                                {{ $employeeInitial }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900" title="{{ $employeeName }}">
                                    {{ $employeeName }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs text-slate-400">NIP</span>
                                    <span
                                        class="rounded-md bg-stone-100 px-2 py-0.5 font-mono text-xs font-semibold text-slate-600">{{ $employee->employee_id }}</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-1">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kanmo-50 text-sm font-extrabold text-kanmo-600 ring-1 ring-inset ring-kanmo-100">
                                {{ strtoupper(substr(trim($employeePic), 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900" title="{{ $employeeName }}">
                                    {{ $employeePic }}</p>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-1 text-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900" title="{{ $employeeName }}">
                                {{ $employee->sourceData->source ?? '---' }}</p>
                        </div>

                    </td>

                    @foreach ([[$employeeCompletion, $employeeMeta, $employee->profile_completion_filled, $employee->profile_completion_total, 'Kelengkapan data employee'], [$hrCompletion, $hrMeta, $employee->profile_od_completion_filled, $employee->profile_od_completion_total, 'Kelengkapan data OD']] as [$completion, $meta, $filled, $total, $ariaLabel])
                        <td class="px-4 py-1">
                            <div class="min-w-[205px]">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <span
                                        class="text-sm font-extrabold {{ $meta['text'] }}">{{ number_format($completion, 2, ',', '.') }}%</span>
                                    <span class="whitespace-nowrap text-xs text-slate-400">{{ $filled }} /
                                        {{ $total }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-stone-100" role="progressbar"
                                    aria-label="{{ $ariaLabel }}" aria-valuemin="0" aria-valuemax="100"
                                    aria-valuenow="{{ $completion }}">
                                    <div class="h-full rounded-full {{ $meta['bar'] }}"
                                        style="width: {{ $completion }}%"></div>
                                </div>
                                <p class="mt-2 text-xs font-semibold {{ $meta['text'] }}">{{ $meta['label'] }}</p>
                            </div>
                        </td>
                    @endforeach

                    <td class="px-4 py-1">
                        <div
                            class="min-w-[245px] rounded-xl border border-kanmo-100 bg-gradient-to-r from-kanmo-50/80 to-white p-3">
                            <div class="flex items-center justify-between gap-3">
                                <span
                                    class="text-lg font-extrabold {{ $overallMeta['text'] }}">{{ number_format($overallCompletion, 2, ',', '.') }}%</span>
                                <span
                                    class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset {{ $overallMeta['badge'] }}">{{ $overallMeta['label'] }}</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-200" role="progressbar"
                                aria-label="Overall profile completion" aria-valuemin="0" aria-valuemax="100"
                                aria-valuenow="{{ $overallCompletion }}">
                                <div class="h-full rounded-full {{ $overallMeta['bar'] }}"
                                    style="width: {{ $overallCompletion }}%"></div>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500">
                                <span>Completion</span>
                                <span class="font-semibold">{{ $employee->overall_profile_completion_filled }} /
                                    {{ $employee->overall_profile_completion_total }} Columns</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-1">
                        <div class="w-fit mx-auto">
                            <button type="button"
                                class="inline-flex min-h-9 cursor-pointer items-center justify-center
                                        gap-2 rounded bg-orange-500 px-3.5
                                        text-xs font-bold text-white transition
                                        hover:bg-orange-600 focus:outline-none
                                        focus:ring-4 focus:ring-orange-200"
                                data-employee-details-button
                                data-employee-details-url="{{ route('dashboard.employee-details', $employee->employee_id) }}"
                                aria-haspopup="dialog">

                                Details
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-1">
                        <div class="flex items-center gap-3">
                            @if ($employee->active == 1)
                                <div
                                    class="flex py-2 px-4 w-fit mx-auto items-center justify-center rounded bg-emerald-100 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-emerald-300">
                                    Active
                                </div>
                            @else
                                <div
                                    class="flex py-2 px-4 w-fit mx-auto items-center justify-center rounded bg-red-100 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-red-300">
                                    Blocked
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-kanmo-50 text-kanmo-500 ring-1 ring-kanmo-100">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 15.75l5.25 5.25m-3-11.25a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-bold text-slate-900">Employee tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-slate-500">Coba gunakan NIP atau nama employee yang berbeda.</p>
                        @if (request()->filled('search'))
                            <a href="{{ route('dashboard') }}" class="kanmo-btn-primary mt-4">Tampilkan semua
                                employee</a>
                        @endif
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

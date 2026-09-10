<x-layout :title="$title">
    <x-app-shell title="Progress Report — {{ $source }}" subtitle="Profile completion for {{ $source }}.">
        <section class="minimal-card overflow-hidden">

            {{-- Header --}}
            <div
                class="flex flex-col gap-3 border-b border-slate-200
                       px-5 py-4 sm:flex-row
                       sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ $source }} Completion
                    </h2>
                </div>

                <a href="{{ route('progress-report.index') }}" class="kanmo-btn-secondary">
                    ← Back to Source
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] table-auto text-left">

                    <thead class="border-b border-stone-200
                               bg-stone-50/90 text-center">
                        <tr>
                            <th
                                class="w-16 px-4 py-3
                                       text-xs font-bold text-slate-500">
                                No.
                            </th>

                            <th
                                class="min-w-[280px] px-4 py-3
                                       text-left text-xs font-bold
                                       text-slate-500">
                                {{ $dimensionLabel }}
                            </th>

                            <th
                                class="min-w-[130px] px-4 py-3
                                       text-xs font-bold text-slate-500">
                                Headcount
                            </th>

                            <th
                                class="min-w-[130px] px-4 py-3
                                       text-xs font-bold text-slate-500">
                                Completed
                            </th>

                            <th
                                class="min-w-[150px] px-4 py-3
                                       text-xs font-bold text-slate-500">
                                Not Completed
                            </th>

                            <th
                                class="min-w-[320px] px-4 py-3
                                       text-xs font-bold text-slate-500">
                                Percentage (%)
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($reports as $report)
                            @php
                                $percentage = min(max((float) $report['percentage'], 0), 100);

                                $meta = match (true) {
                                    $percentage >= 100 => [
                                        'bar' => 'bg-emerald-500',
                                        'text' => 'text-emerald-600',
                                    ],

                                    $percentage >= 75 => [
                                        'bar' => 'bg-kanmo-500',
                                        'text' => 'text-kanmo-600',
                                    ],

                                    $percentage >= 40 => [
                                        'bar' => 'bg-amber-500',
                                        'text' => 'text-amber-600',
                                    ],

                                    default => [
                                        'bar' => 'bg-rose-500',
                                        'text' => 'text-rose-600',
                                    ],
                                };

                                /*
                                 * Semua source sekarang bisa drill-down sampai employee.
                                 */
                                $canDrillDown = in_array($source, ['HEAD OFFICE', 'STORE', 'WAREHOUSE'], true);

                                /*
                                 * Warehouse tidak memiliki business unit /
                                 * department group.
                                 */
                                $routeGroupCode = match ($source) {
                                    'WAREHOUSE' => '__ALL__',

                                    default => $report['code'] ?? '__NULL__',
                                };

                                $baseEmployeeUrl = route('progress-report.group-employees', [
                                    'source' => $source,

                                    'groupCode' => $routeGroupCode,
                                ]);

                                $headcountUrl = $baseEmployeeUrl . '?status=all';

                                $completedUrl = $baseEmployeeUrl . '?status=completed';

                                $notCompletedUrl = $baseEmployeeUrl . '?status=not_completed';
                            @endphp

                            @php
                                $canDrillDown = in_array($source, ['HEAD OFFICE', 'STORE', 'WAREHOUSE'], true);

                                $routeGroupCode = match ($source) {
                                    'WAREHOUSE' => '__ALL__',

                                    default => $report['code'] ?? '__NULL__',
                                };

                                $employeeUrl = $canDrillDown
                                    ? route('progress-report.group-employees', [
                                        'source' => $source,

                                        'groupCode' => $routeGroupCode,
                                    ])
                                    : null;
                            @endphp

                            <tr 
                                tabindex="0"
                                role="link" >
                                <td class="px-4 py-3 text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center
               justify-between gap-3">
                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm
                                                font-semibold
                                                text-slate-900">
                                                {{ $report['name'] }}
                                            </p>

                                            @if ($report['code'])
                                                <p
                                                    class="mt-0.5 text-xs
                                                text-slate-400">
                                                    {{ $report['code'] }}
                                                </p>
                                            @endif
                                        </div>

                                        @if ($canDrillDown)
                                            <div
                                                class="flex h-8 w-8 shrink-0
                                                items-center justify-center
                                                rounded-lg text-slate-400
                                                transition
                                                group-hover:text-kanmo-600">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m9 18 6-6-6-6" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ $headcountUrl }}" onclick="event.stopPropagation()"
                                        class="inline-flex min-w-12 items-center
                                        justify-center rounded-lg
                                        px-3 py-2 text-sm font-bold
                                        text-slate-700 transition
                                        hover:bg-slate-100
                                        hover:text-slate-900
                                        focus:outline-none
                                        focus:ring-4 focus:ring-slate-100"
                                        title="View all employees">
                                        {{ number_format($report['headcount'], 0, ',', '.') }}
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ $completedUrl }}" onclick="event.stopPropagation()"
                                        class="inline-flex min-w-12 items-center
                                        justify-center rounded-lg
                                        bg-emerald-50/60
                                        px-3 py-2 text-sm font-bold
                                        text-emerald-600 transition
                                        hover:bg-emerald-100
                                        hover:text-emerald-700
                                        focus:outline-none
                                        focus:ring-4 focus:ring-emerald-100"
                                        title="View completed employees">
                                        {{ number_format($report['completed'], 0, ',', '.') }}
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a href="{{ $notCompletedUrl }}" onclick="event.stopPropagation()"
                                        class="inline-flex min-w-12 items-center
                                        justify-center rounded-lg
                                        bg-rose-50/60
                                        px-3 py-2 text-sm font-bold
                                        text-rose-600 transition
                                        hover:bg-rose-100
                                        hover:text-rose-700
                                        focus:outline-none
                                        focus:ring-4 focus:ring-rose-100"
                                        title="View employees that are not completed">
                                        {{ number_format($report['not_completed'], 0, ',', '.') }}
                                    </a>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between">
                                            <span
                                                class="text-sm
                                                       font-extrabold
                                                       {{ $meta['text'] }}">
                                                {{ number_format($percentage, 2, ',', '.') }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-slate-400">
                                                {{ number_format($report['completed_fields'], 0, ',', '.') }}
                                                /
                                                {{ number_format($report['total_fields'], 0, ',', '.') }}
                                                fields
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 overflow-hidden
                                                   rounded-full
                                                   bg-stone-200">
                                            <div class="h-full rounded-full
                                                       {{ $meta['bar'] }}"
                                                style="width:
                                                    {{ $percentage }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-16
                                           text-center text-sm
                                           text-slate-500">
                                    No data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($reports->isNotEmpty())
                        <tfoot class="border-t-2
                                   border-stone-200 bg-stone-50">
                            <tr>
                                <td colspan="2"
                                    class="px-4 py-4
                                           font-extrabold
                                           text-slate-900">
                                    TOTAL
                                </td>

                                <td
                                    class="px-4 py-4
                                           text-center font-extrabold">
                                    {{ number_format($totalHeadcount, 0, ',', '.') }}
                                </td>

                                <td
                                    class="px-4 py-4 text-center
                                           font-extrabold
                                           text-emerald-600">
                                    {{ number_format($totalCompletedEmployees, 0, ',', '.') }}
                                </td>

                                <td
                                    class="px-4 py-4 text-center
                                           font-extrabold
                                           text-rose-600">
                                    {{ number_format($totalNotCompletedEmployees, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between">
                                            <span
                                                class="font-extrabold
                                                       text-kanmo-600">
                                                {{ number_format($totalPercentage, 2, ',', '.') }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-slate-500">
                                                Overall
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 overflow-hidden
                                                   rounded-full
                                                   bg-stone-200">
                                            <div class="h-full rounded-full
                                                       bg-kanmo-500"
                                                style="width:
                                                    {{ min($totalPercentage, 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>
        </section>
    </x-app-shell>
</x-layout>

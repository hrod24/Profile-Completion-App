<x-layout :title="$title">
    <x-app-shell title="Progress Report" subtitle="Monitor field-level employee profile completion by source.">
        {{-- Table --}}
        <section class="minimal-card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-900">
                    Completion by Source
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Percentage is calculated from completed
                    required fields, not completed employees.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px]
                           table-auto text-left">
                    <thead class="border-b border-stone-200
                               bg-stone-50/90">
                        <tr>
                            <th
                                class="w-16 px-4 py-3 text-center
                                       text-xs font-bold
                                       text-slate-500">
                                No.
                            </th>

                            <th
                                class="min-w-[180px] px-4 py-3
                                       text-xs font-bold
                                       text-slate-500">
                                Source
                            </th>

                            <th
                                class="min-w-[140px] px-4 py-3
                                       text-xs font-bold
                                       text-slate-500">
                                Headcount
                            </th>

                            <th
                                class="min-w-[160px] px-4 py-3
                                       text-xs font-bold
                                       text-slate-500">
                                Completed
                            </th>

                            <th
                                class="min-w-[170px] px-4 py-3
                                       text-xs font-bold
                                       text-slate-500">
                                Not Completed
                            </th>

                            <th
                                class="min-w-[320px] px-4 py-3
                                       text-xs font-bold
                                       text-slate-500">
                                Percentage
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100
                               bg-white">
                        @forelse ($progressReports as $report)
                            @php
                                $percentage = min(max((float) $report['percentage'], 0), 100);

                                $progressMeta = match (true) {
                                    $percentage >= 100 => [
                                        'bar' => 'bg-emerald-500',
                                        'text' => 'text-emerald-600',
                                        'label' => 'Complete',
                                    ],

                                    $percentage >= 75 => [
                                        'bar' => 'bg-kanmo-500',
                                        'text' => 'text-kanmo-600',
                                        'label' => 'Almost complete',
                                    ],

                                    $percentage >= 40 => [
                                        'bar' => 'bg-amber-500',
                                        'text' => 'text-amber-600',
                                        'label' => 'In progress',
                                    ],

                                    default => [
                                        'bar' => 'bg-rose-500',
                                        'text' => 'text-rose-600',
                                        'label' => 'Needs completion',
                                    ],
                                };
                            @endphp

                            <tr class="transition-colors
                                       hover:bg-kanmo-50/35">
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex h-8 min-w-8
                                               items-center justify-center
                                               rounded-lg bg-stone-100
                                               px-2 text-xs font-bold
                                               text-slate-600">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="text-sm font-bold
                                               text-slate-900">
                                        {{ $report['source'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="text-sm font-semibold
                                               text-slate-700">
                                        {{ number_format($report['headcount'], 0, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="text-sm font-bold
                                               text-emerald-600">
                                        <a href="">{{ number_format($report['completed'], 0, ',', '.') }}</a>
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="text-sm font-bold
                                               text-rose-600">
                                        <a href="">{{ number_format($report['not_completed'], 0, ',', '.') }}</a>
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between gap-4">
                                            <span
                                                class="text-sm
                                                       font-extrabold
                                                       {{ $progressMeta['text'] }}">
                                                {{ number_format($percentage, 2, ',', '.') }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       font-semibold
                                                       {{ $progressMeta['text'] }}">
                                                {{ $progressMeta['label'] }}
                                            </span>
                                        </div>

                                        <div class="h-2 overflow-hidden
                                                   rounded-full
                                                   bg-stone-200"
                                            role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                            aria-valuenow="{{ $percentage }}">
                                            <div class="h-full rounded-full
                                                       {{ $progressMeta['bar'] }}"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>

                                        <div
                                            class="mt-2 flex
                                                   justify-between
                                                   text-[11px]
                                                   text-slate-400">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-16 text-center
                                           text-sm text-slate-500">
                                    Source data is not available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($progressReports->isNotEmpty())
                        <tfoot class="border-t-2 border-stone-200
                                   bg-stone-50">
                            <tr>
                                <td colspan="2"
                                    class="px-4 py-4 text-sm
                                           font-extrabold
                                           text-slate-900">
                                    TOTAL
                                </td>

                                <td
                                    class="px-4 py-4 text-sm
                                           font-extrabold
                                           text-slate-900">
                                    {{ number_format($totalHeadcount, 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-4 text-sm font-extrabold text-emerald-600">
                                    <a href="">{{ number_format($totalCompletedEmployees, 0, ',', '.') }}</a>
                                </td>

                                <td class="px-4 py-4 text-sm font-extrabold text-rose-600">
                                    <a href="">{{ number_format($totalNotCompletedEmployees, 0, ',', '.') }}</a>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between">
                                            <span
                                                class="text-base
                                                       font-extrabold
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

<x-layout :title="$title">
    <x-app-shell
        title="Progress Report — {{ $source }}"
        subtitle="Profile completion for {{ $source }}."
    >
        <section class="minimal-card overflow-hidden">

            {{-- Header --}}
            <div
                class="flex flex-col gap-3 border-b border-slate-200
                       px-5 py-4 sm:flex-row
                       sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ $source }} Completion
                    </h2>
                </div>

                <a
                    href="{{ route('progress-report.index') }}"
                    class="kanmo-btn-secondary"
                >
                    ← Back to Source
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] table-auto text-left">

                    <thead
                        class="border-b border-stone-200
                               bg-stone-50/90 text-center"
                    >
                        <tr>
                            <th
                                class="w-16 px-4 py-3
                                       text-xs font-bold text-slate-500"
                            >
                                No.
                            </th>

                            <th
                                class="min-w-[280px] px-4 py-3
                                       text-left text-xs font-bold
                                       text-slate-500"
                            >
                                {{ $dimensionLabel }}
                            </th>

                            <th
                                class="min-w-[130px] px-4 py-3
                                       text-xs font-bold text-slate-500"
                            >
                                Headcount
                            </th>

                            <th
                                class="min-w-[130px] px-4 py-3
                                       text-xs font-bold text-slate-500"
                            >
                                Completed
                            </th>

                            <th
                                class="min-w-[150px] px-4 py-3
                                       text-xs font-bold text-slate-500"
                            >
                                Not Completed
                            </th>

                            <th
                                class="min-w-[320px] px-4 py-3
                                       text-xs font-bold text-slate-500"
                            >
                                Percentage (%)
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($reports as $report)
                            @php
                                $percentage = min(
                                    max(
                                        (float) $report['percentage'],
                                        0
                                    ),
                                    100
                                );

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
                            @endphp

                            <tr
                                class="transition-colors
                                       hover:bg-kanmo-50/40"
                            >
                                <td class="px-4 py-3 text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-3">
                                    <p
                                        class="text-sm font-semibold
                                               text-slate-900"
                                    >
                                        {{ $report['name'] }}
                                    </p>

                                    @if ($report['code'])
                                        <p
                                            class="mt-0.5 text-xs
                                                   text-slate-400"
                                        >
                                            {{ $report['code'] }}
                                        </p>
                                    @endif
                                </td>

                                <td
                                    class="px-4 py-3 text-center
                                           text-sm font-semibold"
                                >
                                    {{ number_format(
                                        $report['headcount'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td
                                    class="px-4 py-3 text-center
                                           text-sm font-bold
                                           text-emerald-600"
                                >
                                    {{ number_format(
                                        $report['completed'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td
                                    class="px-4 py-3 text-center
                                           text-sm font-bold
                                           text-rose-600"
                                >
                                    {{ number_format(
                                        $report['not_completed'],
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between"
                                        >
                                            <span
                                                class="text-sm
                                                       font-extrabold
                                                       {{ $meta['text'] }}"
                                            >
                                                {{ number_format(
                                                    $percentage,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-slate-400"
                                            >
                                                {{ number_format(
                                                    $report['completed_fields'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                                /
                                                {{ number_format(
                                                    $report['total_fields'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                                fields
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 overflow-hidden
                                                   rounded-full
                                                   bg-stone-200"
                                        >
                                            <div
                                                class="h-full rounded-full
                                                       {{ $meta['bar'] }}"
                                                style="width:
                                                    {{ $percentage }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-16
                                           text-center text-sm
                                           text-slate-500"
                                >
                                    No data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($reports->isNotEmpty())
                        <tfoot
                            class="border-t-2
                                   border-stone-200 bg-stone-50"
                        >
                            <tr>
                                <td
                                    colspan="2"
                                    class="px-4 py-4
                                           font-extrabold
                                           text-slate-900"
                                >
                                    TOTAL
                                </td>

                                <td
                                    class="px-4 py-4
                                           text-center font-extrabold"
                                >
                                    {{ number_format(
                                        $totalHeadcount,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td
                                    class="px-4 py-4 text-center
                                           font-extrabold
                                           text-emerald-600"
                                >
                                    {{ number_format(
                                        $totalCompletedEmployees,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td
                                    class="px-4 py-4 text-center
                                           font-extrabold
                                           text-rose-600"
                                >
                                    {{ number_format(
                                        $totalNotCompletedEmployees,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="min-w-[280px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between"
                                        >
                                            <span
                                                class="font-extrabold
                                                       text-kanmo-600"
                                            >
                                                {{ number_format(
                                                    $totalPercentage,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-slate-500"
                                            >
                                                Overall
                                            </span>
                                        </div>

                                        <div
                                            class="h-2 overflow-hidden
                                                   rounded-full
                                                   bg-stone-200"
                                        >
                                            <div
                                                class="h-full rounded-full
                                                       bg-kanmo-500"
                                                style="width:
                                                    {{ min(
                                                        $totalPercentage,
                                                        100
                                                    ) }}%"
                                            ></div>
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
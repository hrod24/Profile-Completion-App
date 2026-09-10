<x-layout :title="$title ?? 'Attendance Overview'">
    @php
        /*
         * Replace this sample array with data from your controller.
         * The percentage values below follow the source table exactly.
         */
        $attendanceRows = $attendanceRows ?? [
            ['division' => 'BUSINESS DEVELOPMENT', 'office' => 4, 'wfh' => 2, 'percentage' => 50],
            ['division' => 'CREATIVE & EXPERIENCE EXECUTION', 'office' => 13, 'wfh' => 12, 'percentage' => 93],
            ['division' => 'DIGITAL, OMNICHANNEL & INTELLIGENT TECHNOLOGY', 'office' => 131, 'wfh' => 60, 'percentage' => 46],
            ['division' => 'FASHION & ACCESSORIES', 'office' => 51, 'wfh' => 20, 'percentage' => 40],
            ['division' => 'FASHION & ACCESSORIES 2', 'office' => 2, 'wfh' => 2, 'percentage' => 100],
            ['division' => 'FINANCE', 'office' => 52, 'wfh' => 48, 'percentage' => 93],
            ['division' => 'FOOTWEAR & ACTIVE', 'office' => 48, 'wfh' => 36, 'percentage' => 75],
            ['division' => 'HUMAN RESOURCES', 'office' => 30, 'wfh' => 26, 'percentage' => 87],
            ['division' => 'LEGAL & COMPLIANCE', 'office' => 7, 'wfh' => 6, 'percentage' => 86],
            ['division' => 'LIFESTYLE', 'office' => 26, 'wfh' => 13, 'percentage' => 50],
            ['division' => 'PROJECT & MAINTENANCE', 'office' => 14, 'wfh' => 8, 'percentage' => 58],
            ['division' => 'SHIPPING', 'office' => 12, 'wfh' => 12, 'percentage' => 100],
            ['division' => 'THE COACH RESTAURANT', 'office' => 1, 'wfh' => 0, 'percentage' => 0],
        ];

        $attendanceDate = $attendanceDate ?? 'Wednesday, September 2, 2026';

        $divisionCount = count($attendanceRows);
        $totalOffice = collect($attendanceRows)->sum('office');
        $totalWfh = collect($attendanceRows)->sum('wfh');
        $totalTracked = $totalOffice + $totalWfh;

        $averageAttendance = $divisionCount > 0
            ? round(collect($attendanceRows)->avg('percentage'))
            : 0;

        $chartLabels = collect($attendanceRows)
            ->pluck('division')
            ->values()
            ->all();

        $chartPercentages = collect($attendanceRows)
            ->pluck('percentage')
            ->values()
            ->all();
    @endphp

    <div class="min-h-dvh bg-slate-50/70 lg:h-dvh lg:overflow-hidden">
        <main
            class="mx-auto flex min-h-dvh w-full max-w-[1500px] flex-col
                   px-4 py-3
                   sm:px-5
                   lg:h-full lg:min-h-0 lg:overflow-hidden lg:px-6"
        >
            {{-- ====================================================== --}}
            {{-- HEADER --}}
            {{-- ====================================================== --}}
            <header
                class="mb-3 flex shrink-0 flex-col gap-2
                       sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full
                                   bg-kanmo-50 px-2.5 py-1 text-[10px] font-extrabold
                                   uppercase tracking-[0.08em] text-kanmo-700
                                   ring-1 ring-inset ring-kanmo-100"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-kanmo-500"></span>
                            Attendance Overview
                        </span>

                        <span
                            class="rounded-full bg-white px-2.5 py-1
                                   text-[10px] font-bold text-slate-500
                                   ring-1 ring-inset ring-slate-200"
                        >
                            {{ $divisionCount }} divisions
                        </span>
                    </div>
                </div>
            </header>


            {{-- ====================================================== --}}
            {{-- DASHBOARD BODY --}}
            {{-- ====================================================== --}}
            <section
                class="grid min-h-0 flex-1 gap-3  "
            >
                {{-- ================================================== --}}
                {{-- ALL DIVISION TABLE --}}
                {{-- ================================================== --}}
                <article
                    class="flex min-h-0 flex-col overflow-hidden rounded-xl
                           border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex shrink-0 items-center justify-between
                               border-b border-slate-200 px-4 py-2.5"
                    >
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">
                                Division Attendance - {{ $attendanceDate }}
                            </h2>
                            <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                                Complete attendance snapshot for all divisions.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-kanmo-50 px-2.5 py-1
                                   text-[9px] font-extrabold uppercase tracking-wide
                                   text-kanmo-700 ring-1 ring-inset ring-kanmo-100"
                        >
                            {{ $divisionCount }} records
                        </span>
                    </div>

                    <div class="min-h-0 flex-1 overflow-hidden">
                        <table class="w-full table-fixed border-collapse text-left">
                            <thead class="bg-slate-50/90">
                                <tr class="border-b border-slate-200">
                                    <th class="w-10 px-3 py-2 text-center text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                                        No.
                                    </th>

                                    <th class="px-2 py-2 w-80 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                                        Division
                                    </th>

                                    <th class="w-16 px-2 py-2 text-center text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                                        Office
                                    </th>

                                    <th class="w-16 px-2 py-2 text-center text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                                        Offsite
                                    </th>

                                    <th class="w-[180px] px-3 py-2 text-[10px] font-extrabold uppercase tracking-wide text-slate-400">
                                        Attendance
                                      </th>
                                </tr>
                            </thead>

                            <tbody class=" divide-slate-100">
                                @foreach ($attendanceRows as $index => $row)
                                    <tr class="transition-colors hover:bg-kanmo-50/30">
                                        <td class="px-3 py-[15px] text-center">
                                            <span
                                                class="inline-flex h-5 min-w-5 items-center justify-center
                                                       rounded-md bg-slate-100 px-1 text-[20px] font-bold
                                                       text-slate-500"
                                            >
                                                {{ $index + 1 }}
                                            </span>
                                        </td>

                                        <td class="px-2 py-[15px]">
                                            <p
                                                class="truncate text-[25px] py-1 font-bold leading-4 text-slate-700"
                                                title="{{ $row['division'] }}"
                                            >
                                                {{ $row['division'] }}
                                            </p>
                                        </td>

                                        <td class="px-2 py-[6px] text-center text-[25px] font-extrabold text-slate-700">
                                            {{ number_format($row['office']) }}
                                        </td>

                                        <td class="px-2 py-[6px] text-center text-[25px] font-extrabold text-slate-500">
                                            {{ number_format($row['wfh']) }}
                                        </td>

                                        <td class="px-3 py-[6px]">
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                    <div
                                                        class="h-full rounded-full
                                                            @if ($row['percentage'] >= 80)
                                                                bg-kanmo-500
                                                            @elseif ($row['percentage'] >= 50)
                                                                bg-kanmo-400
                                                            @else
                                                                bg-slate-300
                                                            @endif"
                                                        style="width: {{ max(0, min(100, $row['percentage'])) }}%"
                                                    ></div>
                                                </div>

                                                <span
                                                    class="w-9 text-right text-[14px] font-extrabold
                                                        @if ($row['percentage'] >= 80)
                                                            text-kanmo-700
                                                        @elseif ($row['percentage'] >= 50)
                                                            text-kanmo-600
                                                        @else
                                                            text-slate-500
                                                        @endif"
                                                >
                                                    {{ $row['percentage'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </main>
    </div>

</x-layout>

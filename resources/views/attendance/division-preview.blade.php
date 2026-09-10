<x-layout :title="$title ?? 'Attendance Report'">
    @php
        $divisionName = $divisionName ?? 'Business Development';

        $attendanceDate = $attendanceDate ?? 'Wednesday, September 2, 2026';

        $office = $office ?? 4;
        $wfhAbsent = $wfhAbsent ?? 2;

        $attendancePercentage = $attendancePercentage ?? 50;

        $totalEmployees = $office + $wfhAbsent;

        $officePercentage = $totalEmployees > 0 ? round(($office / $totalEmployees) * 100) : 0;

        $wfhPercentage = $totalEmployees > 0 ? round(($wfhAbsent / $totalEmployees) * 100) : 0;

        $attendanceStatus = match (true) {
            $attendancePercentage >= 80 => 'High Attendance',

            $attendancePercentage >= 50 => 'Moderate Attendance',

            default => 'Low Attendance',
        };

        $history = $history ?? [
            [
                'label' => 'Aug 27',
                'office' => 4,
                'wfh' => 2,
            ],
            [
                'label' => 'Aug 28',
                'office' => 5,
                'wfh' => 1,
            ],
            [
                'label' => 'Aug 29',
                'office' => 2,
                'wfh' => 5,
            ],
            [
                'label' => 'Aug 30',
                'office' => 1,
                'wfh' => 6,
            ],
            [
                'label' => 'Aug 31',
                'office' => 5,
                'wfh' => 2,
            ],
            [
                'label' => 'Sep 01',
                'office' => 4,
                'wfh' => 2,
            ],
            [
                'label' => 'Sep 02',
                'office' => 4,
                'wfh' => 2,
            ],
        ];

        $chartLabels = array_column($history, 'label');

        $officeHistory = array_column($history, 'office');

        $wfhHistory = array_column($history, 'wfh');
    @endphp

    <div class="h-dvh overflow-hidden bg-slate-50/70">
        <main
            class="mx-auto flex h-full w-full max-w-[1280px]
               flex-col overflow-hidden
               px-4 py-4
               sm:px-6
               lg:px-8">
            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}

            <header
                class="mb-4 flex shrink-0 flex-col gap-3
           lg:flex-row
           lg:items-end
           lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap
                               items-center gap-2">
                        <span
                            class="inline-flex items-center
                                   gap-2 rounded-full
                                   bg-kanmo-50
                                   px-3 py-1.5
                                   text-xs font-bold
                                   text-kanmo-700
                                   ring-1 ring-inset
                                   ring-kanmo-100">
                            <span
                                class="h-2 w-2
                                       rounded-full
                                       bg-kanmo-500"></span>

                            Attendance Report
                        </span>

                        @if ($attendancePercentage >= 80)
                            <span
                                class="inline-flex rounded-full
                                       bg-emerald-50
                                       px-3 py-1.5
                                       text-xs font-bold
                                       text-emerald-700
                                       ring-1 ring-inset
                                       ring-emerald-200">
                                {{ $attendanceStatus }}
                            </span>
                        @elseif ($attendancePercentage >= 50)
                            <span
                                class="inline-flex rounded-full
                                       bg-amber-50
                                       px-3 py-1.5
                                       text-xs font-bold
                                       text-amber-700
                                       ring-1 ring-inset
                                       ring-amber-200">
                                {{ $attendanceStatus }}
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full
                                       bg-rose-50
                                       px-3 py-1.5
                                       text-xs font-bold
                                       text-rose-700
                                       ring-1 ring-inset
                                       ring-rose-200">
                                {{ $attendanceStatus }}
                            </span>
                        @endif
                    </div>

                    <h1
                        class="text-2xl font-extrabold
                               tracking-tight text-slate-950
                               sm:text-3xl">
                        {{ $divisionName }}
                    </h1>

                    <p class="mt-1.5 text-sm
                               text-slate-500">
                        Employee attendance overview

                        <span class="mx-1.5
                                   text-slate-300">
                            •
                        </span>

                        {{ $attendanceDate }}
                    </p>
                </div>

                {{-- Total employees --}}
                <div
                    class="flex w-full items-center gap-3
                           rounded-2xl border
                           border-slate-200
                           bg-white px-4 py-3.5
                           shadow-sm
                           sm:w-auto">
                    <div
                        class="flex h-11 w-11
                               shrink-0 items-center
                               justify-center
                               rounded-xl bg-kanmo-50
                               text-kanmo-600
                               ring-1 ring-inset
                               ring-kanmo-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6
                                   a4 4 0 0 0-4 4v2
                                   M9 11a4 4 0 1 0 0-8
                                   4 4 0 0 0 0 8
                                   m13 10v-2
                                   a4 4 0 0 0-3-3.87" />
                        </svg>
                    </div>

                    <div>
                        <p
                            class="text-[11px] font-bold
                                   uppercase tracking-[0.08em]
                                   text-slate-400">
                            Total Employees
                        </p>

                        <p
                            class="mt-0.5 text-xl
                                   font-extrabold
                                   text-slate-900">
                            {{ $totalEmployees }}
                        </p>
                    </div>
                </div>
            </header>

            {{-- ================================================= --}}
            {{-- SUMMARY --}}
            {{-- ================================================= --}}

            <section class="grid shrink-0 grid-cols-1
           gap-3 md:grid-cols-3">
                {{-- Office --}}
                <article
                    class="rounded-2xl border
                           border-slate-200
                           bg-white p-4
                           shadow-sm">
                    <div class="flex items-start
                               justify-between gap-4">
                        <div
                            class="flex h-11 w-11
                                   items-center justify-center
                                   rounded-xl
                                   bg-kanmo-50
                                   text-kanmo-600
                                   ring-1 ring-inset
                                   ring-kanmo-100">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 21V7.5
                                       A1.5 1.5 0 0 1 7.5 6h9
                                       A1.5 1.5 0 0 1 18 7.5V21
                                       M4 21h16
                                       M9 10h2m2 0h2
                                       m-6 4h2m2 0h2" />
                            </svg>
                        </div>

                        <span
                            class="rounded-full
                                   bg-kanmo-50
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-kanmo-700">
                            {{ $officePercentage }}%
                        </span>
                    </div>

                    <div class="mt-5">
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-[0.08em]
                                   text-slate-400">
                            In Office
                        </p>

                        <div class="mt-1 flex
                                   items-end gap-2">
                            <span
                                class="text-4xl
                                       font-extrabold
                                       tracking-tight
                                       text-slate-950">
                                {{ $office }}
                            </span>

                            <span
                                class="mb-1 text-sm
                                       font-semibold
                                       text-slate-400">
                                employees
                            </span>
                        </div>

                        <p
                            class="mt-2 text-sm
                                   leading-6
                                   text-slate-500">
                            Employees currently working
                            from the office.
                        </p>
                    </div>
                </article>

                {{-- WFH --}}
                <article
                    class="rounded-2xl border
                           border-slate-200
                           bg-white p-4
                           shadow-sm">
                    <div class="flex items-start
                               justify-between gap-4">
                        <div
                            class="flex h-11 w-11
                                   items-center justify-center
                                   rounded-xl
                                   bg-slate-100
                                   text-slate-600
                                   ring-1 ring-inset
                                   ring-slate-200">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5
                                       M5.5 10v10h13V10
                                       M9 20v-6h6v6" />
                            </svg>
                        </div>

                        <span
                            class="rounded-full
                                   bg-slate-100
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-slate-600">
                            {{ $wfhPercentage }}%
                        </span>
                    </div>

                    <div class="mt-5">
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-[0.08em]
                                   text-slate-400">
                            Offsite
                        </p>

                        <div class="mt-1 flex
                                   items-end gap-2">
                            <span
                                class="text-4xl
                                       font-extrabold
                                       tracking-tight
                                       text-slate-950">
                                {{ $wfhAbsent }}
                            </span>

                            <span
                                class="mb-1 text-sm
                                       font-semibold
                                       text-slate-400">
                                employees
                            </span>
                        </div>

                        <p
                            class="mt-2 text-sm
                                   leading-6
                                   text-slate-500">
                            Employees working remotely
                            or currently absent.
                        </p>
                    </div>
                </article>

                {{-- Attendance rate --}}
                <article
                    class="relative overflow-hidden
                           rounded-2xl
                           bg-kanmo-500 p-4
                           text-white shadow-sm
                           ring-1 ring-kanmo-500">
                    <div
                        class="pointer-events-none
                               absolute -right-12
                               -top-16 h-44 w-44
                               rounded-full
                               bg-white/10">
                    </div>

                    <div
                        class="relative z-10
                               flex items-start
                               justify-between gap-4">
                        <div
                            class="flex h-11 w-11
                                   items-center justify-center
                                   rounded-xl
                                   bg-white/15
                                   ring-1 ring-inset
                                   ring-white/20">
                            <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4
                                       m6 2
                                       a9 9 0 1 1-18 0
                                       9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>

                        <span
                            class="rounded-full
                                   bg-white/15
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-white/90
                                   ring-1 ring-inset
                                   ring-white/15">
                            {{ $totalEmployees }} total
                        </span>
                    </div>

                    <div class="relative z-10
                               mt-5">
                        <p
                            class="text-xs font-bold
                                   uppercase
                                   tracking-[0.08em]
                                   text-white/70">
                            Attendance Rate
                        </p>

                        <div class="mt-1 flex
                                   items-end gap-1">
                            <span
                                class="text-5xl
                                       font-extrabold
                                       tracking-tight">
                                {{ $attendancePercentage }}
                            </span>

                            <span
                                class="mb-1
                                       text-2xl
                                       font-extrabold">
                                %
                            </span>
                        </div>

                        <div
                            class="mt-4 h-2
                                   overflow-hidden
                                   rounded-full
                                   bg-white/20">
                            <div class="h-full
                                       rounded-full
                                       bg-white"
                                style="
                                    width:
                                    {{ max(0, min(100, $attendancePercentage)) }}%;
                                ">
                            </div>
                        </div>

                        <p class="mt-3 text-sm
                                   text-white/80">
                            Current attendance performance
                            for this division.
                        </p>
                    </div>
                </article>
            </section>

            {{-- ================================================= --}}
            {{-- CHART --}}
            {{-- ================================================= --}}

            <section
                class="mt-4 flex min-h-0 flex-1
          flex-col overflow-hidden
          rounded-2xl border
        border-slate-200
        bg-white shadow-sm">
                <div
                    class="flex shrink-0 flex-col gap-2
                    border-b border-slate-200
                    px-5 py-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                    lg:px-6">
                    <div>
                        <div class="flex flex-wrap
                                   items-center gap-2.5">
                            <h2
                                class="text-base
                                       font-extrabold
                                       text-slate-900">
                                Attendance Trend
                            </h2>

                            <span
                                class="rounded-full
                                       bg-kanmo-50
                                       px-2.5 py-1
                                       text-[10px]
                                       font-bold uppercase
                                       tracking-wide
                                       text-kanmo-700
                                       ring-1 ring-inset
                                       ring-kanmo-100">
                                Last 7 Days
                            </span>
                        </div>

                        <p class="mt-1 text-sm
                                   text-slate-500">
                            Daily employee work-location trend.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap
                               items-center gap-4
                               text-xs font-semibold
                               text-slate-500">
                        <div class="flex
                                   items-center gap-2">
                            <span
                                class="h-2.5 w-2.5
                                       rounded-full
                                       bg-kanmo-500"></span>

                            Office
                        </div>

                        <div class="flex
                                   items-center gap-2">
                            <span
                                class="h-2.5 w-2.5
                                       rounded-full
                                       bg-slate-400"></span>

                            Offsite
                        </div>
                    </div>
                </div>

                <div class="relative min-h-0 flex-1
                      px-3 py-3
                      sm:px-5
                      lg:px-6">
                    <canvas id="attendanceTrendChart" class="h-full w-full"></canvas>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const canvas =
                    document.getElementById(
                        'attendanceTrendChart'
                    );

                if (
                    !canvas ||
                    typeof Chart === 'undefined'
                ) {
                    return;
                }

                const labels = @json($chartLabels);

                const officeData =
                    @json($officeHistory);

                const wfhData =
                    @json($wfhHistory);

                const ctx =
                    canvas.getContext('2d');

                const chartHeight =
                    canvas.parentElement
                    ?.offsetHeight || 360;

                const officeGradient =
                    ctx.createLinearGradient(
                        0,
                        0,
                        0,
                        chartHeight
                    );

                officeGradient.addColorStop(
                    0,
                    'rgba(249, 115, 22, 0.8)'
                );

                officeGradient.addColorStop(
                    0.55,
                    'rgba(249, 115, 22, 0.1)'
                );

                officeGradient.addColorStop(
                    1,
                    'rgba(249, 115, 22, 0)'
                );

                const wfhGradient =
                    ctx.createLinearGradient(
                        0,
                        0,
                        0,
                        chartHeight
                    );

                wfhGradient.addColorStop(
                    0,
                    'rgba(148, 163, 184, 1)'
                );

                wfhGradient.addColorStop(
                    0.55,
                    'rgba(148, 163, 184, 0.1)'
                );

                wfhGradient.addColorStop(
                    1,
                    'rgba(148, 163, 184, 0)'
                );

                new Chart(ctx, {
                    type: 'line',

                    data: {
                        labels,

                        datasets: [{
                                label: 'Office',

                                data: officeData,

                                borderColor: '#F97316',

                                backgroundColor: officeGradient,

                                borderWidth: 2.5,

                                tension: 0,

                                fill: true,

                                pointRadius: 5,

                                pointHoverRadius: 8.2,

                                pointBackgroundColor: '#F97316',

                                pointBorderColor: '#FFFFFF',

                                pointBorderWidth: 2,
                            },
                            {
                                label: 'WFH / Absent',

                                data: wfhData,

                                borderColor: '#94A3B8',

                                backgroundColor: wfhGradient,

                                borderWidth: 2.5,

                                tension: 0,

                                fill: true,

                                pointRadius: 5,

                                pointHoverRadius: 8.2,

                                pointBackgroundColor: '#94A3B8',

                                pointBorderColor: '#FFFFFF',

                                pointBorderWidth: 2,
                            },
                        ],
                    },

                    options: {
                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },

                        plugins: {
                            legend: {
                                display: false,
                            },

                            tooltip: {
                                backgroundColor: '#0F172A',

                                titleColor: '#FFFFFF',

                                bodyColor: '#E2E8F0',

                                padding: 12,

                                cornerRadius: 10,

                                displayColors: true,

                                callbacks: {
                                    label(context) {
                                        return (
                                            ` ${context.dataset.label}: ` +
                                            `${context.parsed.y} employees`
                                        );
                                    },
                                },
                            },
                        },

                        scales: {
                            x: {
                                border: {
                                    display: false,
                                },

                                grid: {
                                    display: false,
                                },

                                ticks: {
                                    color: '#94A3B8',

                                    padding: 10,

                                    maxRotation: 0,

                                    font: {
                                        size: 11,
                                        weight: '600',
                                    },
                                },
                            },

                            y: {
                                beginAtZero: true,

                                grace: '10%',

                                border: {
                                    display: false,
                                },

                                grid: {
                                    color: 'rgba(226, 232, 240, 0.8)',

                                    drawTicks: false,
                                },

                                ticks: {
                                    precision: 0,

                                    stepSize: 1,

                                    color: '#94A3B8',

                                    padding: 10,

                                    font: {
                                        size: 11,
                                        weight: '600',
                                    },
                                },
                            },
                        },

                        animation: {
                            duration: 600,

                            easing: 'easeOutQuart',
                        },
                    },
                });
            }
        );
    </script>
</x-layout>

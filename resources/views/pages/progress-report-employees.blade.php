<x-layout :title="$title">
    <x-app-shell :title="'Progress Report — ' . $groupName" :subtitle="'Employee profile completion for ' . $groupName . '.'">
        {{-- Breadcrumb / Navigation --}}
        <div
            class="mb-5 flex flex-col gap-3
                   sm:flex-row sm:items-center
                   sm:justify-between">
            <div class="flex flex-wrap items-center
                       gap-2 text-xs text-slate-500">
                <a href="{{ route('progress-report.index') }}"
                    class="font-semibold
                           hover:text-kanmo-600">
                    Progress Report
                </a>

                <span>/</span>

                <a href="{{ route('progress-report.source', [
                    'source' => $source,
                ]) }}"
                    class="font-semibold
                           hover:text-kanmo-600">
                    {{ $source }}
                </a>

                <span>/</span>

                <span class="font-bold text-slate-900">
                    {{ $groupName }}
                </span>
            </div>

            <a href="{{ route('progress-report.source', [
                'source' => $source,
            ]) }}"
                class="kanmo-btn-secondary">
                ← Back
            </a>
        </div>

        {{-- Summary --}}
        <section class="minimal-card mb-5 overflow-hidden">
            <div
                class="grid divide-y divide-stone-100
                       sm:grid-cols-3 sm:divide-x
                       sm:divide-y-0">
                <div class="px-5 py-5">
                    <p
                        class="text-[10px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        Headcount
                    </p>

                    <p class="mt-2 text-2xl
                               font-extrabold text-slate-900">
                        {{ number_format($headcount, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Total employee
                    </p>
                </div>

                <div class="px-5 py-5">
                    <p
                        class="text-[10px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        Completed
                    </p>

                    <p
                        class="mt-2 text-2xl
                               font-extrabold
                               text-emerald-600">
                        {{ number_format($completedEmployees, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Employee profile complete
                    </p>
                </div>

                <div class="px-5 py-5">
                    <p
                        class="text-[10px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        Not Completed
                    </p>

                    <p
                        class="mt-2 text-2xl
                               font-extrabold
                               text-rose-600">
                        {{ number_format($notCompletedEmployees, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Employee still needs completion
                    </p>
                </div>
            </div>
        </section>

        @php
            $statusMeta = match ($status ?? 'all') {
                'completed' => [
                    'label' => 'Completed',

                    'description' => 'Showing employees with complete HR and employee profiles.',

                    'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                ],

                'not_completed' => [
                    'label' => 'Not Completed',

                    'description' => 'Showing employees that still have incomplete required fields.',

                    'class' => 'bg-rose-50 text-rose-700 ring-rose-200',
                ],

                default => [
                    'label' => 'All Employees',

                    'description' => 'Showing all employees in this group.',

                    'class' => 'bg-slate-100 text-slate-700 ring-slate-200',
                ],
            };
        @endphp

        {{-- Employee Table --}}
        <section class="minimal-card overflow-hidden">
            {{-- Header --}}
            <div
                class="flex flex-col gap-4
                       border-b border-slate-200
                       px-5 py-4
                       lg:flex-row lg:items-center
                       lg:justify-between">
                <div>
                    <p
                        class="text-[10px] font-bold
                               uppercase tracking-wider
                               text-kanmo-500">
                        {{ $source }}
                    </p>

                    <h2 class="mt-1 text-sm
                               font-bold text-slate-900">
                        {{ $groupName }}

                    </h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex rounded-full
                            px-2.5 py-1 text-[11px]
                            font-bold ring-1 ring-inset
                            {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>

                        <span class="text-xs text-slate-500">
                            {{ $statusMeta['description'] }}
                        </span>
                    </div>


                    <p class="mt-1 text-xs
                               text-slate-500">
                        {{ $dimensionLabel }}
                        employee profile completion.
                    </p>
                </div>

                {{-- Search --}}
                <form action="{{ url()->current() }}" method="GET" class="flex w-full gap-2 lg:max-w-lg">
                    {{-- Pertahankan status ketika melakukan search --}}
                    <input type="hidden" name="status" value="{{ $status ?? 'all' }}">

                    <label for="employee-search" class="sr-only">
                        Search employee
                    </label>

                    <div class="relative flex-1">
                        <div
                            class="pointer-events-none absolute inset-y-0
                   left-0 flex items-center pl-3.5">
                            <svg class="h-4.5 w-4.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3a6 6 0 104.472 10.003l3.262
                       3.263a1 1 0 001.414-1.414
                       l-3.263-3.262A6 6 0 009 3z
                       M5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <input type="search" id="employee-search" name="search" value="{{ $search }}"
                            placeholder="Search NIP or employee name" autocomplete="off"
                            class="kanmo-input py-2.5 pl-10">
                    </div>

                    <button type="submit" class="kanmo-btn-primary">
                        Search
                    </button>

                    @if ($search !== '')
                        <a href="{{ url()->current() }}?status={{ urlencode($status ?? 'all') }}"
                            class="kanmo-btn-secondary">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            @if ($search !== '')
                <div
                    class="flex items-center
                           justify-between
                           border-b border-kanmo-100
                           bg-kanmo-50/70
                           px-5 py-3">
                    <p class="text-sm text-kanmo-800">
                        Search results for

                        <strong>
                            “{{ $search }}”
                        </strong>
                    </p>

                    <span class="text-xs font-bold
                               text-kanmo-600">
                        {{ number_format($employees->total(), 0, ',', '.') }}
                        results
                    </span>
                </div>
            @endif



            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px]
                           table-auto text-left">
                    <thead class="border-b border-stone-200
                               bg-stone-50/90">
                        <tr>
                            <th
                                class="w-16 px-4 py-3
                                       text-center text-xs
                                       font-bold text-slate-500">
                                No.
                            </th>

                            <th
                                class="min-w-[280px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Employee
                            </th>

                            <th
                                class="min-w-[220px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                PIC
                            </th>

                            <th
                                class="min-w-[180px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Company
                            </th>

                            <th
                                class="min-w-[150px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Status
                            </th>

                            <th
                                class="min-w-[280px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Overall Completion
                            </th>

                            <th
                                class="w-28 px-4 py-3 text-center
                                text-xs font-bold text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100
                               bg-white">
                        @forelse ($employees as $employee)
                            @php
                                $employeeName = $employee->display_name ?: 'Tanpa Nama';

                                $initial = strtoupper(substr(trim($employeeName), 0, 1));

                                $completion = min(max((float) $employee->overall_profile_completion, 0), 100);

                                $isComplete = $completion >= 100;
                            @endphp

                            <tr class="transition-colors
                                       hover:bg-kanmo-50/40">
                                <td class="px-4 py-3
                                           text-center">
                                    <span
                                        class="inline-flex h-8
                                               min-w-8 items-center
                                               justify-center
                                               rounded-lg
                                               bg-stone-100
                                               px-2 text-xs
                                               font-bold
                                               text-slate-600">
                                        {{ $employees->firstItem() + $loop->index }}
                                    </span>
                                </td>

                                {{-- Employee --}}
                                <td class="px-4 py-3">
                                    <div class="flex
                                               items-center gap-3">
                                        <div
                                            class="flex h-10 w-10
                                                   shrink-0
                                                   items-center
                                                   justify-center
                                                   rounded-xl
                                                   bg-kanmo-50
                                                   text-sm
                                                   font-extrabold
                                                   text-kanmo-600
                                                   ring-1 ring-inset
                                                   ring-kanmo-100">
                                            {{ $initial }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate
                                                       text-sm
                                                       font-bold
                                                       text-slate-900">
                                                {{ $employeeName }}
                                            </p>

                                            <div
                                                class="mt-1 flex
                                                       items-center
                                                       gap-2">
                                                <span
                                                    class="text-xs
                                                           text-slate-400">
                                                    NIP
                                                </span>

                                                <span
                                                    class="rounded-md
                                                           bg-stone-100
                                                           px-2 py-0.5
                                                           font-mono
                                                           text-xs
                                                           font-semibold
                                                           text-slate-600">
                                                    {{ $employee->employee_id }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- PIC --}}
                                <td class="px-4 py-3">
                                    <span
                                        class="text-sm
                                               font-semibold
                                               text-slate-700">
                                        {{ $employee->pic?->name ?? 'Belum ada PIC' }}
                                    </span>
                                </td>

                                {{-- Company --}}
                                <td class="px-4 py-3">
                                    <span class="text-sm
                                               text-slate-600">
                                        {{ $employee->company ?: '---' }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3">
                                    @if ($isComplete)
                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-emerald-50
                                                   px-2.5 py-1
                                                   text-[11px]
                                                   font-bold
                                                   text-emerald-700
                                                   ring-1 ring-inset
                                                   ring-emerald-200">
                                            Complete
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-rose-50
                                                   px-2.5 py-1
                                                   text-[11px]
                                                   font-bold
                                                   text-rose-700
                                                   ring-1 ring-inset
                                                   ring-rose-200">
                                            Not Completed
                                        </span>
                                    @endif
                                </td>

                                {{-- Overall Completion --}}
                                <td class="px-4 py-3">
                                    <div class="min-w-[250px]">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between">
                                            <span
                                                class="text-sm
                                                       font-extrabold
                                                       {{ $isComplete ? 'text-emerald-600' : 'text-orange-600' }}">
                                                {{ number_format($completion, 2, ',', '.') }}%
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-slate-400">
                                                {{ $employee->overall_profile_completion_filled }}
                                                /
                                                {{ $employee->overall_profile_completion_total }}
                                            </span>
                                        </div>

                                        <div
                                            class="h-2
                                                   overflow-hidden
                                                   rounded-full
                                                   bg-stone-200">
                                            <div class="h-full
                                                       rounded-full
                                                       {{ $isComplete ? 'bg-emerald-500' : 'bg-orange-500' }}"
                                                style="width:
                                                    {{ $completion }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action --}}
                                <td class="px-4 py-1">
                                    <div class="w-fit mx-auto">
                                        <button type="button"
                                            class="inline-flex min-h-9 cursor-pointer items-center justify-center
                                        gap-2 rounded bg-orange-500 px-3.5
                                        text-xs font-bold text-white transition
                                        hover:bg-orange-600 focus:outline-none
                                        focus:ring-4 focus:ring-orange-200"
                                            data-employee-details-button
                                            data-details-url="{{ route('dashboard.employee-details', [
                                            'employeeId' => $employee->employee_id,]) }}"
                                            aria-haspopup="dialog">

                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="px-6 py-16
                                           text-center
                                           text-sm
                                           text-slate-500">
                                    Employee tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div
                    class="border-t border-stone-200
                           bg-stone-50/70
                           px-5 py-4">
                    {{ $employees->onEachSide(1)->links() }}
                </div>
            @endif
        </section>

        {{-- Employee Details Modal --}}
        <div id="employee-details-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px]" data-employee-details-close></div>

            <div class="relative flex min-h-full items-center
               justify-center p-3 sm:p-6">
                <div class="relative flex max-h-[92vh] w-full
                   max-w-5xl flex-col overflow-hidden
                   rounded-2xl border border-slate-200
                   bg-slate-50 shadow-2xl"
                    role="dialog" aria-modal="true" aria-labelledby="employee-details-modal-title">
                    {{-- Modal Header --}}
                    <div
                        class="flex shrink-0 items-center justify-between
                       border-b border-slate-200 bg-white
                       px-5 py-4">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase
                               tracking-wider text-orange-500">
                                Employee Profile
                            </p>

                            <h2 id="employee-details-modal-title"
                                class="mt-1 text-sm font-extrabold
                               text-slate-900">
                                Employee Details
                            </h2>
                        </div>

                        <button type="button" data-employee-details-close
                            class="flex h-9 w-9 items-center
                           justify-center rounded-xl
                           text-slate-400 transition
                           hover:bg-slate-100
                           hover:text-slate-700"
                            aria-label="Close employee details">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal body --}}
                    <div id="employee-details-modal-body" class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5">
                        {{-- Loading --}}
                        <div class="flex min-h-[300px]
                           items-center justify-center"
                            data-employee-details-loading>
                            <div class="text-center">
                                <svg class="mx-auto h-7 w-7 animate-spin
                                   text-orange-500"
                                    viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>

                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0
                                   C5.373 0 0 5.373 0 12h4Z"></path>
                                </svg>

                                <p class="mt-3 text-sm text-slate-500">
                                    Loading employee details...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal =
                    document.getElementById('employee-details-modal');

                const modalBody =
                    document.getElementById('employee-details-modal-body');

                if (!modal || !modalBody) {
                    return;
                }

                let activeRequest = null;

                /*
                 * ============================================================
                 * OPEN MODAL
                 * ============================================================
                 */
                const openModal = () => {
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');

                    document.body.classList.add('overflow-hidden');
                };

                /*
                 * ============================================================
                 * CLOSE MODAL
                 * ============================================================
                 */
                const closeModal = () => {
                    activeRequest?.abort();

                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');

                    document.body.classList.remove('overflow-hidden');
                };

                /*
                 * ============================================================
                 * LOADING
                 * ============================================================
                 */
                const showLoading = () => {
                    modalBody.innerHTML = `
            <div
                class="flex min-h-[300px]
                       items-center justify-center"
            >
                <div class="text-center">
                    <svg
                        class="mx-auto h-7 w-7 animate-spin
                               text-orange-500"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0
                               C5.373 0 0 5.373 0 12h4Z"
                        ></path>
                    </svg>

                    <p class="mt-3 text-sm text-slate-500">
                        Loading employee details...
                    </p>
                </div>
            </div>
        `;
                };

                /*
                 * ============================================================
                 * INITIALIZE SEARCH / FILTER DALAM EMPLOYEE DETAILS
                 * ============================================================
                 */
                const initializeDetailsPanel = () => {
                    const panel =
                        modalBody.querySelector(
                            '[data-employee-details-panel]'
                        );

                    if (!panel) {
                        return;
                    }

                    const searchInput =
                        panel.querySelector(
                            '[data-details-search]'
                        );

                    const filterButtons =
                        panel.querySelectorAll(
                            '[data-details-filter]'
                        );

                    const rows =
                        panel.querySelectorAll(
                            '[data-details-row]'
                        );

                    const groups =
                        panel.querySelectorAll(
                            '[data-details-group]'
                        );

                    const emptyState =
                        panel.querySelector(
                            '[data-details-empty]'
                        );

                    let activeFilter = 'all';

                    const applyFilter = () => {
                        const keyword =
                            (
                                searchInput?.value ?? ''
                            )
                            .trim()
                            .toLowerCase();

                        let visibleRows = 0;

                        rows.forEach((row) => {
                            const status =
                                row.dataset.detailsStatus;

                            const searchText =
                                row.dataset.detailsSearchText ?? '';

                            const matchesStatus =
                                activeFilter === 'all' ||
                                status === activeFilter;

                            const matchesSearch =
                                keyword === '' ||
                                searchText.includes(keyword);

                            const visible =
                                matchesStatus &&
                                matchesSearch;

                            row.classList.toggle(
                                'hidden',
                                !visible
                            );

                            if (visible) {
                                visibleRows++;
                            }
                        });

                        /*
                         * Sembunyikan group yang tidak memiliki
                         * field visible.
                         */
                        groups.forEach((group) => {
                            const hasVisibleRows =
                                Array.from(
                                    group.querySelectorAll(
                                        '[data-details-row]'
                                    )
                                ).some(
                                    (row) =>
                                    !row.classList.contains(
                                        'hidden'
                                    )
                                );

                            group.classList.toggle(
                                'hidden',
                                !hasVisibleRows
                            );
                        });

                        emptyState?.classList.toggle(
                            'hidden',
                            visibleRows > 0
                        );
                    };

                    searchInput?.addEventListener(
                        'input',
                        applyFilter
                    );

                    filterButtons.forEach((button) => {
                        button.addEventListener(
                            'click',
                            () => {
                                activeFilter =
                                    button.dataset
                                    .detailsFilter;

                                filterButtons.forEach(
                                    (item) => {
                                        const isActive =
                                            item === button;

                                        item.setAttribute(
                                            'aria-pressed',
                                            isActive ?
                                            'true' :
                                            'false'
                                        );

                                        item.classList.toggle(
                                            'bg-white',
                                            isActive
                                        );

                                        item.classList.toggle(
                                            'text-slate-900',
                                            isActive
                                        );

                                        item.classList.toggle(
                                            'shadow-sm',
                                            isActive
                                        );

                                        item.classList.toggle(
                                            'text-slate-500',
                                            !isActive
                                        );
                                    }
                                );

                                applyFilter();
                            }
                        );
                    });
                };

                /*
                 * ============================================================
                 * LOAD EMPLOYEE DETAILS
                 * ============================================================
                 */
                const loadEmployeeDetails =
                    async (url) => {

                        activeRequest?.abort();

                        const controller =
                            new AbortController();

                        activeRequest =
                            controller;

                        openModal();
                        showLoading();

                        try {
                            const response = await fetch(
                                url, {
                                    headers: {
                                        Accept: 'text/html',

                                        'X-Requested-With': 'XMLHttpRequest',
                                    },

                                    signal: controller.signal,
                                }
                            );

                            if (!response.ok) {
                                throw new Error(
                                    `Request failed: ${response.status}`
                                );
                            }

                            const contentType =
                                response.headers.get(
                                    'content-type'
                                ) ?? '';

                            /*
                             * Mendukung endpoint yang return:
                             *
                             * return view(...)
                             *
                             * maupun:
                             *
                             * return response()->json([
                             *     'html' => ...
                             * ])
                             */
                            if (
                                contentType.includes(
                                    'application/json'
                                )
                            ) {
                                const data =
                                    await response.json();

                                modalBody.innerHTML =
                                    data.html ?? '';
                            } else {
                                modalBody.innerHTML =
                                    await response.text();
                            }

                            initializeDetailsPanel();

                        } catch (error) {
                            if (
                                error.name ===
                                'AbortError'
                            ) {
                                return;
                            }

                            console.error(
                                'Employee details failed:',
                                error
                            );

                            modalBody.innerHTML = `
                <div
                    class="rounded-2xl border
                           border-rose-200
                           bg-rose-50 px-6
                           py-12 text-center"
                >
                    <p
                        class="text-sm font-bold
                               text-rose-700"
                    >
                        Employee details gagal dimuat.
                    </p>

                    <p
                        class="mt-1 text-xs
                               text-rose-500"
                    >
                        Silakan coba kembali.
                    </p>
                </div>
            `;
                        } finally {
                            if (
                                activeRequest === controller
                            ) {
                                activeRequest = null;
                            }
                        }
                    };

                /*
                 * Menggunakan delegation agar tetap bekerja
                 * walaupun tabel berubah karena pagination/search.
                 */
                document.addEventListener(
                    'click',
                    (event) => {
                        if (
                            !(
                                event.target instanceof Element
                            )
                        ) {
                            return;
                        }

                        const detailsButton =
                            event.target.closest(
                                '[data-employee-details-button]'
                            );

                        if (detailsButton) {
                            event.preventDefault();

                            loadEmployeeDetails(
                                detailsButton.dataset
                                .detailsUrl
                            );

                            return;
                        }

                        if (
                            event.target.closest(
                                '[data-employee-details-close]'
                            )
                        ) {
                            closeModal();
                        }
                    }
                );

                /*
                 * ESC untuk close.
                 */
                document.addEventListener(
                    'keydown',
                    (event) => {
                        if (
                            event.key === 'Escape' &&
                            !modal.classList.contains(
                                'hidden'
                            )
                        ) {
                            closeModal();
                        }
                    }
                );
            });
        </script>
    </x-app-shell>
</x-layout>

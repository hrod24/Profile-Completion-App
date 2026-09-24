<x-layout :title="$title">
    <x-app-shell :title="'Not Completed Employees — ' . $source" :subtitle="'Employees requiring profile completion for ' . $source . '.'" full-width>
        {{-- Navigation --}}
        <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('progress-report.index') }}" class="font-semibold hover:text-kanmo-600">
                    Progress Report
                </a>

                <span>/</span>

                <a href="{{ route('progress-report.source', [
                    'source' => $source,
                ]) }}"
                    class="font-semibold hover:text-kanmo-600">
                    {{ $source }}
                </a>

                <span>/</span>

                <span class="font-bold text-slate-900">
                    Not Completed
                </span>
            </div>

            <a href="{{ route('progress-report.source', [
                'source' => $source,
            ]) }}"
                class="kanmo-btn-secondary">
                ← Back
            </a>
        </div>


        {{-- Header / Search --}}
        <section class="mb-5 overflow-hidden minimal-card">
            <div class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p
                        class="text-[10px] font-bold
                               uppercase tracking-wider
                               text-rose-500">
                        Requires Attention
                    </p>

                    <div class="flex items-baseline gap-3 mt-1">
                        <h2 class="text-lg font-extrabold text-slate-900">
                            Not Completed Employees
                        </h2>

                        <span
                            class="rounded-full
                                   bg-rose-50
                                   px-2.5 py-1
                                   text-xs font-bold
                                   text-rose-600
                                   ring-1 ring-inset
                                   ring-rose-200">
                            {{ number_format($totalNotCompletedEmployees, 0, ',', '.') }}
                        </span>
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        Missing data below only shows
                        fields that must be completed
                        by the employee.
                    </p>
                </div>

                <form action="{{ url()->current() }}" method="GET" class="flex w-full gap-2 lg:max-w-lg">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none
                                   absolute left-3.5
                                   top-1/2 h-4 w-4
                                   -translate-y-1/2
                                   text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.4
                                   a6.75 6.75 0 11-13.5 0
                                   6.75 6.75 0 0113.5 0z" />
                        </svg>

                        <input type="search" name="search" value="{{ $search }}"
                            placeholder="Search NIP, name or email..." class="kanmo-input py-2.5 pl-10">
                    </div>

                    <button type="submit" class="kanmo-btn-primary">
                        Search
                    </button>

                    @if ($search !== '')
                        <a href="{{ url()->current() }}" class="kanmo-btn-secondary">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </section>


        {{-- Table --}}
        <section class="overflow-hidden minimal-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1450px]
                           table-auto text-left">
                    <thead class="border-b border-stone-200 bg-stone-50/90">
                        <tr>
                            <th class="w-16 px-4 py-3 text-xs font-bold text-center text-slate-500">
                                No.
                            </th>

                            <th class="w-32 px-4 py-3 text-xs font-bold text-center text-slate-500">
                                Reminder
                            </th>

                            <th
                                class="min-w-[220px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Name
                            </th>

                            <th
                                class="min-w-[210px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Division
                            </th>

                            <th
                                class="min-w-[210px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Department
                            </th>

                            <th
                                class="min-w-[180px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Email
                            </th>

                            <th
                                class="min-w-[580px]
                                       px-4 py-3 text-xs
                                       font-bold text-slate-500">
                                Data
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-stone-100" data-reminder-table-body>
                        @forelse ($employees as $employee)
                            @php
                                $employeeName = $employee->display_name ?: 'Name not available';

                                $missingFields = $employee->missing_employee_fields ?? [];

                                $missingLabels = collect($missingFields)->pluck('label')->values()->all();

                                $canRemind = filled($employee->primary_email) && count($missingLabels) > 0;
                            @endphp

                            <tr class="transition-colors hover:bg-kanmo-50/35" data-employee-row>
                                {{-- No --}}
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex items-center justify-center h-8 px-2 text-xs font-bold rounded-lg min-w-8 bg-stone-100 text-slate-600"
                                        data-row-number>
                                        {{ $loop->index + 1 }}
                                    </span>
                                </td>

                                {{-- Reminder --}}
                                <td class="px-4 py-4 text-center">
                                    <button type="button" data-teams-reminder
                                        data-email="{{ $employee->primary_email }}" data-name="{{ $employeeName }}"
                                        data-nip="{{ $employee->employee_id }}"
                                        data-missing="{{ implode(', ', $missingLabels) }}" @disabled(!$canRemind)
                                        class="
                                            inline-flex items-center
                                            justify-center gap-2
                                            rounded px-3 py-3
                                            text-xs font-bold
                                            transition

                                            {{ $canRemind
                                                ? 'cursor-pointer bg-[#676cdd] hover:bg-[#444791] text-white '
                                                : 'cursor-not-allowed bg-slate-100 text-slate-400' }}
                                        "
                                        title="{{ $canRemind
                                            ? 'Send reminder via Microsoft Teams'
                                            : (blank($employee->primary_email)
                                                ? 'Email is not available'
                                                : 'Employee profile data is already complete') }}">
                                        Teams
                                    </button>
                                </td>

                                {{-- Name --}}
                                <td class="px-4 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $employeeName }}
                                    </p>
                                </td>

                                {{-- Division --}}
                                <td class="px-4 py-4">
                                    <span class="text-sm text-slate-700">
                                        {{ $employee->businessUnit?->business_unit_name ?? '---' }}
                                    </span>
                                </td>

                                {{-- Department --}}
                                <td class="px-4 py-4">
                                    <span class="text-sm text-slate-700">
                                        {{ $employee->department?->department_name ?? '---' }}
                                    </span>
                                </td>

                                {{-- Email --}}
                                <td class="px-4 py-4">
                                    @if ($employee->primary_email)
                                        <span class="text-sm break-all text-slate-700">
                                            {{ $employee->primary_email }}
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold text-rose-500">
                                            Email not available
                                        </span>
                                    @endif
                                </td>

                                {{-- Missing Employee Data --}}
                                <td class="px-4 py-4">
                                    @if (count($missingFields) > 0)
                                        <div
                                            class="flex
                                                   flex-wrap
                                                   gap-1.5">
                                            @foreach ($missingFields as $field)
                                                <span
                                                    class="inline-flex
                                                           rounded-md
                                                           bg-rose-50
                                                           px-2 py-1
                                                           text-[11px]
                                                           font-semibold
                                                           text-rose-700
                                                           ring-1
                                                           ring-inset
                                                           ring-rose-200">
                                                    {{ $field['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-sm text-center text-slate-500">
                                    No incomplete employees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>


        {{-- Teams Reminder --}}
        <script>
            document.addEventListener(
                'click',
                (event) => {
                    if (
                        !(event.target instanceof Element)
                    ) {
                        return;
                    }

                    const button =
                        event.target.closest(
                            '[data-teams-reminder]'
                        );

                    if (
                        !button ||
                        button.disabled
                    ) {
                        return;
                    }

                    const email =
                        button.dataset.email
                        ?.trim();

                    const name =
                        button.dataset.name
                        ?.trim() ||
                        'Employee';

                    const nip =
                        button.dataset.nip
                        ?.trim() ||
                        '';

                    const missing =
                        button.dataset.missing
                        ?.trim() ||
                        '';

                    if (!email) {
                        return;
                    }

                    /**
                     * ============================================
                     * TEAMS MESSAGE
                     * ============================================
                     */

                    let message = `Hi ${name}`;

                    if (nip) {
                        message += ` (${nip})`;
                    }

                    message += `,\n\n`;
                    message += `This is a reminder to complete your Employee Profile.`;

                    if (missing) {
                        message +=
                            `\n\nThe following data is still incomplete:\n` +
                            `${missing}`;
                    }


                    message +=
                        `\n\nPlease complete the missing information as soon as possible.` +
                        `\n\nPlease complete your Employee Profile here:` +
                        `\n\n<a href="https://epc.kanmoemployeeportal.com/login">Complete Employee Profile</a>` +
                        `\n\nUsername: ${nip}` +
                        `\nPassword: ${nip}` +
                        `\n\nThank you.`;

                    const teamsLink =
                        `https://teams.microsoft.com/l/chat/0/0` +
                        `?users=${encodeURIComponent(email)}` +
                        `&message=${encodeURIComponent(message)}`;

                    /*
                     * Buka Microsoft Teams.
                     */
                    window.open(
                        teamsLink,
                        '_blank',
                        'noopener,noreferrer'
                    );

                    /*
                     * ============================================
                     * UPDATE BUTTON
                     * ============================================
                     *
                     * Jangan menggunakan "Sent", karena aplikasi
                     * hanya membuka chat Teams dan belum tahu
                     * apakah message benar-benar dikirim.
                     */

                    button.classList.remove(
                        'bg-[#676cdd]',
                        'hover:bg-[#444791]'
                    );

                    button.classList.add(
                        'bg-slate-300',
                        'text-slate-50'
                    );

                    button.textContent = 'Opened';

                    /*
                     * ============================================
                     * MOVE EMPLOYEE TO BOTTOM
                     * ============================================
                     */

                    const row =
                        button.closest(
                            '[data-employee-row]'
                        );

                    const tableBody =
                        document.querySelector(
                            '[data-reminder-table-body]'
                        );

                    if (
                        row &&
                        tableBody
                    ) {
                        /*
                         * appendChild tidak membuat duplicate.
                         *
                         * Karena row sudah ada di tbody,
                         * browser akan memindahkannya
                         * menjadi child terakhir.
                         */
                        tableBody.appendChild(row);

                        /*
                         * Berikan highlight ringan sebagai
                         * tanda bahwa employee sudah dibuka
                         * melalui Teams.
                         */
                        row.classList.add(
                            'bg-slate-50/70'
                        );

                        /*
                         * ========================================
                         * RENUMBER TABLE
                         * ========================================
                         */

                        const rows =
                            tableBody.querySelectorAll(
                                '[data-employee-row]'
                            );

                        rows.forEach(
                            (employeeRow, index) => {
                                const numberElement =
                                    employeeRow.querySelector(
                                        '[data-row-number]'
                                    );

                                if (
                                    numberElement
                                ) {
                                    numberElement.textContent =
                                        index + 1;
                                }
                            }
                        );
                    }
                }
            );
        </script>
    </x-app-shell>
</x-layout>

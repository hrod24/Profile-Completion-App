{{-- Statistic cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">

    {{-- Total employee --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Total Employee
                </p>

                <p class="mt-3 text-3xl font-bold text-slate-900">
                    {{ number_format($totalEmployees, 0, ',', '.') }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    All Registered Employees
                </p>
            </div>

            <div class="rounded-lg bg-blue-100 p-3 text-blue-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-4a4 4 0 11-8 0 4 4 0 018 0zm6 1a3 3 0 10-4-2.83">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Completed employee --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Complete
                </p>

                <p class="mt-3 text-3xl font-bold text-emerald-600">
                    {{ number_format($completedEmployees, 0, ',', '.') }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Employee has completed profile data
                </p>
            </div>

            <div class="rounded-lg bg-emerald-100 p-3 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                    </path>
                </svg>
            </div>
        </div>
    </div>


    {{-- Pending employee --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Not Complete
                </p>

                <p class="mt-3 text-3xl font-bold text-amber-600">
                    {{ number_format($pendingEmployees, 0, ',', '.') }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Employee has not completed profile data
                </p>
            </div>

            <div class="rounded-lg bg-amber-100 p-3 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Percentage --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Percentage Completed
                </p>

                <p class="mt-3 text-3xl font-bold text-violet-600">
                    {{ number_format($completionPercentage, 2, ',', '.') }}%
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Of all employees who have filled in their profile data
                </p>
            </div>

            <div class="rounded-lg bg-violet-100 p-3 text-violet-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M7 20L17 4M6 9a3 3 0 100-6 3 3 0 000 6zm12 12a3 3 0 100-6 3 3 0 000 6z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Data HR belum lengkap --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    HR Data is Incomplete
                </p>

                <p class="mt-3 text-3xl font-bold text-red-600">
                    {{ number_format($hrIncompleteEmployees, 0, ',', '.') }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    There are still empty columns that HR must fill in.
                </p>
            </div>

            <div class="rounded-lg bg-red-100 p-3 text-red-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m9-1.386
                           c0 4.97-4.03 9-9 9s-9-4.03-9-9
                           4.03-9 9-9 9 4.03 9 9z
                           M12 16.5h.008v.008H12V16.5z">
                    </path>
                </svg>
            </div>
        </div>
    </div>
</div>

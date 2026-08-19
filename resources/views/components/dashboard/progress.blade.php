<section class="mt-8 rounded-xl border flex justify-between border-slate-200 bg-white p-6 shadow-sm">

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

    <div class="progress w-full h-fit my-auto p-6">
        <div class="flex flex-col gap-2
               sm:flex-row sm:items-center
               sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Employee Profile Completion Progress
                </h2>

            </div>

            <span class="text-xl font-bold text-green-600">
                {{ number_format($fieldCompletionPercentage, 2, ',', '.') }}%
            </span>
        </div>

        <div class="mt-5 h-4 w-full overflow-hidden
               rounded-full bg-slate-200" role="progressbar"
            aria-label="Employee profile field completion" aria-valuenow="{{ $fieldCompletionPercentage }}"
            aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-green-600
                   transition-all duration-500"
                style="width: {{ min($fieldCompletionPercentage, 100) }}%">
            </div>
        </div>

        <div
            class="mt-3 flex flex-col gap-1
               text-xs text-slate-500
               sm:flex-row sm:justify-between">
        </div>
    </div>
</section>

<section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">
                Employee Profile Completion Progress
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ number_format($fullyCompleteEmployees, 0, ',', '.') }}
                dari
                {{ number_format($totalEmployees, 0, ',', '.') }}
                Employees already have complete HR data and employee data.
            </p>
        </div>

        <span class="text-xl font-bold text-green-600">
            {{ number_format($fullCompletionPercentage, 2, ',', '.') }}%
        </span>
    </div>

    <div class="mt-5 h-4 w-full overflow-hidden rounded-full bg-slate-200" role="progressbar"
        aria-label="Progress kelengkapan profil employee" aria-valuenow="{{ $fullCompletionPercentage }}" aria-valuemin="0"
        aria-valuemax="100">

        <div class="h-full rounded-full bg-green-600 transition-all duration-500"
            style="width: {{ min($fullCompletionPercentage, 100) }}%">
        </div>
    </div>

    <div class="mt-3 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:justify-between">
        <span>
            {{ number_format($fullyIncompleteEmployees, 0, ',', '.') }}
            incomplete profile
        </span>

        <span>
            Complete HR data + complete employee data
        </span>
    </div>
</section>

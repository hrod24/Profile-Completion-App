<x-layout :title="$title">
    <x-app-shell title="Upload Employee Excel"
        subtitle="Insert new employee records and update existing records using Employee ID as the unique reference.">
        <x-slot:actions>
            <a href="{{ route('dashboard') }}" class="kanmo-btn-secondary">Back to Dashboard</a>
        </x-slot:actions>

        <div class="mx-auto max-w-4xl">
            @if (session('success'))
                <div class="minimal-card mb-4 border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    <p class="font-bold">Import Successful</p>
                    <p class="mt-1">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="minimal-card mb-4 border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-bold">Import Failed</p>
                    <p class="mt-1">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="minimal-card mb-4 border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-bold">Invalid File</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="minimal-card overflow-hidden">

                <div class="border-b flex justify-between border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Select Excel File</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">The system reads Employee Details, Employee ID,
                            and columns marked as Mandatory.</p>
                    </div>

                    <button type="button" id="synchronize-account-button"
                        data-start-url="{{ route('employee.accounts.synchronize.start') }}"
                        data-chunk-url="{{ route('employee.accounts.synchronize.chunk') }}" class="kanmo-btn-primary">
                        Synchronize Employee Accounts
                    </button>

                    <div id="synchronize-account-status" class="mt-3 hidden text-sm text-slate-600"></div>
                </div>

                <form id="employee-excel-import-form" action="{{ route('employee.import.start') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-6 p-6"
                    data-start-url="{{ route('employee.import.start') }}"
                    data-chunk-url="{{ route('employee.import.chunk') }}"
                    data-finish-url="{{ route('employee.import.finish') }}">
                    @csrf

                    <div>
                        <label for="excel_file" class="kanmo-label">Employee File</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required
                            class="block w-full cursor-pointer rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 file:mr-4 file:border-0 file:bg-kanmo-500 file:px-5 file:py-3 file:font-bold file:text-white hover:file:bg-kanmo-600 focus:outline-none focus:ring-4 focus:ring-kanmo-100">
                        <p class="kanmo-help">XLSX/XLS format, maximum 20 MB. Keep the sheet, Mandatory row, and header
                            positions unchanged.</p>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <h3 class="text-sm font-bold text-amber-900">Import Rules</h3>
                        <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm leading-6 text-amber-800">
                            <li>Existing Employee IDs will be updated.</li>
                            <li>New Employee IDs will be inserted.</li>
                            <li>Empty values will not erase existing data.</li>
                            <li>Any conflict will cancel the entire import.</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <div id="employee-import-progress"
                            class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-sm font-bold text-slate-900">
                                Importing Employee Data
                            </p>

                            <p id="employee-import-progress-text" class="mt-1 text-sm text-slate-600">
                                Preparing import...
                            </p>
                        </div>

                        <button type="submit" class="kanmo-btn-primary" data-import-submit>
                            Upload and Import
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </x-app-shell>
</x-layout>

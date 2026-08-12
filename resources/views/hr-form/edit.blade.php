<x-layout title="Edit HR Employee Profile">
    @php
        /*
         * Field dependent select.
         */
        $businessUnitFieldName = 'business_unit_org_element_1';

        $departmentFieldName = 'department_org_element_2';

        $selectedBusinessUnit = old($businessUnitFieldName, $employeeData[$businessUnitFieldName] ?? '');

        $selectedDepartment = old($departmentFieldName, $employeeData[$departmentFieldName] ?? '');

        $initialDepartmentOptions = $departmentsByBusinessUnit[$selectedBusinessUnit] ?? [];

        /*
         * Perhitungan progress HR.
         */
        $allFields = collect($groups)->flatMap(fn(array $group) => $group['fields']);

        $totalFields = $allFields->count();

        $completedFields = $allFields
            ->filter(function (array $field) use ($employeeData): bool {
                $fieldName = $field['name'];

                $value = old($fieldName, $employeeData[$fieldName] ?? null);

                return $value !== null && trim((string) $value) !== '';
            })
            ->count();

        $completionPercentage = $totalFields > 0 ? (int) round(($completedFields / $totalFields) * 100) : 0;
    @endphp

    <x-app-shell title="Edit HR Employee Profile" subtitle="Complete the remaining HR fields and save the changes.">
        <x-slot:actions>
            <a href="{{ route('hr-form.index', request()->only('pic')) }}"
                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                </svg>
                Back to Employee List
            </a>
        </x-slot:actions>

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800"
                role="alert">
                <p class="font-bold">The data could not be saved</p>
                <p class="mt-1 text-xs leading-5">Please review the highlighted fields.</p>
            </div>
        @endif

        <section class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-5 sm:grid-cols-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Employee</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">
                        {{ $employee->display_name ?: 'Name not available' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Employee ID</p>
                    <p class="mt-1 font-mono text-sm font-bold text-slate-900">{{ $employee->employee_id }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">PIC</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $employee->pic_name }}</p>
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-700">HR Data Completion</p>
                        <p class="mt-1 text-[11px] text-slate-500" data-hr-progress-count>{{ $completedFields }} of
                            {{ $totalFields }} fields completed</p>
                    </div>
                    <p class="text-2xl font-extrabold {{ $completionPercentage >= 100 ? 'text-emerald-600' : 'text-orange-600' }}"
                        data-hr-progress-text>{{ $completionPercentage }}%</p>
                </div>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuemin="0"
                    aria-valuemax="100" aria-valuenow="{{ $completionPercentage }}" data-hr-progress-track>
                    <div class="h-full rounded-full {{ $completionPercentage >= 100 ? 'bg-emerald-500' : 'bg-orange-500' }} transition-all duration-300"
                        style="width: {{ $completionPercentage }}%" data-hr-progress-bar></div>
                </div>
            </div>
        </section>

        <form action="{{ route('hr-form.update', $employee->employee_id) }}" method="POST" data-hr-form>
            @csrf
            @method('PUT')
            <input type="hidden" name="_pic_filter" value="{{ request('pic') }}">

            <input type="hidden" name="_search_filter" value="{{ request('search') }}">

            <div class="space-y-5">
                @foreach ($groups as $groupIndex => $group)
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-start gap-3 border-b border-slate-200 px-5 py-4">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-extrabold text-orange-600 ring-1 ring-inset ring-orange-100">{{ $groupIndex + 1 }}</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">{{ $group['title'] }}</h2>
                                @if ($group['description'])
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $group['description'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-x-5 gap-y-5 p-5 sm:grid-cols-2">
                            @foreach ($group['fields'] as $field)
                                @php
                                    $fieldName = $field['name'];
                                    $fieldValue = old($fieldName, $employeeData[$fieldName] ?? '');
                                    $hasError = $errors->has($fieldName);
                                    $isWide = $field['type'] === 'textarea';
                                    $isMissing = $fieldValue === '' || $fieldValue === null;
                                    $baseClass =
                                        'block w-full rounded-xl border px-3.5 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-400 focus:border-orange-500 focus:ring-4 focus:ring-orange-100';
                                    $stateClass = $hasError
                                        ? 'border-rose-400 bg-rose-50/40'
                                        : ($isMissing
                                            ? 'border-amber-300 bg-amber-50/35'
                                            : 'border-slate-300 bg-white');
                                @endphp

                                <div class="{{ $isWide ? 'sm:col-span-2' : '' }}">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <label for="{{ $fieldName }}"
                                            class="block text-sm font-bold text-slate-700">{{ $field['label'] }} <span
                                                class="text-rose-500">*</span></label>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $isMissing ? 'bg-amber-100 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}"
                                            data-field-state>
                                            {{ $isMissing ? 'Incomplete' : 'Completed' }}
                                        </span>
                                    </div>



                                    @if ($fieldName === $businessUnitFieldName)
                                        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                            data-business-unit-field data-hr-required-field
                                            class="{{ $baseClass }} {{ $stateClass }}">
                                            <option value="">Select a business unit</option>

                                            @foreach ($businessUnits as $businessUnit)
                                                <option value="{{ $businessUnit->code }}" @selected((string) $fieldValue === (string) $businessUnit->code)>
                                                    {{ $businessUnit->name }} ({{ $businessUnit->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif ($fieldName === $departmentFieldName)
                                        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                            data-department-field data-initial-department="{{ $selectedDepartment }}"
                                            data-hr-required-field @disabled($selectedBusinessUnit === '')
                                            class="{{ $baseClass }} {{ $stateClass }} disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">
                                                {{ $selectedBusinessUnit === '' ? 'Select a business unit first' : 'Select a department' }}
                                            </option>

                                            @foreach ($initialDepartmentOptions as $department)
                                                <option value="{{ $department['code'] }}" @selected((string) $fieldValue === (string) $department['code'])>
                                                    {{ $department['name'] }}
                                                    —
                                                    {{ $department['code'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif ($field['type'] === 'select')
                                        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                            class="{{ $baseClass }} {{ $stateClass }}" data-hr-required-field>
                                            <option value="">Select a value</option>

                                            @foreach ($field['options'] as $option)
                                                <option value="{{ $option }}" @selected((string) $fieldValue === (string) $option)>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif ($field['type'] === 'textarea')
                                        <textarea id="{{ $fieldName }}" name="{{ $fieldName }}" rows="{{ $field['rows'] }}" required
                                            placeholder="{{ $field['placeholder'] }}" class="{{ $baseClass }} {{ $stateClass }} resize-y"
                                            data-hr-required-field>{{ $fieldValue }}</textarea>
                                    @else
                                        <input id="{{ $fieldName }}" name="{{ $fieldName }}"
                                            type="{{ $field['type'] }}" value="{{ $fieldValue }}" required
                                            @if ($field['inputmode']) inputmode="{{ $field['inputmode'] }}" @endif
                                            placeholder="{{ $field['placeholder'] }}"
                                            class="{{ $baseClass }} {{ $stateClass }}" data-hr-required-field>
                                    @endif

                                    @error($fieldName)
                                        <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div
                class="sticky bottom-4 z-20 mt-6 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-[0_16px_50px_rgba(15,23,42,0.14)] backdrop-blur sm:flex sm:items-center sm:justify-between sm:px-4">
                <p class="mb-3 text-xs leading-5 text-slate-500 sm:mb-0">Once all HR fields are complete, the employee
                    will automatically be removed from this list.</p>

                <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-6 text-sm font-bold text-white shadow-[0_8px_20px_rgba(249,115,22,0.22)] transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200 disabled:cursor-not-allowed cursor-pointer disabled:opacity-70 sm:w-auto"
                    data-save-hr-button>
                    <svg class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" data-save-hr-spinner
                        aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span data-save-hr-label>Save Data</span>
                </button>
            </div>
        </form>
    </x-app-shell>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('[data-hr-form]');
                if (!form) return;

                const fields = Array.from(form.querySelectorAll('[data-hr-required-field]'));
                const progressText = document.querySelector('[data-hr-progress-text]');
                const progressCount = document.querySelector('[data-hr-progress-count]');
                const progressTrack = document.querySelector('[data-hr-progress-track]');
                const progressBar = document.querySelector('[data-hr-progress-bar]');
                const saveButton = form.querySelector('[data-save-hr-button]');
                const saveSpinner = form.querySelector('[data-save-hr-spinner]');
                const saveLabel = form.querySelector('[data-save-hr-label]');
                const businessUnitField = form.querySelector('[data-business-unit-field]');
                const departmentField = form.querySelector('[data-department-field]');

                const departmentsByBusinessUnit =
                    {{ \Illuminate\Support\Js::from($departmentsByBusinessUnit) }};

                const hasValue = (field) => String(field.value ?? '').trim() !== '';

                const updateProgress = () => {
                    const completed = fields.filter(hasValue).length;
                    const total = fields.length;
                    const percentage = total ? Math.round((completed / total) * 100) : 0;

                    progressText.textContent = `${percentage}%`;
                    progressCount.textContent = `${completed} of ${total} fields completed`;
                    progressTrack.setAttribute('aria-valuenow', String(percentage));
                    progressBar.style.width = `${percentage}%`;

                    progressText.classList.toggle('text-emerald-600', percentage >= 100);
                    progressText.classList.toggle('text-orange-600', percentage < 100);
                    progressBar.classList.toggle('bg-emerald-500', percentage >= 100);
                    progressBar.classList.toggle('bg-orange-500', percentage < 100);

                    fields.forEach((field) => {
                        const state = field.closest('div')?.querySelector('[data-field-state]');
                        if (!state) return;
                        const filled = hasValue(field);
                        state.textContent = filled ? 'Completed' : 'Incomplete';
                        state.className = filled ?
                            'rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700' :
                            'rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700';
                    });
                };

                const createDepartmentOption = (department) => {
                    const option = document.createElement('option');

                    option.value = department.code;
                    option.textContent =
                        `${department.name} — ${department.code}`;

                    return option;
                };

                const renderDepartmentOptions = (
                    preserveInitialValue = false
                ) => {
                    if (!businessUnitField || !departmentField) {
                        return;
                    }

                    const businessUnitCode =
                        String(businessUnitField.value ?? '').trim();

                    const selectedDepartment = preserveInitialValue ?
                        String(
                            departmentField.dataset.initialDepartment ?? ''
                        ) :
                        '';

                    departmentField.innerHTML = '';

                    const placeholder = document.createElement('option');

                    placeholder.value = '';

                    if (businessUnitCode === '') {
                        placeholder.textContent =
                            'Select a business unit first';

                        departmentField.appendChild(placeholder);
                        departmentField.disabled = true;
                        departmentField.value = '';

                        updateProgress();

                        return;
                    }

                    placeholder.textContent = 'Select a department';

                    departmentField.appendChild(placeholder);
                    departmentField.disabled = false;

                    const departments =
                        departmentsByBusinessUnit[businessUnitCode] ?? [];

                    departments.forEach((department) => {
                        departmentField.appendChild(
                            createDepartmentOption(department)
                        );
                    });

                    const departmentStillAvailable =
                        departments.some(
                            (department) =>
                            String(department.code) ===
                            selectedDepartment
                        );

                    departmentField.value = departmentStillAvailable ?
                        selectedDepartment :
                        '';

                    updateProgress();
                };

                businessUnitField?.addEventListener(
                    'change',
                    () => {
                        /*
                         * Ketika Business Unit berubah,
                         * Department lama harus dikosongkan.
                         */
                        if (departmentField) {
                            departmentField.dataset.initialDepartment = '';
                        }

                        renderDepartmentOptions(false);
                    }
                );

                fields.forEach((field) => {
                    field.addEventListener('input', updateProgress);
                    field.addEventListener('change', updateProgress);
                });

                form.addEventListener('submit', function() {
                    if (!form.checkValidity()) return;
                    saveButton.disabled = true;
                    saveSpinner?.classList.remove('hidden');
                    if (saveLabel) saveLabel.textContent = 'Saving HR Data...';
                });

                renderDepartmentOptions(true);
                updateProgress();
            });
        </script>
    @endpush
</x-layout>

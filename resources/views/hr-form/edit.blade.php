<x-layout title="Edit HR Employee Profile">

    @push('styles')
        <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet">

        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    @endpush


    @php
        $businessUnitFieldName = 'business_unit_org_element_1';

        $departmentFieldName = 'department_org_element_2';

        $selectedBusinessUnit = old($businessUnitFieldName, $employeeData[$businessUnitFieldName] ?? '');

        $selectedDepartment = old($departmentFieldName, $employeeData[$departmentFieldName] ?? '');

        $initialDepartmentOptions = $departmentsByBusinessUnit[$selectedBusinessUnit] ?? [];

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

        $activeTab = old('_form_section', request('tab', 'employment'));

        $employeeRequiredFields = $employeeRequiredFields ?? config('employee.employee_required_fields', []);

        $isEmployeeRequired = fn(string $field): bool => in_array($field, $employeeRequiredFields, true);
    @endphp


    <x-app-shell title="Edit Employee Profile" subtitle="Manage employment and personal employee information.">

        <x-slot:actions>
            <a href="{{ route('hr-form.index', request()->only('pic', 'search')) }}"
                class="inline-flex min-h-10
                       items-center justify-center
                       gap-2 rounded-xl border
                       border-slate-300 bg-white
                       px-4 text-xs font-bold
                       text-slate-700 transition
                       hover:border-orange-200
                       hover:bg-orange-50
                       hover:text-orange-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
                </svg>

                Back to Employee List
            </a>
        </x-slot:actions>


        {{-- ===================================================== --}}
        {{-- ALERT --}}
        {{-- ===================================================== --}}

        @if (session('success'))
            <div class="mb-5 rounded-2xl border
                       border-emerald-200 bg-emerald-50
                       px-4 py-4 text-sm
                       text-emerald-800"
                role="alert">
                <p class="font-bold">
                    Data saved successfully
                </p>

                <p class="mt-1 text-xs leading-5">
                    {{ session('success') }}
                </p>
            </div>
        @endif


        @if ($errors->any())
            <div class="mb-5 rounded-2xl border
                       border-rose-200 bg-rose-50
                       px-4 py-4 text-sm
                       text-rose-800"
                role="alert">
                <p class="font-bold">
                    The data could not be saved
                </p>

                <p class="mt-1 text-xs leading-5">
                    Please review the highlighted fields.
                </p>
            </div>
        @endif


        {{-- ===================================================== --}}
        {{-- EMPLOYEE HEADER --}}
        {{-- ===================================================== --}}

        <section
            class="mb-5 overflow-hidden
                   rounded-2xl border
                   border-slate-200 bg-white
                   shadow-sm">
            <div
                class="grid gap-4 border-b
                       border-slate-200
                       px-5 py-5
                       sm:grid-cols-3">
                <div>
                    <p
                        class="text-[11px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        Employee
                    </p>

                    <p class="mt-1 text-sm font-bold
                               text-slate-900">
                        {{ $employee->display_name ?: 'Name not available' }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-[11px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        Employee ID
                    </p>

                    <p
                        class="mt-1 font-mono
                               text-sm font-bold
                               text-slate-900">
                        {{ $employee->employee_id }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-[11px] font-bold
                               uppercase tracking-wider
                               text-slate-400">
                        PIC
                    </p>

                    <p class="mt-1 text-sm font-bold
                               text-slate-900">
                        {{ $employee->pic_name }}
                    </p>
                </div>
            </div>


            <div class="px-5 py-4">
                <div
                    class="flex flex-col gap-3
                           sm:flex-row
                           sm:items-center
                           sm:justify-between">
                    <div>
                        <p class="text-xs font-bold
                                   text-slate-700">
                            HR Data Completion
                        </p>

                        <p class="mt-1 text-[11px]
                                   text-slate-500"
                            data-hr-progress-count>
                            {{ $completedFields }}
                            of
                            {{ $totalFields }}
                            fields completed
                        </p>
                    </div>

                    <p class="text-2xl font-extrabold
                        {{ $completionPercentage >= 100 ? 'text-emerald-600' : 'text-orange-600' }}"
                        data-hr-progress-text>
                        {{ $completionPercentage }}%
                    </p>
                </div>

                <div class="mt-3 h-2 overflow-hidden
                           rounded-full bg-slate-200"
                    role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $completionPercentage }}"
                    data-hr-progress-track>
                    <div class="h-full rounded-full
                        {{ $completionPercentage >= 100 ? 'bg-emerald-500' : 'bg-orange-500' }}
                        transition-all duration-300"
                        style="width: {{ $completionPercentage }}%" data-hr-progress-bar></div>
                </div>
            </div>
        </section>


        {{-- ===================================================== --}}
        {{-- TAB NAVIGATION --}}
        {{-- ===================================================== --}}

        <div class="mb-5 border-b
                   border-slate-200" data-employee-edit-tabs>
            <nav class="-mb-px flex gap-6">

                <button type="button" data-edit-tab="employment"
                    class="border-b-2 px-1 py-4
                           text-sm font-bold transition
                        {{ $activeTab === 'employment'
                            ? 'border-orange-500 text-orange-600'
                            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    Employment Info
                </button>


                <button type="button" data-edit-tab="personal"
                    class="border-b-2 px-1 py-4
                           text-sm font-bold transition
                        {{ $activeTab === 'personal'
                            ? 'border-orange-500 text-orange-600'
                            : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    Personal Info
                </button>

            </nav>
        </div>


        {{-- ===================================================== --}}
        {{-- EMPLOYMENT INFO --}}
        {{-- ===================================================== --}}

        <div data-edit-tab-panel="employment" @if ($activeTab !== 'employment') hidden @endif>
            <form
                action="{{ route('hr-form.update', $employee->employee_id) }}"
                method="POST" data-hr-form>
                @csrf
                @method('PUT')

                <input type="hidden" name="_form_section" value="employment">

                <input type="hidden" name="_pic_filter" value="{{ request('pic') }}">

                <input type="hidden" name="_search_filter" value="{{ request('search') }}">


                <div class="space-y-5">

                    @foreach ($groups as $groupIndex => $group)
                        <section
                            class="overflow-hidden
                                   rounded-2xl border
                                   border-slate-200
                                   bg-white shadow-sm">
                            <div
                                class="flex items-start
                                       gap-3 border-b
                                       border-slate-200
                                       px-5 py-4">
                                <span
                                    class="flex h-9 w-9
                                           shrink-0 items-center
                                           justify-center
                                           rounded-xl
                                           bg-orange-50
                                           text-sm
                                           font-extrabold
                                           text-orange-600
                                           ring-1 ring-inset
                                           ring-orange-100">
                                    {{ $groupIndex + 1 }}
                                </span>

                                <div>
                                    <h2
                                        class="text-sm font-bold
                                               text-slate-900">
                                        {{ $group['title'] }}
                                    </h2>

                                    @if ($group['description'])
                                        <p
                                            class="mt-1 text-xs
                                                   leading-5
                                                   text-slate-500">
                                            {{ $group['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>


                            <div
                                class="grid gap-x-5
                                       gap-y-5 p-5
                                       sm:grid-cols-2">
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


                                    <div
                                        class="{{ $isWide ? 'sm:col-span-2' : '' }}">
                                        <div
                                            class="mb-2 flex
                                                   items-center
                                                   justify-between
                                                   gap-3">
                                            <label for="{{ $fieldName }}"
                                                class="block
                                                       text-sm
                                                       font-bold
                                                       text-slate-700">
                                                {{ $field['label'] }}

                                                <span class="text-rose-500">
                                                    *
                                                </span>
                                            </label>

                                            <span
                                                class="rounded-full
                                                       px-2.5 py-1
                                                       text-[10px]
                                                       font-bold
                                                       uppercase
                                                       tracking-wide
                                                {{ $isMissing ? 'bg-amber-100 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}"
                                                data-field-state>
                                                {{ $isMissing ? 'Incomplete' : 'Completed' }}
                                            </span>
                                        </div>


                                        @if ($fieldName === $businessUnitFieldName)
                                            <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                                data-business-unit-field data-hr-required-field
                                                class="{{ $baseClass }} {{ $stateClass }}">
                                                <option value="">
                                                    Select a business unit
                                                </option>

                                                @foreach ($businessUnits as $businessUnit)
                                                    <option value="{{ $businessUnit->code }}"
                                                        @selected((string) $fieldValue === (string) $businessUnit->code)>
                                                        {{ $businessUnit->name }}
                                                        ({{ $businessUnit->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif ($fieldName === $departmentFieldName)
                                            <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                                data-department-field
                                                data-initial-department="{{ $selectedDepartment }}"
                                                data-hr-required-field @disabled($selectedBusinessUnit === '')
                                                class="{{ $baseClass }} {{ $stateClass }} disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                                <option value="">
                                                    {{ $selectedBusinessUnit === '' ? 'Select a business unit first' : 'Select a department' }}
                                                </option>

                                                @foreach ($initialDepartmentOptions as $department)
                                                    <option
                                                        value="{{ $department['code'] }}"
                                                        @selected((string) $fieldValue === (string) $department['code'])>
                                                        {{ $department['name'] }}
                                                        —
                                                        {{ $department['code'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif ($field['type'] === 'select')
                                            <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                                class="{{ $baseClass }} {{ $stateClass }}"
                                                data-hr-required-field>
                                                <option value="">
                                                    Select a value
                                                </option>

                                                @foreach ($field['options'] as $option)
                                                    <option value="{{ $option }}" @selected((string) $fieldValue === (string) $option)>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif ($field['type'] === 'textarea')
                                            <textarea id="{{ $fieldName }}" name="{{ $fieldName }}" rows="{{ $field['rows'] }}" required
                                                placeholder="{{ $field['placeholder'] }}"
                                                class="{{ $baseClass }} {{ $stateClass }} resize-y" data-hr-required-field>{{ $fieldValue }}</textarea>
                                        @else
                                            <input id="{{ $fieldName }}" name="{{ $fieldName }}"
                                                type="{{ $field['type'] }}" value="{{ $fieldValue }}" required
                                                @if ($field['inputmode']) inputmode="{{ $field['inputmode'] }}" @endif
                                                placeholder="{{ $field['placeholder'] }}"
                                                class="{{ $baseClass }} {{ $stateClass }}"
                                                data-hr-required-field>
                                        @endif


                                        @error($fieldName)
                                            <p
                                                class="mt-2 text-xs
                                                       font-semibold
                                                       text-rose-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                </div>


                <div
                    class="sticky bottom-4 z-20
                           mt-6 rounded-2xl border
                           border-slate-200 bg-white/95
                           p-3
                           shadow-[0_16px_50px_rgba(15,23,42,0.14)]
                           backdrop-blur
                           sm:flex sm:items-center
                           sm:justify-between
                           sm:px-4">
                    <p
                        class="mb-3 text-xs
                               leading-5 text-slate-500
                               sm:mb-0">
                        Save employment information
                        after completing the HR fields.
                    </p>

                    <button type="submit"
                        class="inline-flex min-h-11
                               w-full cursor-pointer
                               items-center
                               justify-center gap-2
                               rounded-xl bg-orange-500
                               px-6 text-sm font-bold
                               text-white
                               shadow-[0_8px_20px_rgba(249,115,22,0.22)]
                               transition
                               hover:bg-orange-600
                               focus:outline-none
                               focus:ring-4
                               focus:ring-orange-200
                               disabled:cursor-not-allowed
                               disabled:opacity-70
                               sm:w-auto"
                        data-save-hr-button>
                        <svg class="hidden h-4 w-4
                                   animate-spin"
                            viewBox="0 0 24 24" fill="none" data-save-hr-spinner aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>

                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0
                                   018-8V0C5.373
                                   0 0 5.373
                                   0 12h4zm2
                                   5.291A7.962
                                   7.962 0 014
                                   12H0c0 3.042
                                   1.135 5.824
                                   3 7.938l3-2.647z"></path>
                        </svg>

                        <span data-save-hr-label>
                            Save Employment Info
                        </span>
                    </button>
                </div>

            </form>
        </div>


        {{-- ===================================================== --}}
        {{-- PERSONAL INFO --}}
        {{-- ===================================================== --}}

        <div data-edit-tab-panel="personal" @if ($activeTab !== 'personal') hidden @endif>
            <form
                action="{{ route('hr-form.update-personal', $employee->employee_id) }}"
                method="POST" enctype="multipart/form-data" data-hr-personal-form>
                @csrf
                @method('PUT')

                <input type="hidden" name="_form_section" value="personal">

                <input type="hidden" name="_pic_filter" value="{{ request('pic') }}">

                <input type="hidden" name="_search_filter" value="{{ request('search') }}">


                <x-form.personal-fields :user="$employee" :isEmployeeRequired="$isEmployeeRequired" :existingDocuments="$existingDocuments" mode="hr" />


                <div
                    class="sticky bottom-4 z-20
                           mt-6 rounded-2xl border
                           border-slate-200 bg-white/95
                           p-4
                           shadow-[0_16px_50px_rgba(15,23,42,0.14)]
                           backdrop-blur">
                    <div class="flex items-center
                               justify-end">
                        <button type="submit" class="kanmo-btn-primary" data-save-personal-button>
                            <svg class="hidden h-4 w-4
                                       animate-spin"
                                viewBox="0 0 24 24" fill="none" data-save-personal-spinner aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>

                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0
                                       018-8V0C5.373
                                       0 0 5.373
                                       0 12h4zm2
                                       5.291A7.962
                                       7.962 0 014
                                       12H0c0 3.042
                                       1.135 5.824
                                       3 7.938l3-2.647z"></path>
                            </svg>

                            <span data-save-personal-label>
                                Save Personal Info
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </x-app-shell>


    @push('scripts')
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

        <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>


        <script>
            document.addEventListener(
                'DOMContentLoaded',
                function() {

                    /*
                     * =================================================
                     * TAB
                     * =================================================
                     */
                    const tabButtons =
                        document.querySelectorAll(
                            '[data-edit-tab]'
                        );

                    const tabPanels =
                        document.querySelectorAll(
                            '[data-edit-tab-panel]'
                        );

                    const activateTab =
                        (tabName) => {
                            tabPanels.forEach(
                                (panel) => {
                                    panel.hidden =
                                        panel.dataset
                                        .editTabPanel !==
                                        tabName;
                                }
                            );

                            tabButtons.forEach(
                                (button) => {
                                    const active =
                                        button.dataset
                                        .editTab ===
                                        tabName;

                                    button.classList.toggle(
                                        'border-orange-500',
                                        active
                                    );

                                    button.classList.toggle(
                                        'text-orange-600',
                                        active
                                    );

                                    button.classList.toggle(
                                        'border-transparent',
                                        !active
                                    );

                                    button.classList.toggle(
                                        'text-slate-500',
                                        !active
                                    );
                                }
                            );

                            const url =
                                new URL(
                                    window.location.href
                                );

                            url.searchParams.set(
                                'tab',
                                tabName
                            );

                            window.history.replaceState({},
                                '',
                                url
                            );
                        };

                    tabButtons.forEach(
                        (button) => {
                            button.addEventListener(
                                'click',
                                () => {
                                    activateTab(
                                        button.dataset
                                        .editTab
                                    );
                                }
                            );
                        }
                    );


                    /*
                     * =================================================
                     * EMPLOYMENT INFO
                     * =================================================
                     */
                    const hrForm =
                        document.querySelector(
                            '[data-hr-form]'
                        );

                    if (hrForm) {
                        const fields =
                            Array.from(
                                hrForm.querySelectorAll(
                                    '[data-hr-required-field]'
                                )
                            );

                        const progressText =
                            document.querySelector(
                                '[data-hr-progress-text]'
                            );

                        const progressCount =
                            document.querySelector(
                                '[data-hr-progress-count]'
                            );

                        const progressTrack =
                            document.querySelector(
                                '[data-hr-progress-track]'
                            );

                        const progressBar =
                            document.querySelector(
                                '[data-hr-progress-bar]'
                            );

                        const saveButton =
                            hrForm.querySelector(
                                '[data-save-hr-button]'
                            );

                        const saveSpinner =
                            hrForm.querySelector(
                                '[data-save-hr-spinner]'
                            );

                        const saveLabel =
                            hrForm.querySelector(
                                '[data-save-hr-label]'
                            );

                        const businessUnitField =
                            hrForm.querySelector(
                                '[data-business-unit-field]'
                            );

                        const departmentField =
                            hrForm.querySelector(
                                '[data-department-field]'
                            );

                        const departmentsByBusinessUnit =
                            {{ \Illuminate\Support\Js::from($departmentsByBusinessUnit) }};


                        const hasValue =
                            (field) =>
                            String(
                                field.value ?? ''
                            ).trim() !== '';


                        const updateProgress =
                            () => {
                                const completed =
                                    fields.filter(
                                        hasValue
                                    ).length;

                                const total =
                                    fields.length;

                                const percentage =
                                    total ?
                                    Math.round(
                                        (
                                            completed /
                                            total
                                        ) * 100
                                    ) :
                                    0;

                                if (
                                    progressText
                                ) {
                                    progressText
                                        .textContent =
                                        `${percentage}%`;
                                }

                                if (
                                    progressCount
                                ) {
                                    progressCount
                                        .textContent =
                                        `${completed} of ${total} fields completed`;
                                }

                                progressTrack
                                    ?.setAttribute(
                                        'aria-valuenow',
                                        String(
                                            percentage
                                        )
                                    );

                                if (
                                    progressBar
                                ) {
                                    progressBar
                                        .style.width =
                                        `${percentage}%`;
                                }

                                progressText
                                    ?.classList
                                    .toggle(
                                        'text-emerald-600',
                                        percentage >= 100
                                    );

                                progressText
                                    ?.classList
                                    .toggle(
                                        'text-orange-600',
                                        percentage < 100
                                    );

                                progressBar
                                    ?.classList
                                    .toggle(
                                        'bg-emerald-500',
                                        percentage >= 100
                                    );

                                progressBar
                                    ?.classList
                                    .toggle(
                                        'bg-orange-500',
                                        percentage < 100
                                    );

                                fields.forEach(
                                    (field) => {
                                        const state =
                                            field
                                            .closest(
                                                'div'
                                            )
                                            ?.querySelector(
                                                '[data-field-state]'
                                            );

                                        if (!state) {
                                            return;
                                        }

                                        const filled =
                                            hasValue(
                                                field
                                            );

                                        state.textContent =
                                            filled ?
                                            'Completed' :
                                            'Incomplete';

                                        state.className =
                                            filled ?
                                            'rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700' :
                                            'rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700';
                                    }
                                );
                            };


                        const createDepartmentOption =
                            (department) => {
                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    department.code;

                                option.textContent =
                                    `${department.name} — ${department.code}`;

                                return option;
                            };


                        const renderDepartmentOptions =
                            (
                                preserveInitialValue =
                                false
                            ) => {
                                if (
                                    !businessUnitField ||
                                    !departmentField
                                ) {
                                    return;
                                }

                                const businessUnitCode =
                                    String(
                                        businessUnitField
                                        .value ?? ''
                                    ).trim();

                                const selectedDepartment =
                                    preserveInitialValue ?
                                    String(
                                        departmentField
                                        .dataset
                                        .initialDepartment ??
                                        ''
                                    ) :
                                    '';

                                departmentField
                                    .innerHTML = '';

                                const placeholder =
                                    document.createElement(
                                        'option'
                                    );

                                placeholder.value = '';

                                if (
                                    businessUnitCode === ''
                                ) {
                                    placeholder
                                        .textContent =
                                        'Select a business unit first';

                                    departmentField
                                        .appendChild(
                                            placeholder
                                        );

                                    departmentField
                                        .disabled = true;

                                    departmentField
                                        .value = '';

                                    updateProgress();

                                    return;
                                }

                                placeholder.textContent =
                                    'Select a department';

                                departmentField
                                    .appendChild(
                                        placeholder
                                    );

                                departmentField
                                    .disabled = false;

                                const departments =
                                    departmentsByBusinessUnit[
                                        businessUnitCode
                                    ] ?? [];

                                departments.forEach(
                                    (department) => {
                                        departmentField
                                            .appendChild(
                                                createDepartmentOption(
                                                    department
                                                )
                                            );
                                    }
                                );

                                const departmentStillAvailable =
                                    departments.some(
                                        (department) =>
                                        String(
                                            department.code
                                        ) ===
                                        selectedDepartment
                                    );

                                departmentField.value =
                                    departmentStillAvailable ?
                                    selectedDepartment :
                                    '';

                                updateProgress();
                            };


                        businessUnitField
                            ?.addEventListener(
                                'change',
                                () => {
                                    if (
                                        departmentField
                                    ) {
                                        departmentField
                                            .dataset
                                            .initialDepartment =
                                            '';
                                    }

                                    renderDepartmentOptions(
                                        false
                                    );
                                }
                            );


                        fields.forEach(
                            (field) => {
                                field.addEventListener(
                                    'input',
                                    updateProgress
                                );

                                field.addEventListener(
                                    'change',
                                    updateProgress
                                );
                            }
                        );


                        hrForm.addEventListener(
                            'submit',
                            function() {
                                if (
                                    !hrForm.checkValidity()
                                ) {
                                    return;
                                }

                                if (saveButton) {
                                    saveButton.disabled =
                                        true;
                                }

                                saveSpinner
                                    ?.classList
                                    .remove(
                                        'hidden'
                                    );

                                if (saveLabel) {
                                    saveLabel.textContent =
                                        'Saving Employment Info...';
                                }
                            }
                        );


                        renderDepartmentOptions(
                            true
                        );

                        updateProgress();
                    }


                    /*
                     * =================================================
                     * PERSONAL FORM
                     * =================================================
                     */
                    const personalForm =
                        document.querySelector(
                            '[data-hr-personal-form]'
                        );

                    if (personalForm) {
                        const saveButton =
                            personalForm.querySelector(
                                '[data-save-personal-button]'
                            );

                        const spinner =
                            personalForm.querySelector(
                                '[data-save-personal-spinner]'
                            );

                        const label =
                            personalForm.querySelector(
                                '[data-save-personal-label]'
                            );


                        /*
                         * Copy current address ke KTP.
                         */
                        const copyAddress =
                            personalForm.querySelector(
                                '[data-copy-address]'
                            );

                        const addressMap = {
                            current_address: 'ktp_address',

                            current_provinsi: 'ktp_provinsi',

                            current_kotamadya_kabupaten: 'ktp_kotamadya_kabupaten',

                            current_kecamatan: 'ktp_kecamatan',

                            current_kelurahan: 'ktp_kelurahan',
                        };


                        const syncAddress =
                            () => {
                                if (
                                    !copyAddress
                                    ?.checked
                                ) {
                                    return;
                                }

                                Object.entries(
                                    addressMap
                                ).forEach(
                                    (
                                        [
                                            sourceName,
                                            targetName,
                                        ]
                                    ) => {
                                        const source =
                                            personalForm
                                            .querySelector(
                                                `[name="${sourceName}"]`
                                            );

                                        const target =
                                            personalForm
                                            .querySelector(
                                                `[name="${targetName}"]`
                                            );

                                        if (
                                            source &&
                                            target
                                        ) {
                                            target.value =
                                                source.value;

                                            target.dispatchEvent(
                                                new Event(
                                                    'input', {
                                                        bubbles: true,
                                                    }
                                                )
                                            );
                                        }
                                    }
                                );
                            };


                        copyAddress
                            ?.addEventListener(
                                'change',
                                syncAddress
                            );


                        Object.keys(
                            addressMap
                        ).forEach(
                            (sourceName) => {
                                personalForm
                                    .querySelector(
                                        `[name="${sourceName}"]`
                                    )
                                    ?.addEventListener(
                                        'input',
                                        syncAddress
                                    );
                            }
                        );


                        /*
                         * FilePond
                         */
                        if (
                            window.FilePond
                        ) {
                            FilePond.registerPlugin(
                                FilePondPluginImagePreview,
                                FilePondPluginFileValidateSize,
                                FilePondPluginFileValidateType
                            );

                            const fileInputs =
                                personalForm
                                .querySelectorAll(
                                    'input.filepond[type="file"]'
                                );

                            fileInputs.forEach(
                                (input) => {
                                    FilePond.create(
                                        input, {
                                            acceptedFileTypes: [
                                                'image/*',
                                                'application/pdf',
                                            ],

                                            maxFileSize: '5MB',

                                            storeAsFile: true,

                                            allowMultiple: false,

                                            maxFiles: 1,

                                            required: input.required,

                                            labelIdle: 'Drag & Drop file atau <span class="filepond--label-action">Browse</span>',
                                        }
                                    );
                                }
                            );
                        }


                        personalForm
                            .addEventListener(
                                'submit',
                                function() {
                                    if (
                                        !personalForm
                                        .checkValidity()
                                    ) {
                                        return;
                                    }

                                    if (saveButton) {
                                        saveButton.disabled =
                                            true;
                                    }

                                    spinner
                                        ?.classList
                                        .remove(
                                            'hidden'
                                        );

                                    if (label) {
                                        label.textContent =
                                            'Saving Personal Info...';
                                    }
                                }
                            );
                    }
                }
            );
        </script>
    @endpush

</x-layout>

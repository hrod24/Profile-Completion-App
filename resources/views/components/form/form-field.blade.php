@props([
    'isEmployeeRequired' => false,
    'user' => null,
    'existingDocuments' => [],
])

<form action="{{ route('employee.form.submit') }}" method="POST"
    data-save-step-url="{{ route('employee.form.save-step') }}"
    class="mt-6 grid items-start gap-6
           lg:grid-cols-[280px_minmax(0,1fr)]" data-employee-form novalidate
    enctype="multipart/form-data">
    @csrf

    {{-- ========================================================= --}}
    {{-- SIDEBAR --}}
    {{-- ========================================================= --}}
    <aside class="kanmo-card p-4
               lg:sticky lg:top-5">
        <div class="border-b border-stone-100
                   px-2 pb-4">
            <div class="flex items-center
                       justify-between">
                <p class="text-sm font-bold
                           text-slate-900">
                    Form Completion
                </p>

                <span class="text-sm font-extrabold
                           text-kanmo-600" data-form-progress-text>
                    0%
                </span>
            </div>

            <div class="kanmo-progress-track
                       mt-3 h-2" role="progressbar"
                aria-label="Form Completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="kanmo-progress-bar" style="width: 0%" data-form-progress-bar></div>
            </div>

            <p class="mt-2 text-xs leading-5
                       text-slate-500" data-form-progress-count>
                Calculating required fields...
            </p>
        </div>


        <nav class="mt-3 space-y-1" aria-label="Employee form steps">
            @foreach ([['Identity', 'Employee ID & KTP'], ['Personal Profile', 'Personal data'], ['Contact & Address', 'Contact and residence'], ['Family & Education', 'Family and education data'], ['Tax & Review', 'NPWP and final review'], ['Document Attachment', 'Upload employee documents']] as $index => [$stepTitle, $stepDescription])
                <button type="button" class="kanmo-step-button" data-step-button data-step-index="{{ $index }}"
                    data-state="{{ $index === 0 ? 'active' : 'idle' }}">
                    <span class="kanmo-step-number">
                        {{ $index + 1 }}
                    </span>

                    <span class="min-w-0">
                        <span
                            class="kanmo-step-title
                                   block text-sm
                                   font-bold text-slate-700">
                            {{ $stepTitle }}
                        </span>

                        <span class="mt-0.5 block truncate
                                   text-xs text-slate-500">
                            {{ $stepDescription }}
                        </span>
                    </span>
                </button>
            @endforeach
        </nav>


        <div class="mt-4 rounded-xl border
                   border-kanmo-100
                   bg-kanmo-50 p-3">
            <div class="flex gap-2.5">
                <svg class="mt-0.5 h-4 w-4
                           shrink-0 text-kanmo-600" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12
                           V16.5zm9-4.5a9 9 0 11-18
                           0 9 9 0 0118 0z" />
                </svg>

                <p class="text-xs leading-5
                           text-kanmo-900/80">
                    Identity data is sensitive.
                    Use a secure device and network.
                </p>
            </div>
        </div>
    </aside>


    {{-- ========================================================= --}}
    {{-- CONTENT --}}
    {{-- ========================================================= --}}
    <div class="min-w-0">

        <div
            class="mb-3 flex items-center
                   justify-between px-1
                   text-xs font-semibold
                   text-slate-500 lg:hidden">
            <span data-current-step>
                Step 1 of 6
            </span>

            <span>
                Fields marked
                <span class="text-kanmo-600">*</span>
                are required
            </span>
        </div>


        <x-form.personal-fields :user="$user" :isEmployeeRequired="$isEmployeeRequired" :existingDocuments="$existingDocuments" mode="employee" />


        {{-- ===================================================== --}}
        {{-- ACTION BUTTONS --}}
        {{-- ===================================================== --}}
        <div
            class="sticky bottom-0 z-20 mt-5
                   rounded-2xl border
                   border-stone-200 bg-white/95
                   p-3 shadow-[0_-10px_30px_rgba(28,25,23,0.08)]
                   backdrop-blur sm:p-4">
            <div
                class="flex flex-col gap-2
                       sm:flex-row
                       sm:items-center
                       sm:justify-end">
                <div class="flex flex-col gap-2
                           sm:flex-row">
                    <button type="button" class="kanmo-btn-secondary hidden" data-previous-step>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0
                                   0l7.5-7.5M3 12h18" />
                        </svg>

                        Previous
                    </button>


                    <button type="button" class="kanmo-btn-primary" data-next-step>
                        <svg class="hidden h-4 w-4
                                   animate-spin" viewBox="0 0 24 24"
                            fill="none" data-step-save-spinner aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>

                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0
                                   018-8V0C5.373
                                   0 0 5.373 0
                                   12h4zm2
                                   5.291A7.962
                                   7.962 0 014
                                   12H0c0
                                   3.042 1.135
                                   5.824 3
                                   7.938l3-2.647z"></path>
                        </svg>

                        <span data-step-save-label>
                            Save & Next
                        </span>
                    </button>


                    <button type="submit" class="kanmo-btn-primary hidden" data-submit-form>
                        <svg class="hidden h-4 w-4
                                   animate-spin" viewBox="0 0 24 24"
                            fill="none" data-submit-spinner aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>

                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0
                                   018-8V0C5.373
                                   0 0 5.373 0
                                   12h4zm2
                                   5.291A7.962
                                   7.962 0 014
                                   12H0c0
                                   3.042 1.135
                                   5.824 3
                                   7.938l3-2.647z"></path>
                        </svg>

                        <span data-submit-label>
                            Save Employee Data
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

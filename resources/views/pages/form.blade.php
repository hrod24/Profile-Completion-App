<x-layout :title="$title">

    @push('styles')
        <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
            rel="stylesheet" />
    @endpush

    @php
        $employeeRequiredFields = $employeeRequiredFields ?? config('employee.employee_required_fields', []);

        $isEmployeeRequired = fn(string $field): bool => in_array($field, $employeeRequiredFields, true);

        $user = $user ?? null;
    @endphp

    <div class="kanmo-page">
        <header class="border-b border-stone-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-4">
                        <img width="40" src="{{ asset('img/kanmo-logo.jpeg') }}" alt="">
                        <div>
                            <p class="text-base font-extrabold tracking-[0.12em] text-kanmo-600">KANMO <span
                                    class="font-medium text-slate-500">GROUP</span></p>
                            <p class="text-xs font-medium text-slate-500">People Profile Portal</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <section
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-kanmo-600 via-kanmo-500 to-orange-400 px-6 py-7 text-white shadow-[0_22px_55px_rgba(241,90,36,0.18)] sm:px-8 sm:py-8">
                <div aria-hidden="true"
                    class="absolute -right-16 -top-20 h-52 w-52 rounded-full border-[34px] border-white/10"></div>
                <div class="relative max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.17em] text-white/80">Employee data completion form
                    </p>
                    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">
                        Complete Your Employee Profile
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/85">
                        Please complete each section carefully. Your information
                        will be used for employee administration.
                    </p>
                </div>
            </section>

            <div class="mt-6 space-y-3">
                @if (session('success'))
                    <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"
                        role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>
                            <p class="font-bold">Data Saved Successfully</p>
                            <p class="mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                        <p class="font-bold">Something Went Wrong</p>
                        <p class="mt-0.5">{{ session('error') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                        <p class="font-bold">Some Data Is Not Valid</p>
                        <p class="mt-1">We have opened the section that needs attention. Please check the marked
                            fields.</p>
                    </div>
                @endif
            </div>

            <x-form.form-field :isEmployeeRequired="$isEmployeeRequired" :user="$user" :existingDocuments="$existingDocuments"></x-form.form-field>
        </main>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                FilePond.registerPlugin(
                    FilePondPluginImagePreview,
                    FilePondPluginFileValidateSize,
                    FilePondPluginFileValidateType
                );

                const form = document.querySelector(
                    "[data-employee-form]"
                );

                const fileInputs = document.querySelectorAll(
                    'input.filepond[type="file"]'
                );

                fileInputs.forEach(function(input) {
                    const isRequired = input.required;

                    /*
                     * Status file yang sudah tersimpan di database.
                     */
                    const hasExistingFile =
                        input.dataset.existingFile === "true";

                    const existingFileName =
                        input.dataset.existingFileName ?? "";

                    const pond = FilePond.create(input, {
                        acceptedFileTypes: [
                            "image/*",
                            "application/pdf"
                        ],
                        maxFileSize: "5MB",
                        storeAsFile: true,
                        allowMultiple: false,
                        maxFiles: 1,
                        required: isRequired,
                        labelIdle: 'Drag & Drop file atau <span class="filepond--label-action">Browse</span>',
                    });

                    const pondElement = pond.element;

                    pondElement.dataset.filepondField = "true";
                    pondElement.dataset.filepondRequired =
                        isRequired ? "true" : "false";

                    pondElement.dataset.existingFile =
                        hasExistingFile ? "true" : "false";

                    pondElement.dataset.existingFileName =
                        existingFileName;

                    const updateFilePondState = () => {
                        /*
                         * File baru yang dipilih oleh employee.
                         */
                        const hasNewFile =
                            pond.getFiles().length > 0;

                        /*
                         * Attachment dianggap terisi jika:
                         * 1. Sudah ada di database, atau
                         * 2. Employee memilih file baru.
                         */
                        const attachmentIsFilled =
                            hasExistingFile || hasNewFile;

                        pondElement.dataset.filepondHasFile =
                            attachmentIsFilled ? "true" : "false";

                        form?.dispatchEvent(
                            new CustomEvent("filepond:state-change")
                        );
                    };

                    pond.on("updatefiles", updateFilePondState);

                    /*
                     * Langsung periksa database saat halaman dibuka.
                     */
                    updateFilePondState();
                });
            });
        </script>
    @endpush
</x-layout>

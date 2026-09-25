import "./bootstrap";
import "./dashboard-live-search";
import "flowbite";
import "./set-pic";
import "./sidebar-navigation";
import "./employee-account-synchronize";
import "./employee-excel-import";
import "./hr-form-live-search";
import "./employee-details-modal";
import "./employee-export";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();


document.addEventListener(
    "DOMContentLoaded",
    () => {
        initEmployeeForm();
    }
);


function initEmployeeForm() {
    const form =
        document.querySelector(
            "[data-employee-form]"
        );

    if (!form) {
        return;
    }


    /*
     * ============================================================
     * ELEMENTS
     * ============================================================
     */
    const panels =
        Array.from(
            form.querySelectorAll(
                "[data-form-step]"
            )
        );

    const stepButtons =
        Array.from(
            form.querySelectorAll(
                "[data-step-button]"
            )
        );

    const previousButton =
        form.querySelector(
            "[data-previous-step]"
        );

    const nextButton =
        form.querySelector(
            "[data-next-step]"
        );

    const submitButton =
        form.querySelector(
            "[data-submit-form]"
        );

    const currentStepText =
        form.querySelector(
            "[data-current-step]"
        );

    const progressText =
        form.querySelector(
            "[data-form-progress-text]"
        );

    const progressBar =
        form.querySelector(
            "[data-form-progress-bar]"
        );

    const progressCount =
        form.querySelector(
            "[data-form-progress-count]"
        );


    /*
     * ============================================================
     * REQUIRED FIELD HELPERS
     * ============================================================
     */
    const getRequiredNormalFields =
        (scope = form) => {
            return Array.from(
                scope.querySelectorAll(
                    'input[required]:not([type="file"]), select[required], textarea[required]'
                )
            ).filter(
                (field) => {
                    return (
                        field.name &&
                        field.name !== "_token" &&
                        !field.disabled
                    );
                }
            );
        };


    const getFilePondFields =
        (scope = form) => {
            return Array.from(
                scope.querySelectorAll(
                    '[data-filepond-field="true"]'
                )
            );
        };


    const getRequiredFilePondFields =
        (scope = form) => {
            return getFilePondFields(
                scope
            ).filter(
                (field) =>
                    field.dataset
                        .filepondRequired ===
                    "true"
            );
        };


    /*
     * ============================================================
     * STATE
     * ============================================================
     */
    let currentStep = 0;

    let isSubmitting = false;

    let isSavingStep = false;


    /*
     * Kalau ada validation error dari Laravel,
     * buka step yang mempunyai error.
     */
    const panelWithError =
        panels.findIndex(
            (panel) =>
                panel.querySelector(
                    ".kanmo-error"
                )
        );

    if (panelWithError >= 0) {
        currentStep =
            panelWithError;
    }


    /*
     * ============================================================
     * FIELD VALUE HELPERS
     * ============================================================
     */
    const fieldHasValue =
        (field) => {
            if (
                field.type ===
                    "checkbox" ||
                field.type ===
                    "radio"
            ) {
                return field.checked;
            }

            return (
                String(
                    field.value ?? ""
                ).trim() !== ""
            );
        };


    const filePondHasValue =
        (filePondElement) => {
            return (
                filePondElement
                    .dataset
                    .filepondHasFile ===
                "true"
            );
        };


    /*
     * ============================================================
     * FORM COMPLETION
     * ============================================================
     */
    const updateCompletion =
        () => {
            const requiredNormalFields =
                getRequiredNormalFields();

            const requiredFilePondFields =
                getRequiredFilePondFields();


            const completedNormalFields =
                requiredNormalFields.filter(
                    fieldHasValue
                ).length;


            const completedFilePondFields =
                requiredFilePondFields.filter(
                    filePondHasValue
                ).length;


            const completed =
                completedNormalFields +
                completedFilePondFields;


            const total =
                requiredNormalFields.length +
                requiredFilePondFields.length;


            const percentage =
                total > 0
                    ? Math.round(
                        (
                            completed /
                            total
                        ) * 100
                    )
                    : 0;


            if (progressText) {
                progressText.textContent =
                    `${percentage}%`;
            }


            if (progressCount) {
                progressCount.textContent =
                    `${completed} dari ${total} field wajib telah diisi`;
            }


            if (progressBar) {
                progressBar.style.width =
                    `${percentage}%`;

                progressBar.setAttribute(
                    "aria-valuenow",
                    String(
                        percentage
                    )
                );
            }
        };


    /*
     * ============================================================
     * DOCUMENT PREVIEW MODAL
     * ============================================================
     *
     * IMAGE:
     * GET Laravel endpoint
     *      ↓
     * fetch()
     *      ↓
     * Blob
     *      ↓
     * Object URL
     *      ↓
     * modal <img>
     *
     * PDF tidak menggunakan bagian ini.
     * PDF tetap didownload langsung lewat <a>.
     * ============================================================
     */

    const documentModal =
        form.querySelector(
            "[data-document-modal]"
        );

    const documentModalImage =
        documentModal?.querySelector(
            "[data-document-modal-image]"
        );

    const documentModalTitle =
        documentModal?.querySelector(
            "[data-document-modal-title]"
        );

    const documentModalClose =
        documentModal?.querySelector(
            "[data-document-modal-close]"
        );

    const documentLoading =
        documentModal?.querySelector(
            "[data-document-loading]"
        );

    const previewButtons =
        form.querySelectorAll(
            "[data-document-preview]"
        );


    /*
     * Object URL image yang sedang
     * digunakan modal.
     */
    let documentObjectUrl =
        null;


    /*
     * Fetch yang sedang berjalan.
     */
    let documentAbortController =
        null;


    /*
     * Tombol View terakhir.
     *
     * Setelah modal ditutup,
     * focus dikembalikan ke tombol.
     */
    let lastDocumentTrigger =
        null;


    /*
     * HTML sebelumnya mungkin mempunyai:
     *
     * <img src="">
     *
     * Hapus supaya browser tidak
     * mencoba request URL kosong.
     */
    if (documentModalImage) {
        documentModalImage
            .removeAttribute(
                "src"
            );
    }


    /*
     * Bersihkan image/blob sebelumnya.
     */
    const cleanupDocumentPreview =
        () => {
            /*
             * Batalkan request apabila
             * user menutup modal saat
             * file masih di-load.
             */
            if (
                documentAbortController
            ) {
                documentAbortController
                    .abort();

                documentAbortController =
                    null;
            }


            /*
             * Hapus blob URL lama.
             */
            if (documentObjectUrl) {
                URL.revokeObjectURL(
                    documentObjectUrl
                );

                documentObjectUrl =
                    null;
            }


            if (
                documentModalImage
            ) {
                documentModalImage.onload =
                    null;

                documentModalImage.onerror =
                    null;

                documentModalImage
                    .removeAttribute(
                        "src"
                    );

                documentModalImage.alt =
                    "";

                documentModalImage
                    .classList
                    .add(
                        "hidden"
                    );
            }


            documentLoading
                ?.classList
                .remove(
                    "hidden"
                );
        };


    /*
     * Tutup modal.
     */
    const closeDocumentModal =
        (
            restoreFocus = true
        ) => {
            if (!documentModal) {
                return;
            }


            documentModal
                .classList
                .add(
                    "hidden"
                );

            documentModal
                .classList
                .remove(
                    "flex"
                );

            documentModal
                .setAttribute(
                    "aria-hidden",
                    "true"
                );


            document.body
                .classList
                .remove(
                    "overflow-hidden"
                );


            cleanupDocumentPreview();


            if (
                restoreFocus &&
                lastDocumentTrigger
            ) {
                lastDocumentTrigger
                    .focus();
            }


            lastDocumentTrigger =
                null;
        };


    /*
     * Buka modal preview.
     */
    const openDocumentModal =
        async (
            url,
            title,
            trigger = null
        ) => {
            if (
                !documentModal ||
                !documentModalImage ||
                !url
            ) {
                return;
            }


            /*
             * Bersihkan request/image
             * sebelumnya.
             */
            cleanupDocumentPreview();


            lastDocumentTrigger =
                trigger;


            if (documentModalTitle) {
                documentModalTitle
                    .textContent =
                    title ||
                    "Employee Document";
            }


            documentModalImage.alt =
                title ||
                "Employee Document";


            /*
             * Tampilkan modal lebih dulu
             * sehingga user melihat spinner.
             */
            documentModal
                .classList
                .remove(
                    "hidden"
                );

            documentModal
                .classList
                .add(
                    "flex"
                );

            documentModal
                .setAttribute(
                    "aria-hidden",
                    "false"
                );


            document.body
                .classList
                .add(
                    "overflow-hidden"
                );


            documentLoading
                ?.classList
                .remove(
                    "hidden"
                );


            /*
             * AbortController supaya request
             * bisa dihentikan jika modal
             * ditutup.
             */
            documentAbortController =
                new AbortController();


            try {
                const response =
                    await fetch(
                        url,
                        {
                            method:
                                "GET",

                            credentials:
                                "same-origin",

                            headers: {
                                Accept:
                                    "image/jpeg,image/png,image/*",
                            },

                            signal:
                                documentAbortController
                                    .signal,
                        }
                    );


                /*
                 * Informasi ini sangat berguna
                 * ketika debug di F12 Console.
                 */
                console.log(
                    "Document preview response:",
                    {
                        url:
                            response.url,

                        status:
                            response.status,

                        redirected:
                            response.redirected,

                        contentType:
                            response.headers.get(
                                "content-type"
                            ),
                    }
                );


                /*
                 * 404 / 403 / 500 dll.
                 */
                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}`
                    );
                }


                /*
                 * Controller Laravel harus
                 * memberikan Content-Type:
                 *
                 * image/jpeg
                 * atau
                 * image/png
                 */
                const contentType =
                    (
                        response.headers.get(
                            "content-type"
                        ) ?? ""
                    )
                        .toLowerCase();


                if (
                    !contentType
                        .startsWith(
                            "image/"
                        )
                ) {
                    throw new Error(
                        `Response bukan image (${contentType || "content-type kosong"})`
                    );
                }


                /*
                 * Ambil response image menjadi Blob.
                 */
                const blob =
                    await response.blob();


                /*
                 * Request mungkin sudah dibatalkan
                 * ketika blob selesai dibuat.
                 */
                if (
                    documentAbortController
                        ?.signal
                        .aborted
                ) {
                    return;
                }


                /*
                 * Buat temporary browser URL.
                 */
                documentObjectUrl =
                    URL.createObjectURL(
                        blob
                    );


                documentModalImage.onload =
                    () => {
                        documentLoading
                            ?.classList
                            .add(
                                "hidden"
                            );

                        documentModalImage
                            .classList
                            .remove(
                                "hidden"
                            );
                    };


                /*
                 * Bisa terjadi jika response
                 * mempunyai image MIME tetapi
                 * isi file rusak.
                 */
                documentModalImage.onerror =
                    () => {
                        console.error(
                            "Browser gagal membaca blob sebagai image."
                        );

                        closeDocumentModal(
                            false
                        );

                        alert(
                            "Document gagal ditampilkan. File gambar mungkin rusak atau formatnya tidak valid."
                        );
                    };


                /*
                 * Tampilkan Blob ke <img>.
                 */
                documentModalImage.src =
                    documentObjectUrl;


                /*
                 * fetch selesai.
                 */
                documentAbortController =
                    null;
            } catch (error) {
                /*
                 * Abort karena user menutup modal
                 * bukan error.
                 */
                if (
                    error?.name ===
                    "AbortError"
                ) {
                    return;
                }


                console.error(
                    "Document preview gagal:",
                    error
                );


                closeDocumentModal(
                    false
                );


                /*
                 * Pada development, pesan ini
                 * membantu mengetahui apakah
                 * endpoint menghasilkan 404/500
                 * atau bukan image.
                 */
                const reason =
                    error instanceof Error
                        ? error.message
                        : "";


                alert(
                    reason
                        ? `Document gagal ditampilkan. (${reason})`
                        : "Document gagal ditampilkan."
                );
            }
        };


    /*
     * Tombol VIEW.
     */
    previewButtons.forEach(
        (button) => {
            button.addEventListener(
                "click",
                () => {
                    const url =
                        button.dataset
                            .documentUrl;

                    const title =
                        button.dataset
                            .documentTitle;


                    if (!url) {
                        console.error(
                            "data-document-url tidak tersedia."
                        );

                        return;
                    }


                    openDocumentModal(
                        url,
                        title,
                        button
                    );
                }
            );
        }
    );


    /*
     * Tombol X.
     */
    documentModalClose
        ?.addEventListener(
            "click",
            () => {
                closeDocumentModal();
            }
        );


    /*
     * Klik area gelap/backdrop.
     */
    documentModal
        ?.addEventListener(
            "click",
            (event) => {
                if (
                    event.target ===
                    documentModal
                ) {
                    closeDocumentModal();
                }
            }
        );


    /*
     * ESC menutup modal.
     */
    document.addEventListener(
        "keydown",
        (event) => {
            if (
                event.key ===
                    "Escape" &&
                documentModal &&
                !documentModal
                    .classList
                    .contains(
                        "hidden"
                    )
            ) {
                closeDocumentModal();
            }
        }
    );


    /*
     * ============================================================
     * STEP STATE
     * ============================================================
     */
    const markStepStates =
        () => {
            stepButtons.forEach(
                (
                    button,
                    index
                ) => {
                    const panel =
                        panels[index];


                    if (!panel) {
                        button.dataset.state =
                            "idle";

                        return;
                    }


                    /*
                     * Field biasa hanya menghitung
                     * field required.
                     */
                    const normalFields =
                        getRequiredNormalFields(
                            panel
                        );


                    /*
                     * Semua FilePond pada Step 6
                     * ikut menentukan status.
                     */
                    const filePondFields =
                        getFilePondFields(
                            panel
                        );


                    const hasCompletionFields =
                        normalFields.length >
                            0 ||
                        filePondFields.length >
                            0;


                    const normalFieldsComplete =
                        normalFields.every(
                            fieldHasValue
                        );


                    const filePondFieldsComplete =
                        filePondFields.every(
                            filePondHasValue
                        );


                    const stepComplete =
                        hasCompletionFields &&
                        normalFieldsComplete &&
                        filePondFieldsComplete;


                    if (
                        index ===
                            currentStep &&
                        stepComplete
                    ) {
                        button.dataset.state =
                            "active-complete";
                    } else if (
                        index ===
                        currentStep
                    ) {
                        button.dataset.state =
                            "active";
                    } else if (
                        stepComplete
                    ) {
                        button.dataset.state =
                            "complete";
                    } else {
                        button.dataset.state =
                            "idle";
                    }


                    button.setAttribute(
                        "aria-current",
                        index ===
                            currentStep
                            ? "step"
                            : "false"
                    );
                }
            );
        };


    /*
     * ============================================================
     * SHOW STEP
     * ============================================================
     */
    const showStep =
        (
            index,
            shouldFocus = false
        ) => {
            if (
                panels.length === 0
            ) {
                return;
            }


            currentStep =
                Math.min(
                    Math.max(
                        index,
                        0
                    ),
                    panels.length - 1
                );


            panels.forEach(
                (
                    panel,
                    panelIndex
                ) => {
                    const isActive =
                        panelIndex ===
                        currentStep;


                    panel.dataset.active =
                        isActive
                            ? "true"
                            : "false";


                    panel.setAttribute(
                        "aria-hidden",
                        isActive
                            ? "false"
                            : "true"
                    );
                }
            );


            if (currentStepText) {
                currentStepText.textContent =
                    `Langkah ${currentStep + 1} dari ${panels.length}`;
            }


            previousButton
                ?.classList
                .toggle(
                    "hidden",
                    currentStep === 0
                );


            nextButton
                ?.classList
                .toggle(
                    "hidden",
                    currentStep ===
                        panels.length - 1
                );


            submitButton
                ?.classList
                .toggle(
                    "hidden",
                    currentStep !==
                        panels.length - 1
                );


            markStepStates();


            if (shouldFocus) {
                panels[currentStep]
                    ?.querySelector(
                        "input:not([readonly]), select, textarea"
                    )
                    ?.focus({
                        preventScroll:
                            true,
                    });


                panels[currentStep]
                    ?.scrollIntoView({
                        behavior:
                            "smooth",

                        block:
                            "start",
                    });
            }
        };


    /*
     * ============================================================
     * VALIDATE CURRENT STEP
     * ============================================================
     */
    const validateCurrentStep =
        () => {
            const panel =
                panels[currentStep];

            if (!panel) {
                return false;
            }


            const fields =
                Array.from(
                    panel.querySelectorAll(
                        "input, select, textarea"
                    )
                ).filter(
                    (field) =>
                        !field.disabled &&
                        field.type !==
                            "hidden"
                );


            for (
                const field of fields
            ) {
                if (
                    !field.checkValidity()
                ) {
                    field.reportValidity();

                    field.focus();

                    return false;
                }
            }


            return true;
        };


    /*
     * ============================================================
     * SAVE CURRENT STEP
     * ============================================================
     */
    const saveStepUrl =
        form.dataset
            .saveStepUrl;


    const createStepFormData =
        () => {
            const panel =
                panels[currentStep];


            const formData =
                new FormData();


            /*
             * CSRF.
             */
            const csrfToken =
                form.querySelector(
                    'input[name="_token"]'
                );


            if (csrfToken) {
                formData.append(
                    "_token",
                    csrfToken.value
                );
            }


            /*
             * Nomor step Laravel:
             *
             * index 0 = step 1
             */
            formData.append(
                "step",
                String(
                    currentStep + 1
                )
            );


            /*
             * Hanya field dari step aktif.
             */
            const fields =
                panel?.querySelectorAll(
                    "input, select, textarea"
                ) ?? [];


            fields.forEach(
                (field) => {
                    if (
                        !field.name ||
                        field.disabled ||
                        field.name ===
                            "_token" ||
                        field.name ===
                            "employee_id"
                    ) {
                        return;
                    }


                    /*
                     * FILE.
                     */
                    if (
                        field.type ===
                        "file"
                    ) {
                        const files =
                            field.files;


                        if (
                            files &&
                            files.length >
                                0
                        ) {
                            formData.append(
                                field.name,
                                files[0]
                            );
                        }


                        return;
                    }


                    /*
                     * CHECKBOX / RADIO.
                     */
                    if (
                        (
                            field.type ===
                                "checkbox" ||
                            field.type ===
                                "radio"
                        ) &&
                        !field.checked
                    ) {
                        return;
                    }


                    formData.append(
                        field.name,
                        field.value
                    );
                }
            );


            return formData;
        };


    const saveCurrentStep =
        async () => {
            if (
                isSavingStep ||
                !saveStepUrl
            ) {
                return false;
            }


            if (
                !validateCurrentStep()
            ) {
                return false;
            }


            isSavingStep =
                true;


            const label =
                nextButton
                    ?.querySelector(
                        "[data-step-save-label]"
                    );


            const spinner =
                nextButton
                    ?.querySelector(
                        "[data-step-save-spinner]"
                    );


            if (nextButton) {
                nextButton.disabled =
                    true;
            }


            if (label) {
                label.textContent =
                    "Saving...";
            }


            spinner
                ?.classList
                .remove(
                    "hidden"
                );


            try {
                const response =
                    await fetch(
                        saveStepUrl,
                        {
                            method:
                                "POST",

                            headers: {
                                Accept:
                                    "application/json",

                                "X-Requested-With":
                                    "XMLHttpRequest",
                            },

                            body:
                                createStepFormData(),
                        }
                    );


                /*
                 * Laravel validation error.
                 */
                if (
                    response.status ===
                    422
                ) {
                    const data =
                        await response.json();


                    const errors =
                        data.errors ??
                        {};


                    const firstFieldName =
                        Object.keys(
                            errors
                        )[0];


                    if (
                        firstFieldName
                    ) {
                        const field =
                            form.querySelector(
                                `[name="${CSS.escape(
                                    firstFieldName
                                )}"]`
                            );


                        field?.focus();


                        field
                            ?.setCustomValidity(
                                errors[
                                    firstFieldName
                                ][0]
                            );


                        field
                            ?.reportValidity();


                        /*
                         * Hapus custom validity
                         * setelah ditampilkan supaya
                         * employee bisa memperbaiki.
                         */
                        field
                            ?.setCustomValidity(
                                ""
                            );
                    }


                    return false;
                }


                if (
                    !response.ok
                ) {
                    throw new Error(
                        `HTTP ${response.status}`
                    );
                }


                await response.json();


                /*
                 * Tandai step sudah tersimpan.
                 */
                if (
                    panels[currentStep]
                ) {
                    panels[
                        currentStep
                    ].dataset.saved =
                        "true";
                }


                return true;
            } catch (error) {
                console.error(
                    "Gagal menyimpan step:",
                    error
                );


                alert(
                    "Data belum berhasil disimpan. Silakan coba kembali."
                );


                return false;
            } finally {
                isSavingStep =
                    false;


                if (nextButton) {
                    nextButton.disabled =
                        false;
                }


                if (label) {
                    label.textContent =
                        "Save & Next";
                }


                spinner
                    ?.classList
                    .add(
                        "hidden"
                    );
            }
        };


    /*
     * ============================================================
     * NEXT
     * ============================================================
     */
    nextButton
        ?.addEventListener(
            "click",
            async () => {
                const saved =
                    await saveCurrentStep();


                if (!saved) {
                    return;
                }


                showStep(
                    currentStep + 1,
                    true
                );
            }
        );


    /*
     * ============================================================
     * PREVIOUS
     * ============================================================
     */
    previousButton
        ?.addEventListener(
            "click",
            () => {
                showStep(
                    currentStep - 1,
                    true
                );
            }
        );


    /*
     * ============================================================
     * SIDEBAR STEP BUTTON
     * ============================================================
     */
    stepButtons.forEach(
        (
            button,
            targetStep
        ) => {
            button.addEventListener(
                "click",
                async () => {
                    /*
                     * Mundur boleh langsung.
                     */
                    if (
                        targetStep <
                        currentStep
                    ) {
                        showStep(
                            targetStep,
                            true
                        );

                        return;
                    }


                    /*
                     * Step yang sama.
                     */
                    if (
                        targetStep ===
                        currentStep
                    ) {
                        return;
                    }


                    /*
                     * Sebelum maju,
                     * simpan current step.
                     */
                    const saved =
                        await saveCurrentStep();


                    if (!saved) {
                        return;
                    }


                    showStep(
                        targetStep,
                        true
                    );
                }
            );
        }
    );


    /*
     * ============================================================
     * UPDATE PROGRESS WHEN INPUT CHANGES
     * ============================================================
     */
    getRequiredNormalFields()
        .forEach(
            (field) => {
                [
                    "input",
                    "change",
                ].forEach(
                    (
                        eventName
                    ) => {
                        field.addEventListener(
                            eventName,
                            () => {
                                updateCompletion();

                                markStepStates();
                            }
                        );
                    }
                );
            }
        );


    /*
     * FilePond mengirim custom event ini.
     */
    form.addEventListener(
        "filepond:state-change",
        () => {
            updateCompletion();

            markStepStates();
        }
    );


    /*
     * ============================================================
     * COPY CURRENT ADDRESS TO KTP ADDRESS
     * ============================================================
     */
    const copyAddressCheckbox =
        form.querySelector(
            "[data-copy-address]"
        );

    const currentAddress =
        form.querySelector(
            "#current_address"
        );

    const ktpAddress =
        form.querySelector(
            "#ktp_address"
        );

    const ktpProvince =
        form.querySelector(
            "#ktp_provinsi"
        );

    const currentProvince =
        form.querySelector(
            "#current_provinsi"
        );

    const ktpCity =
        form.querySelector(
            "#ktp_kotamadya_kabupaten"
        );

    const currentCity =
        form.querySelector(
            "#current_kotamadya_kabupaten"
        );

    const ktpDistrict =
        form.querySelector(
            "#ktp_kecamatan"
        );

    const currentDistrict =
        form.querySelector(
            "#current_kecamatan"
        );

    const ktpSubdistrict =
        form.querySelector(
            "#ktp_kelurahan"
        );

    const currentSubdistrict =
        form.querySelector(
            "#current_kelurahan"
        );


    const copyAddress =
        () => {
            if (
                !copyAddressCheckbox
                    ?.checked ||
                !currentAddress ||
                !ktpAddress ||
                !ktpProvince ||
                !currentProvince ||
                !ktpCity ||
                !currentCity ||
                !ktpDistrict ||
                !currentDistrict ||
                !ktpSubdistrict ||
                !currentSubdistrict
            ) {
                return;
            }


            ktpAddress.value =
                currentAddress.value;

            ktpProvince.value =
                currentProvince.value;

            ktpCity.value =
                currentCity.value;

            ktpDistrict.value =
                currentDistrict.value;

            ktpSubdistrict.value =
                currentSubdistrict.value;


            ktpAddress.dispatchEvent(
                new Event(
                    "input",
                    {
                        bubbles:
                            true,
                    }
                )
            );

            ktpProvince.dispatchEvent(
                new Event(
                    "input",
                    {
                        bubbles:
                            true,
                    }
                )
            );

            ktpCity.dispatchEvent(
                new Event(
                    "input",
                    {
                        bubbles:
                            true,
                    }
                )
            );

            ktpDistrict.dispatchEvent(
                new Event(
                    "input",
                    {
                        bubbles:
                            true,
                    }
                )
            );

            ktpSubdistrict.dispatchEvent(
                new Event(
                    "input",
                    {
                        bubbles:
                            true,
                    }
                )
            );
        };


    copyAddressCheckbox
        ?.addEventListener(
            "change",
            copyAddress
        );

    currentAddress
        ?.addEventListener(
            "input",
            copyAddress
        );

    currentProvince
        ?.addEventListener(
            "input",
            copyAddress
        );

    currentCity
        ?.addEventListener(
            "input",
            copyAddress
        );

    currentDistrict
        ?.addEventListener(
            "input",
            copyAddress
        );

    currentSubdistrict
        ?.addEventListener(
            "input",
            copyAddress
        );


    /*
     * ============================================================
     * EMPLOYEE SYNC
     * ============================================================
     */
    const syncButton =
        form.querySelector(
            "[data-sync-button]"
        );

    const employeeIdInput =
        form.querySelector(
            "#employee_id"
        );


    syncButton
        ?.addEventListener(
            "click",
            () => {
                const employeeId =
                    employeeIdInput
                        ?.value
                        .trim();


                if (!employeeId) {
                    employeeIdInput
                        ?.setCustomValidity(
                            "Employee ID wajib diisi sebelum melakukan sinkronisasi."
                        );

                    employeeIdInput
                        ?.reportValidity();

                    employeeIdInput
                        ?.setCustomValidity(
                            ""
                        );

                    return;
                }


                syncButton.disabled =
                    true;


                const syncLabel =
                    syncButton.querySelector(
                        "[data-sync-label]"
                    );


                if (syncLabel) {
                    syncLabel.textContent =
                        "Menyinkronkan...";
                }


                syncButton
                    .querySelector(
                        "[data-sync-spinner]"
                    )
                    ?.classList
                    .remove(
                        "hidden"
                    );


                const url =
                    new URL(
                        window.location.href
                    );


                url.searchParams.set(
                    "employee_id",
                    employeeId
                );


                window.location.assign(
                    url.toString()
                );
            }
        );


    /*
     * ============================================================
     * FINAL SUBMIT
     * ============================================================
     */
    form.addEventListener(
        "submit",
        (event) => {
            /*
             * Hindari double submit.
             */
            if (isSubmitting) {
                event.preventDefault();

                return;
            }


            /*
             * HTML validation seluruh form.
             */
            if (
                !form.checkValidity()
            ) {
                event.preventDefault();


                const invalidField =
                    form.querySelector(
                        ":invalid"
                    );


                const invalidStep =
                    panels.findIndex(
                        (panel) =>
                            panel.contains(
                                invalidField
                            )
                    );


                if (
                    invalidStep >= 0
                ) {
                    showStep(
                        invalidStep,
                        true
                    );
                }


                invalidField
                    ?.reportValidity();


                return;
            }


            isSubmitting =
                true;


            if (submitButton) {
                submitButton.disabled =
                    true;


                const submitLabel =
                    submitButton
                        .querySelector(
                            "[data-submit-label]"
                        );


                if (submitLabel) {
                    submitLabel.textContent =
                        "Menyimpan data...";
                }


                submitButton
                    .querySelector(
                        "[data-submit-spinner]"
                    )
                    ?.classList
                    .remove(
                        "hidden"
                    );
            }
        }
    );


    /*
     * ============================================================
     * INITIALIZE
     * ============================================================
     */
    updateCompletion();

    showStep(
        currentStep
    );
}
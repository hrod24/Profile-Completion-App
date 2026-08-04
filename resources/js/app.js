import "./bootstrap";
import "./dashboard-live-search";
import "flowbite";
import './set-pic';
import './sidebar-navigation';
import "./employee-account-synchronize";
import "./employee-excel-import";
import './hr-form-live-search';
import "./employee-details-modal";

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    initEmployeeForm();
});

function initEmployeeForm() {
    const form = document.querySelector("[data-employee-form]");
    if (!form) return;

    const panels = Array.from(form.querySelectorAll("[data-form-step]"));
    const stepButtons = Array.from(document.querySelectorAll("[data-step-button]"));
    const previousButton = form.querySelector("[data-previous-step]");
    const nextButton = form.querySelector("[data-next-step]");
    const submitButton = form.querySelector("[data-submit-form]");
    const currentStepText = document.querySelector("[data-current-step]");
    const progressText = document.querySelector("[data-form-progress-text]");
    const progressBar = document.querySelector("[data-form-progress-bar]");
    const progressCount = document.querySelector("[data-form-progress-count]");
    const getRequiredNormalFields = (scope = form) => {
        return Array.from(
            scope.querySelectorAll(
                'input[required]:not([type="file"]), select[required], textarea[required]'
            )
        ).filter((field) => {
            return field.name && field.name !== "_token" && !field.disabled;
        });
    };

    const getFilePondFields = (scope = form) => {
        return Array.from(
            scope.querySelectorAll('[data-filepond-field="true"]')
        );
    };

    const getRequiredFilePondFields = (scope = form) => {
        return getFilePondFields(scope).filter(
            (field) => field.dataset.filepondRequired === "true"
        );
    };

    let currentStep = 0;
    let isSubmitting = false;

    const panelWithError = panels.findIndex((panel) => panel.querySelector(".kanmo-error"));
    if (panelWithError >= 0) currentStep = panelWithError;

    const fieldHasValue = (field) => {
        if (
            field.type === "checkbox" ||
            field.type === "radio"
        ) {
            return field.checked;
        }

        return String(field.value ?? "").trim() !== "";
    };

    const filePondHasValue = (filePondElement) => {
        return filePondElement.dataset.filepondHasFile === "true";
    };

    const updateCompletion = () => {
        const requiredNormalFields = getRequiredNormalFields();
        const requiredFilePondFields = getRequiredFilePondFields();

        const completedNormalFields =
            requiredNormalFields.filter(fieldHasValue).length;

        const completedFilePondFields =
            requiredFilePondFields.filter(filePondHasValue).length;

        const completed =
            completedNormalFields + completedFilePondFields;

        const total =
            requiredNormalFields.length +
            requiredFilePondFields.length;

        const percentage =
            total > 0
                ? Math.round((completed / total) * 100)
                : 0;

        if (progressText) {
            progressText.textContent = `${percentage}%`;
        }

        if (progressCount) {
            progressCount.textContent =
                `${completed} dari ${total} field wajib telah diisi`;
        }

        if (progressBar) {
            progressBar.style.width = `${percentage}%`;

            progressBar.setAttribute(
                "aria-valuenow",
                String(percentage)
            );
        }
    };

    const markStepStates = () => {
        stepButtons.forEach((button, index) => {
            const panel = panels[index];

            if (!panel) {
                button.dataset.state = "idle";
                return;
            }

            /*
            * Field biasa hanya menghitung field required.
            */
            const normalFields =
                getRequiredNormalFields(panel);

            /*
            * Semua FilePond dalam step dihitung.
            *
            * Jadi Step 6 baru hijau jika semua attachment
            * pada Step 6 sudah dipilih.
            */
            const filePondFields =
                getFilePondFields(panel);

            const hasCompletionFields =
                normalFields.length > 0 ||
                filePondFields.length > 0;

            const normalFieldsComplete =
                normalFields.every(fieldHasValue);

            const filePondFieldsComplete =
                filePondFields.every(filePondHasValue);

            const stepComplete =
                hasCompletionFields &&
                normalFieldsComplete &&
                filePondFieldsComplete;

            if (index === currentStep && stepComplete) {
                button.dataset.state = "active-complete";
            } else if (index === currentStep) {
                button.dataset.state = "active";
            } else if (stepComplete) {
                button.dataset.state = "complete";
            } else {
                button.dataset.state = "idle";
            }

            button.setAttribute(
                "aria-current",
                index === currentStep
                    ? "step"
                    : "false"
            );
        });
    };

    const showStep = (index, shouldFocus = false) => {
        currentStep = Math.min(Math.max(index, 0), panels.length - 1);

        panels.forEach((panel, panelIndex) => {
            const isActive = panelIndex === currentStep;
            panel.dataset.active = isActive ? "true" : "false";
            panel.setAttribute("aria-hidden", isActive ? "false" : "true");
        });

        if (currentStepText) currentStepText.textContent = `Langkah ${currentStep + 1} dari ${panels.length}`;
        previousButton?.classList.toggle("hidden", currentStep === 0);
        nextButton?.classList.toggle("hidden", currentStep === panels.length - 1);
        submitButton?.classList.toggle("hidden", currentStep !== panels.length - 1);
        markStepStates();

        if (shouldFocus) {
            panels[currentStep]
                ?.querySelector("input:not([readonly]), select, textarea")
                ?.focus({ preventScroll: true });
            panels[currentStep]?.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    };

    const validateCurrentStep = () => {
        const fields = Array.from(
            panels[currentStep].querySelectorAll("input, select, textarea")
        ).filter((field) => !field.disabled && field.type !== "hidden");

        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }
        return true;
    };

    nextButton?.addEventListener("click", () => {
        if (validateCurrentStep()) showStep(currentStep + 1, true);
    });

    previousButton?.addEventListener("click", () => showStep(currentStep - 1, true));

    stepButtons.forEach((button, targetStep) => {
        button.addEventListener("click", () => {
            if (targetStep > currentStep && !validateCurrentStep()) return;
            showStep(targetStep, true);
        });
    });

    getRequiredNormalFields().forEach((field) => {
        ["input", "change"].forEach((eventName) => {
            field.addEventListener(eventName, () => {
                updateCompletion();
                markStepStates();
            });
        });
    });

    form.addEventListener("filepond:state-change", () => {
        updateCompletion();
        markStepStates();
    });

    const copyAddressCheckbox = form.querySelector("[data-copy-address]");
    const currentAddress = form.querySelector("#current_address");
    const ktpAddress = form.querySelector("#ktp_address");
    const ktpProvince = form.querySelector("#ktp_provinsi");
    const currentProvince = form.querySelector("#current_provinsi");
    const ktpCity = form.querySelector("#ktp_kotamadya_kabupaten");
    const currentCity = form.querySelector("#current_kotamadya_kabupaten");
    const ktpDistrict = form.querySelector("#ktp_kecamatan");
    const currentDistrict = form.querySelector("#current_kecamatan");
    const ktpSubdistrict = form.querySelector("#ktp_kelurahan");
    const currentSubdistrict = form.querySelector("#current_kelurahan");

    const copyAddress = () => {
        if (!copyAddressCheckbox?.checked || !currentAddress || !ktpAddress || !ktpProvince || !currentProvince || !ktpCity || !ktpDistrict || !ktpSubdistrict || !currentCity || !currentDistrict || !currentSubdistrict) return;
        ktpAddress.value = currentAddress.value;
        ktpProvince.value = currentProvince.value;
        ktpCity.value = currentCity.value;
        ktpDistrict.value = currentDistrict.value;
        ktpSubdistrict.value = currentSubdistrict.value;
        ktpAddress.dispatchEvent(new Event("input", { bubbles: true }));
        ktpProvince.dispatchEvent(new Event("input", { bubbles: true }));
        ktpCity.dispatchEvent(new Event("input", { bubbles: true }));
        ktpDistrict.dispatchEvent(new Event("input", { bubbles: true }));
        ktpSubdistrict.dispatchEvent(new Event("input", { bubbles: true }));
    };

    copyAddressCheckbox?.addEventListener("change", copyAddress);
    currentAddress?.addEventListener("input", copyAddress);
    currentProvince?.addEventListener("input", copyAddress);
    currentCity?.addEventListener("input", copyAddress);
    currentDistrict?.addEventListener("input", copyAddress);
    currentSubdistrict?.addEventListener("input", copyAddress);

    const syncButton = form.querySelector("[data-sync-button]");
    const employeeIdInput = form.querySelector("#employee_id");

    syncButton?.addEventListener("click", () => {
        const employeeId = employeeIdInput?.value.trim();
        if (!employeeId) {
            employeeIdInput?.setCustomValidity("Employee ID wajib diisi sebelum melakukan sinkronisasi.");
            employeeIdInput?.reportValidity();
            employeeIdInput?.setCustomValidity("");
            return;
        }

        syncButton.disabled = true;
        syncButton.querySelector("[data-sync-label]").textContent = "Menyinkronkan...";
        syncButton.querySelector("[data-sync-spinner]")?.classList.remove("hidden");

        const url = new URL(window.location.href);
        url.searchParams.set("employee_id", employeeId);
        window.location.assign(url.toString());
    });

    form.addEventListener("submit", (event) => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            const invalidField = form.querySelector(":invalid");
            const invalidStep = panels.findIndex((panel) => panel.contains(invalidField));
            if (invalidStep >= 0) showStep(invalidStep, true);
            invalidField?.reportValidity();
            return;
        }

        isSubmitting = true;
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.querySelector("[data-submit-label]").textContent = "Menyimpan data...";
            submitButton.querySelector("[data-submit-spinner]")?.classList.remove("hidden");
        }
    });

    updateCompletion();
    showStep(currentStep);
}

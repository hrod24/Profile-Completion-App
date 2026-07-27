const importForm = document.querySelector(
    "#employee-excel-import-form"
);

const importButton = importForm?.querySelector(
    "[data-import-submit]"
);

const importProgress = document.querySelector(
    "#employee-import-progress"
);

const importProgressText = document.querySelector(
    "#employee-import-progress-text"
);

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

async function parseResponse(
    response,
    fallbackMessage
) {
    const contentType =
        response.headers.get("content-type") ?? "";

    if (
        !contentType.includes(
            "application/json"
        )
    ) {
        throw new Error(
            `${fallbackMessage} Server mengembalikan HTTP ${response.status}.`
        );
    }

    const result = await response.json();

    if (!response.ok) {
        throw new Error(
            result.message ?? fallbackMessage
        );
    }

    return result;
}

async function uploadExcel(
    url,
    formData
) {
    const response = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: formData,
    });

    return parseResponse(
        response,
        "File gagal diunggah."
    );
}

async function postJson(
    url,
    body
) {
    const response = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(body),
    });

    return parseResponse(
        response,
        "Proses import gagal."
    );
}

importForm?.addEventListener(
    "submit",
    async (event) => {
        event.preventDefault();

        if (!importButton) {
            return;
        }

        importButton.disabled = true;
        importButton.textContent =
            "Preparing Import...";

        importProgress?.classList.remove(
            "hidden"
        );

        if (importProgressText) {
            importProgressText.textContent =
                "Uploading Excel file...";
        }

        try {
            const startUrl =
                importForm.dataset.startUrl;

            const chunkUrl =
                importForm.dataset.chunkUrl;

            const finishUrl =
                importForm.dataset.finishUrl;

            if (
                !startUrl
                || !chunkUrl
                || !finishUrl
            ) {
                throw new Error(
                    "URL proses import tidak lengkap."
                );
            }

            const startResult =
                await uploadExcel(
                    startUrl,
                    new FormData(importForm)
                );

            let done = false;
            let previousProcessed = -1;

            while (!done) {
                const chunkResult =
                    await postJson(
                        chunkUrl,
                        {
                            batch_id:
                                startResult.batch_id,
                        }
                    );

                if (
                    !chunkResult.done
                    && chunkResult.processed
                        <= previousProcessed
                ) {
                    throw new Error(
                        "Progress import tidak bergerak."
                    );
                }

                previousProcessed =
                    chunkResult.processed;

                done = chunkResult.done;

                importButton.textContent =
                    `Importing ${chunkResult.processed}/${chunkResult.total}`;

                if (importProgressText) {
                    importProgressText.textContent =
                        `${chunkResult.processed}` +
                        ` dari ${chunkResult.total}` +
                        ` baris diproses.`;
                }
            }

            const finishResult =
                await postJson(
                    finishUrl,
                    {
                        batch_id:
                            startResult.batch_id,
                    }
                );

            importButton.textContent =
                "Import Completed";

            if (importProgressText) {
                importProgressText.textContent =
                    `Import selesai. ` +
                    `${finishResult.inserted} data baru, ` +
                    `${finishResult.updated} diperbarui, ` +
                    `${finishResult.skipped} dilewati, dan ` +
                    `${finishResult.deactivated} dinonaktifkan.`;
            }

            importForm.reset();
        } catch (error) {
            console.error(error);

            importButton.textContent =
                "Upload and Import";

            if (importProgressText) {
                importProgressText.textContent =
                    error instanceof Error
                        ? error.message
                        : "Import gagal.";
            }
        } finally {
            importButton.disabled = false;
        }
    }
);
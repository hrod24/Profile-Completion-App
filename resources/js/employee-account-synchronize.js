const synchronizeButton = document.querySelector(
    "#synchronize-account-button"
);

const synchronizeStatus = document.querySelector(
    "#synchronize-account-status"
);

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

async function postJson(url, body = {}) {
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

    let result = {};

    try {
        result = await response.json();
    } catch {
        throw new Error(
            "Server tidak mengembalikan respons JSON yang valid."
        );
    }

    if (!response.ok) {
        throw new Error(
            result.message ?? "Proses sinkronisasi gagal."
        );
    }

    return result;
}

synchronizeButton?.addEventListener(
    "click",
    async () => {
        const confirmed = window.confirm(
            "Akun employee nonaktif akan dihapus. Lanjutkan sinkronisasi?"
        );

        if (!confirmed) {
            return;
        }

        const startUrl =
            synchronizeButton.dataset.startUrl;

        const chunkUrl =
            synchronizeButton.dataset.chunkUrl;

        synchronizeButton.disabled = true;
        synchronizeStatus?.classList.remove("hidden");

        let processed = 0;
        let created = 0;
        let updated = 0;

        try {
            synchronizeButton.textContent =
                "Starting synchronization...";

            const startResult = await postJson(
                startUrl
            );

            let afterId = startResult.after_id;
            let done = false;

            while (!done) {
                const chunkResult = await postJson(
                    chunkUrl,
                    {
                        after_id: afterId,
                    }
                );

                processed += chunkResult.processed;
                created += chunkResult.created;
                updated += chunkResult.updated;

                afterId = chunkResult.after_id;
                done = chunkResult.done;

                synchronizeButton.textContent =
                    `Synchronizing ${processed}/${startResult.total}`;
            }

            synchronizeButton.textContent =
                "Synchronization Completed";

            
        } catch (error) {
            console.error(error);

            synchronizeButton.textContent =
                "Synchronize Employee Accounts";

            if (synchronizeStatus) {
                synchronizeStatus.textContent =
                    error instanceof Error
                        ? error.message
                        : "Sinkronisasi gagal.";
            }
        } finally {
            synchronizeButton.disabled = false;
        }
    }
);
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.querySelector(
        "[data-employee-details-modal]"
    );

    const content = document.querySelector(
        "[data-employee-details-content]"
    );

    const title = document.querySelector(
        "#employee-details-title"
    );

    if (!modal || !content) {
        return;
    }

    let activeRequest = null;
    let lastFocusedElement = null;

    const loadingMarkup = `
        <div class="flex min-h-72 items-center justify-center">
            <div class="text-center">
                <svg
                    class="mx-auto h-8 w-8 animate-spin text-orange-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373
                           0 0 5.373 0 12h4zm2
                           5.291A7.962 7.962 0 014
                           12H0c0 3.042 1.135 5.824
                           3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p class="mt-3 text-sm font-semibold text-slate-600">
                    Loading employee data...
                </p>
            </div>
        </div>
    `;

    const errorMarkup = `
        <div class="flex min-h-72 items-center justify-center">
            <div class="max-w-md text-center">
                <div
                    class="mx-auto flex h-12 w-12 items-center
                           justify-center rounded-xl bg-rose-50
                           text-lg font-bold text-rose-600"
                >
                    !
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-900">
                    Unable to load employee details
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Please close this window and try again.
                </p>
            </div>
        </div>
    `;

    const initializeDetailsFilters = () => {
        const panel = content.querySelector(
            "[data-employee-details-panel]"
        );

        if (!panel) {
            return;
        }

        const searchInput = panel.querySelector(
            "[data-details-search]"
        );

        const filterButtons = Array.from(
            panel.querySelectorAll("[data-details-filter]")
        );

        const rows = Array.from(
            panel.querySelectorAll("[data-details-row]")
        );

        const groups = Array.from(
            panel.querySelectorAll("[data-details-group]")
        );

        const emptyState = panel.querySelector(
            "[data-details-empty]"
        );

        let activeFilter = "all";

        const updateButtonState = () => {
            filterButtons.forEach((button) => {
                const isActive =
                    button.dataset.detailsFilter === activeFilter;

                button.setAttribute(
                    "aria-pressed",
                    String(isActive)
                );

                button.classList.toggle("bg-white", isActive);
                button.classList.toggle("shadow-sm", isActive);
                button.classList.toggle(
                    "text-slate-900",
                    isActive
                );
                button.classList.toggle(
                    "text-slate-500",
                    !isActive
                );
            });
        };

        const applyFilter = () => {
            const searchTerm = (
                searchInput?.value ?? ""
            ).trim().toLowerCase();

            let visibleRows = 0;

            rows.forEach((row) => {
                const status =
                    row.dataset.detailsStatus ?? "";

                const searchable =
                    row.dataset.detailsSearchText ?? "";

                const matchesFilter =
                    activeFilter === "all"
                    || status === activeFilter;

                const matchesSearch =
                    !searchTerm
                    || searchable.includes(searchTerm);

                const visible =
                    matchesFilter && matchesSearch;

                row.classList.toggle("hidden", !visible);

                if (visible) {
                    visibleRows += 1;
                }
            });

            groups.forEach((group) => {
                const groupRows = Array.from(
                    group.querySelectorAll(
                        "[data-details-row]"
                    )
                );

                const hasVisibleRows = groupRows.some(
                    (row) => !row.classList.contains("hidden")
                );

                group.classList.toggle(
                    "hidden",
                    !hasVisibleRows
                );

                if (hasVisibleRows && searchTerm) {
                    group.open = true;
                }
            });

            emptyState?.classList.toggle(
                "hidden",
                visibleRows > 0
            );
        };

        searchInput?.addEventListener(
            "input",
            applyFilter
        );

        filterButtons.forEach((button) => {
            button.addEventListener("click", () => {
                activeFilter =
                    button.dataset.detailsFilter ?? "all";

                updateButtonState();
                applyFilter();
            });
        });

        updateButtonState();
        applyFilter();
    };

    const openModal = async (button) => {
        lastFocusedElement = button;

        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.classList.add("overflow-hidden");

        content.innerHTML = loadingMarkup;

        if (title) {
            title.textContent = "Employee Details";
        }

        activeRequest?.abort();
        activeRequest = new AbortController();

        try {
            const response = await fetch(
                button.dataset.employeeDetailsUrl,
                {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    signal: activeRequest.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Request failed with status ${response.status}`
                );
            }

            const data = await response.json();

            content.innerHTML = data.html;

            if (title) {
                title.textContent =
                    data.employee_name ?? "Employee Details";
            }

            initializeDetailsFilters();
        } catch (error) {
            if (error.name === "AbortError") {
                return;
            }

            console.error(
                "Employee detail request failed:",
                error
            );

            content.innerHTML = errorMarkup;
        } finally {
            activeRequest = null;
        }
    };

    const closeModal = () => {
        activeRequest?.abort();
        activeRequest = null;

        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.classList.remove("overflow-hidden");

        content.innerHTML = "";
        lastFocusedElement?.focus();
    };

    document.addEventListener("click", (event) => {
        const detailButton = event.target.closest(
            "[data-employee-details-button]"
        );

        if (detailButton) {
            openModal(detailButton);
            return;
        }

        const closeButton = event.target.closest(
            "[data-employee-details-close]"
        );

        if (closeButton) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape"
            && !modal.classList.contains("hidden")
        ) {
            closeModal();
        }
    });
});

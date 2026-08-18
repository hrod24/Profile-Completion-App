document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('set-pic-page');

    if (!page) {
        return;
    }

    /*
     * ============================================================
     * ELEMENTS
     * ============================================================
     */

    const endpoint = page.dataset.endpoint;

    const searchInput = document.getElementById(
        'pic-employee-search'
    );

    const clearCompanyButton = document.getElementById(
        'clear-company-filters'
    );

    const clearSourceButton = document.getElementById(
        'clear-source-filters'
    );

    const clearSelectionButton = document.getElementById(
        'clear-employee-selection'
    );

    const tableContainer = document.getElementById(
        'set-pic-table-container'
    );

    const loadingIndicator = document.getElementById(
        'employee-table-loading'
    );

    const resultCount = document.getElementById(
        'employee-result-count'
    );

    const selectedCount = document.getElementById(
        'selected-employee-count'
    );

    const assignButton = document.getElementById(
        'assign-pic-button'
    );

    const assignForm = document.getElementById(
        'assign-pic-form'
    );

    const selectedInputsContainer = document.getElementById(
        'selected-employee-inputs'
    );

    const searchStatus = document.getElementById(
        'set-pic-search-status'
    );

    const companyFilterLabel = document.getElementById(
        'set-pic-company-filter-label'
    );

    const sourceFilterLabel = document.getElementById(
        'set-pic-source-filter-label'
    );

    const downloadButton =
        document.querySelector("#set-pic-download");


    downloadButton?.addEventListener(
        "click",
        (event) => {
            event.preventDefault();

            const url = new URL(
                downloadButton.href,
                window.location.origin
            );

            /*
            * SEARCH
            */
            const search =
                searchInput?.value.trim() ?? "";

            if (search) {
                url.searchParams.set(
                    "search",
                    search
                );
            }

            /*
            * COMPANY
            */
            document
                .querySelectorAll(
                    ".company-filter-checkbox:checked"
                )
                .forEach((checkbox) => {
                    url.searchParams.append(
                        "companies[]",
                        checkbox.value
                    );
                });

            /*
            * SOURCE
            */
            document
                .querySelectorAll(
                    ".source-filter-checkbox:checked"
                )
                .forEach((checkbox) => {
                    url.searchParams.append(
                        "sources[]",
                        checkbox.value
                    );
                });

            window.location.href =
                url.toString();
        }
    );

    /*
     * Pastikan elemen utama tersedia.
     */
    if (
        !endpoint ||
        !searchInput ||
        !tableContainer ||
        !selectedInputsContainer
    ) {
        return;
    }

    /*
     * ============================================================
     * STATE
     * ============================================================
     */

    /*
     * Menyimpan employee yang dipilih.
     *
     * Selection tetap tersimpan walaupun:
     * - search berubah;
     * - company filter berubah;
     * - source filter berubah;
     * - pagination berubah.
     */
    const selectedEmployeeIds = new Set();

    let searchTimer = null;

    /*
     * Digunakan untuk membatalkan AJAX sebelumnya
     * ketika user melakukan search/filter dengan cepat.
     */
    let activeRequest = null;

    /*
     * ============================================================
     * FILTER HELPERS
     * ============================================================
     */

    const getCompanyCheckboxes = () => {
        return Array.from(
            document.querySelectorAll(
                '.company-filter-checkbox'
            )
        );
    };

    const getSourceCheckboxes = () => {
        return Array.from(
            document.querySelectorAll(
                '.source-filter-checkbox'
            )
        );
    };

    /*
     * Company yang sedang dipilih.
     */
    const getSelectedCompanies = () => {
        return getCompanyCheckboxes()
            .filter(
                (checkbox) => checkbox.checked
            )
            .map(
                (checkbox) => checkbox.value
            );
    };

    /*
     * Source yang sedang dipilih.
     */
    const getSelectedSources = () => {
        return getSourceCheckboxes()
            .filter(
                (checkbox) => checkbox.checked
            )
            .map(
                (checkbox) => checkbox.value
            );
    };

    const updateFilterLabels = () => {
        const selectedCompanies =
            getSelectedCompanies();

        const selectedSources =
            getSelectedSources();

        /*
        * Company label
        */
        if (companyFilterLabel) {
            if (selectedCompanies.length > 0) {
                companyFilterLabel.textContent =
                    `Company (${selectedCompanies.length})`;
            } else {
                companyFilterLabel.textContent =
                    'Filter Company';
            }
        }

        /*
        * Source label
        */
        if (sourceFilterLabel) {
            if (selectedSources.length > 0) {
                sourceFilterLabel.textContent =
                    `Source (${selectedSources.length})`;
            } else {
                sourceFilterLabel.textContent =
                    'Filter Source';
            }
        }
    };

    /*
     * ============================================================
     * URL BUILDER
     * ============================================================
     */

    const buildRequestUrl = () => {
        const url = new URL(
            endpoint,
            window.location.origin
        );

        /*
         * Search.
         */
        const search = searchInput.value.trim();

        if (search !== '') {
            url.searchParams.set(
                'search',
                search
            );
        }

        /*
         * Company filter.
         */
        getSelectedCompanies().forEach(
            (company) => {
                url.searchParams.append(
                    'companies[]',
                    company
                );
            }
        );

        /*
         * Source filter.
         */
        getSelectedSources().forEach(
            (source) => {
                url.searchParams.append(
                    'sources[]',
                    source
                );
            }
        );

        return url.toString();
    };

    /*
     * ============================================================
     * SELECTED EMPLOYEE
     * ============================================================
     */

    const syncHiddenInputs = () => {
        selectedInputsContainer.innerHTML = '';

        selectedEmployeeIds.forEach(
            (employeeId) => {
                const input =
                    document.createElement(
                        'input'
                    );

                input.type = 'hidden';

                input.name =
                    'employee_ids[]';

                input.value =
                    employeeId;

                selectedInputsContainer.appendChild(
                    input
                );
            }
        );

        if (selectedCount) {
            selectedCount.textContent =
                selectedEmployeeIds.size;
        }

        if (assignButton) {
            assignButton.disabled =
                selectedEmployeeIds.size === 0;
        }
    };

    /*
     * ============================================================
     * SELECT ALL STATE
     * ============================================================
     */

    const updateSelectAllState = () => {
        const selectAll =
            document.getElementById(
                'select-all-employees'
            );

        if (!selectAll) {
            return;
        }

        const rowCheckboxes = Array.from(
            tableContainer.querySelectorAll(
                '.employee-checkbox'
            )
        );

        /*
         * Tidak ada employee pada halaman.
         */
        if (rowCheckboxes.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;

            return;
        }

        const selectedOnPage =
            rowCheckboxes.filter(
                (checkbox) =>
                    checkbox.checked
            ).length;

        /*
         * Semua employee di halaman dipilih.
         */
        selectAll.checked =
            selectedOnPage ===
            rowCheckboxes.length;

        /*
         * Hanya sebagian employee dipilih.
         */
        selectAll.indeterminate =
            selectedOnPage > 0 &&
            selectedOnPage <
                rowCheckboxes.length;
    };

    /*
     * ============================================================
     * TABLE EVENTS
     * ============================================================
     */

    const bindTableEvents = () => {
        const rowCheckboxes =
            tableContainer.querySelectorAll(
                '.employee-checkbox'
            );

        /*
         * Employee checkbox.
         */
        rowCheckboxes.forEach(
            (checkbox) => {
                const employeeId =
                    String(
                        checkbox.dataset
                            .employeeId ?? ''
                    );

                if (employeeId === '') {
                    return;
                }

                /*
                 * Pulihkan status checkbox dari Set.
                 */
                checkbox.checked =
                    selectedEmployeeIds.has(
                        employeeId
                    );

                checkbox.addEventListener(
                    'change',
                    () => {
                        if (
                            checkbox.checked
                        ) {
                            selectedEmployeeIds.add(
                                employeeId
                            );
                        } else {
                            selectedEmployeeIds.delete(
                                employeeId
                            );
                        }

                        syncHiddenInputs();

                        updateSelectAllState();
                    }
                );
            }
        );

        /*
         * Select All.
         *
         * Hanya memilih employee yang ada
         * pada halaman pagination saat ini.
         */
        const selectAll =
            document.getElementById(
                'select-all-employees'
            );

        selectAll?.addEventListener(
            'change',
            () => {
                rowCheckboxes.forEach(
                    (checkbox) => {
                        const employeeId =
                            String(
                                checkbox.dataset
                                    .employeeId ?? ''
                            );

                        if (
                            employeeId === ''
                        ) {
                            return;
                        }

                        checkbox.checked =
                            selectAll.checked;

                        if (
                            selectAll.checked
                        ) {
                            selectedEmployeeIds.add(
                                employeeId
                            );
                        } else {
                            selectedEmployeeIds.delete(
                                employeeId
                            );
                        }
                    }
                );

                syncHiddenInputs();

                updateSelectAllState();
            }
        );

        /*
         * ========================================================
         * AJAX PAGINATION
         * ========================================================
         */

        tableContainer
            .querySelectorAll(
                'a[href]'
            )
            .forEach((link) => {
                link.addEventListener(
                    'click',
                    (event) => {
                        const url =
                            new URL(
                                link.href,
                                window.location
                                    .origin
                            );

                        /*
                         * Hanya interception link pagination.
                         */
                        if (
                            !url.searchParams.has(
                                'page'
                            )
                        ) {
                            return;
                        }

                        event.preventDefault();

                        loadEmployees(
                            url.toString()
                        );
                    }
                );
            });

        updateSelectAllState();
    };

    /*
     * ============================================================
     * AJAX LOAD
     * ============================================================
     */

    const loadEmployees = async (
        requestedUrl = null
    ) => {
        const url =
            requestedUrl ??
            buildRequestUrl();

        /*
         * Simpan filter ke browser URL langsung.
         *
         * Jadi ketika:
         *
         * Company A dicentang:
         * ?companies[]=Company+A
         *
         * Source HEAD OFFICE dicentang:
         * ?sources[]=HEAD+OFFICE
         */
        window.history.replaceState(
            {},
            '',
            url
        );

        /*
         * Batalkan request sebelumnya.
         */
        if (activeRequest) {
            activeRequest.abort();
        }

        const controller =
            new AbortController();

        activeRequest =
            controller;

        /*
         * Loading UI.
         */
        loadingIndicator?.classList.remove(
            'hidden'
        );

        loadingIndicator?.classList.add(
            'flex'
        );

        tableContainer.setAttribute(
            'aria-busy',
            'true'
        );

        tableContainer.classList.add(
            'opacity-50'
        );

        if (searchStatus) {
            searchStatus.textContent =
                'Loading employee data.';
        }

        try {
            const response = await fetch(
                url,
                {
                    method: 'GET',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    signal:
                        controller.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Failed to load employees. HTTP ${response.status}`
                );
            }

            const data =
                await response.json();

            /*
             * Abaikan response lama jika request
             * lain sudah dibuat setelah request ini.
             */
            if (
                activeRequest !==
                controller
            ) {
                return;
            }

            /*
             * Update table.
             */
            tableContainer.innerHTML =
                data.html;

            /*
             * Update total employee.
             */
            if (resultCount) {
                resultCount.textContent =
                    Number(
                        data.total ?? 0
                    ).toLocaleString();
            }

            /*
             * Karena table HTML diganti,
             * event checkbox/pagination harus
             * dipasang kembali.
             */
            bindTableEvents();

            /*
             * Sinkronkan hidden input.
             */
            syncHiddenInputs();

            if (searchStatus) {
                searchStatus.textContent =
                    `${data.total ?? 0} employee found.`;
            }
        } catch (error) {
            /*
             * AbortError adalah normal ketika
             * request sebelumnya dibatalkan.
             */
            if (
                error.name ===
                'AbortError'
            ) {
                return;
            }

            console.error(
                'Set PIC employee request failed:',
                error
            );

            if (searchStatus) {
                searchStatus.textContent =
                    'Employee data failed to load.';
            }

            alert(
                'Data employee gagal dimuat. '
                + 'Silakan coba kembali.'
            );
        } finally {
            /*
             * Hanya request paling baru yang boleh
             * mematikan loading indicator.
             */
            if (
                activeRequest ===
                controller
            ) {
                activeRequest = null;

                loadingIndicator?.classList.add(
                    'hidden'
                );

                loadingIndicator?.classList.remove(
                    'flex'
                );

                tableContainer.setAttribute(
                    'aria-busy',
                    'false'
                );

                tableContainer.classList.remove(
                    'opacity-50'
                );
            }
        }
    };

    /*
     * ============================================================
     * SEARCH
     * ============================================================
     */

    searchInput.addEventListener(
        'input',
        () => {
            window.clearTimeout(
                searchTimer
            );

            searchTimer =
                window.setTimeout(
                    () => {
                        /*
                         * buildRequestUrl()
                         * tidak memiliki page,
                         * sehingga search baru
                         * kembali ke page pertama.
                         */
                        loadEmployees();
                    },
                    350
                );
        }
    );

    /*
     * ============================================================
     * COMPANY FILTER
     * ============================================================
     */

    getCompanyCheckboxes().forEach(
        (checkbox) => {
            checkbox.addEventListener(
                'change',
                () => {
                    updateFilterLabels();

                    loadEmployees();
                }
            );
        }
    );

    /*
     * ============================================================
     * SOURCE FILTER
     * ============================================================
     */

    getSourceCheckboxes().forEach(
        (checkbox) => {
            checkbox.addEventListener(
                'change',
                () => {
                    updateFilterLabels();

                    loadEmployees();
                }
            );
        }
    );

    /*
     * ============================================================
     * CLEAR COMPANY FILTER
     * ============================================================
     */

    clearCompanyButton?.addEventListener(
        'click',
        () => {
            getCompanyCheckboxes().forEach(
                (checkbox) => {
                    checkbox.checked = false;
                }
            );

            updateFilterLabels();

            loadEmployees();
        }
    );

    /*
     * ============================================================
     * CLEAR SOURCE FILTER
     * ============================================================
     */

    clearSourceButton?.addEventListener(
        'click',
        () => {
            getSourceCheckboxes().forEach(
                (checkbox) => {
                    checkbox.checked = false;
                }
            );

            updateFilterLabels();

            loadEmployees();
        }
    );

    /*
     * ============================================================
     * CLEAR EMPLOYEE SELECTION
     * ============================================================
     */

    clearSelectionButton?.addEventListener(
        'click',
        () => {
            selectedEmployeeIds.clear();

            tableContainer
                .querySelectorAll(
                    '.employee-checkbox'
                )
                .forEach(
                    (checkbox) => {
                        checkbox.checked =
                            false;
                    }
                );

            syncHiddenInputs();

            updateSelectAllState();
        }
    );

    /*
     * ============================================================
     * ASSIGN PIC FORM
     * ============================================================
     */

    assignForm?.addEventListener(
        'submit',
        (event) => {
            /*
             * Employee wajib dipilih.
             */
            if (
                selectedEmployeeIds.size ===
                0
            ) {
                event.preventDefault();

                alert(
                    'Pilih minimal satu employee.'
                );

                return;
            }

            /*
             * Pastikan hidden input terbaru
             * sudah masuk ke form sebelum submit.
             */
            syncHiddenInputs();
        }
    );

    /*
     * ============================================================
     * INITIALIZE
     * ============================================================
     */

    syncHiddenInputs();

    bindTableEvents();

    updateFilterLabels();
});
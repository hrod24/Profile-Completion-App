document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('set-pic-page');

    if (!page) {
        return;
    }

    const endpoint = page.dataset.endpoint;
    const searchInput = document.getElementById('pic-employee-search');
    const companyCheckboxes = document.querySelectorAll(
        '.company-filter-checkbox'
    );

    const clearCompanyButton = document.getElementById(
        'clear-company-filters'
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

    /*
     * Selection disimpan dalam Set agar pilihan tidak hilang
     * ketika search, filter, atau pagination berubah.
     */
    const selectedEmployeeIds = new Set();

    let searchTimer = null;
    let activeRequest = null;

    const getSelectedCompanies = () => {
        return Array.from(companyCheckboxes)
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);
    };

    const buildRequestUrl = () => {
        const url = new URL(endpoint, window.location.origin);

        const search = searchInput.value.trim();

        if (search !== '') {
            url.searchParams.set('search', search);
        }

        getSelectedCompanies().forEach((company) => {
            url.searchParams.append('companies[]', company);
        });

        return url.toString();
    };

    const syncHiddenInputs = () => {
        selectedInputsContainer.innerHTML = '';

        selectedEmployeeIds.forEach((employeeId) => {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = employeeId;

            selectedInputsContainer.appendChild(input);
        });

        selectedCount.textContent = selectedEmployeeIds.size;
        assignButton.disabled = selectedEmployeeIds.size === 0;
    };

    const updateSelectAllState = () => {
        const selectAll = document.getElementById(
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

        const selectedOnPage = rowCheckboxes.filter(
            (checkbox) => checkbox.checked
        ).length;

        selectAll.checked =
            rowCheckboxes.length > 0 &&
            selectedOnPage === rowCheckboxes.length;

        selectAll.indeterminate =
            selectedOnPage > 0 &&
            selectedOnPage < rowCheckboxes.length;
    };

    const bindTableEvents = () => {
        const rowCheckboxes = tableContainer.querySelectorAll(
            '.employee-checkbox'
        );

        rowCheckboxes.forEach((checkbox) => {
            const employeeId = checkbox.dataset.employeeId;

            checkbox.checked = selectedEmployeeIds.has(
                employeeId
            );

            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    selectedEmployeeIds.add(employeeId);
                } else {
                    selectedEmployeeIds.delete(employeeId);
                }

                syncHiddenInputs();
                updateSelectAllState();
            });
        });

        const selectAll = document.getElementById(
            'select-all-employees'
        );

        selectAll?.addEventListener('change', () => {
            rowCheckboxes.forEach((checkbox) => {
                const employeeId =
                    checkbox.dataset.employeeId;

                checkbox.checked = selectAll.checked;

                if (selectAll.checked) {
                    selectedEmployeeIds.add(employeeId);
                } else {
                    selectedEmployeeIds.delete(employeeId);
                }
            });

            syncHiddenInputs();
            updateSelectAllState();
        });

        /*
         * Pagination tetap dilakukan melalui AJAX.
         */
        tableContainer
            .querySelectorAll('a[href]')
            .forEach((link) => {
                link.addEventListener('click', (event) => {
                    const url = new URL(link.href);

                    if (!url.searchParams.has('page')) {
                        return;
                    }

                    event.preventDefault();
                    loadEmployees(link.href);
                });
            });

        updateSelectAllState();
    };

    const loadEmployees = async (requestedUrl = null) => {
        const url = requestedUrl ?? buildRequestUrl();

        /*
         * Batalkan request sebelumnya ketika user mengetik cepat.
         */
        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        loadingIndicator.classList.remove('hidden');
        loadingIndicator.classList.add('flex');

        tableContainer.setAttribute('aria-busy', 'true');
        tableContainer.classList.add('opacity-50');

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error(
                    'Gagal mengambil data employee.'
                );
            }

            const data = await response.json();

            tableContainer.innerHTML = data.html;
            resultCount.textContent = data.total;

            bindTableEvents();

            /*
             * Simpan filter pada URL browser.
             */
            window.history.replaceState(
                {},
                '',
                url
            );
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
                alert(
                    'Data employee gagal dimuat. Silakan coba kembali.'
                );
            }
        } finally {
            loadingIndicator.classList.add('hidden');
            loadingIndicator.classList.remove('flex');

            tableContainer.setAttribute('aria-busy', 'false');
            tableContainer.classList.remove('opacity-50');
        }
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);

        searchTimer = window.setTimeout(() => {
            loadEmployees();
        }, 350);
    });

    companyCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            loadEmployees();
        });
    });

    clearCompanyButton.addEventListener('click', () => {
        companyCheckboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });

        loadEmployees();
    });

    clearSelectionButton.addEventListener('click', () => {
        selectedEmployeeIds.clear();

        tableContainer
            .querySelectorAll('.employee-checkbox')
            .forEach((checkbox) => {
                checkbox.checked = false;
            });

        syncHiddenInputs();
        updateSelectAllState();
    });

    assignForm.addEventListener('submit', (event) => {
        if (selectedEmployeeIds.size === 0) {
            event.preventDefault();

            alert('Pilih minimal satu employee.');
        }
    });

    syncHiddenInputs();
    bindTableEvents();
});
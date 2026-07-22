document.addEventListener(
    "DOMContentLoaded",
    () => {
        /*
         * ============================================================
         * 1. AMBIL ELEMEN UTAMA DASHBOARD
         * ============================================================
         */
        const form = document.querySelector(
            "[data-employee-search-form]"
        );

        /*
         * File ini juga di-import pada halaman lain.
         * Jika form dashboard tidak ada, hentikan script.
         */
        if (!form) {
            return;
        }

        const input = form.querySelector(
            "[data-employee-search-input]"
        );

        const resetSearchButton =
            form.querySelector(
                "[data-employee-search-reset]"
            );

        const results = document.querySelector(
            "[data-employee-search-results]"
        );

        const statistics =
            document.querySelector(
                "[data-dashboard-statistics]"
            );

        const loadingIndicator =
            document.querySelector(
                "[data-employee-search-loading]"
            );

        const status = document.querySelector(
            "[data-employee-search-status]"
        );

        /*
         * Root dari seluruh filter.
         *
         * Fallback data-company-filter ditambahkan agar tetap
         * kompatibel dengan versi component lama.
         */
        const filtersRoot =
            document.querySelector(
                "[data-dashboard-filters]"
            ) ??
            document.querySelector(
                "[data-company-filter]"
            );

        /*
         * Isi <ul> department akan diganti melalui AJAX.
         */
        const departmentOptions =
            document.querySelector(
                "[data-department-filter-options]"
            );

        const companyFilterLabel =
            document.querySelector(
                "[data-company-filter-label]"
            );

        const businessUnitFilterLabel =
            document.querySelector(
                "[data-business-unit-filter-label]"
            );

        const departmentFilterLabel =
            document.querySelector(
                "[data-department-filter-label]"
            );

        if (!input || !results) {
            return;
        }

        /*
         * Timer untuk debounce live search.
         */
        let debounceTimer = null;

        /*
         * Menyimpan request yang sedang berjalan.
         *
         * Jika user mengganti filter terlalu cepat,
         * request sebelumnya akan dibatalkan.
         */
        let activeRequest = null;

        /*
         * ============================================================
         * 2. HELPER CHECKBOX
         * ============================================================
         */

        /*
         * Selector checkbox setiap jenis filter.
         */
        const checkboxSelectors = {
            company:
                "[data-company-filter-checkbox]",

            businessUnit:
                "[data-business-unit-filter-checkbox]",

            department:
                "[data-department-filter-checkbox]",
        };

        /*
         * Ambil checkbox dari DOM setiap kali fungsi dipanggil.
         *
         * Department checkbox tidak boleh disimpan satu kali saja,
         * karena isi dropdown department diganti melalui AJAX.
         */
        const getCheckboxes = (selector) => {
            return Array.from(
                document.querySelectorAll(
                    selector
                )
            );
        };

        /*
         * Ambil seluruh value checkbox yang sedang dicentang.
         */
        const getCheckedValues = (
            selector
        ) => {
            return getCheckboxes(selector)
                .filter(
                    (checkbox) =>
                        checkbox.checked
                )
                .map(
                    (checkbox) =>
                        checkbox.value
                );
        };

        /*
         * Menyesuaikan checkbox berdasarkan URL.
         *
         * Digunakan ketika user menekan tombol:
         * - Back.
         * - Forward.
         */
        const syncCheckboxesFromUrl = (
            selector,
            parameterName,
            url
        ) => {
            const selectedValues =
                url.searchParams.getAll(
                    parameterName
                );

            getCheckboxes(selector).forEach(
                (checkbox) => {
                    checkbox.checked =
                        selectedValues.includes(
                            checkbox.value
                        );
                }
            );
        };

        /*
         * ============================================================
         * 3. LOADING STATE
         * ============================================================
         */
        const setLoading = (isLoading) => {
            results.setAttribute(
                "aria-busy",
                isLoading
                    ? "true"
                    : "false"
            );

            if (loadingIndicator) {
                loadingIndicator.hidden =
                    !isLoading;

                loadingIndicator.style.display =
                    isLoading
                        ? "flex"
                        : "none";
            }

            input.classList.toggle(
                "pr-11",
                isLoading
            );
        };

        /*
         * ============================================================
         * 4. LABEL TOMBOL FILTER
         * ============================================================
         */
        const updateFilterLabel = ({
            labelElement,
            checkboxSelector,
            defaultText,
        }) => {
            if (!labelElement) {
                return;
            }

            const selectedCount =
                getCheckedValues(
                    checkboxSelector
                ).length;

            labelElement.textContent =
                selectedCount > 0
                    ? `${defaultText} (${selectedCount})`
                    : defaultText;
        };

        const updateAllFilterLabels = () => {
            updateFilterLabel({
                labelElement:
                    companyFilterLabel,

                checkboxSelector:
                    checkboxSelectors.company,

                defaultText:
                    "Filter Company",
            });

            updateFilterLabel({
                labelElement:
                    businessUnitFilterLabel,

                checkboxSelector:
                    checkboxSelectors
                        .businessUnit,

                defaultText:
                    "Filter Division",
            });

            updateFilterLabel({
                labelElement:
                    departmentFilterLabel,

                checkboxSelector:
                    checkboxSelectors.department,

                defaultText:
                    "Filter Department",
            });
        };

        /*
         * ============================================================
         * 5. BUAT URL SEARCH DAN FILTER
         * ============================================================
         */
        const createSearchUrl = () => {
            /*
             * Mulai dari URL action form agar parameter lama
             * yang sudah tidak dipilih tidak ikut terbawa.
             */
            const url = new URL(
                form.action,
                window.location.origin
            );

            const keyword =
                input.value.trim();

            if (keyword !== "") {
                url.searchParams.set(
                    "search",
                    keyword
                );
            }

            /*
             * Company.
             */
            getCheckedValues(
                checkboxSelectors.company
            ).forEach((value) => {
                url.searchParams.append(
                    "company[]",
                    value
                );
            });

            /*
             * Business Unit / Division.
             */
            getCheckedValues(
                checkboxSelectors.businessUnit
            ).forEach((value) => {
                url.searchParams.append(
                    "business_unit[]",
                    value
                );
            });

            /*
             * Department.
             */
            getCheckedValues(
                checkboxSelectors.department
            ).forEach((value) => {
                url.searchParams.append(
                    "department[]",
                    value
                );
            });

            return url;
        };

        /*
         * ============================================================
         * 6. REQUEST AJAX UTAMA
         * ============================================================
         */
        const loadResults = async (
            url,
            {
                updateHistory = true,
            } = {}
        ) => {
            /*
             * Batalkan request sebelumnya.
             */
            activeRequest?.abort();

            const requestController =
                new AbortController();

            activeRequest =
                requestController;

            setLoading(true);

            try {
                const response = await fetch(
                    url.toString(),
                    {
                        method: "GET",

                        headers: {
                            Accept:
                                "application/json",

                            "X-Requested-With":
                                "XMLHttpRequest",
                        },

                        signal:
                            requestController
                                .signal,
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Request failed with status ${response.status}`
                    );
                }

                const data =
                    await response.json();

                /*
                 * Perbarui tabel dan pagination.
                 */
                if (
                    typeof data.html ===
                    "string"
                ) {
                    results.innerHTML =
                        data.html;
                }

                /*
                 * Perbarui card dan progress.
                 */
                if (
                    statistics &&
                    typeof data.statisticsHtml ===
                        "string"
                ) {
                    statistics.innerHTML =
                        data.statisticsHtml;
                }

                /*
                 * Perbarui isi dropdown department.
                 *
                 * Saat business unit berubah, server akan
                 * mengembalikan department yang sesuai.
                 */
                if (
                    departmentOptions &&
                    typeof data.departmentOptionsHtml ===
                        "string"
                ) {
                    departmentOptions.innerHTML =
                        data.departmentOptionsHtml;
                }

                /*
                 * Department checkbox baru sudah masuk ke DOM.
                 * Hitung ulang label filter.
                 */
                updateAllFilterLabels();

                /*
                 * Controller dapat membuang department lama
                 * yang tidak lagi valid setelah business unit berubah.
                 *
                 * Karena itu URL harus diperbaiki berdasarkan
                 * selectedDepartments dari response server.
                 */
                const canonicalUrl =
                    new URL(
                        url.toString()
                    );

                canonicalUrl.searchParams.delete(
                    "department[]"
                );

                const validDepartments =
                    Array.isArray(
                        data.selectedDepartments
                    )
                        ? data.selectedDepartments
                        : [];

                validDepartments.forEach(
                    (departmentCode) => {
                        canonicalUrl.searchParams.append(
                            "department[]",
                            departmentCode
                        );
                    }
                );

                /*
                 * Perbarui URL browser tanpa reload halaman.
                 */
                if (updateHistory) {
                    window.history.replaceState(
                        {},
                        "",
                        canonicalUrl
                    );
                }

                /*
                 * Perbarui tulisan jumlah hasil.
                 */
                if (status) {
                    const total =
                        Number(data.total ?? 0);

                    status.textContent =
                        `${total} employee found / ` +
                        `${total} employee ditemukan`;
                }

                /*
                 * Tampilkan tombol reset search hanya ketika
                 * input search memiliki isi.
                 */
                resetSearchButton?.classList.toggle(
                    "hidden",
                    input.value.trim() === ""
                );
            } catch (error) {
                /*
                 * AbortError bukan kegagalan.
                 *
                 * Error ini muncul ketika request lama sengaja
                 * dibatalkan karena ada request yang lebih baru.
                 */
                if (
                    error.name ===
                    "AbortError"
                ) {
                    return;
                }

                console.error(
                    "Dashboard search/filter failed:",
                    error
                );

                if (status) {
                    status.textContent =
                        "Search failed / Pencarian gagal";
                }
            } finally {
                /*
                 * Request lama tidak boleh mematikan loading
                 * milik request yang lebih baru.
                 */
                if (
                    activeRequest ===
                    requestController
                ) {
                    setLoading(false);
                    activeRequest = null;
                }
            }
        };

        /*
         * ============================================================
         * 7. LIVE SEARCH
         * ============================================================
         */
        input.addEventListener(
            "input",
            () => {
                window.clearTimeout(
                    debounceTimer
                );

                debounceTimer =
                    window.setTimeout(
                        () => {
                            loadResults(
                                createSearchUrl()
                            );
                        },
                        350
                    );
            }
        );

        /*
         * Submit manual melalui tombol search.
         */
        form.addEventListener(
            "submit",
            (event) => {
                event.preventDefault();

                window.clearTimeout(
                    debounceTimer
                );

                loadResults(
                    createSearchUrl()
                );
            }
        );

        /*
         * Reset hanya keyword search.
         *
         * Company, business unit, dan department
         * tetap dipertahankan.
         */
        resetSearchButton?.addEventListener(
            "click",
            (event) => {
                event.preventDefault();

                input.value = "";
                input.focus();

                loadResults(
                    createSearchUrl()
                );
            }
        );

        /*
         * ============================================================
         * 8. EVENT CHECKBOX FILTER
         * ============================================================
         *
         * Menggunakan event delegation.
         *
         * Ini penting karena department checkbox akan diganti
         * melalui AJAX.
         */
        filtersRoot?.addEventListener(
            "change",
            (event) => {
                if (
                    !(
                        event.target instanceof
                        Element
                    )
                ) {
                    return;
                }

                const checkbox =
                    event.target.closest(
                        [
                            checkboxSelectors.company,
                            checkboxSelectors
                                .businessUnit,
                            checkboxSelectors
                                .department,
                        ].join(",")
                    );

                if (!checkbox) {
                    return;
                }

                window.clearTimeout(
                    debounceTimer
                );

                updateAllFilterLabels();

                loadResults(
                    createSearchUrl()
                );
            }
        );

        /*
         * ============================================================
         * 9. RESET FILTER
         * ============================================================
         */
        const resetSelectorMap = {
            company:
                checkboxSelectors.company,

            "business-unit":
                checkboxSelectors.businessUnit,

            department:
                checkboxSelectors.department,
        };

        filtersRoot?.addEventListener(
            "click",
            (event) => {
                if (
                    !(
                        event.target instanceof
                        Element
                    )
                ) {
                    return;
                }

                const resetButton =
                    event.target.closest(
                        "[data-filter-clear]"
                    );

                if (!resetButton) {
                    return;
                }

                event.preventDefault();

                const filterName =
                    resetButton.dataset
                        .filterClear;

                const checkboxSelector =
                    resetSelectorMap[
                        filterName
                    ];

                if (!checkboxSelector) {
                    return;
                }

                /*
                 * Lepaskan centang pada filter terkait.
                 */
                getCheckboxes(
                    checkboxSelector
                ).forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateAllFilterLabels();

                /*
                 * Jika business unit di-reset:
                 * - Department yang sedang dipilih tetap dikirim.
                 * - Controller akan menampilkan semua department.
                 *
                 * Hal ini memungkinkan filter hanya berdasarkan
                 * department tanpa business unit.
                 */
                loadResults(
                    createSearchUrl()
                );
            }
        );

        /*
         * ============================================================
         * 10. PAGINATION AJAX
         * ============================================================
         */
        results.addEventListener(
            "click",
            (event) => {
                if (
                    !(
                        event.target instanceof
                        Element
                    )
                ) {
                    return;
                }

                const link =
                    event.target.closest(
                        "a[href]"
                    );

                if (
                    !link ||
                    !results.contains(link)
                ) {
                    return;
                }

                const url = new URL(
                    link.href
                );

                /*
                 * Hanya intercept link internal.
                 */
                if (
                    url.origin !==
                    window.location.origin
                ) {
                    return;
                }

                event.preventDefault();

                loadResults(url);

                results.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        );

        /*
         * ============================================================
         * 11. BROWSER BACK DAN FORWARD
         * ============================================================
         */
        window.addEventListener(
            "popstate",
            () => {
                const url = new URL(
                    window.location.href
                );

                /*
                 * Sinkronkan input search.
                 */
                input.value =
                    url.searchParams.get(
                        "search"
                    ) ?? "";

                /*
                 * Sinkronkan checkbox yang saat ini tersedia.
                 *
                 * Department dropdown akan dirender ulang oleh
                 * response AJAX setelah request selesai.
                 */
                syncCheckboxesFromUrl(
                    checkboxSelectors.company,
                    "company[]",
                    url
                );

                syncCheckboxesFromUrl(
                    checkboxSelectors.businessUnit,
                    "business_unit[]",
                    url
                );

                syncCheckboxesFromUrl(
                    checkboxSelectors.department,
                    "department[]",
                    url
                );

                updateAllFilterLabels();

                loadResults(url, {
                    updateHistory: false,
                });
            }
        );

        /*
         * ============================================================
         * 12. INITIAL STATE
         * ============================================================
         */
        updateAllFilterLabels();

        resetSearchButton?.classList.toggle(
            "hidden",
            input.value.trim() === ""
        );
    }
);
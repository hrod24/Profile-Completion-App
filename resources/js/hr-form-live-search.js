document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector(
        '[data-live-employee-filter]'
    );

    if (!form) {
        return;
    }

    const searchInput = form.querySelector(
        '[data-employee-search]'
    );

    const picSelect = form.querySelector(
        '[data-pic-filter]'
    );

    const resultsContainer = document.querySelector(
        '[data-employee-results]'
    );

    const statusElement = document.querySelector(
        '[data-live-search-status]'
    );

    const resetButton = document.querySelector(
        '[data-reset-employee-filter]'
    );

    let debounceTimer = null;
    let requestController = null;

    const setLoading = (loading) => {
        resultsContainer?.setAttribute(
            'aria-busy',
            loading ? 'true' : 'false'
        );

        if (!statusElement) {
            return;
        }

        if (loading) {
            statusElement.textContent = 'Searching employee data...';
            statusElement.classList.remove('hidden');
            return;
        }

        statusElement.classList.add('hidden');
    };

    const buildUrl = () => {
        const url = new URL(
            form.action,
            window.location.origin
        );

        const search = searchInput?.value.trim() ?? '';
        const pic = picSelect?.value ?? '';

        if (search !== '') {
            url.searchParams.set('search', search);
        }

        if (pic !== '') {
            url.searchParams.set('pic', pic);
        }

        return url;
    };

    const updateResetButton = (url) => {
        if (!resetButton) {
            return;
        }

        const hasFilter =
            url.searchParams.has('search')
            || url.searchParams.has('pic');

        resetButton.classList.toggle(
            'hidden',
            !hasFilter
        );
    };

    const loadEmployees = async (
        url,
        updateHistory = true
    ) => {
        requestController?.abort();
        requestController = new AbortController();

        setLoading(true);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error(
                    `Request failed with status ${response.status}`
                );
            }

            const html = await response.text();

            const documentResult = new DOMParser()
                .parseFromString(html, 'text/html');

            const newResults = documentResult.querySelector(
                '[data-employee-results]'
            );

            if (!newResults || !resultsContainer) {
                throw new Error(
                    'Employee result container was not found.'
                );
            }

            resultsContainer.innerHTML =
                newResults.innerHTML;

            const newTotal = documentResult.querySelector(
                '[data-employee-total]'
            );

            document
                .querySelectorAll('[data-employee-total]')
                .forEach((element) => {
                    element.textContent =
                        newTotal?.textContent?.trim() ?? '0';
                });

            if (updateHistory) {
                window.history.replaceState(
                    {},
                    '',
                    url.toString()
                );
            }

            updateResetButton(url);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(error);

            if (statusElement) {
                statusElement.textContent =
                    'Employee data could not be loaded. Please try again.';

                statusElement.classList.remove('hidden');
                statusElement.classList.add(
                    'text-red-600'
                );
            }
        } finally {
            setLoading(false);
        }
    };

    const scheduleSearch = () => {
        window.clearTimeout(debounceTimer);

        debounceTimer = window.setTimeout(() => {
            loadEmployees(buildUrl());
        }, 350);
    };

    searchInput?.addEventListener(
        'input',
        scheduleSearch
    );

    picSelect?.addEventListener('change', () => {
        window.clearTimeout(debounceTimer);
        loadEmployees(buildUrl());
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        window.clearTimeout(debounceTimer);
        loadEmployees(buildUrl());
    });

    resultsContainer?.addEventListener(
        'click',
        (event) => {
            const paginationLink = event.target.closest(
                '[data-employee-pagination] a'
            );

            if (!paginationLink) {
                return;
            }

            event.preventDefault();

            loadEmployees(
                new URL(paginationLink.href)
            );
        }
    );

    resetButton?.addEventListener(
        'click',
        (event) => {
            event.preventDefault();

            window.clearTimeout(debounceTimer);

            if (searchInput) {
                searchInput.value = '';
            }

            if (picSelect) {
                picSelect.value = '';
            }

            loadEmployees(
                new URL(
                    form.action,
                    window.location.origin
                )
            );
        }
    );
});
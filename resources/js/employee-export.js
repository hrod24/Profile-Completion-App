document.addEventListener("click", (event) => {
    const exportLink = event.target.closest(
        "[data-employee-export-link]"
    );

    if (!exportLink) {
        return;
    }

    event.preventDefault();

    const exportUrl = new URL(
        exportLink.dataset.employeeExportUrl,
        window.location.origin
    );

    const currentParameters = new URLSearchParams(
        window.location.search
    );

    /* Pagination and AJAX-only flags must not affect the exported dataset. */
    currentParameters.delete("page");
    currentParameters.delete("partial");
    currentParameters.delete("ajax");

    currentParameters.forEach((value, key) => {
        exportUrl.searchParams.append(key, value);
    });

    window.location.assign(exportUrl.toString());
});

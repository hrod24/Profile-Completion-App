document.addEventListener("DOMContentLoaded", () => {
    const shell = document.querySelector("[data-app-shell]");

    if (!shell) {
        return;
    }

    const toggleButtons = document.querySelectorAll("[data-sidebar-toggle]");
    const closeButtons = document.querySelectorAll("[data-sidebar-close]");
    const overlay = document.querySelector("[data-sidebar-overlay]");
    const navLinks = document.querySelectorAll("[data-sidebar-nav-link]");
    const desktopMedia = window.matchMedia("(min-width: 1024px)");
    const storageKey = "kanmo-sidebar-collapsed";

    const closeMobileSidebar = () => {
        shell.classList.remove("is-mobile-open");
        document.body.style.overflow = "";
    };

    const restoreDesktopState = () => {
        if (!desktopMedia.matches) {
            shell.classList.remove("is-collapsed");
            return;
        }

        const collapsed = window.localStorage.getItem(storageKey) === "true";
        shell.classList.toggle("is-collapsed", collapsed);
    };

    const toggleSidebar = () => {
        if (desktopMedia.matches) {
            const collapsed = shell.classList.toggle("is-collapsed");
            window.localStorage.setItem(storageKey, String(collapsed));
            return;
        }

        const isOpen = shell.classList.toggle("is-mobile-open");
        document.body.style.overflow = isOpen ? "hidden" : "";
    };

    toggleButtons.forEach((button) => button.addEventListener("click", toggleSidebar));
    closeButtons.forEach((button) => button.addEventListener("click", closeMobileSidebar));
    overlay?.addEventListener("click", closeMobileSidebar);

    navLinks.forEach((link) => {
        link.addEventListener("click", () => {
            if (!desktopMedia.matches) {
                closeMobileSidebar();
            }
        });
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMobileSidebar();
        }
    });

    desktopMedia.addEventListener("change", () => {
        closeMobileSidebar();
        restoreDesktopState();
    });

    restoreDesktopState();
});

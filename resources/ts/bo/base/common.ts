import errors from "../../modules/errors";
import route from "../../modules/route";
import sweetalert from "../../modules/sweetalert";

window.addEventListener("DOMContentLoaded", () => {
    /** Popup action item. */
    const elementsAction = document.getElementsByClassName(
        "confirmActionTS"
    ) as HTMLCollectionOf<Element>;
    for (const element of elementsAction) {
        element.addEventListener("submit", function (e) {
            e.preventDefault();
            const el = e.target;
            if (!el || !(el instanceof HTMLFormElement)) {
                throw new Error(
                    "confirmDoneJS can only be executed on an exising form"
                );
            }
            sweetalert.methods.confirm(
                el,
                function () {
                    el.submit();
                },
                { icon: "warning" },
                el.getAttribute("data-sweetalert-title") ?? undefined,
                el.getAttribute("data-sweetalert-message") ?? undefined,
                el.getAttribute("data-sweetalert-btn-accept") ?? undefined,
                el.getAttribute("data-sweetalert-btn-deny") ?? undefined,
                el.getAttribute("data-sweetalert-btn-color") ?? undefined,
            );
            return false;
        });
    }
    /** Navigation size */
    const navigation = document.getElementById("offcanvas-navigation");
    const btnSideMenuToggle = document.getElementById("side-menu-toggle") as HTMLButtonElement | null;
    const routeNavigation = route.methods.route("bo.navigation.set");
    if (!routeNavigation) {
        throw new Error("Undefined route bo.navigation.set");
    }
    btnSideMenuToggle?.addEventListener("click", function () {
        const isNavigationExtended = navigation?.classList.contains("navigation-extended");
        window.axios
            .post(routeNavigation, { isExtended: !isNavigationExtended })
            .then(() => {
                btnSideMenuToggle.disabled = true;
                if (isNavigationExtended) {
                    navigation?.classList.remove("navigation-extended");
                    setTimeout(() => { btnSideMenuToggle.disabled = false; }, 300);
                } else {
                    btnSideMenuToggle.disabled = false;
                    navigation?.classList.add("navigation-labels-hidden");
                    navigation?.classList.add("navigation-transition");
                    navigation?.classList.add("navigation-extended");
                    setTimeout(() => {
                        navigation?.classList.remove("navigation-labels-hidden");
                        navigation?.classList.add("navigation-transition");
                    }, 300);
                    setTimeout(() => {
                        navigation?.classList.remove("navigation-transition");
                    }, 600);
                }
            })
            .catch(errors.methods.ajaxErrorHandler);
    });
    /** Navigation button */
    const myOffcanvas = document.getElementById("offcanvas-navigation") as HTMLDivElement|null;
    const navbarTogglerNavigation = document.getElementById("navbar-toggler-navigation") as HTMLButtonElement|null;
    navbarTogglerNavigation?.addEventListener("click", function () {
        navbarTogglerNavigation?.classList.toggle("navbar-toggler-active");
    });
    myOffcanvas?.addEventListener("hidden.bs.offcanvas", () => {
        navbarTogglerNavigation?.classList.remove("navbar-toggler-active");
    });
});

window.onload = function() {
    let documentBody: HTMLBodyElement|null;
    let loadingScreen: HTMLDivElement|null;
    let btnScroll: HTMLButtonElement|null;
    let homeTextContent: HTMLDivElement|null;

    selectors();
    events();
    setLatestGamesWidth();
    hideLoadingScreen();

    /**
     * Hide loading screen.
     */
    function hideLoadingScreen() {
        // Wait for components to be mounted.
        setTimeout(() => {
            documentBody?.classList.remove("overflow-hidden");
            loadingScreen?.classList.add("opacity-0");
            // Wait for the transition end.
            setTimeout(() => {
                loadingScreen?.classList.add("invisible");
            }, 300);
        }, 300);
    }

    /**
     * Set all selectors on the page.
     */
    function selectors() {
        documentBody = document.querySelector("body");
        loadingScreen = document.querySelector("#loading-screen");
        btnScroll = document.querySelector(".btn-scroll");
        homeTextContent = document.querySelector(".main-home-latest");
    }

    /**
     * Set all events on the page.
     */
    function events() {
        btnScroll?.addEventListener("click", scrollToTheTop);
        window.addEventListener("resize", setLatestGamesWidth);
    }

    /**
     * Set the width of the latest games content.
     */
    function setLatestGamesWidth() {
        setTimeout(() => {
            if (homeTextContent && homeTextContent.nextElementSibling)
                if (window.matchMedia("(min-width: 992px)").matches)
                    homeTextContent.setAttribute("style", "width:calc(100% - " +
                        (homeTextContent.nextElementSibling as HTMLDivElement).offsetWidth + "px)");
                else
                    homeTextContent.setAttribute("style", "width:100%");
        }, 100);
    }

    /**
     * Scroll to the top of the page.
     */
    function scrollToTheTop() {
        window.scrollTo(0, 0);
    }
};

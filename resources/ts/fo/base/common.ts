window.onload = function() {
    let documentBody: HTMLBodyElement|null;
    let loadingScreen: HTMLDivElement|null;
    let btnScroll: NodeListOf<HTMLButtonElement>;
    let homeTextContent: HTMLDivElement|null;
    let homeTextWrapper: HTMLDivElement|null;
    let homeTextTitle: HTMLParagraphElement|null;
    let latestGamesResizeObserver: ResizeObserver|null;
    let latestGamesStyleEl: HTMLStyleElement | null = null;

    const latestGamesAnimationName = `latest-games-scroll-${Math.random().toString(36).slice(2, 8)}`;
    const PX_PER_SECOND = 40;
    const PAUSE_START = 2;
    const PAUSE_END = 2;
    const FADE_DURATION = 0.3;
    const MARGE = 16;

    selectors();
    events();
    setLatestGamesWidth();
    initLatestGamesScroll();
    hideLoadingScreen();

    /**
     * Hide loading screen.
     * @return {void}
     */
    function hideLoadingScreen(): void {
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
     * @return {void}
     */
    function selectors(): void {
        documentBody = document.querySelector("body");
        loadingScreen = document.querySelector("#loading-screen");
        btnScroll = document.querySelectorAll(".btn-scroll");
        homeTextContent = document.querySelector(".main-home-latest");
        homeTextWrapper = document.querySelector(".home-text-content .position-relative");
        homeTextTitle = document.querySelector(".home-text-content .position-relative > p");
    }

    /**
     * Set all events on the page.
     * @return {void}
     */
    function events(): void {
        btnScroll?.forEach((btn): void => {
            btn.addEventListener("click", scrollToTheTop);
        });
        window.addEventListener("resize", setLatestGamesWidth);
        window.addEventListener("resize", updateLatestGamesScrollState);
    }

    /**
     * Set the width of the latest games content.
     * @return {void}
     */
    function setLatestGamesWidth(): void {
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
     * @return {void}
     */
    function scrollToTheTop(): void {
        window.scrollTo(0, 0);
    }

    /**
     * Initialise the latest games scrolling title animation.
     * @return {void}
     */
    function initLatestGamesScroll(): void {
        if (!homeTextWrapper || !homeTextTitle) return;
        updateLatestGamesScrollState();
        latestGamesResizeObserver = new ResizeObserver(updateLatestGamesScrollState);
        latestGamesResizeObserver.observe(homeTextWrapper);
    }

    /**
     * Ensure the dynamic style element exists.
     * @return {HTMLStyleElement}
     */
    function ensureLatestGamesStyleEl(): HTMLStyleElement {
        if (!latestGamesStyleEl) {
            latestGamesStyleEl = document.createElement("style");
            document.head.appendChild(latestGamesStyleEl);
        }
        return latestGamesStyleEl;
    }

    /**
     * Build the scrolling keyframes for the latest games title.
     * @param {number} distance
     * @return {void}
     */
    function buildLatestGamesKeyframes(distance: number): void {
        const scrollPhase = Math.max(distance / PX_PER_SECOND, 0.5);
        const total = PAUSE_START + scrollPhase + PAUSE_END + FADE_DURATION * 2;

        const p1 = (PAUSE_START / total) * 100;
        const p2 = ((PAUSE_START + scrollPhase) / total) * 100;
        const p3 = ((PAUSE_START + scrollPhase + PAUSE_END) / total) * 100;
        const p4 = ((PAUSE_START + scrollPhase + PAUSE_END + FADE_DURATION) / total) * 100;
        const p4Snap = Math.min(p4 + 0.05, 99.99);

        homeTextTitle?.style.setProperty("--scroll-duration", `${total}s`);
        homeTextTitle?.style.setProperty("--scroll-anim-name", latestGamesAnimationName);

        ensureLatestGamesStyleEl().textContent = `
            @keyframes ${latestGamesAnimationName} {
                0% { transform: translate3d(0,0,0); opacity: 1; }
                ${p1.toFixed(3)}% { transform: translate3d(0,0,0); opacity: 1; }
                ${p2.toFixed(3)}% { transform: translate3d(-${distance}px,0,0); opacity: 1; }
                ${p3.toFixed(3)}% { transform: translate3d(-${distance}px,0,0); opacity: 1; }
                ${p4.toFixed(3)}% { transform: translate3d(-${distance}px,0,0); opacity: 0; }
                ${p4Snap.toFixed(3)}% { transform: translate3d(0,0,0); opacity: 0; }
                100% { transform: translate3d(0,0,0); opacity: 1; }
            }
        `;
    }

    /**
     * Update the scroll state of the latest games title.
     * @return {void}
     */
    function updateLatestGamesScrollState(): void {
        if (!homeTextWrapper || !homeTextTitle) return;

        const wrapperWidth = homeTextWrapper.clientWidth;
        const titleWidth = homeTextTitle.scrollWidth;
        const overflow = titleWidth - wrapperWidth;

        homeTextTitle.classList.remove("position-absolute", "top-0", "start-0", "is-scrolling");
        homeTextTitle.style.transform = "";

        if (overflow > 0) {
            const distance = overflow + MARGE;
            buildLatestGamesKeyframes(distance);
            homeTextTitle.classList.add("position-absolute", "top-0", "start-0", "is-scrolling");
        }
    }
};

import { MarqueeScroll } from "../../modules/marqueeScroll";

window.onload = function() {
    let documentBody: HTMLBodyElement|null;
    let loadingScreen: HTMLDivElement|null;
    let btnScroll: NodeListOf<HTMLButtonElement>;
    let homeTextContent: HTMLDivElement|null;
    let homeTextWrapper: HTMLDivElement|null;
    let homeTextTitle: HTMLParagraphElement|null;

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
        new MarqueeScroll({
            wrapper: homeTextWrapper,
            title: homeTextTitle,
            pauseStart: 4,
            classes: ["position-absolute", "top-0", "start-0", "is-scrolling"],
        }).start();
    }
};

import GLightbox from "glightbox";
import { type App, createApp, h, onBeforeUnmount, ref } from "vue";
import GlightboxToolbar from "./../components/GlightboxToolbar.vue";

interface GlightboxOptions {
  selector: string;
  labels: { like: string, fullscreen: string };
  likes: {
    isLiked: (pictureId: number) => boolean,
    count: (pictureId: number) => number,
    isBusy: () => boolean,
    toggle: (pictureId: number) => void,
  };
  getPictureId: (slideIndex: number) => number | null;
  pagination: {
    hasMore: () => boolean,
    loadMore: (loadedCount: number) => Promise<Array<string>>,
    threshold?: number,
  };
}

interface SlideEvent {
  current: { index: number };
}

export function useGlightbox({ selector, labels, likes, getPictureId, pagination }: GlightboxOptions) {
    const currentPictureId = ref<number | null>(null);
    const isFullscreen = ref<boolean>(false);
    const threshold = pagination.threshold ?? 2;
    let viewer: GLightbox | null = null;
    let toolbarApp: App | null = null;
    let toolbarHost: HTMLElement | null = null;
    let isOpen = false;
    let slideCount = 0;
    let loadingMore = false;
    let needsRebuild = false;

    /**
    * Toggle the browser fullscreen mode.
    * @return {void}
    */
    function toggleFullscreen(): void {
        if (document.fullscreenElement) {
            void document.exitFullscreen();
        } else {
            void document.documentElement.requestFullscreen();
        }
    }

    /**
    * Mount the toolbar component inside the lightbox.
    * The render function reads reactive state, so it re-renders by itself.
    * @return {void}
    */
    function mountToolbar(): void {
        const container = document.querySelector(".glightbox-container .gcontainer");
        if (!container) {
            return;
        }
        toolbarHost = document.createElement("div");
        container.appendChild(toolbarHost);
        toolbarApp = createApp({
            render: () => {
                const pictureId = currentPictureId.value;
                return h(GlightboxToolbar, {
                    showLike: pictureId !== null,
                    liked: pictureId !== null && likes.isLiked(pictureId),
                    count: pictureId !== null ? likes.count(pictureId) : 0,
                    busy: likes.isBusy(),
                    fullscreen: isFullscreen.value,
                    fullscreenSupported: document.fullscreenEnabled,
                    likeLabel: labels.like,
                    fullscreenLabel: labels.fullscreen,
                    onLike: () => pictureId !== null && likes.toggle(pictureId),
                    onFullscreen: toggleFullscreen,
                });
            },
        });
        toolbarApp.mount(toolbarHost);
    }

    /**
    * Unmount the toolbar component.
    * @return {void}
    */
    function unmountToolbar(): void {
        toolbarApp?.unmount();
        toolbarHost?.remove();
        toolbarApp = null;
        toolbarHost = null;
    }

    /**
    * Load the next pictures and append them as new slides.
    * @return {Promise<void>}
    */
    async function loadMoreSlides(): Promise<void> {
        const instance = viewer;
        if (!instance || loadingMore || !pagination.hasMore()) {
            return;
        }
        loadingMore = true;
        try {
            const hrefs = await pagination.loadMore(slideCount);
            if (viewer !== instance) {
                return;
            }
            hrefs.forEach((href) => instance.insertSlide({ href, type: "image" }));
            slideCount += hrefs.length;
            if (hrefs.length > 0) {
                needsRebuild = true;
            }
        } finally {
            loadingMore = false;
        }
    }

    /**
    * Recreate the lightbox instance.
    * If the viewer is currently open, the rebuild is deferred until it closes.
    * @return {void}
    */
    function init(): void {
        if (isOpen) {
            needsRebuild = true;
            return;
        }
        destroy();
        needsRebuild = false;
        viewer = new GLightbox({ selector });
        slideCount = document.querySelectorAll(selector).length;

        viewer.on("open", () => {
            isOpen = true;
            mountToolbar();
        });
        viewer.on("slide_changed", ({ current }: SlideEvent) => {
            currentPictureId.value = getPictureId(current.index);
            if (current.index >= slideCount - threshold) {
                void loadMoreSlides();
            }
        });
        viewer.on("close", () => {
            isOpen = false;
            unmountToolbar();
            currentPictureId.value = null;
            if (document.fullscreenElement) {
                void document.exitFullscreen();
            }
            if (needsRebuild) {
                setTimeout(init, 400);
            }
        });
    }

    /**
    * Destroy the lightbox instance and its toolbar.
    * @return {void}
    */
    function destroy(): void {
        unmountToolbar();
        viewer?.destroy();
        viewer = null;
        isOpen = false;
    }

    function onFullscreenChange(): void {
        isFullscreen.value = !!document.fullscreenElement;
    }

    document.addEventListener("fullscreenchange", onFullscreenChange);
    onBeforeUnmount(() => {
        destroy();
        document.removeEventListener("fullscreenchange", onFullscreenChange);
    });

    return { init, destroy };
}

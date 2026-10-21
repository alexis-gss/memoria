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
}

interface SlideEvent {
  current: { trigger: HTMLElement | null };
}

export function useGlightbox({ selector, labels, likes }: GlightboxOptions) {
    const currentPictureId = ref<number | null>(null);
    const isFullscreen = ref<boolean>(false);
    let viewer: GLightbox | null = null;
    let toolbarApp: App | null = null;
    let toolbarHost: HTMLElement | null = null;

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
    * Recreate the lightbox instance.
    * @return {void}
    */
    function init(): void {
        destroy();
        viewer = new GLightbox({ selector });
        viewer.on("open", mountToolbar);
        viewer.on("slide_changed", ({ current }: SlideEvent) => {
            const pictureId = current.trigger?.dataset.pictureId;
            currentPictureId.value = pictureId ? Number(pictureId) : null;
        });
        viewer.on("close", () => {
            unmountToolbar();
            currentPictureId.value = null;
            if (document.fullscreenElement) {
                void document.exitFullscreen();
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

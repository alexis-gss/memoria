export interface MarqueeState {
    shouldScroll: boolean;
    duration: number;
}

export interface MarqueeOptions {
    wrapper: HTMLElement;
    title: HTMLElement;
    pxPerSecond?: number;
    pauseStart?: number;
    pauseEnd?: number;
    fadeDuration?: number;
    /** Extra distance added to the scroll so the end of the text isn't cut. */
    margin?: number;
    /** Horizontal offset of the resting position (px). */
    startOffset?: number;
    /** Px subtracted from the wrapper width when detecting overflow. */
    tolerance?: number;
    /** CSS classes added to the title while it scrolls (and removed before measuring). */
    classes?: string[];
    /** Called after each update, useful to sync a reactive state. */
    onChange?: (state: MarqueeState) => void;
}

export class MarqueeScroll {
    private readonly animationName = `marquee-scroll-${Math.random().toString(36).slice(2, 8)}`;
    private readonly wrapper: HTMLElement;
    private readonly title: HTMLElement;
    private readonly opts: Required<Omit<MarqueeOptions, "wrapper" | "title" | "onChange">>
    & Pick<MarqueeOptions, "onChange">;
    private styleEl: HTMLStyleElement | null = null;
    private resizeObserver: ResizeObserver | null = null;

    constructor(options: MarqueeOptions) {
        this.wrapper = options.wrapper;
        this.title = options.title;
        this.opts = {
            pxPerSecond: options.pxPerSecond ?? 40,
            pauseStart: options.pauseStart ?? 4,
            pauseEnd: options.pauseEnd ?? 2,
            fadeDuration: options.fadeDuration ?? 0.3,
            margin: options.margin ?? 16,
            startOffset: options.startOffset ?? 0,
            tolerance: options.tolerance ?? 0,
            classes: options.classes ?? [],
            onChange: options.onChange,
        };
    }

    /** Measure once and keep measuring whenever the wrapper is resized. */
    start(): void {
        this.update();
        this.resizeObserver = new ResizeObserver(() => this.update());
        this.resizeObserver.observe(this.wrapper);
    }

    /** Re-measure and rebuild the animation. */
    update(): void {
        const { classes, margin, tolerance, onChange } = this.opts;

        // Remove scroll classes first so the measure isn't affected by them.
        this.title.classList.remove(...classes);

        const overflow = this.title.scrollWidth - (this.wrapper.clientWidth - tolerance);

        if (overflow > 0) {
            const duration = this.buildKeyframes(overflow + margin);
            this.title.classList.add(...classes);
            onChange?.({ shouldScroll: true, duration });
        } else {
            onChange?.({ shouldScroll: false, duration: 0 });
        }
    }

    /** Stop observing and remove the injected <style>. */
    destroy(): void {
        this.resizeObserver?.disconnect();
        this.styleEl?.remove();
        this.styleEl = null;
    }

    private buildKeyframes(distance: number): number {
        const { pxPerSecond, pauseStart, pauseEnd, fadeDuration, startOffset } = this.opts;

        const scrollPhase = Math.max(distance / pxPerSecond, 0.5);
        const total = pauseStart + scrollPhase + pauseEnd + fadeDuration * 2;

        const p1 = (pauseStart / total) * 100;
        const p2 = ((pauseStart + scrollPhase) / total) * 100;
        const p3 = ((pauseStart + scrollPhase + pauseEnd) / total) * 100;
        const p4 = ((pauseStart + scrollPhase + pauseEnd + fadeDuration) / total) * 100;
        const p4Snap = Math.min(p4 + 0.05, 99.99);

        this.title.style.setProperty("--scroll-duration", `${total}s`);
        this.title.style.setProperty("--scroll-anim-name", this.animationName);

        if (!this.styleEl) {
            this.styleEl = document.createElement("style");
            document.head.appendChild(this.styleEl);
        }

        const rest = `translate3d(${startOffset}px,0,0)`;
        const end = `translate3d(-${distance}px,0,0)`;
        this.styleEl.textContent = `
            @keyframes ${this.animationName} {
                0% { transform: ${rest}; opacity: 1; }
                ${p1.toFixed(3)}% { transform: ${rest}; opacity: 1; }
                ${p2.toFixed(3)}% { transform: ${end}; opacity: 1; }
                ${p3.toFixed(3)}% { transform: ${end}; opacity: 1; }
                ${p4.toFixed(3)}% { transform: ${end}; opacity: 0; }
                ${p4Snap.toFixed(3)}% { transform: ${rest}; opacity: 0; }
                100% { transform: ${rest}; opacity: 1; }
            }
        `;

        return total;
    }
}

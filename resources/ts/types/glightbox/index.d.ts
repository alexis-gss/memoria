declare module "glightbox" {
    export interface GLightboxSlide {
        href: string;
        type?: string;
        title?: string;
        description?: string;
    }

    export default class GLightbox {
        constructor(options: { [key: string]: unknown });

        on(
            event: string,
            callback: (...args: any[]) => void
        ): GLightbox;

        insertSlide(slide: GLightboxSlide, index?: number): void;

        destroy(): void;
    }
}

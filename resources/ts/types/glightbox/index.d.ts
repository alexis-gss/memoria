declare module "glightbox" {
    export interface GLightboxSlide {
        href: string;
        type?: string;
        title?: string;
        description?: string;
    }

    export default class GLightbox {
        constructor(options: { [key: string]: unknown });

        on<TArgs extends unknown[] = []>(
            event: string,
            callback: (...args: TArgs) => void
        ): GLightbox;

        insertSlide(slide: GLightboxSlide, index?: number): void;

        destroy(): void;
    }
}

declare module "glightbox" {
    export default class GLightbox {
        constructor(options: { [key: string]: unknown });

        on(
            event: string,
            callback: (...args: any[]) => void
        ): GLightbox;

        destroy(): void;
    }
}

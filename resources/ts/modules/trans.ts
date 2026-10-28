/** Arbre de traductions PHP (fichiers de langue Laravel, clés imbriquées). */
type TranslationNode = string | { [key: string]: TranslationNode };

export default {
    data() {
        return {
            locale: window.__SYSTEM._locale,
        };
    },
    methods: {
        /**
         * Translate the given key,
         * ex: __('name', { 'parameter': 'value' }).
         */
        __(key: string, replace: Record<string, string> = {}): string {
            const phpTranslation = key
                .split(".")
                .reduce<TranslationNode | null>(
                    (node, segment) =>
                        node !== null && typeof node === "object"
                            ? node[segment] ?? null
                            : null,
                    window.__SYSTEM._translations.php
                );

            const translation =
                typeof phpTranslation === "string" && phpTranslation !== ""
                    ? phpTranslation
                    : window.__SYSTEM._translations.json[key] ?? key;

            return translation.replace(
                /:([0-9A-Za-z_]+)/g,
                (placeholder, name: string) => replace[name] ?? placeholder
            );
        },
    },
};

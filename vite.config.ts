import purge from "@erbelion/vite-plugin-laravel-purgecss";
import basicSsl from "@vitejs/plugin-basic-ssl";
import vue from "@vitejs/plugin-vue";
import autoprefixer from "autoprefixer";
import laravel from "laravel-vite-plugin";
import path from "path";
import postcssDiscard from "postcss-discard-comments";
import { type ConfigEnv, type UserConfig, defineConfig } from "vite";
import babel from "vite-plugin-babel";
import checker from "vite-plugin-checker";
import { viteStaticCopy } from "vite-plugin-static-copy";
import stylelint from "vite-plugin-stylelint";
import { sassSafelist } from "./resources/ts/utilities/sass-safelist.ts";

const purgePlugin = purge({
    paths: [
        "resources/views/**/*.blade.php",
        "resources/{js,ts,views}/**/*.{ts,js,vue}",
    ],
    safelist: sassSafelist,
});

const debugMode = ["development"].find((mod) => process.env.NODE_ENV === mod) !== undefined;

// eslint-disable-next-line @typescript-eslint/no-unused-vars
purgePlugin.apply = (config: UserConfig, env: ConfigEnv) => !debugMode;

const faviconFiles = [
    "android-icon-36x36.png",
    "android-icon-48x48.png",
    "android-icon-72x72.png",
    "android-icon-96x96.png",
    "android-icon-144x144.png",
    "android-icon-192x192.png",
    "apple-icon-57x57.png",
    "apple-icon-60x60.png",
    "apple-icon-72x72.png",
    "apple-icon-76x76.png",
    "apple-icon-114x114.png",
    "apple-icon-120x120.png",
    "apple-icon-144x144.png",
    "apple-icon-152x152.png",
    "apple-icon-180x180.png",
    "browserconfig.xml",
    "favicon-16x16.png",
    "favicon-32x32.png",
    "favicon-96x96.png",
    "favicon.ico",
    "webmanifest",
    "ms-icon-70x70.png",
    "ms-icon-144x144.png",
    "ms-icon-150x150.png",
    "ms-icon-310x310.png",
].map((file) => path.normalize(`resources/favicon/${file}`));

export default defineConfig({
    build: {
        rolldownOptions: {
            output: {
                entryFileNames: `assets/${debugMode ? "[name]-" : ""}[hash].js`,
                chunkFileNames: `assets/${debugMode ? "[name]-" : ""}[hash].js`,
                assetFileNames: (asset) => {
                    const name = asset.names?.[0] ?? "";
                    if (name.endsWith("mail-theme.scss") || name === "mail-theme.css") {
                        return "vendor/mail/html/themes/default.[ext]";
                    }
                    return `assets/${debugMode ? "[name]-" : ""}[hash].[ext]`;
                },
            },
        },
        chunkSizeWarningLimit: 700,
        emptyOutDir: true,
        sourcemap: debugMode ? "inline" : false,
    },
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                silenceDeprecations: ["import"],
            },
        },
        devSourcemap: debugMode,
        postcss: {
            map: {
                inline: true,
            },
            plugins: [
                postcssDiscard({
                    removeAll: true,
                }),
                autoprefixer(),
            ],
        },
    },
    plugins: [
        basicSsl(),
        checker({
            enableBuild: true,
            vueTsc: {
                root: ".",
                tsconfigPath: "tsconfig.json",
            },
            typescript: {
                root: ".",
                tsconfigPath: "tsconfig.json",
            },
            eslint: {
                lintCommand: "eslint --fix --quiet \"./resources\"",
            },
            stylelint: {
                lintCommand: "stylelint -q --fix \"**/resources/**/*.scss\"",
            },
        }),
        laravel({
            input: [
                "resources/sass/bo/back.scss",
                "resources/ts/bo/back.ts",
                "resources/sass/fo/front.scss",
                "resources/ts/fo/front.ts",
            ],
        }),
        stylelint({
            dev: false,
            build: true,
            cache: false,
            quiet: false,
            include: ["**/resources/**/*.scss", "**/resources/**/*.css"],
            exclude: ["node_modules", "vendor"],
            lintOnStart: true,
            emitError: true,
            emitWarning: true,
            emitErrorAsWarning: false,
            emitWarningAsError: true,
        }),
        babel(),
        vue({
            isProduction: !debugMode,
            exclude: ["node_modules", "vendor"],
        }),
        viteStaticCopy({
            targets: [
                {
                    src: faviconFiles,
                    dest: path.normalize(".."),
                    rename: { stripBase: true },
                },
            ],
        }),
        purgePlugin,
    ],
    resolve: {
        extensions: [".mjs", ".js", ".mts", ".ts", ".jsx", ".tsx", ".json", ".vue"],
        alias: [
            {
                find: "vue",
                replacement: "vue/dist/vue.runtime.esm-bundler.js",
            },
            {
                find: "@",
                replacement: "/resources/assets",
            },
            {
                find: "~",
                replacement: path.resolve(import.meta.dirname, "./node_modules"),
            },
        ],
    },
});

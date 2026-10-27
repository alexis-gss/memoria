import js from "@eslint/js";
import globals from "globals";
import pluginVue from "eslint-plugin-vue";
import vueTsEslintConfig from "@vue/eslint-config-typescript";

export default [
    {
        ignores: ["**/vendor/**", "**/node_modules/**", "public/**"],
    },
    js.configs.recommended,
    ...pluginVue.configs["flat/strongly-recommended"],
    ...vueTsEslintConfig(),
    {
        files: ["**/*.{js,ts,vue}"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: { ...globals.browser },
        },
        rules: {
            "indent": ["error", 4, { SwitchCase: 1 }],
            "linebreak-style": ["error", "unix"],
            "quotes": ["error", "double"],
            "semi": ["error", "always"],
            "vue/script-indent": ["error", 2, { baseIndent: 0, switchCase: 1 }],
            "vue/one-component-per-file": "off",
        },
    },
    {
        files: ["**/*.{ts,vue}"],
        rules: { "no-undef": "off" },
    },
    {
        files: ["**/*.vue"],
        rules: { "indent": "off" },
    },
];

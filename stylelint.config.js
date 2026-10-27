const trbl = (prefix) => {
    const p = prefix ? `${prefix}-` : "";
    return [...(prefix ? [prefix] : []), `${p}top`, `${p}right`, `${p}bottom`, `${p}left`];
};
const minMax = (suffix) => [suffix, `min-${suffix}`, `max-${suffix}`];
const border = (infix) => {
    const b = infix ? `border-${infix}` : "border";
    return [b, `${b}-width`, `${b}-style`, `${b}-color`, `${b}-radius`];
};

const propertiesOrder = [
    // CSS modules
    "composes",
    // Reset
    "all",
    // Positioning
    "position",
    "z-index",
    ...trbl(),
    // Display & box model
    "display",
    "overflow",
    ...minMax("width"),
    ...minMax("height"),
    "box-sizing",
    "flex",
    "flex-basis",
    "flex-direction",
    "flex-flow",
    "flex-grow",
    "flex-shrink",
    "flex-wrap",
    "align-content",
    "align-items",
    "align-self",
    "justify-content",
    "order",
    ...trbl("padding"),
    ...border(),
    ...border("top"),
    ...border("right"),
    ...border("bottom"),
    ...border("left"),
    ...trbl("margin"),
];

/** @type {import("stylelint").Config} */
export default {
    extends: [
        "stylelint-config-standard-scss",
        "stylelint-config-sass-guidelines",
    ],
    plugins: ["stylelint-order", "stylelint-scss"],
    ignoreFiles: ["**/vendor/**"],
    rules: {
        "order/properties-order": [propertiesOrder, { unspecified: "bottomAlphabetical" }],
        "order/properties-alphabetical-order": null,
        "declaration-property-value-no-unknown": true,
        "selector-max-id": null,
        "color-no-invalid-hex": true,
        "max-nesting-depth": 5,
        "selector-max-compound-selectors": 5,
        "selector-no-qualifying-type": null,
        "scss/double-slash-comment-empty-line-before": [
            "always",
            { ignore: ["between-comments", "stylelint-commands", "inside-block"] },
        ],
    },
    overrides: [
        { files: ["**/*.vue"], customSyntax: "postcss-html" },
    ],
};

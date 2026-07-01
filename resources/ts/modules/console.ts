document.addEventListener("DOMContentLoaded", function () {
    /**
     * Show and style the text.
     */
    const consoleText = [
        "font-weight: bold;",
        "font-size: 14px;",
        "color: rgb(255, 255, 255);",
    ].join(";");
    console.log("%cMaybe there is something here… ?", consoleText);
});

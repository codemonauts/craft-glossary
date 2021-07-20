const path = require('path');
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
    mode: "production",
    entry: "./resources/src",
    output: {
        path: path.resolve(__dirname, "resources/js"),
        filename: "Glossary.js"
    },
    plugins: [
        new CopyPlugin({
            patterns: [
                { from: "./node_modules/tippy.js/dist/tippy.css", to: "../css/tippy.css" },
                { from: "./node_modules/tippy.js/themes/light.css", to: "../css/light.css" }
            ],
        }),
    ],
}
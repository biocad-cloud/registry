var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
///<reference path="../linq.d.ts" />
var app;
(function (app) {
    function run() {
        Router.AddAppHandler(new pages.spectrum_data());
        Router.RunApp();
    }
    app.run = run;
})(app || (app = {}));
$ts.mode = Modes.debug;
$ts(app.run);
var pages;
(function (pages) {
    var spectrum_data = /** @class */ (function (_super) {
        __extends(spectrum_data, _super);
        function spectrum_data() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(spectrum_data.prototype, "appName", {
            get: function () {
                return "spectrum_data";
            },
            enumerable: false,
            configurable: true
        });
        spectrum_data.prototype.init = function () {
        };
        return spectrum_data;
    }(Bootstrap));
    pages.spectrum_data = spectrum_data;
})(pages || (pages = {}));
//# sourceMappingURL=registry.js.map
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
        Router.AddAppHandler(new pages.taxonomy_data());
        Router.AddAppHandler(new pages.metabolite_data());
        Router.RunApp();
    }
    app.run = run;
})(app || (app = {}));
$ts.mode = Modes.debug;
$ts(app.run);
var pages;
(function (pages) {
    var metabolite_data = /** @class */ (function (_super) {
        __extends(metabolite_data, _super);
        function metabolite_data() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(metabolite_data.prototype, "appName", {
            get: function () {
                return "metabolite_data";
            },
            enumerable: false,
            configurable: true
        });
        metabolite_data.prototype.init = function () {
            this.load();
        };
        metabolite_data.prototype.load = function () {
            var _this = this;
            $ts.get("/registry/spectrum_list/", function (msg) {
                if (msg.code == 0) {
                    var list = $from(msg.info).Select(function (entry) {
                        return {
                            Adduct: entry.adducts,
                            "m/z": entry.mz,
                            "Num Peaks": entry.npeaks,
                            "Splash_id": "<a href=\"#\" onclick=\"javascript:void(0);\" class=\"splash_id\" data=\"".concat(entry.splash_id, "\">").concat(entry.splash_id, "</a>")
                        };
                    });
                    $ts("#spectrum_list").clear();
                    $ts.appendTable(list, "#spectrum_list", null, { class: "table" });
                    $ts.select(".splash_id").onClick(function (a) { return _this.click_splash(a.getAttribute("data")); });
                }
            });
        };
        metabolite_data.prototype.click_splash = function (splash_id) {
            $ts.get("/registry/spectrum/?splash=".concat(splash_id), function (data) {
                if (data.code == 0) {
                    var spectrum = data.info;
                    // display the spectrum on page
                }
            });
        };
        return metabolite_data;
    }(Bootstrap));
    pages.metabolite_data = metabolite_data;
})(pages || (pages = {}));
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
            this.load_exp();
        };
        spectrum_data.prototype.load_exp = function () {
            var url = "/registry/experiment_source/";
            $ts.get(url, function (msg) {
                if (msg.code == 0) {
                    var data_1 = $ts(msg.info).Select(function (a) {
                        return {
                            "Organism Source": "<a href=\"/taxonomy/?id=".concat(a.taxid, "\">").concat(a.taxname, "</a>"),
                            "Tissue": a.tissue,
                            "Adducts": a.adducts,
                            "Size": a.size
                        };
                    });
                    $ts("#exp_table").clear();
                    $ts.appendTable(data_1, "#exp_table", null, { class: "table" });
                }
            });
        };
        return spectrum_data;
    }(Bootstrap));
    pages.spectrum_data = spectrum_data;
})(pages || (pages = {}));
var pages;
(function (pages) {
    var taxonomy_data = /** @class */ (function (_super) {
        __extends(taxonomy_data, _super);
        function taxonomy_data() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(taxonomy_data.prototype, "appName", {
            get: function () {
                return "taxonomy_data";
            },
            enumerable: false,
            configurable: true
        });
        taxonomy_data.prototype.taxid = function () {
            return $ts.location("id");
        };
        taxonomy_data.prototype.init = function () {
            $ts.get("/registry/organism_source/?taxid=".concat(this.taxid()), function (msg) {
                if (msg.code == 0) {
                    var data_2 = $from(msg.info).Select(function (a) {
                        return {
                            "ID": "<a href=\"/metabolite/".concat(a.id, "\">").concat(a.id, "</a>"),
                            "Name": "<a href=\"/spectrum/?metab=".concat(a.id, "\">").concat(a.name, "</a>"),
                            "Formula": a.formula,
                            "Exact Mass": a.exact_mass,
                            "Hits": a.size
                        };
                    });
                    $ts("#metab-source").clear();
                    $ts.appendTable(data_2, "#metab-source", null, { class: "table" });
                }
            });
        };
        return taxonomy_data;
    }(Bootstrap));
    pages.taxonomy_data = taxonomy_data;
})(pages || (pages = {}));
//# sourceMappingURL=registry.js.map
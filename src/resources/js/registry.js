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
var viewer;
(function (viewer) {
    var SpectrumViewer = /** @class */ (function () {
        function SpectrumViewer() {
            var _this = this;
            this.chartInstance = null;
            // 初始化时可以预绑定 Modal，或者使用时再获取
            var modalDom = document.getElementById('spectrumModal');
            if (modalDom) {
                // 这里的 Modal 是 Bootstrap 的全局对象，根据你的项目引入方式调整
                this.modalInstance = new window.bootstrap.Modal(modalDom);
                // 监听模态框显示事件，触发 ECharts resize，防止图表变形
                modalDom.addEventListener('shown.bs.modal', function () {
                    if (_this.chartInstance) {
                        _this.chartInstance.resize();
                    }
                });
            }
        }
        /**
         * 渲染质谱图
         */
        SpectrumViewer.prototype.renderSpectrum = function (rawData) {
            // 1. 数据清洗与类型转换
            // API 返回的是字符串数组，需要转换为数字数组
            var mzValues = rawData.mz.map(function (v) { return parseFloat(v); });
            var intensityValues = rawData.intensity.map(function (v) { return parseFloat(v); });
            var precursorMz = typeof rawData.precursor === 'string' ? parseFloat(rawData.precursor) : rawData.precursor;
            var smilesArr = rawData.smiles || [];
            // 构造 ECharts 所需的数据格式
            // data 结构: [mz, intensity, smiles]
            var chartData = mzValues.map(function (mz, index) {
                return [
                    mz,
                    intensityValues[index],
                    smilesArr[index] || '' // 防止 smiles 数组缺失
                ];
            });
            // 2. 初始化图表
            var chartDom = document.getElementById('spectrum_chart');
            if (!chartDom)
                return;
            if (this.chartInstance) {
                this.chartInstance.dispose(); // 销毁旧实例
            }
            this.chartInstance = echarts.init(chartDom);
            // 3. ECharts 配置项
            var option = {
                backgroundColor: '#fff', // 亮色主题
                title: {
                    text: rawData.name || 'MS/MS Spectrum',
                    subtext: "Precursor m/z: ".concat(precursorMz.toFixed(4), " | Adduct: ").concat(rawData.adducts),
                    left: 'center',
                    top: 10,
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                },
                tooltip: {
                    trigger: 'item', // 触发方式：数据项图形触发
                    axisPointer: {
                        type: 'shadow'
                    },
                    formatter: function (params) {
                        // params.data 结构对应上面构造的 [mz, intensity, smiles]
                        var mz = params.data[0];
                        var intensity = params.data[1];
                        var smiles = params.data[2];
                        // 格式化输出: m/z: mz(intensity) [smiles]
                        return "m/z: <strong>".concat(mz.toFixed(4), "</strong> (").concat(intensity.toFixed(2), ")<br/>") +
                            "<span style=\"font-size:10px;color:#666;\">[".concat(smiles, "]</span>");
                    }
                },
                grid: {
                    left: 60,
                    right: 20,
                    top: 80,
                    bottom: 60,
                    containLabel: false
                },
                xAxis: {
                    type: 'value',
                    name: 'm/z',
                    nameLocation: 'middle',
                    nameGap: 30,
                    min: function (value) { return value.min - 10; }, // 留出边距
                    splitLine: {
                        show: false // 质谱图通常不显示垂直网格线
                    },
                    axisLine: {
                        show: true,
                        lineStyle: { color: '#333' }
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Intensity (%)', // 假设是相对丰度
                    nameLocation: 'middle',
                    nameGap: 40,
                    min: 0,
                    max: 100, // 根据数据看最大值是 100，通常 MS2 归一化到 100
                    splitLine: {
                        lineStyle: { color: '#eee' } // 淡灰色网格线
                    },
                    axisLine: {
                        show: true,
                        lineStyle: { color: '#333' }
                    }
                },
                // 数据区域缩放组件，方便查看局部细节
                dataZoom: [
                    {
                        type: 'inside', // 内置型，支持鼠标滚轮缩放
                        xAxisIndex: 0,
                        filterMode: 'none'
                    },
                    {
                        type: 'slider', // 滑动条型
                        xAxisIndex: 0,
                        bottom: 10,
                        height: 20,
                        filterMode: 'none'
                    }
                ],
                series: [
                    {
                        type: 'bar',
                        data: chartData,
                        barWidth: 1.5, // 极细的柱宽，模拟质谱棒状图
                        itemStyle: {
                            color: '#000', // 经典质谱图通常为黑色线条
                        },
                        emphasis: {
                            itemStyle: {
                                color: '#0d6efd' // Bootstrap primary color，鼠标悬停高亮
                            }
                        },
                        // 标记前体离子位置
                        markLine: {
                            symbol: 'none',
                            label: {
                                show: true,
                                position: 'end',
                                formatter: 'Precursor',
                                fontSize: 10
                            },
                            lineStyle: {
                                color: '#dc3545', // Bootstrap danger color
                                type: 'dashed'
                            },
                            data: [
                                { xAxis: precursorMz }
                            ]
                        }
                    }
                ]
            };
            this.chartInstance.setOption(option);
            // 显示模态框
            if (this.modalInstance) {
                this.modalInstance.show();
            }
        };
        return SpectrumViewer;
    }());
    viewer.SpectrumViewer = SpectrumViewer;
})(viewer || (viewer = {}));
/// <reference path="../viewer/SpectrumViewer.ts"/>
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
                    var display = new viewer.SpectrumViewer();
                    // display the spectrum on page
                    display.renderSpectrum(spectrum);
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
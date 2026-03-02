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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
///<reference path="../linq.d.ts" />
var app;
(function (app) {
    function run() {
        Router.AddAppHandler(new pages.spectrum_data());
        Router.AddAppHandler(new pages.taxonomy_data());
        Router.AddAppHandler(new pages.metabolite_data());
        Router.AddAppHandler(new pages.landscapes.metabolic());
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
                // // 数据区域缩放组件，方便查看局部细节
                // dataZoom: [
                //     {
                //         type: 'inside', // 内置型，支持鼠标滚轮缩放
                //         xAxisIndex: 0,
                //         filterMode: 'none'
                //     },
                //     {
                //         type: 'slider', // 滑动条型
                //         xAxisIndex: 0,
                //         bottom: 10,
                //         height: 20,
                //         filterMode: 'none'
                //     }
                // ],
                series: [
                    {
                        type: 'bar',
                        data: chartData,
                        barWidth: 2, // 极细的柱宽，模拟质谱棒状图
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
    var url_spectrum_list = "/mzvault/spectrum_list/";
    var url_spectrum_data = "/mzvault/spectrum/";
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
            $ts.get(url_spectrum_list, function (msg) {
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
            $ts.get("".concat(url_spectrum_data, "?splash=").concat(splash_id), function (data) {
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
    var url_experiment_source = "/mzvault/experiment_source/";
    var url_annotation_hits = "/mzvault/annotation_hits/";
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
            this.load_pie();
        };
        spectrum_data.prototype.load_exp = function () {
            $ts.get(url_experiment_source, function (msg) {
                if (msg.code == 0) {
                    var data_1 = $ts(msg.info).Select(function (a) {
                        return {
                            "Organism Source": "<a href=\"/taxonomy/?id=".concat(a.taxid, "\">").concat(a.taxname, "</a>"),
                            "Tissue": a.tissue,
                            "Adducts": a.adducts,
                            "Size": a.size
                        };
                    });
                    if (data_1.Count > 0) {
                        $ts("#exp_table").clear();
                        $ts.appendTable(data_1, "#exp_table", null, { class: "table" });
                    }
                }
            });
        };
        spectrum_data.prototype.load_pie = function () {
            $ts.get(url_annotation_hits, function (msg) {
                if (msg.code == 0) {
                    var anno_hits = msg.info;
                    viewer.PieViewer.viz_pie(anno_hits.organism, "org_pie", '物种分布统计');
                    viewer.PieViewer.viz_pie(anno_hits.tissue, "tissue_pie", '来源分布统计');
                }
            });
        };
        return spectrum_data;
    }(Bootstrap));
    pages.spectrum_data = spectrum_data;
})(pages || (pages = {}));
var pages;
(function (pages) {
    var url_organism_source = "/registry/organism_source/";
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
            $ts.get("".concat(url_organism_source, "?taxid=").concat(this.taxid()), function (msg) {
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
var pages;
(function (pages) {
    var landscapes;
    (function (landscapes) {
        var metabolic = /** @class */ (function (_super) {
            __extends(metabolic, _super);
            function metabolic() {
                return _super !== null && _super.apply(this, arguments) || this;
            }
            Object.defineProperty(metabolic.prototype, "appName", {
                get: function () {
                    return "metabolic_landscape";
                },
                enumerable: false,
                configurable: true
            });
            metabolic.prototype.init = function () {
                this.viz = new viewer.GenomeEmbeddingViz();
                this.viz.initCharts();
            };
            return metabolic;
        }(Bootstrap));
        landscapes.metabolic = metabolic;
    })(landscapes = pages.landscapes || (pages.landscapes = {}));
})(pages || (pages = {}));
var viewer;
(function (viewer) {
    // 初始化 ECharts 实例
    function initChart(containerId, option) {
        var container = document.getElementById(containerId);
        if (!container) {
            throw new Error("Container with id \"".concat(containerId, "\" not found"));
        }
        var chart = echarts.init(container, undefined, {
            renderer: 'canvas'
        });
        // 设置配置项
        chart.setOption(option);
        // 响应式调整
        window.addEventListener('resize', function () {
            chart.resize();
        });
        return chart;
    }
    viewer.initChart = initChart;
})(viewer || (viewer = {}));
var viewer;
(function (viewer) {
    // Color palette for taxonomy categories
    var COLOR_PALETTE = [
        '#2563eb', '#dc2626', '#16a34a', '#ea580c', '#7c3aed',
        '#0891b2', '#c026d3', '#65a30d', '#0d9488', '#db2777',
        '#f59e0b', '#4f46e5', '#059669', '#e11d48', '#7c2d12',
        '#1d4ed8', '#b91c1c', '#15803d', '#c2410c', '#6d28d9',
        '#0e7490', '#a21caf', '#4d7c0f', '#0f766e', '#be185d',
        '#d97706', '#4338ca', '#047857', '#be123c', '#713f12',
        '#1e40af', '#991b1b', '#166534', '#9a3412', '#5b21b6',
        '#155e75', '#86198f', '#3f6212', '#115e59', '#9d174d'
    ];
    // EC number class labels
    var EC_LABELS = [
        'EC1 Oxidoreductases',
        'EC2 Transferases',
        'EC3 Hydrolases',
        'EC4 Lyases',
        'EC5 Isomerases',
        'EC6 Ligases',
        'EC7 Translocases'
    ];
    /**
      * Genome Embedding Visualization Module
      * Provides 3D scatter plot and radar chart visualization for microbial genome embedding data
      */
    var GenomeEmbeddingViz = /** @class */ (function () {
        function GenomeEmbeddingViz() {
            // State
            this.scatterChart = null;
            this.radarChart = null;
            this.currentData = [];
            this.currentTaxonomyLevel = 'kingdom';
            this.colorMap = {};
            this.selectedGenome = null;
        }
        /**
         * Initialize charts
         */
        GenomeEmbeddingViz.prototype.initCharts = function () {
            var _this = this;
            // Initialize scatter chart
            var scatterDom = document.getElementById('scatter-chart');
            this.scatterChart = echarts.init(scatterDom, null, { renderer: 'canvas' });
            // Initialize radar chart
            var radarDom = document.getElementById('radar-chart');
            this.radarChart = echarts.init(radarDom, null, { renderer: 'canvas' });
            // Handle resize
            window.addEventListener('resize', function () {
                if (_this.scatterChart)
                    _this.scatterChart.resize();
                if (_this.radarChart)
                    _this.radarChart.resize();
            });
            // Initialize empty radar chart
            this.updateRadarChart(null);
            // Bind taxonomy select change event
            document.getElementById('taxonomy-select').addEventListener('change', function (e) {
                _this.setTaxonomyLevel(e.target.value);
            });
        };
        /**
         * Generate color map for unique values
         */
        GenomeEmbeddingViz.prototype.generateColorMap = function (uniqueValues) {
            var map = {};
            uniqueValues.forEach(function (value, index) {
                map[value] = COLOR_PALETTE[index % COLOR_PALETTE.length];
            });
            return map;
        };
        /**
         * Get unique values for a taxonomy level
         */
        GenomeEmbeddingViz.prototype.getUniqueValues = function (data, level) {
            var values = new Set();
            data.forEach(function (item) {
                if (item[level]) {
                    values.add(item[level]);
                }
            });
            return Array.from(values).sort();
        };
        /**
         * Update legend
         */
        GenomeEmbeddingViz.prototype.updateLegend = function () {
            var _this = this;
            var container = document.getElementById('legend-container');
            var uniqueValues = Object.keys(this.colorMap).sort();
            container.innerHTML = uniqueValues.map(function (value) { return "\n            <div class=\"legend-item\" data-value=\"".concat(value, "\">\n                <span class=\"legend-color\" style=\"background-color: ").concat(_this.colorMap[value], "\"></span>\n                <span class=\"legend-label\" title=\"").concat(value, "\">").concat(value || 'Unknown', "</span>\n            </div>\n        "); }).join('');
        };
        /**
         * Update stats bar
         */
        GenomeEmbeddingViz.prototype.updateStats = function () {
            document.getElementById('stat-total').textContent = this.currentData.length.toString();
            document.getElementById('stat-colorby').textContent =
                this.currentTaxonomyLevel.charAt(0).toUpperCase() + this.currentTaxonomyLevel.slice(1);
            document.getElementById('stat-categories').textContent = Object.keys(this.colorMap).length.toString();
        };
        /**
         * Update 3D scatter chart
         */
        GenomeEmbeddingViz.prototype.updateScatterChart = function () {
            var _this = this;
            var scatterChart = this.scatterChart;
            var currentData = this.currentData;
            if (!scatterChart || !currentData.length)
                return;
            var uniqueValues = this.getUniqueValues(currentData, this.currentTaxonomyLevel);
            this.colorMap = this.generateColorMap(uniqueValues);
            // Group data by taxonomy value
            var seriesMap = {};
            currentData.forEach(function (item) {
                var taxValue = item[_this.currentTaxonomyLevel] || 'Unknown';
                if (!seriesMap[taxValue]) {
                    seriesMap[taxValue] = [];
                }
                seriesMap[taxValue].push([
                    item.x,
                    item.y,
                    item.z,
                    item.scientific_name,
                    item.ncbi_taxid,
                    item.assembly_id
                ]);
            });
            // Create series for each taxonomy group
            var series = Object.entries(seriesMap).map(function (_a) {
                var taxValue = _a[0], data = _a[1];
                return ({
                    name: taxValue,
                    type: 'scatter3D',
                    data: data.map(function (d) { return ({
                        value: [d[0], d[1], d[2]],
                        itemStyle: {
                            color: _this.colorMap[taxValue]
                        },
                        genomeData: {
                            scientific_name: d[3],
                            ncbi_taxid: d[4],
                            assembly_id: d[5]
                        }
                    }); }),
                    symbolSize: 12,
                    emphasis: {
                        itemStyle: {
                            borderWidth: 2,
                            borderColor: '#1a1d26'
                        }
                    }
                });
            });
            var option = {
                tooltip: {
                    formatter: function (params) {
                        if (params.data && params.data.genomeData) {
                            var g = params.data.genomeData;
                            return "<strong>".concat(g.scientific_name, "</strong><br/>\n                                <span style=\"color:#8b92a5\">NCBI TaxID: ").concat(g.ncbi_taxid, "</span>");
                        }
                        return '';
                    },
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#d8dce6',
                    borderWidth: 1,
                    padding: [8, 12],
                    textStyle: {
                        color: '#1a1d26',
                        fontSize: 12,
                        fontFamily: 'Space Grotesk'
                    }
                },
                xAxis3D: {
                    type: 'value',
                    name: 'UMAP-1',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                yAxis3D: {
                    type: 'value',
                    name: 'UMAP-2',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                zAxis3D: {
                    type: 'value',
                    name: 'UMAP-3',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                grid3D: {
                    boxWidth: 120,
                    boxHeight: 80,
                    boxDepth: 80,
                    viewControl: {
                        autoRotate: false,
                        distance: 200,
                        alpha: 20,
                        beta: 40
                    },
                    light: {
                        main: {
                            intensity: 1.2,
                            shadow: true
                        },
                        ambient: {
                            intensity: 0.3
                        }
                    },
                    postEffect: {
                        enable: true,
                        bloom: {
                            enable: true,
                            intensity: 0.1
                        }
                    }
                },
                series: series
            };
            scatterChart.setOption(option, true);
            // Add click handler
            scatterChart.off('click');
            scatterChart.on('click', function (params) {
                if (params.data && params.data.genomeData) {
                    var assemblyId_1 = params.data.genomeData.assembly_id;
                    var genome = currentData.find(function (g) { return g.assembly_id === assemblyId_1; });
                    if (genome) {
                        this.selectGenome(genome);
                    }
                }
            });
            this.updateLegend();
            this.updateStats();
        };
        /**
         * Select a genome and display its details
         */
        GenomeEmbeddingViz.prototype.selectGenome = function (genome) {
            this.selectedGenome = genome;
            // Update genome detail panel
            var detailContainer = document.getElementById('genome-detail');
            var taxonomyHtml = "\n            <li><span class=\"taxonomy-rank\">Kingdom</span><span class=\"taxonomy-value\">".concat(genome.kingdom || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Phylum</span><span class=\"taxonomy-value\">").concat(genome.phylum || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Class</span><span class=\"taxonomy-value\">").concat(genome.class || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Order</span><span class=\"taxonomy-value\">").concat(genome.order || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Family</span><span class=\"taxonomy-value\">").concat(genome.family || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Genus</span><span class=\"taxonomy-value\">").concat(genome.genus || '-', "</span></li>\n            <li><span class=\"taxonomy-rank\">Species</span><span class=\"taxonomy-value\">").concat(genome.species || '-', "</span></li>\n        ");
            detailContainer.innerHTML = "\n            <div class=\"genome-card fade-in\">\n                <div class=\"genome-name\">".concat(genome.scientific_name, "</div>\n                <div class=\"genome-taxid\">NCBI TaxID: ").concat(genome.ncbi_taxid, "</div>\n            </div>\n            <div class=\"section-title\" style=\"margin-top: 0.5rem;\">Taxonomy</div>\n            <ul class=\"taxonomy-list\">\n                ").concat(taxonomyHtml, "\n            </ul>\n        ");
            // Update radar chart
            this.updateRadarChart(genome);
            // Highlight selected point in scatter
            this.highlightPoint(genome);
        };
        /**
         * Highlight selected point in scatter chart
         */
        GenomeEmbeddingViz.prototype.highlightPoint = function (genome) {
            var _this = this;
            // Re-render with emphasis on selected point
            var uniqueValues = this.getUniqueValues(this.currentData, this.currentTaxonomyLevel);
            var seriesMap = {};
            this.currentData.forEach(function (item) {
                var taxValue = item[_this.currentTaxonomyLevel] || 'Unknown';
                if (!seriesMap[taxValue]) {
                    seriesMap[taxValue] = [];
                }
                seriesMap[taxValue].push(item);
            });
            var series = Object.entries(seriesMap).map(function (_a) {
                var taxValue = _a[0], data = _a[1];
                return ({
                    name: taxValue,
                    type: 'scatter3D',
                    data: data.map(function (d) { return ({
                        value: [d.x, d.y, d.z],
                        itemStyle: {
                            color: _this.colorMap[taxValue],
                            borderWidth: d.assembly_id === genome.assembly_id ? 3 : 0,
                            borderColor: '#1a1d26'
                        },
                        symbolSize: d.assembly_id === genome.assembly_id ? 20 : 12,
                        genomeData: {
                            scientific_name: d.scientific_name,
                            ncbi_taxid: d.ncbi_taxid,
                            assembly_id: d.assembly_id
                        }
                    }); }),
                    emphasis: {
                        itemStyle: {
                            borderWidth: 2,
                            borderColor: '#1a1d26'
                        }
                    }
                });
            });
            this.scatterChart.setOption({ series: series });
        };
        /**
         * Update radar chart for EC number embedding
         */
        GenomeEmbeddingViz.prototype.updateRadarChart = function (genome) {
            if (!this.radarChart)
                return;
            var ecData = genome ? genome.ec_number : [0, 0, 0, 0, 0, 0, 0];
            // Calculate max value for radar scale
            var maxVal = Math.max.apply(Math, __spreadArray(__spreadArray([], ecData, false), [1], false));
            var option = {
                radar: {
                    indicator: EC_LABELS.map(function (label, index) { return ({
                        name: label,
                        max: maxVal * 1.2
                    }); }),
                    radius: '60%',
                    center: ['50%', '50%'],
                    axisName: {
                        color: '#5a6275',
                        fontSize: 9,
                        fontWeight: 500
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    },
                    splitArea: {
                        areaStyle: { color: ['transparent', 'rgba(13, 110, 253, 0.03)'] }
                    }
                },
                series: [{
                        type: 'radar',
                        data: [{
                                value: ecData,
                                name: genome ? genome.scientific_name : 'No selection',
                                symbol: 'circle',
                                symbolSize: 5,
                                lineStyle: {
                                    width: 2,
                                    color: '#2563eb'
                                },
                                areaStyle: {
                                    color: 'rgba(37, 99, 235, 0.2)'
                                },
                                itemStyle: {
                                    color: '#2563eb',
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }
                            }]
                    }]
            };
            this.radarChart.setOption(option, true);
        };
        /**
         * Show/hide loading overlay
         */
        GenomeEmbeddingViz.prototype.setLoading = function (isLoading) {
            var overlay = document.getElementById('loading-overlay');
            if (isLoading) {
                overlay.classList.add('active');
            }
            else {
                overlay.classList.remove('active');
            }
        };
        // ========================================
        // PUBLIC API
        // ========================================
        /**
         * Load embedding data and render visualization
         * @param {Array} data - Array of metabolic_embedding objects
         */
        GenomeEmbeddingViz.prototype.loadData = function (data) {
            var _this = this;
            if (!Array.isArray(data)) {
                console.error('Invalid data: expected an array of metabolic_embedding objects');
                return;
            }
            this.setLoading(true);
            this.currentData = data;
            this.selectedGenome = null;
            // Reset genome detail panel
            document.getElementById('genome-detail').innerHTML = "\n            <div class=\"no-selection\">\n                <div class=\"no-selection-icon\">\n                    <svg width=\"48\" height=\"48\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\">\n                        <circle cx=\"12\" cy=\"12\" r=\"10\"/>\n                        <path d=\"M12 8v4M12 16h.01\"/>\n                    </svg>\n                </div>\n                <p class=\"no-selection-text\">Click a point in the scatter plot to view genome details</p>\n            </div>\n        ";
            // Reset radar chart
            this.updateRadarChart(null);
            // Update scatter chart
            setTimeout(function () {
                _this.updateScatterChart();
                _this.setLoading(false);
            }, 100);
        };
        /**
         * Set taxonomy level for coloring
         * @param {string} level - Taxonomy level (kingdom, phylum, class, order, family, genus, species)
         */
        GenomeEmbeddingViz.prototype.setTaxonomyLevel = function (level) {
            var validLevels = ['kingdom', 'phylum', 'class', 'order', 'family', 'genus', 'species'];
            if (!validLevels.includes(level)) {
                console.error('Invalid taxonomy level:', level);
                return;
            }
            this.currentTaxonomyLevel = level;
            document.getElementById('taxonomy-select').value = level;
            this.updateScatterChart();
        };
        /**
         * Get currently selected genome
         * @returns {Object|null} Selected genome or null
         */
        GenomeEmbeddingViz.prototype.getSelectedGenome = function () {
            return this.selectedGenome;
        };
        /**
         * Get chart instances for external manipulation
         * @returns {Object} Object containing scatterChart and radarChart instances
         */
        GenomeEmbeddingViz.prototype.getChartInstances = function () {
            return {
                scatterChart: this.scatterChart,
                radarChart: this.radarChart
            };
        };
        /**
         * Resize charts
         */
        GenomeEmbeddingViz.prototype.resizeCharts = function () {
            if (this.scatterChart)
                this.scatterChart.resize();
            if (this.radarChart)
                this.radarChart.resize();
        };
        return GenomeEmbeddingViz;
    }());
    viewer.GenomeEmbeddingViz = GenomeEmbeddingViz;
})(viewer || (viewer = {}));
var viewer;
(function (viewer) {
    function toPieData(rawData) {
        return Object.entries(rawData).map(function (_a) {
            var name = _a[0], value = _a[1];
            return ({
                name: name,
                value: value
            });
        });
    }
    viewer.toPieData = toPieData;
    var PieViewer = /** @class */ (function () {
        function PieViewer() {
        }
        PieViewer.viz_pie = function (rawData, chart_id, title) {
            // 转换为 ECharts 需要的格式
            var pieData = viewer.toPieData(rawData);
            // 计算总和
            var total = pieData.reduce(function (sum, item) { return sum + item.value; }, 0);
            // ECharts 配置项
            var option = {
                // 标题配置
                title: {
                    text: title,
                    subtext: "\u603B\u8BA1: ".concat(total.toFixed(2)),
                    left: 'center',
                    top: 20,
                    textStyle: {
                        fontSize: 24,
                        fontWeight: 'bold',
                        color: '#2C3E50'
                    },
                    subtextStyle: {
                        fontSize: 14,
                        color: '#7F8C8D'
                    }
                },
                // 提示框配置
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#E0E0E0',
                    borderWidth: 1,
                    textStyle: {
                        color: '#2C3E50'
                    },
                    formatter: function (params) {
                        var percent = ((params.value / total) * 100).toFixed(2);
                        return "\n          <div style=\"padding: 8px;\">\n            <strong style=\"font-size: 14px;\">".concat(params.name, "</strong><br/>\n            <span style=\"color: #7F8C8D;\">\u6570\u503C: </span>\n            <strong>").concat(params.value.toFixed(4), "</strong><br/>\n            <span style=\"color: #7F8C8D;\">\u5360\u6BD4: </span>\n            <strong>").concat(percent, "%</strong>\n          </div>\n        ");
                    }
                },
                // 图例配置
                legend: {
                    type: 'scroll',
                    orient: 'vertical',
                    right: 30,
                    top: 'middle',
                    itemWidth: 16,
                    itemHeight: 16,
                    itemGap: 12,
                    textStyle: {
                        fontSize: 13,
                        color: '#2C3E50'
                    },
                    formatter: function (name) {
                        var item = pieData.find(function (d) { return d.name === name; });
                        if (item) {
                            var percent = ((item.value / total) * 100).toFixed(2);
                            return "".concat(name, "  (").concat(percent, "%)");
                        }
                        return name;
                    }
                },
                // 系列配置
                series: [
                    {
                        name: '比例分布',
                        type: 'pie',
                        radius: ['35%', '70%'], // 环形图，更美观
                        center: ['40%', '55%'],
                        avoidLabelOverlap: true,
                        // 标签配置
                        label: {
                            show: true,
                            position: 'outside',
                            formatter: function (params) {
                                var percent = ((params.value / total) * 100).toFixed(2);
                                // 占比小于1%的不显示标签
                                if (parseFloat(percent) < 1) {
                                    return '';
                                }
                                return "".concat(params.name, "\n").concat(percent, "%");
                            },
                            fontSize: 12,
                            color: '#2C3E50',
                            lineHeight: 18
                        },
                        // 标签引导线
                        labelLine: {
                            show: true,
                            length: 15,
                            length2: 10,
                            smooth: true,
                            lineStyle: {
                                color: '#95A5A6',
                                width: 1.5
                            }
                        },
                        // 强调样式
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 20,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.3)',
                                borderWidth: 3,
                                borderColor: '#FFFFFF'
                            },
                            label: {
                                show: true,
                                fontSize: 14,
                                fontWeight: 'bold'
                            }
                        },
                        // 数据项样式
                        itemStyle: {
                            borderRadius: 8,
                            borderColor: '#FFFFFF',
                            borderWidth: 2
                        },
                        // 数据
                        data: pieData,
                        // 动画配置
                        animationType: 'scale',
                        animationEasing: 'elasticOut',
                        animationDelay: function (idx) { return idx * 100; }
                    }
                ]
            };
            var chart = viewer.initChart(chart_id, option);
            // 可选：添加点击事件
            chart.on('click', function (params) {
                console.log('Clicked:', params.name, params.value);
            });
        };
        return PieViewer;
    }());
    viewer.PieViewer = PieViewer;
})(viewer || (viewer = {}));
//# sourceMappingURL=registry.js.map
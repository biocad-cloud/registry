namespace viewer {

    export class SpectrumViewer {

        private chartInstance: echarts.ECharts | null = null;
        private modalInstance: any; // Bootstrap Modal Instance

        constructor() {
            // 初始化时可以预绑定 Modal，或者使用时再获取
            const modalDom = document.getElementById('spectrumModal');
            if (modalDom) {
                // 这里的 Modal 是 Bootstrap 的全局对象，根据你的项目引入方式调整
                this.modalInstance = new (window as any).bootstrap.Modal(modalDom);

                // 监听模态框显示事件，触发 ECharts resize，防止图表变形
                modalDom.addEventListener('shown.bs.modal', () => {
                    if (this.chartInstance) {
                        this.chartInstance.resize();
                    }
                });
            }
        }

        /**
         * 渲染质谱图
         */
        public renderSpectrum(rawData: pages.spectrum_data) {
            // 1. 数据清洗与类型转换
            // API 返回的是字符串数组，需要转换为数字数组
            const mzValues: number[] = (rawData.mz as string[]).map(v => parseFloat(v));
            const intensityValues: number[] = (rawData.intensity as string[]).map(v => parseFloat(v));
            const precursorMz = typeof rawData.precursor === 'string' ? parseFloat(rawData.precursor) : rawData.precursor;
            const smilesArr = rawData.smiles || [];

            // 构造 ECharts 所需的数据格式
            // data 结构: [mz, intensity, smiles]
            const chartData: any[] = mzValues.map((mz, index) => {
                return [
                    mz,
                    intensityValues[index],
                    smilesArr[index] || '' // 防止 smiles 数组缺失
                ];
            });

            // 2. 初始化图表
            const chartDom = document.getElementById('spectrum_chart');
            if (!chartDom) return;

            if (this.chartInstance) {
                this.chartInstance.dispose(); // 销毁旧实例
            }
            this.chartInstance = echarts.init(chartDom);

            // 3. ECharts 配置项
            const option: echarts.EChartsOption = {
                backgroundColor: '#fff', // 亮色主题
                title: {
                    text: rawData.name || 'MS/MS Spectrum',
                    subtext: `Precursor m/z: ${precursorMz.toFixed(4)} | Adduct: ${rawData.adducts}`,
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
                    formatter: function (params: any) {
                        // params.data 结构对应上面构造的 [mz, intensity, smiles]
                        const mz = params.data[0];
                        const intensity = params.data[1];
                        const smiles = params.data[2];

                        // 格式化输出: m/z: mz(intensity) [smiles]
                        return `m/z: <strong>${mz.toFixed(4)}</strong> (${intensity.toFixed(2)})<br/>` +
                            `<span style="font-size:10px;color:#666;">[${smiles}]</span>`;
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
                    min: function (value: any) { return value.min - 10; }, // 留出边距
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
        }
    }

}
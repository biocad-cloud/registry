namespace viewer {

    // 初始化 ECharts 实例
    export function initChart(containerId: string, option:{}): echarts.ECharts {
        const container = document.getElementById(containerId);
        if (!container) {
            throw new Error(`Container with id "${containerId}" not found`);
        }

        const chart = echarts.init(container, undefined, {
            renderer: 'canvas'
        });

        // 设置配置项
        chart.setOption(option);

        // 响应式调整
        window.addEventListener('resize', () => {
            chart.resize();
        });

        return chart;
    }
}
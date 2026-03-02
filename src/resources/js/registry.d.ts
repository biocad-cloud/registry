declare namespace app {
    function run(): void;
}
declare namespace viewer {
    class SpectrumViewer {
        private chartInstance;
        private modalInstance;
        constructor();
        /**
         * 渲染质谱图
         */
        renderSpectrum(rawData: pages.spectrum_data): void;
    }
}
declare namespace pages {
    class metabolite_data extends Bootstrap {
        get appName(): string;
        protected init(): void;
        private load;
        private click_splash;
    }
    interface spectrum_data {
        splash_id: string;
        name: string;
        adducts: string;
        precursor: number;
        mz: string[];
        intensity: string[];
        smiles: string[];
    }
    interface spectrum_entry {
        adducts: string;
        mz: number;
        npeaks: number;
        splash_id: string;
    }
}
declare namespace pages {
    class spectrum_data extends Bootstrap {
        get appName(): string;
        protected init(): void;
        private load_exp;
        private load_pie;
    }
    interface lcms_exp_result {
        taxname: string;
        taxid: string;
        tissue: string;
        adducts: string;
        size: number;
    }
}
declare namespace pages {
    class taxonomy_data extends Bootstrap {
        get appName(): string;
        taxid(): string;
        protected init(): void;
    }
    interface metabolite_sources {
        id: number;
        name: string;
        formula: string;
        exact_mass: number;
        size: number;
    }
}
declare namespace viewer {
    function initChart(containerId: string, option: {}): echarts.ECharts;
}
declare namespace viewer {
    interface PieChartData {
        name: string;
        value: number;
    }
    interface SpeciesData {
        [key: string]: number;
    }
    function toPieData(rawData: SpeciesData): any;
    class PieViewer {
        static viz_pie(rawData: SpeciesData, chart_id: string, title: string): void;
    }
}

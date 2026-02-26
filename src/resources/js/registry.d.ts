declare namespace app {
    function run(): void;
}
declare namespace pages {
    class spectrum_data extends Bootstrap {
        get appName(): string;
        protected init(): void;
        private load_exp;
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

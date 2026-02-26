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

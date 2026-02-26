declare namespace app {
    function run(): void;
}
declare namespace pages {
    class spectrum_data extends Bootstrap {
        get appName(): string;
        protected init(): void;
    }
}

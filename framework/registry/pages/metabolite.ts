namespace pages {

    export class metabolite_data extends Bootstrap {

        get appName(): string {
            return "metabolite_data";
        }

        protected init(): void {
            this.load();
        }

        private load() {
            $ts.get("/registry/spectrum_list/", msg => {

            });
        }
    }
}
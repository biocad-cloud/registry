namespace pages {

    export class peakdata extends Bootstrap {

        get appName(): string {
            return "peakdata";
        }

        page: number = 1;

        protected init(): void {
            this.show_peakdata();
        }

        private show_peakdata() {
            const mz = $ts("@mz");
            const da = $ts("@da");

            $ts.get(`/mzvault/peakdata/?mz=${mz}&da=${da}`, result => {
                if (result.code == 0) {

                }
            });
        }
    }
}
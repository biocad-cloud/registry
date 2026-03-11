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
                    let peaks = (<any>result.info).peak;
                    let data = $from(<peak_data[]>peaks).Select(a => {
                        return {
                            splash_id: a.splash_id,
                            mz: `${a.mz} (${a.intensity})`,
                            smiles: a.smiles
                        }
                    });

                    console.table(peaks);

                    $ts("#peaksdata").clear();
                    $ts.appendTable(data, "#peaksdata", null, { class: "table" });
                }
            });
        }
    }

    export interface peak_data {
        mz: number
        intensity: number;
        smiles: string;
        splash_id: string;
        db_xref: string;
        name: string;
        adducts: string;
        precursor: number;
    }
}


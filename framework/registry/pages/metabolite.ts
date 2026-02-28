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
                if (msg.code == 0) {
                    let list = $from(<spectrum_entry[]>msg.info).Select(entry => {
                        return {
                            Adduct: entry.adducts,
                            "m/z": entry.mz,
                            "Num Peaks": entry.npeaks,
                            "Splash_id": `<a href="#" onclick="javascript:void(0);" class="splash_id" data="${entry.splash_id}">${entry.splash_id}</a>`
                        };
                    });

                    $ts("#spectrum_list").clear();
                    $ts.appendTable(list, "#spectrum_list", null, { class: "table" });
                    $ts.select(".splash_id").onClick(a => this.click_splash(a.getAttribute("data")));
                }
            });
        }

        private click_splash(splash_id: string) {
            $ts.get(`/registry/spectrum/?splash=${splash_id}`, data => {
                if (data.code == 0) {
                    let spectrum: spectrum_data = <spectrum_data>data.info;

                    // display the spectrum on page
                }
            });
        }
    }

    export interface spectrum_data {
        splash_id: string;
        name: string;
        adducts: string;
        precursor: number;

        mz: number[];
        intensity: number[];
        smiles: string[];
    }

    export interface spectrum_entry {
        adducts: string;
        mz: number;
        npeaks: number;
        splash_id: string;
    }
}
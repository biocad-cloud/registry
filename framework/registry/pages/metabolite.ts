/// <reference path="../viewer/SpectrumViewer.ts"/>

namespace pages {

    type SpectrumViewer = viewer.SpectrumViewer;

    const url_spectrum_list = "/mzvault/spectrum_list/";
    const url_spectrum_data = "/mzvault/spectrum/";

    export class metabolite_data extends Bootstrap {

        get appName(): string {
            return "metabolite_data";
        }

        protected init(): void {
            this.load();
        }

        private load() {
            $ts.get(url_spectrum_list, msg => {
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
            $ts.get(`${url_spectrum_data}?splash=${splash_id}`, data => {
                if (data.code == 0) {
                    let spectrum: spectrum_data = <spectrum_data>data.info;
                    let display: SpectrumViewer = new viewer.SpectrumViewer();

                    // display the spectrum on page
                    display.renderSpectrum(spectrum);
                }
            });
        }
    }

    export interface spectrum_data {
        splash_id: string;
        name: string;
        adducts: string;
        precursor: number;

        mz: string[];
        intensity: string[];
        smiles: string[];
    }

    export interface spectrum_entry {
        adducts: string;
        mz: number;
        npeaks: number;
        splash_id: string;
    }
}
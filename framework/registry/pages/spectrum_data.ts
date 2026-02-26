namespace pages {

    export class spectrum_data extends Bootstrap {

        get appName(): string {
            return "spectrum_data";
        }

        protected init(): void {
            this.load_exp();
        }

        private load_exp() {
            let url = "/registry/experiment_source/";

            $ts.get(url, msg => {
                if (msg.code == 0) {
                    let data = $ts(<lcms_exp_result[]>msg.info).Select(a => {
                        return {
                            "Organism Source": `<a href="${a.taxid}">${a.taxname}</a>`,
                            "Tissue": a.tissue,
                            "Adducts": a.adducts,
                            "Size": a.size
                        }
                    });

                    $ts("#exp_table").clear();
                    $ts.appendTable(data, "#exp_table", null, { class: "table" });
                }
            })
        }
    }

    export interface lcms_exp_result {
        taxname: string;
        taxid: string;
        tissue: string;
        adducts: string;
        size: number;
    }
}
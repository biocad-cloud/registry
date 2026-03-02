namespace pages {

    const url_experiment_source = "/mzvault/experiment_source/";
    const url_annotation_hits = "/mzvault/annotation_hits/";

    export class spectrum_data extends Bootstrap {

        get appName(): string {
            return "spectrum_data";
        }

        protected init(): void {
            this.load_exp();
            this.load_pie();
        }

        private load_exp() {
            $ts.get(url_experiment_source, msg => {
                if (msg.code == 0) {
                    let data = $ts(<lcms_exp_result[]>msg.info).Select(a => {
                        return {
                            "Organism Source": `<a href="/taxonomy/?id=${a.taxid}">${a.taxname}</a>`,
                            "Tissue": a.tissue,
                            "Adducts": a.adducts,
                            "Size": a.size
                        }
                    });

                    if (data.Count > 0) {
                        $ts("#exp_table").clear();
                        $ts.appendTable(data, "#exp_table", null, { class: "table" });
                    }
                }
            })
        }

        private load_pie() {
            $ts.get(url_annotation_hits, msg => {
                if (msg.code == 0) {
                    let anno_hits = <{ organism: viewer.SpeciesData, tissue: viewer.SpeciesData }>msg.info;

                    viewer.PieViewer.viz_pie(anno_hits.organism, "org_pie", '物种分布统计');
                    viewer.PieViewer.viz_pie(anno_hits.tissue, "tissue_pie", '来源分布统计');
                }
            });
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
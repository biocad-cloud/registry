namespace pages {

    export class taxonomy_data extends Bootstrap {

        get appName(): string {
            return "taxonomy_data";
        }

        taxid() {
            return $ts.location("id");
        }

        protected init(): void {
            $ts.get(`/registry/organism_source/?taxid=${this.taxid()}`, msg => {
                if (msg.code == 0) {
                    let data = $from(<metabolite_sources[]>msg.info).Select(a => {
                        return {
                            "ID": `<a href="/metabolite/${a.id}">${a.id}</a>`,
                            "Name": `<a href="/metabolite/${a.id}">${a.name}</a>`,
                            "Formula": a.formula,
                            "Exact Mass": a.exact_mass,
                            "Hits": a.size
                        };
                    });

                    $ts("#metab-source").clear();
                    $ts.appendTable(data, "#metab-source", null, { class: "table" });
                }
            });
        }
    }

    export interface metabolite_sources {
        id: number;
        name: string;
        formula: string;
        exact_mass: number;
        size: number;
    }
}
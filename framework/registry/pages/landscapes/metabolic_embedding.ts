namespace pages.landscapes {

    type GenomeEmbeddingViz = viewer.GenomeEmbeddingViz;

    export class metabolic extends Bootstrap {

        viz: GenomeEmbeddingViz;

        get appName(): string {
            return "metabolic_landscape";
        }

        protected init(): void {
            this.viz = new viewer.GenomeEmbeddingViz();
            this.viz.initCharts();
        }

    }
}
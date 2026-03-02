namespace pages.landscapes {

    export class metabolic extends Bootstrap {

        get appName(): string {
            return "metabolic_landscape";
        }

        protected init(): void {

        }

    }

    /**
     * a scatter point model that represent a microbial genome embedding result
    */
    export interface metabolic_embedding {
        /**
         * the genome assembly id
        */
        assembly_id: string;

        /**
         * umap/pca embedding dimension 1
        */
        x: number;
        /**
         * umap/pca embedding dimension 2
        */
        y: number;
        /**
         * umap/pca embedding dimension 3
        */
        z: number;

        /**
         * enzyme embedding raw data, length of 7, represent the emebdding result of ec number class 1-7
        */
        ec_number: number[];

        scientific_name: string;
        ncbi_taxid: string;

        kingdom: string;
        phylum: string;
        class: string;
        order: string;
        family: string;
        genus: string;
        species: string;
    }
}
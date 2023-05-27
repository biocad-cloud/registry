<?php

class App {

    /**
     * get motif sites by family
     * 
     * @uses api
     * @method GET
    */
    public function motif_sites_family($q) {
        $q = urldecode($q);
        $motifs = (new Table("motif_sites"))->where([
            "family" => $q
        ])->select([
            "gene_id","gene_name","loci","score","`seq` as `site`"
        ]);

        controller::success([
            "count" => count($motifs),
            "sites" => $motifs,
            "family" => $q
        ]);
    }
}
<?php

class cluster_info {

    public static function cluster_table($id, $page = 1, $page_size = 100) {
        $data = (new Table(["cad_registry"=>"protein_data"]))
            ->left_join("ncbi_taxonomy")
            ->on(["ncbi_taxonomy"=>"id","protein_data"=>"ncbi_taxid"])
            ->left_join("protein_cluster")
            ->on("protein_cluster.query_id = {$id} AND protein_cluster.hit_id = protein_data.id")
            ->where(["cluster_id" => $id])
            ->limit(($page-1)* $page_size, $page_size)
            ->select(["`protein_data`.id",
            "source_id",
            "`protein_data`.name",
            "`function`",
            "ncbi_taxid",
            "ncbi_taxonomy.name as taxname", 
            "identities",
            "mis_match",
            "gap_open",
            "e_value",
            "bit_score"])
            ;
        $info = (new Table(["cad_registry"=>"protein_data"]))->where(["id"=>$id])->find();
        $info["prot"] = $data;

        accessController::log_pageview("protein_cluster", $id);

        return list_nav($info, $page);
    }
}
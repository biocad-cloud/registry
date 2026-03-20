<?php

class cluster_info {

    public static function cluster_table($id, $page = 1, $page_size = 100) {
        $data = (new Table(["cad_registry"=>"protein_data"]))
            ->left_join("ncbi_taxonomy")
            ->on(["ncbi_taxonomy"=>"id","protein_data"=>"ncbi_taxid"])
            ->where(["cluster_id" => $id])
            ->limit(($page-1)* $page_size, $page_size)
            ->select(["id","source_id","`protein_data`.name","function","ncbi_taxid","ncbi_taxonomy.name as taxname"])
            ;
        $info = (new Table(["cad_registry"=>"protein_data"]))->where(["id"=>$id])->find();
        $info["prot"] = $data;

        return list_nav($info, $page);
    }
}
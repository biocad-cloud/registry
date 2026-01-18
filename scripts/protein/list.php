<?php

class model_list {

    public static function list_cc($cc, $page=1,$page_size = 35) {
        $offset = ($page-1) * $page_size;
        $data = (new Table(["cad_registry"=>"compartment_location"]))->where(["name"=> urldecode( $cc)])->find();
        $data["cc"] = $cc;

        if (Utils::isDbNull($data)) {
            RFC7231Error::err404("the subcellular compartment you requested could not be found!");
        } else {
            $data["protein"] = (new Table(["cad_registry"=>"subcellular_location"]))
                ->left_join("protein_data")
                ->on(["subcellular_location"=>"protein_id","protein_data"=>"id"])
                ->left_join("ncbi_taxonomy")
                ->on(["ncbi_taxonomy"=>"id","protein_data"=>"ncbi_taxid"])
                ->where(["location_id"=>$data["id"]])
                ->order_by("`protein_data`.id")
                ->limit($offset,$page_size)
                ->select(["protein_data.id","protein_data.name","source_id","`function`","ncbi_taxid",
            "ncbi_taxonomy.name AS taxname"]);
        }

        return list_nav($data, $page);
    }

    public static function list_page($ec,$page, $page_size = 10) {
        $offset = ($page-1) * $page_size;
        $list = null;
        
        if (Utils::isDbNull($ec)) {
            $list = (new Table(["cad_registry"=>"protein"]))->limit($offset,$page_size)->select();
        } else {
            $class_id = ENTITY_PROTEIN;
            $sql = "SELECT 
            protein.*
        FROM
            cad_registry.protein
                LEFT JOIN
            db_xrefs ON db_xrefs.obj_id = protein.id
                AND type = {$class_id}
        WHERE
            db_xrefs.db_xref = '{$ec}'
        LIMIT {$offset} , {$page_size}"
            ;
            $list = (new Table(["cad_registry"=>"protein"]))->exec($sql);
        }
        
        $page_num = $page;
        $data = [
            "protein" => $list
        ];

        return list_nav( $data, $page_num);
    }
}
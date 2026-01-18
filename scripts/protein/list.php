<?php

class model_list {

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
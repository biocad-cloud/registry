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

        if ($page_num == 1) {
            $data["a_1"] = URL::buildPageUrl(1);
            $data["a_2"] = URL::buildPageUrl(2);
            $data["a_3"] = URL::buildPageUrl(3);
            $data["n1"]  = (1);
            $data["n2"]  = (2);
            $data["n3"]  = (3);
        } else {
            $prevPage = max(1, $page_num - 1); // 上一页，最少为1
            $nextPage = $page_num + 1;         // 下一页

            $data["a_1"] = URL::buildPageUrl($prevPage);
            $data["a_2"] = URL::buildPageUrl($page_num);
            $data["a_3"] = URL::buildPageUrl($nextPage);
            $data["n1"]  = ($prevPage);
            $data["n2"]  = ($page_num);
            $data["n3"]  = ($nextPage);
        }

        return $data;
    }
}
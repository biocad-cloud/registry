<?php

class location_list {

    public static function get_list($page=1,$q=null, $page_size=20) {
        $offset = ($page-1) * $page_size;
        $page_num = $page;
        $page_data = null;

        if (Utils::isDbNull($q)) {
            $page_data = (new Table(["cad_registry"=>"compartment_location"]))
                ->limit($offset,$page_size)
                ->select()
                ;
        } else {
            $q = Table::make_fulltext_strips($q);
            $page_data = (new Table(["cad_registry"=>"compartment_location"]))
                ->where("MATCH (fullname , note) AGAINST ('{$q}' IN BOOLEAN MODE)")
                ->limit($offset,$page_size)
                ->select()
                ;
        }

        $page = [
            "loc" => $page_data
        ];

        return list_nav($page, $page_num);
    }
}
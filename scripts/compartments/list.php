<?php

class location_list {

    public static function get_list($page=1,$page_size=20) {
        $offset = ($page-1) * $page_size;
        $page_num = $page;
        $page = [
            "loc" => (new Table(["cad_registry"=>"compartment_location"]))->limit($offset,$page_size)->select()
        ];

        return list_nav($page, $page_num);
    }
}
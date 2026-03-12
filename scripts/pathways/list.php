<?php

class pathway_list {

    public static function get_list($tax=null, $page=1) {
        $q = ["type" => "conserved"];
        $page_size = 20;

        if (!Utils::isDbNull($tax)) {
            $q = ["taxid" => $tax];
        }

        $offset = ($page-1) * $page_size;
        $data = (new Table(["cad_registry"=>"pathway"]))->where($q)->limit($offset, $page_size)->select();

        return list_nav([
            "pathway" => $data
        ], $page);
    }
}
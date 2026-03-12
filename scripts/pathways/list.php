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

        for($i = 0; $i < count($data); $i++) {
            $data[$i]["dois"] = self::doi_list( json_decode($data[$i]["dois"] ));
        }

        return list_nav([
            "pathway" => $data
        ], $page);
    }

    private static function doi_list($dois) {
        if (count($dois) == 0) {
            return "-";
        } else {
            $dois = Strings::Join(array_map(function($li) {
                return "<li><code>{$li}</code></li>";
            },$dois), "");

            return "<ul>{$dois}</ul>";
        }
    }
}
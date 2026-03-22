<?php

class pathway_list {

    public static function get_list($tax=null, $metab=null, $query=null, $page=1) {
        $q = ["type" => "conserved"];
        $page_size = 20;
        $offset = ($page-1) * $page_size;
        $data = [];

        if (!Utils::isDbNull($tax)) {
            $q = ["taxid" => $tax];
        } else if (!Utils::isDbNull($metab)) {
            $metab = Regex::Match($metab, "\d+");
            $list = (new Table(["cad_registry"=>"pathway_network"]))
                ->where(["class_id" => ENTITY_METABOLITE, "model_id" => $metab])
                ->project("pathway_id")
                ;

            if (count($list) > 0) {
                $q = ["id" => in($list)];
            } else {
                RFC7231Error::err404("Sorry, no pathway data is associated with this metabolite!");
            }            
        } else if (!Utils::isDbNull($query)) {
            $query = Table::make_fulltext_strips($query);
            $q = "MATCH (name , note) AGAINST ('{$query}' IN BOOLEAN MODE)";

            accessController::make_stats($query, "pathway");
        }      

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
<?php

class ncbi_taxonomy {

    public static function taxon_data($id) {
        $id = Regex::Match($id, "\d+");
        $tax = self::find_tax($id);
        $tax["title"] = "{$tax["name"]} ({$tax["rank_name"]})";
        $parent = self::find_tax( $tax["ancestor"]);
        $tax["parent_name"] = $parent["name"];
        $tax["parent_rank"] = $parent["rank_name"];
        $childs = json_decode($tax["childs"], true);

        if (!Utils::isDbNull($childs)) {
            if (count($childs) > 0) {
                $childs = (new Table(["cad_registry"=>"ncbi_taxonomy"]))
                    ->left_join("vocabulary")
                    ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
                    ->where(["`ncbi_taxonomy`.id"=>in($childs)])
                    ->select(["ncbi_taxonomy.*","term as rank_name"])
                    ;
                $tax["childs"] = $childs;
            } else {
                $tax["childs"] = [];
            }
        } else {
            $tax["childs"] = [];
        }
        
        return $tax;
    }

    private static function find_tax($q) {
        return (new Table(["cad_registry"=>"ncbi_taxonomy"]))
            ->left_join("vocabulary")
            ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
            ->where(["`ncbi_taxonomy`.id"=>$q])
            ->find(["ncbi_taxonomy.*","term as rank_name"])
            ;
    }
}
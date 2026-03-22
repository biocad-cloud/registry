<?php

class search {

    public static function query_list($q, $page = 1) {
        $name = trim(urldecode($q));
        $term = Table::make_fulltext_strips($q);
        $page_size = 30;
        $list = (new Table(["cad_registry"=>"ncbi_taxonomy"]))
            ->left_join("vocabulary")
            ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
            ->where("MATCH (`ncbi_taxonomy`.name , `ncbi_taxonomy`.zh_name , `ncbi_taxonomy`.note) AGAINST ('{$term}' IN BOOLEAN MODE)")
            ->limit(($page -1) * $page_size, $page_size)
            ->select(["ncbi_taxonomy.*","term as rank_name"])
            ;
        $direct_name = (new Table(["cad_registry"=>"ncbi_taxonomy"]))
            ->left_join("vocabulary")
            ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
            ->where(["name"=>$name])
            ->find(["ncbi_taxonomy.*","term as rank_name"])
            ;

        accessController::make_stats($term);

        $page_num = $page;
        $data = [
            "taxon" => $list,
            "q" => $term,
            "direct_link" => self::direct_link($direct_name)
        ];

        return list_nav( $data, $page_num);
    }

    private static function direct_link($tax) {
        if (Utils::isDbNull($tax)) {
            return "";
        } else {
            return "
<strong>Taxonomy Find By Name:</strong> <a href='/taxonomy/?id={$tax["id"]}'>{$tax["name"]} ({$tax["rank_name"]})</a>
            ";
        }
    }
}
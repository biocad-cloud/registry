<?php

class search {

    public static function query_list($q, $page = 1) {
        $term = Table::make_fulltext_strips($q);
        $page_size = 30;
        $list = (new Table(["cad_registry"=>"ncbi_taxonomy"]))
            ->left_join("vocabulary")
            ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
            ->where("MATCH (`ncbi_taxonomy`.name , `ncbi_taxonomy`.zh_name , `ncbi_taxonomy`.note) AGAINST ('{$term}' IN BOOLEAN MODE)")
            ->limit(($page -1) * $page_size, $page_size)
            ->select(["ncbi_taxonomy.*","term as rank_name"])
            ;

        $page_num = $page;
        $data = [
            "taxon" => $list,
            "q" => $term
        ];

        return list_nav( $data, $page_num);
    }
}
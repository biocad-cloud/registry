<?php

class metabolite_list {

    public static function getList($page, $page_size = 200) {
        $data = ["title" => "Metabolites Page {$page}"];

        return $data;
    }
}
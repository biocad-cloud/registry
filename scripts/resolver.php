<?php

class resolver {

    public static function resolve($ref) {
        
    }

    public static function internal_link($dblinks) {
        $links = [];

        foreach($dblinks as $xref) {
            if ($xref["db_name"] == "EC Number") {
                $id = $xref["db_xref"];
                $xref["db_xref"] = "<a href='/proteins/?ec={$id}'>{$id}</a>";
            }

            array_push($links, $xref);
        }

        return $links;
    }
}
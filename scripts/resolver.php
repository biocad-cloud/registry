<?php

class resolver {

    public static function resolve($ref) {
        
    }

    public static function internal_link($dblinks) {
        $links = [];

        foreach($dblinks as $xref) {
            $db = $xref["db_name"];
            $id = $xref["db_xref"];

            if ($db == "EC Number") {
                $xref["db_xref"] = "<a href='/enzyme?ec={$id}'>{$id}</a>";
            } else if ($db == "KEGG") {
                $xref["db_xref"] = "<a href='https://www.kegg.jp/entry/{$id}'>{$id}</a>";
            }

            array_push($links, $xref);
        }

        return $links;
    }
}
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

    /**
     * get metabolite id by http request referer
     * 
     * @return int the metabolite reference id
    */
    public static function db_xref() {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer,true );

        if (Utils::isDbNull($referer)) {
            RFC7231Error::err405("Unknown data entry point to query!");
        } else {
            $q = $referer["query"];
            
            if ((!Utils::isDbNull($q)) && array_key_exists("metab", $q)) {
                $referer = $q["metab"];
            } else {
                $referer = $referer["path"];
                $referer = Strings::Split($referer, "/");
                $referer = $referer[2];
            }          
        }

        return Regex::Match($referer, "\d+");
    }
}
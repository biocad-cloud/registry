<?php

class search {

    public function get_result($q, $page = 1) {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer );

        if (!Utils::isDbNull($referer)) {
            return self::local_search(trim($referer["path"], "/"), $q, $page);
        } else {
            return self::global_search($q, $page);
        } 
    }

    private static function local_search($refer, $q, $page = 1) {
        $encode_q = urlencode($q);
        $refer = Strings::Split($refer, "/")[0];

        switch(strtolower($refer)) {
            case "compartments":
                Redirect("/compartments/?q={$encode_q}");
                break;

            case "metabolites":
            case "metabolite":
            case "spectrum":
                Redirect("/metabolites/?q={$encode_q}");
                break;

            case "motifs":
            case "motif":
                Redirect("/motifs/?q={$encode_q}");
                break;

            case "enzymes":
            case "enzyme":
                Redirect("/enzymes/?q={$encode_q}");
                break;

            case "taxonomy":
            case "taxonomy_search":
                Redirect("/taxonomy_search/?q={$encode_q}");
                break;

            case "pathways":
            case "pathway":
                Redirect("/pathways/?q={$encode_q}");

            case "database":
                return self::global_search($q, $page);

            default:
                RFC7231Error::err405("search for '{$q}' on resource controller '{$refer}' has not been implemented yet!");
                
        }
    }

    private static function global_search($q, $page = 1) {
        include_once APP_PATH . "/scripts/dbsearch.php";
        $result = portal::db_search($q, $page);
        $result["title"] = "Search Result of '{$q}'";
        return $result;   
    }
}

return new search();
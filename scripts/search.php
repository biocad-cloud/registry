<?php

class search {

    public function get_result($q) {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer );

        if (!Utils::isDbNull($referer)) {
            return self::local_search(trim($referer["path"], "/"), $q);
        } else {
            return self::global_search($q);
        } 
    }

    private static function local_search($refer, $q) {
        $encode_q = urlencode($q);
        $refer = Strings::Split($refer, "/")[0];

        switch(strtolower($refer)) {
            case "compartments":
                Redirect("/compartments/?q={$encode_q}");
                break;

            case "metabolites":
            case "metabolite":
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

            default:
                RFC7231Error::err405("search for '{$q}' on resource controller '{$refer}' has not been implemented yet!");
                return self::global_search($q);
        }
    }

    private static function global_search($q) {
        return [
            "title" => "Search Result of '{$q}'"
        ];   
    }
}

return new search();
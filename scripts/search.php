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

            default:
                breakpoint([$refer,$q]);
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
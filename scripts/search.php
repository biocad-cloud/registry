<?php

class search {

    public function get_result($q) {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer );

        if (!Utils::isDbNull($referer)) {
            return self::local_search($referer["path"], $q);
        } else {
            return self::global_search($q);
        } 
    }

    private static function local_search($refer, $q) {
        $encode_q = urlencode($q);

        switch(strtolower($refer)) {
            case "/compartments/":
            case "/compartments":
                Redirect("/compartments/?q={$encode_q}");
                break;

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
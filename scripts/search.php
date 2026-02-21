<?php

class search {

    public function get_result($q) {
        $referer = $_SERVER['HTTP_REFERER'];

        breakpoint($referer);

        return [
            "title" => "Search Result of '{$q}'"
        ];    
    }
}

return new search();
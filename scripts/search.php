<?php

class search {

    public function get_result($q) {
        return [
            "title" => "Search Result of '{$q}'"
        ];    
    }
}

return new search();
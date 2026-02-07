<?php

include_once __DIR__ . "/page_view.php";

class page_idset extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        return array_map(function($id) {
            return Regex::Match($id, "\d+");
        }, explode(",",$term));
    }
}

return (function($page, $page_size) {
    return new page_idset($page, $page_size);
});
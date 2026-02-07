<?php

include_once __DIR__ . "/page_view.php";

class page_formula extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        return (new Table(["cad_registry"=>"metabolites"]))
            ->where(["formula"=>$term])
            ->limit($this->offset, $this->page_size)
            ->project("id")
            ;
    }
}

return (function($page, $page_size) {
    return new page_formula($page, $page_size);
});
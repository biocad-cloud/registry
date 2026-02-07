<?php

include_once __DIR__ . "/page_view.php";

class page_cc extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        $loc = (new Table(["cad_registry"=>"compartment_location"]))
            ->where(["name"=>urldecode($term)])
            ->find()
            ;

        return (new Table(["cad_registry"=>"compartment_enrich"]))
            ->where(["location_id"=>$loc["id"]])
            ->limit($this->offset,$this->page_size)
            ->distinct()
            ->project("`compartment_enrich`.`metabolite_id`")
            ; 
    }
}

return (function($page, $page_size) {
    return new page_cc($page, $page_size);
});
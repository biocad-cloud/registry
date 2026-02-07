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

        if (Utils::isDbNull($loc)) {
            RFC7231Error::err404("Could not found the sub-cellular location '{$term}'!");
        }

        return (new Table(["cad_registry"=>"compartment_enrich"]))
            ->where(["location_id"=>$loc["id"]])
            ->limit($this->offset,$this->page_size)
            ->distinct()
            ->project("metabolite_id")
            ; 
    }

    public function desc($term) {
        $loc = (new Table(["cad_registry"=>"compartment_location"]))
            ->where(["name"=>urldecode($term)])
            ->find()
            ;

        return $loc["note"];
    }
}

return (function($page, $page_size) {
    return new page_cc($page, $page_size);
});
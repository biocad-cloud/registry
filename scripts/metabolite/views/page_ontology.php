<?php

include_once __DIR__ . "/page_view.php";

class page_ontology extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($class_id) {
        return (new Table(["cad_registry"=>"metabolite_class"]))
            ->where(["class_id"=>$class_id])
            ->distinct()
            ->order_by("metabolite_id")
            ->limit($this->offset,$this->page_size)
            ->project("metabolite_id")
            ;
    }
}

return (function($page, $page_size) {
    return new page_ontology($page, $page_size);
});
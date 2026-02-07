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

    public function desc($term) {
        $term = (new Table(["cad_registry"=>"ontology"]))->where(["id"=>$term])->find();

        if (!Utils::isDbNull($term)) {
            RFC7231Error::err404("missing ontology term inside database!");
        } else {
            $parent = (new Table(["cad_registry"=>"ontology_relation"]))
                ->left_join("ontology")
                ->on(["ontology"=>"id","ontology_relation"=>"is_a"])
                ->where(["`ontology_relation`.term_id"=>$term["id"]])
                ->select("ontology.*")
                ;

            if (count($parent) == 0) {
                return "metabolites belongs to ontology term {$term["term_id"]}({$term["term"]})";
            } else {
                $parent = array_map(function($term) {
                    return "<li>ontology term {$term["term_id"]}({$term["term"]})</li>";
                }, $parent);
                $parent = Strings::Join($parent, "");

                return "metabolites belongs to ontology term {$term["term_id"]}({$term["term"]}), parent ontology terms:<br/>
                <ul>
                {$parent}
                </ul>";
            }
        }
    }
}

return (function($page, $page_size) {
    return new page_ontology($page, $page_size);
});
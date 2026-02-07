<?php

include_once __DIR__ . "/page_view.php";

class page_topic extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        $model_id = (new Table(["cad_registry"=>"topic"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","topic"=>"topic_id"])
            ->where(["category"=>"Topic", "term"=> urldecode($term), "type"=>in(0, ENTITY_METABOLITE)])
            ->limit($this->offset,$this->page_size)
            ->distinct()
            ->project("model_id")
            ;

        if (count($model_id) == 0) {
            return [];
        } else {
            return (new Table(["cad_registry"=>"registry_resolver"]))
                ->where(["id"=> in($model_id)])
                ->distinct()
                ->project("symbol_id")
                ;
        }
    }

    public function desc($term) {
        $term = (new Table(["cad_registry"=>"vocabulary"]))
            ->where(["category"=>"Topic", 
                     "term"=> urldecode($term)])
            ->find()
            ;
            
        return $term["note"];
    }
}

return (function($page, $page_size) {
    return new page_topic($page, $page_size);
});
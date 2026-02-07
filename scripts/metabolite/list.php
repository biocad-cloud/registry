<?php

class metabolite_list {

    public static function getList($q, 
                                   $term, 
                                   $page      = 1,                                    
                                   $page_size = 20) {

        $data = self::page_data($q, $term, $page, $page_size);
        $data["title"] = "Metabolites Page {$page}";

        if (count($data) == 1) {
            # no data
            $data["desc"] = "no related data could be found.";
            $data["page"] = [];
        }

        return list_nav( $data, $page);
    }

    public static function makeQuery($topic      = null, 
                                     $list       = null, 
                                     $location   = null,
                                     $ontology   = null,
                                     $exact_mass = null,
                                     $formula    = null, 
                                     $search     = null) {

        if (!Utils::isDbNull($topic)) {
            return ["topic", $topic];
        } else if (!Utils::isDbNull($list)) {
            return ["list", $list];
        } else if (!Utils::isDbNull($location)) {
            return ["location", $location];   
        } else if (!Utils::isDbNull($ontology)) {
            return ["ontology", $ontology];   
        } else if (!Utils::isDbNull($exact_mass)) {
            return ["exact_mass", $exact_mass];      
        } else if (!Utils::isDbNull($formula)) {
            return ["formula", $formula];   
        } else if (!Utils::isDbNull($search)) {
            return ["search", $search];        
        } else {
            return ["", null];
        }
    }

    private static function load_page($q, $page, $page_size) {
        switch($q) {
            case "topic": return (include_once __DIR__ . "/views/page_topic.php")($page, $page_size);
            case "formula": return (include_once __DIR__ . "/views/page_formula.php")($page, $page_size);
            case "exact_mass": return (include_once __DIR__ . "/views/page_mass.php")($page, $page_size);
            case "ontology": return (include_once __DIR__ . "/views/page_ontology.php")($page, $page_size);
            case "location": return (include_once __DIR__ . "/views/page_cc.php")($page, $page_size);
            case "list": return (include_once __DIR__ . "/views/page_idset.php")($page, $page_size);
            case "search": return (include_once __DIR__ . "/views/page_search.php")($page, $page_size);

            default:
                return (include_once __DIR__ . "/views/page_list.php")($page, $page_size);
        }
    }

    private static function page_data($q, $term, $page, $page_size) {
        $page = self::load_page($q, $page, $page_size);
        $model_id = $page->q($term);
        
        if (count($model_id) == 0) {
            return [];
        } else {
            $data = $page->metabolites($model_id);
            $data = ["page" => array_map(function($meta) {
                return metabolite_list::link_topics($meta);
            }, $data)];
            $data["desc"] = $page->desc($term);

            return $data;
        }
    }

    private static function link_topics($data) {
        $model = (new Table(["cad_registry"=>"registry_resolver"]))->where(["type" => ENTITY_METABOLITE, "symbol_id"=>$data["uid"]])->find();

        if (!Utils::isDbNull($model)) {
            unset($model["symbol_id"]);
            unset($model["type"]);
            unset($model["add_time"]);
            unset($model["note"]);

            $data["registry_model"] = $model;
            $data["topic"] = (new Table(["cad_registry"=>"topic"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id","topic"=>"topic_id"])
                ->where(["model_id"=>$model["id"], "type"=>in(0,ENTITY_METABOLITE)])
                ->distinct()
                ->select(["`vocabulary`.term","`vocabulary`.color"])
                ;   

            $data["topic"] = array_map(function($topic) {
                return "<span class='badge' style='background-color:{$topic["color"]};'><a class='card-link' style='color: white;' href='/metabolites/?topic={$topic["term"]}'>{$topic["term"]}</a></span>";
            }, $data["topic"]);
            $data["topic"] = Strings::Join($data["topic"]," ");
        }

        return $data;
    }
}
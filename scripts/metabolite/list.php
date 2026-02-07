<?php

class metabolite_list {

    public static function getList($q, $term, $page, $page_size = 20) {
        $data = ["title" => "Metabolites Page {$page}"];
        $page_num = $page;
        $page = self::page_data($q, $term, $page, $page_size);

        if (!Utils::isDbNull($topic)) {
            
        } else if (!Utils::isDbNull($list)) {
            $page = self::page_idset($list,$offset,$page_size);
        } else if (!Utils::isDbNull($loc)) {
            $page = self::page_locs(urldecode($loc),$offset,$page_size);   
        } else if (!Utils::isDbNull($ontology)) {
            $page = self::page_ontology($ontology,$offset,$page_size);   
        } else if (!Utils::isDbNull($exact_mass)) {
            $page = self::page_exactmass($exact_mass, $offset,$page_size);      
        } else if (!Utils::isDbNull($formula)) {
            $page = self::page_formula($formula,$offset,$page_size);     
        } else {
            $page = self::page_list($offset, $page_size);
        }

        return list_nav( $data, $page_num);
    }

    private static function load_page($q, $page, $page_size) {
        switch($q) {
            case "topic": return (include_once __DIR__ . "/views/page_topic.php")($page, $page_size);
            case "formula": return (include_once __DIR__ . "/views/page_formula.php")($page, $page_size);
            case "exact_mass": return (include_once __DIR__ . "/views/page_mass.php")($page, $page_size);
            case "ontology": return (include_once __DIR__ . "/views/page_ontology.php")($page, $page_size);
            case "location": return (include_once __DIR__ . "/views/page_cc.php")($page, $page_size);

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
            $data["page"] = array_map(function($meta) {
                return metabolite_list::link_topics($meta);
            }, $page);

            return $data;
        }
    }

    private static function page_locs($loc, $offset, $page_size) {
        $loc = (new Table(["cad_registry"=>"compartment_location"]))->where(["name"=>$loc])->find();
        $sql="SELECT 
        CONCAT('BioCAD', LPAD(metabolites.id, 11, '0')) AS id,
        metabolites.id as uid,
        name,
        IF(formula = '', 'n/a', formula) AS formula,
        ROUND(exact_mass, 4) AS exact_mass,
        smiles,
        metabolites.note
    FROM
        cad_registry.compartment_enrich
            LEFT JOIN
        metabolites ON compartment_enrich.metabolite_id = metabolites.id
            LEFT JOIN
        struct_data ON struct_data.metabolite_id = metabolites.id
    WHERE
        location_id = {$loc["id"]}
    ORDER BY metabolites.id
    LIMIT {$offset}, {$page_size}";

        return (new Table(["cad_registry"=>"metabolites"]))->exec($sql);
    }

    private static function page_idset($list,$offset, $page_size) {
        $list = array_map(function($id) {
            return Regex::Match($id, "\d+");
        }, explode(",",$list));
        $list = Strings::Join($list,",");
        $sql = "SELECT 
        CONCAT('BioCAD', LPAD(metabolites.id, 11, '0')) AS id,
        metabolites.id as uid,
        name,
        IF(formula = '', 'n/a', formula) AS formula,
        ROUND(exact_mass, 4) AS exact_mass,
        smiles,
        metabolites.note
    FROM
        cad_registry.metabolites
            LEFT JOIN
        struct_data ON struct_data.metabolite_id = metabolites.id
    WHERE metabolites.id IN ({$list})
    ORDER BY metabolites.id
    LIMIT {$offset}, {$page_size}"
            ;      
            return (new Table(["cad_registry"=>"metabolites"]))->exec($sql);
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
<?php

class metabolite_list {

    public static function getList($page, $topic = null, $list=null, $loc=null, $ontology=null, $exact_mass=null, $formula=null, $page_size = 20) {
        $data = ["title" => "Metabolites Page {$page}"];
        $offset = ($page -1) * $page_size;
        $page_num = $page;
        $page = null;

        if (!Utils::isDbNull($topic)) {
            $page = self::page_topic(urldecode($topic),$offset,$page_size);
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

        $data["page"] = array_map(function($meta) {
            return metabolite_list::link_topics($meta);
        }, $page);

        if ($page_num == 1) {
            $data["a_1"] = URL::buildPageUrl(1);
            $data["a_2"] = URL::buildPageUrl(2);
            $data["a_3"] = URL::buildPageUrl(3);
            $data["n1"]  = (1);
            $data["n2"]  = (2);
            $data["n3"]  = (3);
        } else {
            $prevPage = max(1, $page_num - 1); // 上一页，最少为1
            $nextPage = $page_num + 1;         // 下一页

            $data["a_1"] = URL::buildPageUrl($prevPage);
            $data["a_2"] = URL::buildPageUrl($page_num);
            $data["a_3"] = URL::buildPageUrl($nextPage);
            $data["n1"]  = ($prevPage);
            $data["n2"]  = ($page_num);
            $data["n3"]  = ($nextPage);
        }

        return $data;
    }

    private static function page_formula($formula, $offset, $page_size) {
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
    WHERE formula = '{$formula}'
    ORDER BY metabolites.id
    LIMIT {$offset}, {$page_size}"
            ;      
            return (new Table(["cad_registry"=>"metabolites"]))->exec($sql);
    }

    private static function page_exactmass($mass, $offset, $page_size) {
        $min = $mass - 0.01;
        $max = $mass + 0.01;
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
    WHERE exact_mass BETWEEN {$min} AND {$max} 
    ORDER BY metabolites.id
    LIMIT {$offset}, {$page_size}"
            ;      
            return (new Table(["cad_registry"=>"metabolites"]))->exec($sql);
    }

    private static function page_ontology($class_id,$offset,$page_size) {
        $list = (new Table(["cad_registry"=>"metabolite_class"]))
            ->where(["class_id"=>$class_id])
            ->distinct()
            ->order_by("metabolite_id")
            ->limit($offset,$page_size)
            ->project("metabolite_id")
            ;

        if (count($list) == 0) {
            return [];
        } else {
            $list = Strings::Join($list,",");
        }
        
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

    private static function page_topic($topic, $offset, $page_size) {
        $model_id = (new Table(["cad_registry"=>"topic"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","topic"=>"topic_id"])
            ->where(["category"=>"Topic", "term"=>$topic])
            ->limit($offset,$page_size)
            ->distinct()
            ->project("model_id")
            ;
        if (count($model_id) == 0) {
            return [];
        } 
        $model_id = (new Table(["cad_registry"=>"registry_resolver"]))
            ->where(["id"=> in($model_id)])
            ->distinct()
            ->project("symbol_id")
            ;
        if (count($model_id) == 0) {
            return [];
        } 
        $model_id = Strings::Join($model_id,",");
        $list = new Table(["cad_registry"=>"metabolites"]);
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
    WHERE metabolites.id IN ({$model_id})
    ORDER BY metabolites.id"
            ;      
            return $list->exec($sql);
    }

    private static function page_list($offset, $page_size) {
        $list = new Table(["cad_registry"=>"metabolites"]);
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
ORDER BY metabolites.id
LIMIT {$offset}, {$page_size}"
        ;       
        return $list->exec($sql);
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
                ->where(["model_id"=>$model["id"]])
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
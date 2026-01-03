<?php

class metabolite_list {

    public static function getList($page, $topic = null, $page_size = 20) {
        $data = ["title" => "Metabolites Page {$page}"];
        $offset = ($page -1) * $page_size;
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
LIMIT {$offset}, {$page_size}";

        $list = new Table(["cad_registry"=>"metabolites"]);
        $page = $list->exec($sql);
        $data["page"] = array_map(function($meta) {
            return metabolite_list::link_topics($meta);
        }, $page);

        return $data;
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
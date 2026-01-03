<?php

class metabolite_page {
    
    public static function page_data($id) {
        $page = self::page_json($id);
        $page["title"] = $page["name"];
        $langs = [];
        $synonyms = array_map(function($name) {
            $dbs = explode(",", $name["db_source"]);
            $dbs = enum_to_dbnames($dbs);

            return [
                "name"=>$name["synonym"],
                "lang"=>$name["language"],
                "db_source"=> Strings::Join($dbs,",")
            ];
        }, $page["synonyms"]);        

        foreach ($synonyms as $item) {
            $langs[$item['lang']][] = "<span title='{$item["db_source"]}'>{$item["name"]}</span>";
        }

        $synonyms = "";

        foreach($langs as $lang => $list) {
            $list = Strings::Join($list,"; ");
            $synonyms = $synonyms . "<h5>Synonyms [$lang]</h5>
            <p>$list</p>";
        }

        $page["synonyms"] = $synonyms;

        return $page;
    }

    public static function page_json($id) {
        $id = Regex::Match($id, "\d+");
        $page = new Table(["cad_registry"=>"metabolites"]);
        $page = $page
            ->left_join("struct_data")
            ->on(["metabolites"=>"id","struct_data"=>"metabolite_id"])
            ->where(["`metabolites`.`id`"=>$id])
            ->find(["metabolites.*", "smiles","fingerprint","pdb_data"])
            ;        
        $page["id"] = "BioCAD" . str_pad($id,11,'0', STR_PAD_LEFT);
        $synonyms = new Table(["cad_registry"=>"synonym"]);
        $synonyms = $synonyms
            ->where(["type" => ENTITY_METABOLITE, "obj_id"=>$id])
            ->group_by("hashcode")
            ->order_by("COUNT(*)", true)
            ->limit(6)
            ->select(["hashcode","MIN(synonym) AS synonym","COUNT(*) AS refers","MIN(lang) AS language","GROUP_CONCAT(DISTINCT db_source) AS db_source"])
            ;
        $page["synonyms"] = $synonyms;

        return $page;
    }
}
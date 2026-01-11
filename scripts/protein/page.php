<?php

class model_data {

    public static function protein_model($id) {
        $prot = (new Table(["cad_registry"=>"protein"]))->where(["id"=>$id])->find();

        if (Utils::isDbNull($prot)) {
            RFC7231Error::err404("can not found the specific protein model!");
        } else {
            $prot["title"] = $prot["name"];
            $prot["dblink"] = (new Table(["cad_registry"=>"db_xrefs"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id","db_xrefs"=>"db_name"])
                ->where(["type"=>ENTITY_PROTEIN,"obj_id"=>$id])
                ->select(["term as db_name","db_xref"])
                ;

            return $prot;
        }
    }
}
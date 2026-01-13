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
        }

        $ec = [];
        
        foreach($prot["dblink"] as $db => $xref) {
            if ($db == "EC Number") {
                array_push($ec, $xref);
            }
        }

        if (count($ec) > 0) {
            # protein sequence
            $prot["fasta"] = (new Table(["cad_registry"=>"db_xrefs"]))
                ->left_join("protein_data")
                ->on(["protein_data"=>"id","db_xrefs" => "obj_id"])
                ->where(["type"=> FASTA_PROTEIN,"db_xref"=>in($ec)])
                ->order_by(["name","`function`"], true)
                ->limit(20)
                ->select(["protein_data.id",
                "protein_data.source_id",
                "protein_data.name",
                "protein_data.function"])
                ;
            # reactions
            $prot["reaction"] = (new Table(["cad_registry"=>"db_xrefs"]))
                ->left_join("reaction")
                ->on(["reaction"=>"id","db_xrefs" => "obj_id"])
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id","reaction"=>"db_source"])
                ->where(["type"=> ENTITY_REACTION,"`db_xrefs`.`db_xref`"=>in($ec)])
                ->limit(20)
                ->select(["reaction.id",
                "reaction.db_xref",
                "term AS db_source",
                "name",
                "reaction.note"])
                ;
        } else {
            $prot["fasta"] = [];
            $prot["reaction"] = [];
        }

        return $prot;
    }
}
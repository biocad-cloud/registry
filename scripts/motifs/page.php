<?php

class motif_data {

    public static function getdata($id) {
        $motif = (new Table(["cad_registry"=>"motif"]))->where(["id"=>$id])->find();
        $motif["title"] = $motif["name"];
        $motif["site"] = (new Table(["cad_registry"=>"nucleotide_data"]))->where(["is_motif"=>1, "model_id"=>$id])->select(["name","sequence"]);

        return $motif;
    }

    public static function getfamily($family) {
        $data = (new Table(["cad_registry"=>"motif"]))->where(["family" => $family])->select(["id","name"]);

        return [
            "motif"=>$data,
            "family"=>$family,
            "title"=>$family,
            "note"=> count($data) . " clusters",
            "name" => $family            
        ];
    }
}
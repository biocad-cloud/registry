<?php

class motif_data {

    public static function getdata($id) {
        $motif = (new Table(["cad_registry"=>"motif"]))->where(["id"=>$id])->find();
        $motif["title"] = $motif["name"];

        return $motif;
    }
}
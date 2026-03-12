<?php

class pathway_model {

    public static function get_model($id) {
        $pwy = (new Table(["cad_registry"=>"pathway"]))->where(["id"=>$id])->find();

        return $pwy;
    }
}
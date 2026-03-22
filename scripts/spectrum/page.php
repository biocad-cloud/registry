<?php

class spectrum_data {

    public static function get_data($id) {
        $meta = (new Table(["cad_registry"=>"metabolites"]))->where(["id"=>$id])->find();
        $meta["title"] = $meta["name"];

        accessController::log_pageview("spectrum_hit",$id);

        return $meta;
    }
}
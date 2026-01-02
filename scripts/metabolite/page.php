<?php

class metabolite_page {
    
    public static function page_data($id) {
        $id = Regex::Match($id, "\d+");
        $page = new Table(["cad_registry"=>"metabolites"]);
        $page = $page->where(["id"=>$id])->find();
        $page["title"] = $page["name"];
        $page["id"] = "BioCAD" . str_pad($id,11,'0', STR_PAD_LEFT);
        return $page;
    }
}
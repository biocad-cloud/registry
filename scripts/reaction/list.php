<?php

class reaction_list {

    public static function get_list($metabolite, $page=1, $page_size = 30) {
        $id = Regex::Match($metabolite,"\d+");
        $offset = ($page -1) * $page_size;
        $meta = (new Table(["cad_registry"=>"metabolites"]))->where(["id"=>$id])->find();
        $network = (new Table(["cad_registry"=>"metabolic_network"]))
            ->where(["species_id"=>$id])
            ->distinct()
            ->limit($offset, $page_size)
            ->project("reaction_id");
        $page_num = $page;
        $data = $meta;
        $data["title"] = $data["name"];

        if (count($network) > 0) {
            $data["network"] = (new Table(["cad_registry"=>"reaction"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id", "reaction"=> "db_source"])
                ->where(["`reaction`.id" => in($network)])
                ->select(["reaction.id", "term AS db_name", "db_xref", "name", "ec_number"])
                ;
        } else {
            $data["network"] = [];
        } 

        return list_nav( $data, $page_num);
    }
}
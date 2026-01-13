<?php

class reaction_list {

    public static function get_list($id, $page=1, $page_size = 30) {
        $id = Regex::Match($id,"\d+");
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

        if ($page_num == 1) {
            $data["a_1"] = URL::buildPageUrl(1);
            $data["a_2"] = URL::buildPageUrl(2);
            $data["a_3"] = URL::buildPageUrl(3);
            $data["n1"]  = (1);
            $data["n2"]  = (2);
            $data["n3"]  = (3);
        } else {
            $prevPage = max(1, $page_num - 1); // 上一页，最少为1
            $nextPage = $page_num + 1;         // 下一页

            $data["a_1"] = URL::buildPageUrl($prevPage);
            $data["a_2"] = URL::buildPageUrl($page_num);
            $data["a_3"] = URL::buildPageUrl($nextPage);
            $data["n1"]  = ($prevPage);
            $data["n2"]  = ($page_num);
            $data["n3"]  = ($nextPage);
        }

        return $data;
    }
}
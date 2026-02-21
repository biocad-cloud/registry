<?php

class motif_list {

    public static function get_list($page,$q=null, $page_size=15) {
        $offset = ($page-1) * $page_size;
        $list = null;

        if (Utils::isDbNull($q)) {
            $list = (new Table(["cad_registry"=>"motif"]))
                ->group_by("family")
                ->order_by("family")
                ->limit($offset,$page_size)
                ->select(["family","count(*) as size"])
                ;

            for($i=0;$i < count($list);$i++) {
                $list[$i]["family_id"] = urlencode($list[$i]["family"]);
            }
        } else {
            include_once __DIR__ . "/page.php";

            $q = Table::make_fulltext_strips($q);
            $list = (new Table(["cad_registry"=>"motif"]))
                ->where("MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE)")
                ->limit($offset,$page_size)
                ->select()
                ;

            for($i =0;$i < count($list); $i++) {
                $list[$i]["logo"] = motif_data::svg_str($list[$i]["logo"]);
            }
        }

        $page_num = $page;
        $data = [
            "motif" => $list
        ];

        return list_nav( $data, $page_num);
    }
}
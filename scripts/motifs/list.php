<?php

class motif_list {

    public static function get_list($page,$page_size=15) {
        $offset = ($page-1) * $page_size;
        $list = (new Table(["cad_registry"=>"motif"]))->group_by("family")->order_by("family")->limit($offset,$page_size)->select(["family","count(*) as size"]);

        for($i=0;$i < count($list);$i++) {
            $list[$i]["family_id"] = urlencode($list[$i]["family"]);
        }

        $page_num = $page;
        $data = [
            "motif" => $list
        ];

        return list_nav( $data, $page_num);
    }
}
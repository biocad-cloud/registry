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
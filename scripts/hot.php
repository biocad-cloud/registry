<?php

class hot_search {

    public static function top($n = 10) {
        return (new Table(["registry_engine"=>"search_hits"]))
            ->order_by("hits", true)
            ->limit($n)
            ->select(["term","hits"])
            ;
    }
}
<?php

class portal {

    public static function db_search($q, $page=1, $page_size = 30) {
        $q = Table::make_fulltext_strips($q);
        $offset = ($page-1)*$page_size;
        $sql = [
            self::enzyme_search($q),
            self::location_search($q)
        ];
        $sql = array_map(function($ql) {
            return "({$ql})";
        }, $sql);
        $sql = Strings::Join($sql, " UNION ");
        $sql = "SELECT * from ({$sql}) t1 ORDER BY score DESC LIMIT {$offset},{$page_size}";
        $data = (new Table(["cad_registry"=>"vocabulary"]))->getDriver()->Fetch($sql);
        $data = [
            "item" => self::assign_url( $data)
        ];

        return list_nav($data, $page);
    }

    private static function assign_url($terms) {
        for($i = 0; $i< count($terms); $i++) {
            $type = $terms[$i]["type"];
            $id = $terms[$i]["id"];
            $url = "";

            switch($type) {
                case "enzyme":
                    $url = "/enzyme/?id={$id}";
                    break;
                case "location":
                    $id = urlencode($terms[$i]["name"]);
                    $url = "/compartment/?name={$id}";
                    break;

                default:
                    RFC7231Error::err500("not implemented for build url of item type '{$type}'.");
            }

            $terms[$i]["url"] = $url;
        }

        return $terms;
    } 

    private static function enzyme_search($q) {
        return "SELECT 
        id,
        recommended_name AS name,
        note,
        MATCH (recommended_name , systematic_name , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'enzyme' AS type
    FROM
        cad_registry.enzyme
    WHERE
        MATCH (recommended_name , systematic_name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }

    private static function location_search($q) {
        return "SELECT 
        id,
        name,
        note,
        MATCH (fullname , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'location' AS type
    FROM
        cad_registry.compartment_location
    WHERE
        MATCH (fullname , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }
}


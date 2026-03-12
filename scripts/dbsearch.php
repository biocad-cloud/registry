<?php

class portal {

    public static function db_search($q, $page=1, $page_size = 50) {
        $q = Table::make_fulltext_strips($q);
        $offset = ($page-1)*$page_size;
        $tr = 200;
        $sql = [
            self::enzyme_search($q),
            self::location_search($q),
            self::metabolite_search($q),
            self::pathway_search($q),
            self::taxonomy_search($q),
            self::motif_search($q)
        ];
        $sql = array_map(function($ql) {
            return "({$ql})";
        }, $sql);
        $sql = Strings::Join($sql, " UNION ");
        $sql = "SELECT id, name, score, `type` 
                FROM ({$sql}) t1 
                ORDER BY score DESC 
                LIMIT {$offset},{$page_size}"
        ;
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
            $color = "";

            switch($type) {
                case "enzyme":
                    $url = "/enzyme/?id={$id}";
                    $color = "primary";
                    break;

                case "location":
                    $id = urlencode($terms[$i]["name"]);
                    $url = "/compartment/?name={$id}";
                    $color = "secondary";
                    break;

                case "metabolite":
                    $url = "/metabolite/?id={$id}";
                    $color = "success";
                    break;

                case "pathway":
                    $url = "/pathway/?id={$id}";
                    $color = "danger";
                    break;

                case "taxonomy":
                    $url = "/taxonomy/?id={$id}";
                    $color = "warning";
                    break;

                case "motif":
                    $url = "/motif/?id={$id}";
                    $color = "info";
                    break;

                default:
                    RFC7231Error::err500("not implemented for build url of item type '{$type}'.");
            }

            $terms[$i]["url"] = $url;
            $terms[$i]["bs_color"] = $color;
        }

        return $terms;
    } 

    private static function pathway_search($q) {
        return "SELECT 
        id,
        name,
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'pathway' AS type
    FROM
        cad_registry.pathway
    WHERE
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }

    private static function taxonomy_search($q) {
        return "SELECT 
        id,
        name,
        MATCH (name , zh_name , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'taxonomy' AS type
    FROM
        cad_registry.ncbi_taxonomy
    WHERE
        MATCH (name , zh_name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    } 

    private static function motif_search($q) {
        return "SELECT 
        id,
        name,
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'motif' AS type
    FROM
        cad_registry.motif
    WHERE
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }

    private static function metabolite_search($q) {
        return "SELECT 
        id,
        name,
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'metabolite' AS type
    FROM
        cad_registry.metabolites
    WHERE
        MATCH (name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }

    private static function enzyme_search($q) {
        return "SELECT 
        id,
        recommended_name AS name,
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
        MATCH (fullname , note) AGAINST ('{$q}' IN BOOLEAN MODE) AS score,
        'location' AS type
    FROM
        cad_registry.compartment_location
    WHERE
        MATCH (fullname , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
    }
}


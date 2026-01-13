<?php

class reaction_model {

    public static function get_data($id) {
        $id = Regex::Match($id,"\d+");
        $rxn = (new Table(["cad_registry"=>"reaction"]))->left_join("vocabulary")->on(["vocabulary"=>"id","reaction"=>"db_source"])->where(["`reaction`.id"=>$id])->find(["reaction.*","term as db_name"]);
        $rxn["title"] = $rxn["name"];
        $rxn["dblink"] = (new Table(["cad_registry"=>"db_xrefs"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","db_xrefs" => "db_name"])
            ->where([
                "type"=> ENTITY_REACTION,
                "obj_id"=>$id
            ])->distinct()
            ->select(["term as db_name","db_xref"])
            ;        
        $left = self::get_network($id, RXN_LEFT);
        $right = self::get_network($id, RXN_RIGHT);
        $rxn["equation"] = self::equation($left, $right);

        if (strlen( $rxn["hashcode"]) > 0 ) {
            $rxn["alias"] = (new table(["cad_registry"=>"reaction"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id","reaction"=>"db_source"])
                ->where(["hashcode"=>$rxn["hashcode"]])
                ->select(["term AS db_name", "db_xref", "reaction.id", "name"])
                ;
        } else {
            $rxn["alias"] = [];
        }

        return $rxn;
    }

    private static function equation($left,$right) {
        $left = Strings::Join( array_map(function($c) {
            return self::build_factor($c);
        }, $left)," + ");
        $right = Strings::Join( array_map(function($c) {
            return self::build_factor($c);
        }, $right), " + ");

        return "{$left} = {$right}";
    }

    private static function build_factor($c) {
        $factor = "";

        if ($c["factor"] > 1) {
            $factor = $c["factor"] . " ";
        }

        return "<a href='/metabolite/{$c["id"]}' title='{$c["symbol_id"]}@{$c["loc"]}'>{$factor}{$c["name"]}[{$c["formula"]}]</a>";
    }

    private static function get_network($id, $role) {
        return (new Table(["cad_registry"=>"metabolic_network"]))
            ->left_join("metabolites")
            ->on(["metabolites"=>"id","metabolic_network" => "species_id"])
            ->left_join("compartment_location")
            ->on(["compartment_location"=>"id","metabolic_network" => "compartment_id"])
            ->where(["reaction_id"=>$id, "`role`"=> $role])
            ->select(["factor",
            "symbol_id",
            "metabolites.id",
            "metabolites.name",
            "metabolites.formula",
            "compartment_location.name as loc"]);
    }
}
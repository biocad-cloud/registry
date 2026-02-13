<?php

class compartment_location {

    public static function location_data($name) {
        $name = urldecode($name);
        $loc = (new Table(["cad_registry"=>"compartment_location"]))->where(["name"=>$name])->find();

        if (Utils::isDbNull($loc)) {
            RFC7231Error::err404("subcellular location compartment '{$name}' can not be found!");
        }

        $metabolites = new Table(["cad_registry"=>"compartment_enrich"]);
        $metabolites = $metabolites
            ->left_join("metabolites")
            ->on(["compartment_enrich"=>"metabolite_id","metabolites"=>"id"])
            ->where(["location_id"=>$loc["id"]])
            ->limit(100)
            ->select(["`metabolites`.id","name","formula","ROUND(exact_mass, 4) AS exact_mass"])
            ;
        $proteins = (new Table(["cad_registry"=>"subcellular_location"]))
            ->left_join("protein_data")
            ->on(["protein_data"=>"id","subcellular_location"=>"protein_id"])
            ->where(["location_id"=>$loc["id"]])
            ->limit(100)
            ->select("protein_data.*")
            ;

        $loc["metabolite"] = $metabolites;
        $loc["protein"] = $proteins;
        $loc["title"] = $loc["fullname"];

        return $loc;
    }
}
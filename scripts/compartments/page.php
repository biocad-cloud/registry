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

        $loc["metabolite"] = $metabolites;

        return $loc;
    }
}
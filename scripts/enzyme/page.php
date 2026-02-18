<?php

class enzyme_data {

    public static function data($ec) {
        $laws = (new Table(["cad_registry"=>"kinetics_law"]))
            ->left_join("reaction")
            ->on(["reaction"=>"id","kinetics_law"=>"metabolic_node"])
            ->where(["`kinetics_law`.ec_number" => $ec])
            ->select(["`kinetics_law`.*", "`reaction`.name"])
            ;

        return [
            "ec" => $ec,
            "law" => $laws
        ];
    }
}
<?php

class pathway_model {

    public static function get_model($id) {
        $pwy = (new Table(["cad_registry"=>"pathway"]))->where(["id"=>$id])->find();
        $pwy["metab"] = (new Table(["cad_registry"=>"pathway_network"]))
            ->left_join("metabolites")->on(["metabolites"=>"id", "pathway_network"=> "model_id"])
            ->left_join("struct_data")->on(["struct_data"=>"metabolite_id","metabolites"=>"id"])
            ->where(["class_id" => ENTITY_METABOLITE, "pathway_id" => $id, "metabolite_id" => not_null()])
            ->select(["metabolite_id", "name", "formula", "exact_mass", "cas_id", "smiles"])
            ;
        $pwy["enzyme"] = (new Table(["cad_registry"=>"pathway_network"]))
            ->left_join("enzyme")
            ->on(["enzyme"=>"id","pathway_network"=>"model_id"])
            ->where(["class_id"=> EC_NUMBER, "pathway_id"=> $id])
            ->select(["`enzyme`.*", "symbol_id as ec_number"])
            ;
        $pwy["prot"] =  (new Table(["cad_registry"=>"pathway_network"]))
            ->left_join("protein_data")
            ->on(["protein_data"=>"id","pathway_network"=>"model_id"])
            ->where(["class_id" => FASTA_PROTEIN, "model_id" => gt(0), "pathway_id"=>$id])
            ->select(["symbol_id AS uniprot_id", "name", "`function`", "`protein_data`.id"])
            ;

        return $pwy;
    }
}
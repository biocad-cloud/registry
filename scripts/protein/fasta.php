<?php

class prot_fasta {

    public static function seqinfo($id) {
        $seq = (new Table(["cad_registry"=>"protein_data"]))
            ->left_join("ncbi_taxonomy")
            ->on(["ncbi_taxonomy"=>"id","protein_data"=>"ncbi_taxid"])
            ->where(["`protein_data`.id"=>$id])
            ->find(["`protein_data`.*",
                "`ncbi_taxonomy`.name as taxname"
            ]);
        $ec = (new Table(["cad_registry"=>"db_xrefs"]))
            ->where(["type" => FASTA_PROTEIN,"db_name"=>EC_NUMBER,"obj_id"=>$id])
            ->project("db_xref")
            ;
        $rxns = [];
        $cc = (new Table(["cad_registry"=>"subcellular_location"]))
            ->left_join("compartment_location")
            ->on(["compartment_location"=>"id","subcellular_location"=>"location_id"])
            ->where(["protein_id"=>$id])
            ->select("compartment_location.*")
            ;

        if (count($ec) > 0) {
            $rxns = (new Table(["cad_registry"=>"db_xrefs"]))->where(["type"=>ENTITY_REACTION,"db_name"=>EC_NUMBER,"db_xref"=>in($ec)])->project("obj_id");

            if (count($rxns) > 0) {
                $rxns = (new Table(["cad_registry"=>"reaction"]))
                    ->left_join("vocabulary")
                    ->on(["vocabulary"=>"id","reaction"=>"db_source"])
                    ->where(["`reaction`.id"=>in($rxns)])
                    ->select(["reaction.id","name","reaction.note","term as db_source","db_xref"])
                    ;
            }
        }

        $seq["subcellular_locations"] = $cc;
        $seq["reaction"] = $rxns;
        $seq["title"] = $seq["name"] . " - " . $seq["taxname"];
        $seq["ec_numbers"] = Strings::Join(array_map(function($ec) {
            return "<a href='/proteins/?ec={$ec}'>$ec</a>";
        }, $ec), " / ");

        return $seq;
    }
}
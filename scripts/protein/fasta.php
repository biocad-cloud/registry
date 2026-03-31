<?php

class prot_fasta {

    public static function seqinfo($id) {
        accessController::log_pageview("protein_fasta", $id);

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
        $pathway = (new Table(["cad_registry"=>"pathway_network"]))
            ->where(["model_id"=>$id, "class_id" => FASTA_PROTEIN])
            ->project("pathway_id")
            ;
        
        if ($seq["cluster_id"] > 0) {
            $seq["cluster"] = (new Table(["cad_registry"=>"protein_data"]))->where(["id" => $seq["cluster_id"]])->find();
            $seq["cluster_name"] = $seq["cluster"]["function"];
        } else {
            $seq["cluster"] = [];
            $seq["cluster_name"] = "";
        }        

        if ($seq["protein_id"] > 0) {
            $seq["model"] = (new Table(["cad_registry"=>"protein"]))->where(["id" => $seq["protein_id"]])->find();
            $seq["model_name"] = $seq["model"]["name"] . " - " . $seq["model"]["function"] ;
        } else {
            $seq["model"] = [];
            $seq["model_name"] = "";
        }

        if (count($pathway) > 0) {
            $pathway = (new Table(["cad_registry"=>"pathway"]))->where(["id" => in($pathway)])->select();
        } else {
            $pathway = [];
        }

        $rxns = [];
        $cc = (new Table(["cad_registry"=>"subcellular_location"]))
            ->left_join("compartment_location")
            ->on(["compartment_location"=>"id","subcellular_location"=>"location_id"])
            ->where(["protein_id"=>$id])
            ->distinct()
            ->select("compartment_location.*")
            ;
        $seq["topic"] = (new Table(["cad_registry"=>"topic"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","topic" => "topic_id"])
            ->where(["type"=>FASTA_PROTEIN,"model_id"=>$id])
            ->distinct()
            ->select("vocabulary.*")
            ;
        $seq["topic"] = Strings::Join(array_map(function($topic) {
            return "<span class='badge' style='background-color:{$topic["color"]};'><a style='color: white;' href='/proteins/?topic={$topic["term"]}'>{$topic["term"]}</a></span>";
        }, $seq["topic"]));
        
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

        $seq["domain"] = (new Table(["cad_registry"=>"conserved_domain"]))
            ->left_join("ontology")->on(["ontology"=>"id","conserved_domain"=>"domain_id"])
            ->left_join("protein_data")->on(["protein_data"=>"id","conserved_domain"=>"protein_id"])
            ->where(["`protein_data`.id"=>$id])
            ->select(["domain_id",
            "term",
            "`left`",
            "`right`",
            "SUBSTRING(protein_data.sequence,
                `left`,
                `right` - `left` + 1) AS site"])
            ;
        $seq["pathway"] = $pathway;
        $seq["subcellular_locations"] = $cc;
        $seq["reaction"] = $rxns;
        $seq["title"] = $seq["name"] . " - " . $seq["taxname"];
        $seq["ec_numbers"] = Strings::Join(array_map(function($ec) {
            return "<a href='/proteins/?ec={$ec}'>$ec</a>";
        }, $ec), " / ");

        return $seq;
    }
}
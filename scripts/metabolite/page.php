<?php

include_once __DIR__ . "/registry.php";

class metabolite_page {
    
    public static function page_data($id) {
        $page = self::page_json($id);
        $page["title"] = $page["name"];
        $langs = [];
        $synonyms = array_map(function($name) {
            $dbs = explode(",", $name["db_source"]);
            $dbs = enum_to_dbnames($dbs);

            return [
                "name"=>$name["synonym"],
                "lang"=>$name["language"],
                "db_source"=> Strings::Join($dbs,",")
            ];
        }, $page["synonyms"]);        

        foreach ($synonyms as $item) {
            $langs[$item['lang']][] = "<span title='{$item["db_source"]}'>{$item["name"]}</span>";
        }

        $synonyms = "";

        foreach($langs as $lang => $list) {
            $list = Strings::Join($list,"; ");
            $synonyms = $synonyms . "<h4>Synonyms [$lang]</h4>
            <p>$list</p>";
        }

        $page["synonyms"] = $synonyms;
        $page["exact_mass"] = round($page["exact_mass"],4);        

        if (strlen($page["cas_id"]) > 0)      $page["cas_id"]       = "<a href='https://commonchemistry.cas.org/detail?cas_rn={$page["cas_id"]}'>{$page["cas_id"]}</a>";
        if (strlen($page["pubchem_cid"]) > 0) $page["pubchem_cid"]  = "<a href='https://pubchem.ncbi.nlm.nih.gov/compound/{$page["pubchem_cid"]}'>{$page["pubchem_cid"]}</a>";
        if (strlen($page["hmdb_id"]) >0)      $page["hmdb_id"]      = "<a href='https://hmdb.ca/metabolites/{$page["hmdb_id"]}'>{$page["hmdb_id"]}</a>";
        if (strlen($page["wikipedia"]) >0)    $page["wikipedia"]    = "<a href='https://en.wikipedia.org/wiki/{$page["wikipedia"]}'>{$page["wikipedia"]}</a>";   
        if (strlen($page["mesh_id"]) >0)      $page["mesh_id"]      = "<a href='https://www.ncbi.nlm.nih.gov/mesh/?term={$page["mesh_id"]}'>{$page["mesh_id"]}</a>";  
        if (strlen($page["chebi_id"]) >0)     $page["chebi_id"]     = "<a href='https://www.ebi.ac.uk/chebi/ChEBI:{$page["chebi_id"]}'>{$page["chebi_id"]}</a>";  
        if (strlen($page["kegg_id"]) >0)      $page["kegg_id"]      = "<a href='https://www.genome.jp/entry/{$page["kegg_id"]}'>{$page["kegg_id"]}</a>";  
        if (strlen($page["biocyc"]) >0)       $page["biocyc"]       = "<a href='https://www.biocyc.org/compound?id={$page["biocyc"]}'>{$page["biocyc"]}</a>";  
        if (strlen($page["lipidmaps_id"]) >0) $page["lipidmaps_id"] = "<a href='https://lipidmaps.org/databases/lmsd/{$page["lipidmaps_id"]}'>{$page["lipidmaps_id"]}</a>";  

        if (!Utils::isDbNull($page["topic"])) {
            $page["topic"] = array_map(function($topic) {
                return "<span class='badge' style='background-color:{$topic["color"]};'><a style='color: white;' href='/metabolites/?topic={$topic["term"]}'>{$topic["term"]}</a></span>";
            }, $page["topic"]);
            $page["topic"] = Strings::Join($page["topic"]," ");
        }

        return $page;
    }

    public static function page_json($id) {
        $id = intval(Regex::Match($id, "\d+"));
        $model_id = (new Table(["cad_registry"=>"registry_resolver"]))->where(["type"=>ENTITY_METABOLITE,"symbol_id"=>$id])->find();

        accessController::log_pageview("metabolite", $id);

        $page = new Table(["cad_registry"=>"metabolites"]);
        $page = $page
            ->left_join("struct_data")
            ->on(["metabolites"=>"id","struct_data"=>"metabolite_id"])
            ->where(["`metabolites`.`id`"=>$id])
            ->find(["metabolites.*", "smiles","fingerprint","pdb_data"])
            ;        
        $page["id"] = "BioCAD" . str_pad($id,11,'0', STR_PAD_LEFT);
        $synonyms = new Table(["cad_registry"=>"synonym"]);
        $synonyms = $synonyms
            ->where(["type" => ENTITY_METABOLITE, "obj_id"=>$id])
            ->group_by("hashcode")
            ->order_by("COUNT(*)", true)
            ->limit(6)
            ->select(["hashcode","MIN(synonym) AS synonym","COUNT(*) AS refers","MIN(lang) AS language","GROUP_CONCAT(DISTINCT db_source) AS db_source"])
            ;
        $page["synonyms"] = $synonyms;
        $page["subcellular_locations"] = (new Table(["cad_registry"=>"compartment_enrich"]))
            ->left_join("compartment_location")
            ->on(["compartment_enrich"=>"location_id","compartment_location"=>"id"])
            ->where(["metabolite_id"=>$id])
            ->select(["`compartment_location`.id","name","fullname","compartment_location.note"]);
        $page["ontology"] = (new Table(["cad_registry"=>"metabolite_class"]))
            ->left_join("ontology")
            ->on(["ontology"=>"id","metabolite_class" => "class_id"])
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","ontology" => "ontology_id"])
            ->where(["metabolite_id"=>$id])
            ->select(["class_id","term_id","`ontology`.term","`vocabulary`.term AS ontology"]);
        $page["db_xrefs"] = (new Table(["cad_registry"=>"db_xrefs"]))
            ->left_join("vocabulary")
            ->on(["db_xrefs"=>"db_name","vocabulary"=>"id"])
            ->where(["obj_id"=>$id,"type"=> ENTITY_METABOLITE ])
            ->distinct()
            ->select(["term AS db_name", "db_xref"])
            ;
        $page["topic"] = (new Table(["cad_registry"=>"topic"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","topic"=>"topic_id"])
            ->where(["model_id"=>$id, "type"=>ENTITY_METABOLITE])
            ->distinct()
            ->select(["`vocabulary`.term","`vocabulary`.color"])
            ;  

        if (!Utils::isDbNull($model_id)) {
            $page["display"] = "block";
            $page["registry_model"] = $model_id;
            $page["symbol"] = $model_id["register_name"];
            $page["reviewed_time"] = date("Y-m-d", strtotime($model_id["add_time"]));
            $page["now_date"] = date("Y-m-d");
        } else {
            $page["display"] = "none";
        }

        $page["organism"] = (new Table(["cad_registry"=>"organism_source"]))
            ->where(["metabolite_id"=>$id])
            ->select(["taxname","organism_id as ncbi_taxid"])
            ;
        $network = (new Table(["cad_registry"=>"metabolic_network"]))
            ->where(["species_id"=>$id])
            ->distinct()
            ->limit(20)
            ->project("reaction_id");

        if (count($network) > 0) {
            $page["network"] = (new Table(["cad_registry"=>"reaction"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id", "reaction"=> "db_source"])
                ->where(["`reaction`.id" => in($network)])
                ->select(["reaction.id", "term AS db_name", "db_xref", "name", "ec_number"])
                ;
        } else {
            $page["network"] = [];
        }

        $pathway = (new Table(["cad_registry"=>"pathway_network"]))
            ->where(["model_id"=>$id, "class_id" => ENTITY_METABOLITE])
            ->project("pathway_id")
            ;
    
        if (count($pathway) > 0) {
            $page["pathway"] = (new Table(["cad_registry"=>"pathway"]))
                ->where(["id" => in($pathway)])
                ->limit(20)
                ->select()
                ;
        } else {
            $page["pathway"] = [];
        }

        return $page;
    }
}
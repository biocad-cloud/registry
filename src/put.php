<?php

include __DIR__ . "/../framework/bootstrap.php";

/**
 * put data to registry
*/
class app {

    /**
     * put new taxonomic group data into registry
     * 
     * @method POST
     * @access *
     * @uses api
    */
    public function taxonomic($name, $id, $note = "") {
        $tax = new Table("taxonomic");
        $name = strip_postVal($name);
        $note = strip_postVal($note);
        $id = strip_postVal($id);
        # check data is exists or not
        $check = $tax->where(["name" => urldecode($name)])->find();

        if (Utils::isDbNull($check)) {
            // add new
            $id = $tax->add([
                "name" => urldecode($name),
                "note" => urldecode($note),
                "id" => $id
            ]);

            controller::success(["id" => $id], $tax->getLastMySql());
        } else {
            // data is already exists
            controller::success(["id" => $check["id"], "exist_data" => $check]);
        }
    }

    /**
     * put a set of the genome into one taxonomic group
     * 
     * @uses api
     * @param integer $grp the taxonomic group id
     * @method POST
    */
    public function genomes($grp) {
        $li = $_POST["li"];
        $tax = new Table("taxonomic");

        if (is_string($li)) {
            $li = json_decode($li, true);
        }

        // check of the taxonomic is exists in database or not?
        $check = $tax->where(["id" => $grp])->find();
        $genome = new Table("genomes");

        if (Utils::isDbNull($check)) {
            controller::error("no target taxonomic group data!");
        } else {
            foreach($li as $name => $t) {
                $genome->add([
                    "id" => $t["id"], 
                    "taxonomic_group" => $grp, 
                    "name" => $t["name"], 
                    "ncbi_taxid" => 0
                ]);
            }

            $tax->where(["id" => $grp])->save([
                "n_tax" => count($li)
            ]);

            controller::success(1);
        }
    }

    /**
     * @uses api
     * @method POST
    */
    public function operons($genome, $li) {
        $tax = new Table("genomes");
        $check = $tax->where(["id" => $genome])->find();
        $genes = new Table("molecules");
        $operons = new Table("operon_group");
        $names = [];
        $operon = null;

        if (is_string($li)) {
            $li = json_decode($li, true);
        }
        if (Utils::isDbNull($check)) {
            controller::error("no target genome data!");
        }

        foreach($li as $gene) {
            array_push($names, $gene["name"]);
        }

        $operon_name = implode(",", $names);
        $check = $operons->where([
            "operon" => $operon_name,
            "genome_id" => $genome
        ])->find();

        if (Utils::isDbNull($check)) {
            $operon = $operons->add([
                "operon" => $operon_name,
                "genome_id" => $genome
            ]);
        } else {
            $operon = $check["id"];
        }

        $operon_graph = new Table("operon_graph");
        $dblinks = new Table("dblinks");
        $vocabulary = new Table("vocabulary");

        foreach($li as $gene) {
            # check gene molecule is exists or not
            $check = $genes->where(["molecule_id" => $gene["locusId"]])->find();
                
            if (Utils::isDbNull($check)) {
                $check = $genes->add([
                    // "id" => $gene["id"],
                    "molecule_id" => $gene["locusId"],
                    "name" => $gene["name"]
                ]);
                $gene["id"] = $check;
            } else {
                $gene["id"] = $check["id"];
            }

            foreach($gene["dblinks"] as $dbname => $xref_id) {
                $dbindex = $vocabulary->where([
                    "term" => $dbname
                ])->find();

                if (Utils::isDbNull($dbindex)) {
                    $vocabulary->add([
                        "term" => $dbname,
                        "hashcode" => registry_key($dbname),
                        "category" => "Biological Database",
                        "description" => "add from operon data"
                    ]);

                    $dbindex = $vocabulary->where([
                        "term" => $dbname
                    ])->find();
                }

                $dblinks->add([
                    "db_src" => $dbindex["id"],
                    "xref_id" => $xref_id,
                    "entity_id" => $gene["id"],
                    "entity_type" => 1
                ]);
            }

            $operon_graph->add([
                "operon_id" => $operon,
                "gene_id" => $gene["id"],
                "genome_id" => $genome
            ]);
        }

        controller::success(1);
    }

    /**
     * @uses api
     * @method POST
    */
    public function regulation($genome, $regulator, $regulator_name, $li, $family, $type) {
        $reg = new Table("regulator");
        $mols = new Table("molecules");
        $family = strip_postVal(urldecode($family));
        $type = strip_postVal(urldecode($type));
        $check = $reg->where(["gene_id" => $regulator, "genome_id" => $genome])->find();
        $motifs = new Table("motif_sites");
        $regulator_id = null;

        if (is_string($li)) {
            $li = json_decode($li, true);
        }

        if (Utils::isDbNull($check)) {
            $gene = $mols->where(["molecule_id" => $regulator])->find();

            if (Utils::isDbNull($gene)) {
                $gene = $mols->add([
                    "molecule_id" => $regulator,
                    "type" => 1,
                    "name" => $regulator_name
                ]);
            } else {
                $gene = $gene["id"];
            }

            $regulator_id = $reg->add([
                "gene_id" => $regulator,
                "mol_id" => $gene,
                "family" => 0,
                "genome_id" => $genome
            ]);
        } else {
            $regulator_id = $check["id"];
        }


    }
}
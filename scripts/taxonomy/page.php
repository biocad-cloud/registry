<?php

class ncbi_taxonomy {

    public static function taxon_data($id) {
        $id = Regex::Match($id, "\d+");
        $tax = self::find_tax($id);
        $tax["title"] = "{$tax["name"]} ({$tax["rank_name"]})";
        $parent = self::find_tax( $tax["ancestor"]);
        $tax["parent_name"] = $parent["name"];
        $tax["parent_rank"] = $parent["rank_name"];
        $childs = json_decode($tax["childs"], true);

        if (!Utils::isDbNull($childs)) {
            if (count($childs) > 0) {
                $childs = (new Table(["cad_registry"=>"ncbi_taxonomy"]))
                    ->left_join("vocabulary")
                    ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
                    ->where(["`ncbi_taxonomy`.id"=>in($childs)])
                    ->select(["ncbi_taxonomy.*","term as rank_name"])
                    ;
                $tax["childs"] = $childs;
            } else {
                $tax["childs"] = [];
            }
        } else {
            $tax["childs"] = [];
        }
        
        $prot_data = FASTA_PROTEIN;
        $ec_number = EC_NUMBER;
        $sql = "SELECT 
        protein_data.id,
        source_id,
        source_db,
        term as db_name,
        name,
        `function`,
        db_xref AS ec_number
    FROM
        cad_registry.protein_data
            LEFT JOIN
        db_xrefs ON db_xrefs.obj_id = protein_data.id
            AND db_xrefs.type = {$prot_data}
            AND db_xrefs.db_name = {$ec_number}
            LEFT JOIN
        vocabulary ON vocabulary.id = protein_data.source_db
    WHERE
        ncbi_taxid = {$id}
            AND NOT db_xref IS NULL";
        $tax["enzyme"] = (new Table(["cad_registry"=>"protein_data"]))->exec($sql);

        return $tax;
    }

    private static function find_tax($q) {
        return (new Table(["cad_registry"=>"ncbi_taxonomy"]))
            ->left_join("vocabulary")
            ->on(["ncbi_taxonomy"=>"rank","vocabulary"=>"id"])
            ->where(["`ncbi_taxonomy`.id"=>$q])
            ->find(["ncbi_taxonomy.*","term as rank_name"])
            ;
    }
}
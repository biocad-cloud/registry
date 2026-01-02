<?php

class metabolite_list {

    public static function getList($page, $page_size = 200) {
        $data = ["title" => "Metabolites Page {$page}"];
        $offset = ($page -1) * $page_size;
        $sql = "SELECT 
    metabolites.id,
    name,
    IF(formula = '', 'n/a', formula) AS formula,
    ROUND(exact_mass, 4) AS exact_mass,
    IF(cas_id IS NULL, '-', cas_id) AS cas_id,
    IF(pubchem_cid IS NULL,
        '-',
        pubchem_cid) AS pubchem,
    IF(chebi_id IS NULL,
        '-',
        CONCAT('ChEBI:', chebi_id)) AS chebi,
    IF(hmdb_id IS NULL, '-', hmdb_id) AS hmdb,
    IF(lipidmaps_id IS NULL,
        '-',
        lipidmaps_id) AS lipidmaps,
    IF(kegg_id IS NULL, '-', kegg_id) AS kegg,
    IF(biocyc IS NULL, '-', biocyc) AS biocyc,
    IF(mesh_id IS NULL, '-', mesh_id) AS mesh_id,
    IF(wikipedia IS NULL, '-', wikipedia) AS wikipedia,
    smiles,
    metabolites.note
FROM
    cad_registry.metabolites
        LEFT JOIN
    struct_data ON struct_data.metabolite_id = metabolites.id
LIMIT {$offset}, {$page_size}";

        $list = new Table(["cad_registry"=>"metabolites"]);
        $page = $list->exec($sql);

        return $data;
    }
}
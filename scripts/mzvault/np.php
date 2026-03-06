<?php

class np {

    public static function plant_np($page=1, $page_size = 50) {
        $offset = ($page - 1) * $page_size;
        $text_len = 150;
        $sql = "SELECT 
        CONCAT('BioCAD', LPAD(metabolite_id, 11, '0')) AS id,
        name,
        formula,
        exact_mass,
        cas_id,
        kegg_id,
        hmdb_id,
        drugbank_id,
        biocyc,
        mesh_id,
        smiles,
        IF(CHAR_LENGTH(metabolites.note) > {$text_len},
            CONCAT(MID(metabolites.note, 1, {$text_len}), '...'),
            metabolites.note) AS note
    FROM
        cad_registry.topic
            LEFT JOIN
        registry_resolver ON registry_resolver.id = model_id
            LEFT JOIN
        struct_data ON struct_data.metabolite_id = symbol_id
            LEFT JOIN
        metabolites ON metabolites.id = struct_data.metabolite_id
    WHERE
        topic_id = 749 AND topic.type = 0
            AND metabolite_id IN (SELECT 
                db_xref
            FROM
                mzvault.annotation_hits)
    LIMIT {$offset} , {$page_size}"
    ;
        $npdata = (new Table(["cad_registry"=>"topic"]))->getDriver()->Fetch($sql);
        $npdata = [
            "np" => $npdata
        ];

        return list_nav( $npdata, $page);
    }
}
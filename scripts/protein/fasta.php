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
        $seq["title"] = $seq["name"] . " - " . $seq["taxname"];

        return $seq;
    }
}
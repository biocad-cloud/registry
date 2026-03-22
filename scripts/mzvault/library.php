<?php

class library_data {

    public static function reference_spectrum_list($id) {
        return (new Table(["mzvault"=>"annotation"]))
            ->left_join("reference_spectrum")
            ->on(["reference_spectrum"=>"annotation_id","annotation"=>"id"])
            ->where(["db_xref"=>$id])
            ->select(["adducts", "ROUND(mz, 4) AS mz", "splash_id", "npeaks"])
            ;
    }

    public static function spectrum_data($id, $splash) {
        $spectrum = (new Table(["mzvault"=>"annotation"]))
            ->left_join("reference_spectrum")
            ->on(["reference_spectrum"=>"annotation_id","annotation"=>"id"])
            ->where(["db_xref"=>$id, "splash_id"=>$splash])
            ->find(["`reference_spectrum`.id",
                "splash_id",
                "name",
                "adducts",
                "ROUND(mz, 4) AS precursor"])
            ;
        
        accessController::log_pageview("reference_spectrum", $splash);

        if (Utils::isDbNull($spectrum)) {
            RFC7231Error::err404("could not found the spectrum data!");
        }

        $data = (new Table(["mzvault"=>"reference_peaks"]))
            ->where(["precursor"=>$spectrum["id"]])
            ->select(["mz","intensity","smiles"])
            ;

        $spectrum["mz"] = array_column($data,"mz");
        $spectrum["intensity"] = array_column($data,"intensity");
        $spectrum["smiles"] = array_column($data,"smiles");

        return $spectrum;
    }
}
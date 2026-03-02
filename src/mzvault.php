<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    public function __construct() {
        include_once APP_PATH . "/scripts/resolver.php";
    }

    public function annotation_hits() {

    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum_list() {
        $id = resolver::db_xref();
        $list = (new Table(["mzvault"=>"annotation"]))
            ->left_join("reference_spectrum")
            ->on(["reference_spectrum"=>"annotation_id","annotation"=>"id"])
            ->where(["db_xref"=>$id])
            ->select(["adducts", "ROUND(mz, 4) AS mz", "splash_id", "npeaks"])
            ;
        
        controller::success($list);
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum($splash) {
        $id = resolver::db_xref();
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

        controller::success($spectrum);
    }
}
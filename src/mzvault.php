<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    public function __construct() {
        include_once APP_PATH . "/scripts/resolver.php";
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function annotation_hits() {
        include_once APP_PATH . "/scripts/mzvault/stats.php";
        controller::success(stats::organism_piedata(resolver::db_xref()));
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum_list() {
        include_once APP_PATH . "/scripts/mzvault/library.php";       
        controller::success(library_data::reference_spectrum_list(resolver::db_xref()));
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum($splash) {
        include_once APP_PATH . "/scripts/mzvault/library.php";
        controller::success(library_data::spectrum_data(resolver::db_xref(), $splash));
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function experiment_source() {
        $exp = (new Table(["mzvault"=>"annotation"]))
            ->left_join("spectrum")
            ->on(["spectrum"=>"annotation_id","annotation"=>"id"])
            ->left_join("sampleinfo")
            ->on(["spectrum"=>"sample_id","sampleinfo"=>"id"])
            ->where([
                "db_xref"=> resolver::db_xref(),
                "CHAR_LENGTH(splash_id)"=>gt("0")
            ])->group_by(["adducts", "taxname" , "taxid" , "tissue"])
            ->order_by("size", true)
            ->select(["taxname", "taxid", "tissue", "adducts", "COUNT(*) AS size"])
            ;

        controller::success($exp);
    }

    /**
     * Plant Natural Product
     * 
     * @access *
     * @uses view
     * @method get
    */
    public function plant_np($page=1) {
        include_once APP_PATH . "/scripts/mzvault/np.php";
        View::Show(APP_VIEWS . "/mzvault/np_lib.html", np::plant_np($page));
    }

    /**
     * Microbial Natural Product
     * 
     * @access *
     * @uses view
     * @method get
    */
    public function microbial_np($page=1) {
        include_once APP_PATH . "/scripts/mzvault/np.php";
        View::Show(APP_VIEWS . "/mzvault/np_lib.html", np::microbial_np($page));
    }

    /**
     * 
    */
    public function peak($mz,$page=1) {
        include_once APP_PATH . "/scripts/mzvault/peak.php";
        View::Display(reference_peak::get_peakdata($mz, $page));
    }
}
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
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer,true );

        if (Utils::isDbNull($referer)) {
            RFC7231Error::err405("Unknown data entry point to query!");
        } else {
            $referer = $referer["query"]["metab"];
            $referer = Regex::Match($referer, "\d+");
        }

        $exp = (new Table(["mzvault"=>"annotation"]))
            ->left_join("spectrum")
            ->on(["spectrum"=>"annotation_id","annotation"=>"id"])
            ->left_join("sampleinfo")
            ->on(["spectrum"=>"sample_id","sampleinfo"=>"id"])
            ->where([
                "db_xref"=>$referer,
                "CHAR_LENGTH(splash_id)"=>gt("0")
            ])->group_by(["adducts", "taxname" , "taxid" , "tissue"])
            ->order_by("size", true)
            ->select(["taxname", "taxid", "tissue", "adducts", "COUNT(*) AS size"])
            ;

        controller::success($exp);
    }
}
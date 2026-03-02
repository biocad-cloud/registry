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
}
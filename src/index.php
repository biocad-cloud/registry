<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    /**
     * 数据之源，洞见之始
     * 
     * @access *
     * @uses view
    */
    public function index() {
        View::Display();
    }

    /**
     * Symbol resolver
    */
    public function s($ref) {
        include APP_PATH . "/scripts/resolver.php";
        resolver::resolve($ref);
    }

    /**
     * @access *
     * @uses view
    */
    public function metabolites($page = 1, $topic = null, $list =null, $loc=null,$ontology=null) {
        include APP_PATH . "/scripts/metabolite/list.php";
        View::Display(metabolite_list::getList($page, $topic,$list,$loc,$ontology));
    }

    /**
     * Subcellular locations
     * 
     * @access *
     * @uses view
    */
    public function compartments() {
        include APP_PATH . "/scripts/compartments/list.php";
        View::Display(location_list::get_list());
    }

    public function compartment($name) {
        include APP_PATH . "/scripts/compartments/page.php";
        View::Display(compartment_location::location_data($name));
    }

    /**
     * @access *
     * @uses view
    */
    public function motif($id) {
        include APP_PATH . "/scripts/motifs/page.php";
        View::Display(motif_data::getdata($id));
    }

    /**
     * @access *
     * @uses view
    */
    public function metabolite($id) {
        include APP_PATH . "/scripts/metabolite/page.php";
        View::Display(metabolite_page::page_data($id));
    }

    /**
     * Resource Downloads
     * 
     * @access *
     * @uses view
    */
    public function downloads() {
        View::Display();
    }
}
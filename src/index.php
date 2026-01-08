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
    public function motif($id = null, $family = null) {
        include APP_PATH . "/scripts/motifs/page.php";

        if (Utils::isDbNull($id) && Utils::isDbNull($family)) {
            RFC7231Error::err400("Both motif id and motif family parameter should not be empty!");
        }

        if (!Utils::isDbNull($id)) {
            View::Show(APP_VIEWS . "/motif_id.html", motif_data::getdata($id));
        } else {
            View::Show(APP_VIEWS . "/motif_family.html", motif_data::getfamily(urldecode($family)));
        }
        
    }

    /**
     * @access *
     * @uses view
    */
    public function motifs($page=1,$page_size=10) {
        include APP_PATH . "/scripts/motifs/list.php";
        View::Display(motif_list::get_list($page, $page_size));
    }

    /**
     * @access *
     * @uses view
    */
    public function metabolite($id) {
        include APP_PATH . "/scripts/metabolite/page.php";

        $id = metabolite_page::resolve_id($id);
        $data = metabolite_page::page_data($id);

        View::Display($data);
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
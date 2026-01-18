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
    public function metabolites($page = 1, $topic = null, $list =null, $loc=null,$ontology=null,$exact_mass=null,$formula=null) {
        include APP_PATH . "/scripts/metabolite/list.php";
        View::Display(metabolite_list::getList($page, $topic,$list,$loc,$ontology,$exact_mass,$formula));
    }

    /**
     * @access *
     * @uses view
    */
    public function proteins($ec=null,$cc=null, $page =1) {
        include APP_PATH . "/scripts/protein/list.php";

        if (!Utils::isDbNull($cc)) {
            View::Show(APP_VIEWS . "/proteins_subcellular.html", model_list::list_cc($cc,$page));
        } else {
            View::Display(model_list::list_page($ec,$page));
        }        
    }

    /**
     * @access *
     * @uses view
    */
    public function protein($id) {
        include APP_PATH . "/scripts/protein/page.php";
        View::Display(model_data::protein_model($id));
    }

    /**
     * @access *
     * @uses view
    */
    public function protein_fasta($id) {
        include APP_PATH . "/scripts/protein/fasta.php";
        View::Display(prot_fasta::seqinfo($id));
    }

    /**
     * Subcellular locations
     * 
     * @access *
     * @uses view
    */
    public function compartments($page=1) {
        include APP_PATH . "/scripts/compartments/list.php";
        View::Display(location_list::get_list($page));
    }

    /**
     * @access *
     * @uses view
    */
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
        $main_id = metabolite_page::resolve_main($id);

        if ($main_id > 0) {
            Redirect("/metabolite/BioCAD" . str_pad($main_id,11,"0", STR_PAD_LEFT));
        } else {
            View::Display(metabolite_page::page_data($id));
        }       
    }

    /**
     * @access *
     * @uses view
    */
    public function reaction($id) {
        include APP_PATH . "/scripts/reaction/page.php";
        View::Display(reaction_model::get_data($id));        
    }

    /**
     * @access *
     * @uses view
    */
    public function reactions($metabolite=null, $page=1) {
        include APP_PATH . "/scripts/reaction/list.php";
        View::Display(reaction_list::get_list($metabolite,$page));
    }

    /**
     * @access *
     * @uses view
    */
    public function taxonomy($id,$page=1) {
        include APP_PATH . "/scripts/taxonomy/page.php";
        View::Display(ncbi_taxonomy::taxon_data($id,$page));
    }

    /**
     * @access *
     * @uses view
    */
    public function database() {
        View::Display();
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

    /**
     * Privacy Policy
     * 
     * @access *
     * @uses view
    */
    public function privacy() {
        View::Display();
    }

    /**
     * @access *
     * @uses view
    */
    public function search($q) {
        View::Display((include APP_PATH . "/scripts/search.php")->get_result(urldecode($q)));
    }
}
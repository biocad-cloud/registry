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
    public function metabolites($page = 1, $page_size = 100) {
        include APP_PATH . "/scripts/metabolite/list.php";
        View::Display(metabolite_list::getList($page, $page_size));
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
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
     * @access *
     * @uses view
    */
    public function metabolites($page = 1, $page_size = 200) {
        View::Display();
    }

    /**
     * @access *
     * @uses view
    */
    public function metabolite($id) {
        View::Display();
    }
}
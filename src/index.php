<?php

include __DIR__ . "/../framework/bootstrap.php";

class App {

    /**
     * GCModeller Git Repository
    */
    public function index() {
        View::Display();
    }

    /**
     * @uses view
     * @method POST
    */
    public function err500_test() {
        RFC7231Error::err500();
    }
}
<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    public function metabolite($id) {
        include APP_PATH . "/scripts/metabolite/page.php";
        controller::success(metabolite_page::page_data($id));
    }
}
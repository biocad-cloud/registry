<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    /**
     * @uses api
    */
    public function metabolite($id) {
        include APP_PATH . "/scripts/metabolite/page.php";

        $id = metabolite_page::resolve_id($id);
        $main_id = metabolite_page::resolve_main($id);

        if ($main_id > 0) {
            $id = $main_id;
        }

        controller::success(metabolite_page::page_data($id));
    }
}
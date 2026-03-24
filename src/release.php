<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    /**
     * @uses file
    */
    public function download_sophia($ver) {
        $ver = trim($ver, ".");
        $res_file = APP_PATH . "/release/sophia_{$ver}.msi";

        if (!file_exists($res_file)) {
            RFC7231Error::err404("Sorry, could not found the release file on server!");
        } else {
            Utils::PushDownload($res_file);
        }
    }
}
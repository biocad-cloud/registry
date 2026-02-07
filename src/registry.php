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

    /**
     * @access *
     * @uses api
    */
    public function metabolites($page       = 1, 
                                $topic      = null, 
                                $list       = null, 
                                $location   = null,
                                $ontology   = null,
                                $exact_mass = null,
                                $formula    = null) {

        include APP_PATH . "/scripts/metabolite/list.php";

        $q = metabolite_list::makeQuery($topic, $list, 
            $location, $ontology, $exact_mass,
            $formula
        );
        $data = metabolite_list::getList($q[0], $q[1], $page);

        controller::success($data);
    }

    /**
     * @uses file
     * @access *
    */
    public function motif($logo) {
        $id = Regex::Match($logo, "\d+");
        $logo = (new Table(["cad_registry"=>"motif"]))->where(["id"=>$id])->findfield("logo");

        echo $logo;
    }
}
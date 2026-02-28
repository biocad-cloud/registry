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

    /**
     * rest api for load enzyme table
     * 
     * @param integer[] $q the ec number query
     * 
     * @uses api
     * @access *
     * @method get
    */
    public function enzyme_data($q) {
        include_once APP_PATH . "/scripts/enzyme/list.php";

        if (!is_array($q)) {
            $q = Strings::Split($q, "."); 
        }

        # enzyme_list::expand_mainclass
        # /registry/enzyme_data/?q=1     
        # enzyme_list::expand_subclass
        # /registry/enzyme_data/?q=1.1
        # enzyme_list::expand_subcategory
        # /registry/enzyme_data/?q=1.1.1

        $class_id    = Utils::ReadValue($q, 0); # first digit in ec number    
        $subclass_id = Utils::ReadValue($q, 1); # second digit in ec number  
        $category_id = Utils::ReadValue($q, 2); # third digit in ec number  

        if (Utils::isDbNull($subclass_id )) {
            controller::success(enzyme_list::expand_mainclass($class_id));
        } else if (Utils::isDbNull($category_id)) {
            controller::success(enzyme_list::expand_subclass($class_id, $subclass_id));
        } else {
            controller::success(enzyme_list::expand_subcategory($class_id, $subclass_id, $category_id));
        }
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum_list() {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer,true );

        if (Utils::isDbNull($referer)) {
            RFC7231Error::err405("Unknown data entry point to query!");
        } else {
            $referer = $referer["path"];
            $referer = Strings::Split($referer, "/");
            $referer = $referer[2];
        }

        $id = Regex::Match($referer, "\d+");
        $list = (new Table(["mzvault"=>"annotation"]))
            ->left_join("reference_spectrum")
            ->on(["reference_spectrum"=>"annotation_id","annotation"=>"id"])
            ->where(["db_xref"=>$id])
            ->select(["adducts", "ROUND(mz, 4) AS mz", "splash_id", "npeaks"])
            ;
        
        controller::success($list);
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function spectrum() {

    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function experiment_source() {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer = Utils::isDbNull($referer) ? null : URL::mb_parse_url ( $referer,true );

        if (Utils::isDbNull($referer)) {
            RFC7231Error::err405("Unknown data entry point to query!");
        } else {
            $referer = $referer["query"]["metab"];
            $referer = Regex::Match($referer, "\d+");
        }

        $exp = (new Table(["mzvault"=>"annotation"]))
            ->left_join("spectrum")
            ->on(["spectrum"=>"annotation_id","annotation"=>"id"])
            ->left_join("sampleinfo")
            ->on(["spectrum"=>"sample_id","sampleinfo"=>"id"])
            ->where([
                "db_xref"=>$referer,
                "CHAR_LENGTH(splash_id)"=>gt("0")
            ])->group_by(["adducts", "taxname" , "taxid" , "tissue"])
            ->order_by("size", true)
            ->select(["taxname", "taxid", "tissue", "adducts", "COUNT(*) AS size"])
            ;

        controller::success($exp);
    }

    /**
     * @access *
     * @uses api
     * @method get
    */
    public function organism_source($taxid) {
        include APP_PATH . "/scripts/taxonomy/metabolites.php";
        $data = metabolite::organism_source($taxid);
        controller::success($data);
    }
}
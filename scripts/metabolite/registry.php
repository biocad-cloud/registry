<?php

class registry {

    public static function resolve_main($id) {
        $db = new Table(["cad_registry"=>"metabolites"]);
        $id = Regex::Match($id, "\d+");
        $meta = $db->where(["id"=>$id])->findfield("main_id");
        return $meta;
    }

    /**
     * cast the possible external db_xref to biocad registry id 
    */
    public static function resolve_id($id, $redirect_url = true) {
        if (StringHelpers::IsPattern($id, "(BioCAD)?\d+")) {
            // is int biocad registry id of metabolite
            return $id;
        } 

        $db = new Table(["cad_registry"=>"metabolites"]);
        $q = $db->where([
            "cas_id|hmdb_id|lipidmaps_id|kegg_id|drugbank_id|biocyc|mesh_id|wikipedia" => $id
        ])
        ->order_by("id")
        ->findfield("id")
        ;

        if (Utils::isDbNull($q)) {
            $db = new Table(["cad_registry"=>"registry_resolver"]);
            $q = $db
                ->where(["type" => ENTITY_METABOLITE,
                    "register_name"=>$id
                ])
                ->findfield("symbol_id")
                ;
        }

        if ($redirect_url) {
            if (Utils::isDbNull($q)) {
                RFC7231Error::err404("Sorry, no id mapping for '{$id}' in biocad metabolite registry.");
            } else {
                $q = "BioCAD" . str_pad($q, 11, '0', STR_PAD_LEFT);
                $url = "/metabolite/{$q}";

                Redirect($url);
            }
        } else {
            return $q;
        }
    }
}
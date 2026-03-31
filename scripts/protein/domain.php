<?php

class domain_info {

    public static function domain_table($id, $page=1) {
        $domain = (new Table(["cad_registry"=>"ontology"]))->where(["id"=>$id]);
        $page_size = 500;
        $domain["site"] = (new Table(["cad_registry"=>"conserved_domain"]))
            ->left_join("protein_data")
            ->on(["protein_data"=>"id","conserved_domain"=>"protein_id"])
            ->where(["domain_id"=>$id])
            ->order_by("name")
            ->limit(($page-1)*$page_size,$page_size)
            ->select(["conserved_domain.protein_id",
            "name",
            "`function`",
            "`left`",
            "`right`",
            "SUBSTRING(protein_data.sequence,
                `left`,
                `right` - `left` + 1) AS site"])
            ;

        return list_nav($domain, $page);
    }
}
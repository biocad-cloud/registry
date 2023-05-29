<?php

include __DIR__ . "/../framework/bootstrap.php";

class App {

    /**
     * add a new ncbi taxonomy node into tree
     * 
     * @uses api
     * @method POST
    */
    public function add($taxid, $name, $rank, $parent, $nchilds) {
        $ncbi_tax = new Table("ncbi_taxonomy_tree");
        $terms = new Table("vocabulary");
        $taxid = strip_postVal($taxid);
        $name = urldecode(strip_postVal($name));
        $rank = urldecode(strip_postVal($rank));
        $parent = strip_postVal($parent);
        $nchilds = strip_postVal($nchilds);

        if (Utils::isDbNull($rank)) {
            $rank = "no rank";
        }

        $rank_term = $terms->where(["hashcode" => registry_key($rank)])->find();

        if (Utils::isDbNull($rank_term)) {
            $rank_term = $terms->add([
                "term" => $rank_term,
                "hashcode" => registry_key($rank),
                "category" => "Category Term",
                "description" => "Imported from ncbi taxonomy dump file"
            ]);
        } else {
            $rank_term = $rank_term["id"];
        }

        $ncbi_tax->add([
            "id" => $taxid,
            "name" => $name,
            "rank" => $rank_term,
            "parent" => $parent,
            "n_childs" => $nchilds
        ]);

        controller::success(1);
    }
}
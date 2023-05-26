<?php

include __DIR__ . "/../framework/bootstrap.php";

/**
 * put data to registry
*/
class app {

    /**
     * put new taxonomic group data into registry
     * 
     * @method POST
     * @access *
     * @uses api
    */
    public function taxonomic($name, $id, $note = "") {
        $tax = new Table("taxonomic");
        $name = trim($name, '"\s');
        $note = trim($note, '"\s');
        $id = trim($id, '"\s');
        # check data is exists or not
        $check = $tax->where(["name" => urldecode($name)])->find();

        if (Utils::isDbNull($check)) {
            // add new
            $id = $tax->add([
                "name" => urldecode($name),
                "note" => urldecode($note),
                "id" => $id
            ]);

            controller::success(["id" => $id], $tax->getLastMySql());
        } else {
            // data is already exists
            controller::success(["id" => $check["id"], "exist_data" => $check]);
        }
    }

    /**
     * put a set of the genome into one taxonomic group
     * 
     * @uses api
     * @param integer $grp the taxonomic group id
     * @method POST
    */
    public function genomes($grp) {
        $li = $_POST["li"];
        $tax = new Table("taxonomic");

        if (is_string($li)) {
            $li = json_decode($li, true);
        }

        // check of the taxonomic is exists in database or not?
        $check = $tax->where(["id" => $grp])->find();
        $genome = new Table("genomes");

        if (Utils::isDbNull($check)) {
            controller::error("no target taxonomic group data!");
        } else {
            foreach($li as $name => $t) {
                $genome->add([
                    "id" => $t["id"], 
                    "taxonomic_group" => $grp, 
                    "name" => $t["name"], 
                    "ncbi_taxid" => 0
                ]);
            }

            $tax->where(["id" => $grp])->save([
                "n_tax" => count($li)
            ]);

            controller::success(1);
        }
    }
}
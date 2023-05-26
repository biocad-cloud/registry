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
}
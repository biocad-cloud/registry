<?php

include __DIR__ . "/../framework/bootstrap.php";

class App {

    /**
     * add a new gene ontology term into registry
    */
    public function add($id, $name, $namespace, $is_a, $xref, $def, $synonym) {
        $go = new Table("gene_ontology");
        $go->add([
            "id" => $id,
            "name" => $name,
            "namespace" => $namespace,
            "def" => $def
        ]);

        
    }
}
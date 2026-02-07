<?php

include_once __DIR__ . "/page_view.php";

class page_search extends page_view {

    public function __construct($page, $page_size = 20) {
        parent::__construct($page, $page_size);
    }

    public function q($term) {
        $term = urldecode($term);
        $term = str_replace("'"," ", $term);
        $term = str_replace('"'," ", $term);
        $term = str_replace("-"," ", $term);
        $term = str_replace("+"," ", $term);
        $term = str_replace("<"," ", $term);
        $term = str_replace(">"," ", $term);
        $term = str_replace("?"," ", $term);

        $data = (new Table(["cad_registry"=>"metabolites"]))->exec("SELECT 
        id
    FROM
        cad_registry.metabolites
    WHERE
        MATCH (name , note) AGAINST ('{$term}' IN BOOLEAN MODE)
    ORDER BY MATCH (name , note) AGAINST ('{$term}' IN BOOLEAN MODE) DESC
    LIMIT {$this->offset},{$this->page_size}
    ;", true);

        if (Utils::isDbNull($data)) {
            return [];
        } else {
            return Table::project_column($data, "id");
        }
    }
}

return (function($page, $page_size) {
    return new page_search($page, $page_size);
});
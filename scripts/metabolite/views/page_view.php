<?php

abstract class page_view {

    /**
     * @var int
    */
    protected $page;
    /**
     * @var int
    */
    protected $page_size;
    /**
     * @var int
    */
    protected $offset;

    public function __construct($page, $page_size = 20) {
        $this->page = $page;
        $this->page_size = $page_size;
        $this->offset = ($page -1) * $page_size;
    }

    /**
     * @param string $term the query term
     * 
     * @return integer[]
    */
    public abstract function q($term);

    public function metabolites($model_id) {
        $model_id = Strings::Join($model_id,",");
        $list = new Table(["cad_registry"=>"metabolites"]);
        $sql = "SELECT 
        CONCAT('BioCAD', LPAD(metabolites.id, 11, '0')) AS id,
        metabolites.id as uid,
        name,
        IF(formula = '', 'n/a', formula) AS formula,
        ROUND(exact_mass, 4) AS exact_mass,
        smiles,
        metabolites.note
    FROM
        cad_registry.metabolites
            LEFT JOIN
        struct_data ON struct_data.metabolite_id = metabolites.id
    WHERE metabolites.id IN ({$model_id})
    ORDER BY metabolites.id"
            ;      

        return $list->exec($sql);
    }
}
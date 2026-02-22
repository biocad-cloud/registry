<?php

class enzyme_list {

    /**
     * @param integer $class_id the first digit inside the ec number
     * @return integer[] an array of sub_class id under the main enzyme class
    */
    public static function expand_mainclass($class_id) {
        return (new Table(["cad_registry"=>"enzyme"]))
            ->where(["enzyme_class" =>$class_id])
            ->distinct()
            ->project("sub_class")
            ;
    }

    /**
     * @param integer $class_id the first digit inside the ec number
     * @param integer $subclass_id the second digit inside the ec number
     * @return integer[] an array of sub_category id under the main enzyme class.sub_class folder
    */
    public static function expand_subclass($class_id, $subclass_id) {
        return (new Table(["cad_registry"=>"enzyme"]))
            ->where(["enzyme_class" =>$class_id, "sub_class"=>$subclass_id])
            ->distinct()
            ->project("sub_category")
            ;
    }

    /**
     * @return array returns a table of enzyme query result via enzyme ec number prefix a.b.c
    */
    public static function expand_subcategory($class_id, $subclass_id, $category_id) {
        return (new Table(["cad_registry"=>"enzyme"]))
            ->where([
                "enzyme_class" =>$class_id, 
                "sub_class"=>$subclass_id,
                "sub_category" => $category_id
            ])->select([
                "id",
                "CONCAT('{$class_id}.{$subclass_id}.{$category_id}', '.', enzyme_number) AS ec_number",
                "recommended_name",
                "systematic_name",
                "note"
            ]);
    }

    public static function search($q, $page =1, $page_size = 20) {
        $offset = ($page-1) * $page_size;
        $q = Table::make_fulltext_strips($q);
        $q = "MATCH (recommended_name , systematic_name , note) AGAINST ('{$q}' IN BOOLEAN MODE)";
        $data = (new Table(["cad_registry"=>"enzyme"]))
            ->where($q)
            ->limit($offset, $page_size)
            ->select(["CONCAT(enzyme_class, '.', sub_class, '.', sub_category, '.', enzyme_number) AS `ec_number`","`enzyme`.*"])
            ;
        
        return list_nav(["enzyme"=>$data] , $page);
    }
}
<?php

class enzyme_data {

    public static function data($ec = null, $id = null) {
        $enzyme = null;

        if (!Utils::isDbNull($id)) {
            $enzyme = (new Table(["cad_registry"=>"enzyme"]))->where(["id"=>$id])->find();
            $ec = Strings::Join([
                $enzyme["enzyme_class"],
                $enzyme["sub_class"],
                $enzyme["sub_category"],
                $enzyme["enzyme_number"]
            ],".");
        } else {
            $enzyme = Strings::Split($ec,".");
            $class_id    = Utils::ReadValue($enzyme, 0,0); # first digit in ec number    
            $subclass_id = Utils::ReadValue($enzyme, 1,0); # second digit in ec number  
            $category_id = Utils::ReadValue($enzyme, 2,0);
            $serial_num  = Utils::ReadValue($enzyme, 3,0);

            $enzyme = (new Table(["cad_registry"=>"enzyme"]))
                ->where(["enzyme_class"=>$class_id,
                    "sub_class"=>$subclass_id,
                    "sub_category"=>$category_id,
                    "enzyme_number"=>$serial_num])
                ->find()
                ;
        }

        if (Utils::isDbNull($enzyme)) {
            RFC7231Error::err404("unknown enzyme data!");
        }

        $note_str = "{$enzyme["recommended_name"]} ({$enzyme["systematic_name"]})<br />{$enzyme["note"]}";
        $laws = (new Table(["cad_registry"=>"kinetics_law"]))
            ->left_join("reaction")
            ->on(["reaction"=>"id","kinetics_law"=>"metabolic_node"])
            ->where(["`kinetics_law`.ec_number" => $ec])
            ->select(["`kinetics_law`.*", "`reaction`.name"])
            ;

        return [
            "ec" => $ec,
            "law" => $laws,
            "note" => $note_str
        ];
    }
}
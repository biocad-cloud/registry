<?php

class stats {

    public static function organism_piedata($cid) {
        $piedata = (new Table(["mzvault"=>"annotation_hits"]))
            ->where(["db_xref"=>$cid])
            ->find();

        if (Utils::isDbNull($piedata)) {
            RFC7231Error::err404("there is no spectrum annotation hit of this metabolite!");
        }

        return [
            "organism" => json_decode($piedata["organism_hits"]),
            "tissue" => json_decode($piedata["tissue_hits"])
        ];
    }
}
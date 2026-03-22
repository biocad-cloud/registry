<?php

class reference_peak {

    public static function get_peakdata($mz, $da, $page = 1) {
        $page_size = 150;
        $peaks = (new Table(["mzvault"=>"reference_peaks"]))
            ->left_join("reference_spectrum")
            ->on(["reference_spectrum"=>"id","reference_peaks"=>"precursor"])
            ->where(["mz" => between($mz-$da, $mz + $da)])
            ->limit(($page -1) * $page_size, $page_size)
            ->select(["`reference_peaks`.mz",
            "intensity",
            "smiles",
            "splash_id",
            "annotation_id"])
            ;
        $ion_data = (new Table(["mzvault"=>"annotation"]))->where(["id" => in(array_column($peaks,"annotation_id"))])->select();
        $ion_index = [];

        foreach($ion_data as $i) {
            $ion_index["ion_{$i["id"]}"] = $i;
        }

        accessController::log_pageview("reference_peak", "m/z:" . $mz);

        $peakdata = array_map(function($pk) use($ion_index) {
            $key = "ion_{$pk["annotation_id"]}";
            $ion = $ion_index[$key];
            $pk[ "db_xref"] = $ion["db_xref"];
            $pk[ "name"]= $ion["name"];
            $pk[ "adducts"]= $ion["adducts"];
            $pk[ "precursor"]= $ion["mz"];

            return $pk;
        }, $peaks);

        $pagedata = [
            "peak" => $peakdata,
            "mz" => $mz,
            "da" => $da
        ];

        return list_nav($pagedata, $page);
    }
}
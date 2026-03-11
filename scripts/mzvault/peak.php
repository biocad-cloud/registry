<?php

class reference_peak {

    public static function get_peakdata($mz, $page = 1) {
        $da = 0.05;
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
        $pagedata = [
            "peak" => $peaks,
            "mz" => $mz,
            "da" => $da
        ];

        return list_nav($pagedata, $page);
    }
}
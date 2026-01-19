<?php

class motif_data {

    public static function getdata($id) {
        $motif = (new Table(["cad_registry"=>"motif"]))->where(["id"=>$id])->find();
        $motif["title"] = $motif["name"];
        $motif["site"] = (new Table(["cad_registry"=>"nucleotide_data"]))->where(["is_motif"=>1, "model_id"=>$id])->select(["name","sequence","organism_source AS ncbi_taxid"]);
        $tax_id = array_column($motif["site"], "ncbi_taxid");
        $motif["tax"] = (new Table(["cad_registry"=>"ncbi_taxonomy"]))->where(["id"=>in($tax_id)])->select();
        $motif["site"] = json_encode($motif["site"]);
        $dataUri = $motif["logo"];

        // 1. 去除头部信息 (data:image/svg+xml;base64,)
        // 使用逗号作为分隔符，取第二部分 (索引为1)
        $parts = explode(',',$dataUri);

        // 检查是否存在逗号分割，防止格式错误
        if (isset($parts[1])) {
            $base64Data =$parts[1];
            
            // 2. 解码 Base64 字符串
            $svgString = base64_decode($base64Data);
            
            // 此时 $svgString 就是类似 "<svg>...</svg>" 的字符串了
            
        } else {
            // 如果没有逗号，说明可能不是标准的 data uri 格式，或者是纯 base64
            $svgString = base64_decode($dataUri);
        }

        $motif["logo"] = $svgString;

        return $motif;
    }

    public static function getfamily($family) {
        $data = (new Table(["cad_registry"=>"motif"]))->where(["family" => $family])->select(["id","name"]);

        return [
            "motif"=>$data,
            "family"=>$family,
            "title"=>$family,
            "note"=> count($data) . " clusters",
            "name" => $family            
        ];
    }
}
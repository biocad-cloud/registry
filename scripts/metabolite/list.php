<?php

class metabolite_list {

    public static function getList($page, $topic = null, $page_size = 20) {
        $data = ["title" => "Metabolites Page {$page}"];
        $offset = ($page -1) * $page_size;
        $page = null;

        if (!Utils::isDbNull($topic)) {
            $page = self::page_topic($topic,$offset,$page_size);
        } else {
            $page = self::page_list($offset, $page_size);
        }

        $data["page"] = array_map(function($meta) {
            return metabolite_list::link_topics($meta);
        }, $page);

        if ($page == 1) {
            $data["a_1"] = self::buildPageUrl(1);
            $data["a_2"] = self::buildPageUrl(2);
            $data["a_3"] = self::buildPageUrl(3);
        } else {
            $prevPage = max(1, $page - 1); // 上一页，最少为1
            $nextPage = $page + 1;         // 下一页

            $data["a_1"] = self::buildPageUrl($prevPage);
            $data["a_2"] = self::buildPageUrl($page);
            $data["a_3"] = self::buildPageUrl($nextPage);
        }

        return $data;
    }

    /**
     * 生成带有分页参数的 URL
     * @param int $pageNo 目标页码
     * @return string 完整的 URL (例如: /metabolites/?topic=bladder&page=2)
     */
    public static function buildPageUrl($pageNo) {
        // 1. 获取当前 URL 的路径部分 (不包含 ? 后面的参数)
        // 例如: /metabolites/
        $baseUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // 2. 获取当前所有的 GET 查询参数
        // $_GET 是一个关联数组，包含当前 URL 中所有的参数
        $currentParams =$_GET;
        // 3. 设置/覆盖 page 参数
        // 无论 page 是否存在，都将其设置为目标页码
        $currentParams['page'] =$pageNo;
        // 4. 构建查询字符串
        // http_build_query 会自动处理 URL 编码和参数拼接
        $queryString = http_build_query($currentParams);

        // 5. 拼接完整 URL
        // 注意：这里始终会带有 page 参数。如果你希望在第1页时省略 page=1，需要加个 if 判断
        if ($pageNo == 1) {
            // 如果是第1页，可以选择移除 page 参数让URL更干净（可选）
            unset($currentParams['page']);
            // 重新构建不带 page 的参数
            $queryString = http_build_query($currentParams);
        }

        // 如果参数为空（没有任何查询参数），直接返回基础路径
        if (empty($queryString)) {
            return $baseUrl;
        }

        return $baseUrl . '?' .$queryString;
    }

    private static function page_topic($topic, $offset, $page_size) {
        $topic = urldecode($topic);
        $model_id = (new Table(["cad_registry"=>"topic"]))
            ->left_join("vocabulary")
            ->on(["vocabulary"=>"id","topic"=>"topic_id"])
            ->where(["category"=>"Topic", "term"=>$topic])
            ->limit($offset,$page_size)
            ->distinct()
            ->project("model_id")
            ;
        $model_id = (new Table(["cad_registry"=>"registry_resolver"]))
            ->where(["id"=> in($model_id)])
            ->distinct()
            ->project("symbol_id")
            ;
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

    private static function page_list($offset, $page_size) {
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
ORDER BY metabolites.id
LIMIT {$offset}, {$page_size}"
        ;       
        return $list->exec($sql);
    }

    private static function link_topics($data) {
        $model = (new Table(["cad_registry"=>"registry_resolver"]))->where(["type" => ENTITY_METABOLITE, "symbol_id"=>$data["uid"]])->find();

        if (!Utils::isDbNull($model)) {
            unset($model["symbol_id"]);
            unset($model["type"]);
            unset($model["add_time"]);
            unset($model["note"]);

            $data["registry_model"] = $model;
            $data["topic"] = (new Table(["cad_registry"=>"topic"]))
                ->left_join("vocabulary")
                ->on(["vocabulary"=>"id","topic"=>"topic_id"])
                ->where(["model_id"=>$model["id"]])
                ->distinct()
                ->select(["`vocabulary`.term","`vocabulary`.color"])
                ;   

            $data["topic"] = array_map(function($topic) {
                return "<span class='badge' style='background-color:{$topic["color"]};'><a class='card-link' style='color: white;' href='/metabolites/?topic={$topic["term"]}'>{$topic["term"]}</a></span>";
            }, $data["topic"]);
            $data["topic"] = Strings::Join($data["topic"]," ");
        }

        return $data;
    }
}
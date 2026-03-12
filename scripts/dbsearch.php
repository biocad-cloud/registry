<?php

class portal {

    // ===== 搜索配置：统一管理各资源的查询逻辑 =====
    private const SEARCH_CONFIGS = [
        'enzyme'    => ['table' => 'cad_registry.enzyme', 'match' => ['recommended_name', 'systematic_name', 'note'], 'name_col' => 'recommended_name AS name'],
        'location'  => ['table' => 'cad_registry.compartment_location', 'match' => ['fullname', 'note'], 'name_col' => 'fullname AS name'],
        'metabolite'=> ['table' => 'cad_registry.metabolites', 'match' => ['name', 'note'], 'name_col' => 'name'],
        'pathway'   => ['table' => 'cad_registry.pathway', 'match' => ['name', 'note'], 'name_col' => 'name'],
        'taxonomy'  => ['table' => 'cad_registry.ncbi_taxonomy', 'match' => ['name', 'zh_name', 'note'], 'name_col' => 'name'],
        'motif'     => ['table' => 'cad_registry.motif', 'match' => ['name', 'note'], 'name_col' => 'name'],
        'protein'   => ['table' => 'cad_registry.protein_data', 'match' => ['function'], 'name_col' => '`function` AS name'],
        'reaction'  => ['table' => 'cad_registry.reaction', 'match' => ['name', 'note'], 'name_col' => 'name'],
    ];

    // ===== URL 与样式映射：消除 switch，支持动态字段与编码 =====
    private const TYPE_CONFIG = [
        'enzyme'    => ['url' => '/enzyme/?id={val}', 'color' => 'primary', 'field' => 'id', 'encode' => false],
        'location'  => ['url' => '/compartment/?name={val}', 'color' => 'secondary', 'field' => 'name', 'encode' => true],
        'metabolite'=> ['url' => '/metabolite/?id={val}', 'color' => 'success', 'field' => 'id', 'encode' => false],
        'pathway'   => ['url' => '/pathway/?id={val}', 'color' => 'danger', 'field' => 'id', 'encode' => false],
        'taxonomy'  => ['url' => '/taxonomy/?id={val}', 'color' => 'warning', 'field' => 'id', 'encode' => false],
        'motif'     => ['url' => '/motif/?id={val}', 'color' => 'info', 'field' => 'id', 'encode' => false],
        'protein'   => ['url' => '/protein/fasta/?id={val}', 'color' => 'dark', 'field' => 'id', 'encode' => false],
        'reaction'  => ['url' => '/reaction/?id={val}', 'color' => 'danger', 'field' => 'id', 'encode' => false],
    ];

    public static function db_search($q, $page = 1, $page_size = 50) {
        $q = Table::make_fulltext_strips($q); // 假设已做安全处理
        $offset = ($page - 1) * $page_size;

        // 动态构建 UNION 查询（自动过滤无效配置）
        $queries = array_filter(array_map(function($type, $cfg) use ($q) {
            if (!isset($cfg['match']) || !is_array($cfg['match'])) return null;
            $matchCols = implode(', ', array_map(fn($c) => "`$c`", $cfg['match']));
            $against = str_replace("'", "''", $q); // 基础转义（强烈建议后续改用参数化）
            return sprintf(
                "(SELECT id, %s AS name, MATCH(%s) AGAINST ('%s' IN BOOLEAN MODE) AS score, '%s' AS type 
                  FROM %s 
                  WHERE MATCH(%s) AGAINST ('%s' IN BOOLEAN MODE))",
                $cfg['name_col'],
                $matchCols,
                $against,
                $type,
                $cfg['table'],
                $matchCols,
                $against
            );
        }, array_keys(self::SEARCH_CONFIGS), self::SEARCH_CONFIGS));

        if (empty($queries)) {
            return list_nav(['item' => []], $page);
        }

        $unionSql = implode(' UNION ', $queries);
        $finalSql = sprintf(
            "SELECT id, name, score, `type` FROM (%s) t1 ORDER BY score DESC LIMIT %d, %d",
            $unionSql,
            $offset,
            $page_size
        );

        $data = (new Table(["cad_registry" => "vocabulary"]))->getDriver()->Fetch($finalSql);
        return list_nav(['item' => self::assign_url($data)], $page);
    }

    private static function assign_url(array $terms): array {
        foreach ($terms as &$term) {
            $type = $term['type'] ?? '';
            $cfg = self::TYPE_CONFIG[$type] ?? null;
            
            if (!$cfg) {
                RFC7231Error::err500("URL builder not implemented for item type '{$type}'.");
            }

            $val = $term[$cfg['field']] ?? '';
            $val = $cfg['encode'] ? urlencode((string)$val) : (string)$val;
            $term['url'] = str_replace('{val}', $val, $cfg['url']);
            $term['bs_color'] = $cfg['color'];
        }
        return $terms;
    }
}



<?php 

imports("MVC.controller");
imports("RFC7231.logger");
imports("RFC7231.index");

/**
 * 用户访问权限控制器
*/
class accessController extends controller {

    function __construct() {
        parent::__construct();
    }

    public function accessControl() {        
        if (MAINTENANCE_MODE && !self::maintenance_mode()) {
            \Redirect("/maintenance_mode/");
        }
        
        if ($this->AccessByEveryOne()) {
            return true;
        }

        return !Utils::isDbNull(user_id());
    }

    /**
     * 判断当前的控制器是否是maintenance_mode以避免陷入跳转死循环
    */
    private static function maintenance_mode() {
        $requestUri =$_SERVER['REQUEST_URI']; 
        // 1. 使用 parse_url 获取路径部分（去掉查询参数）
        $path = parse_url($requestUri, PHP_URL_PATH); // 结果: /maintenance_mode/
        // 2. 去掉首尾斜杠
        $path = trim($path, '/'); // 结果: maintenance_mode
        // 3. 如果有多级路径，只取第一段
        $segments = explode('/',$path);

        // 结果: maintenance_mode
        return $segments[0] == "maintenance_mode";
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
    */
    public function Redirect($code) {
        $url = urlencode(Utils::URL());
        $url = "/login/?goto=$url";

        \Redirect($url);
    }   

    public static function log_pageview($res, $id) {
        $ip = Utils::UserIPAddress();
        $geo = self::geo_loc($ip);
        $ua =$_SERVER['HTTP_USER_AGENT'] ?? '';

        (new Table(["registry_engine"=>"page_view"]))->add([
            "session_id" => session_id(),
            "user_id" => user_id(),
            "ipaddress" => $ip,
            "geo_id" => $geo["id"],
            "user_agent"=> $ua,
            "resource"=>$res,
            "identifier"=>$id
        ]);
    }

    public static function geo_loc($ip) {
        $geo_ip = (new Table(["registry_engine"=>"geo_ip"]));
        $geo = $geo_ip->where(["ipaddress"=> $ip])->find();

        if (Utils::isDbNull($geo)) {
            $geo_ip->add(["ipaddress"=>$ip,"location"=>"-"]);
            $geo = $geo_ip->where(["ipaddress"=> $ip])->order_by("id", true)->find();
        }

        return $geo;
    }
}
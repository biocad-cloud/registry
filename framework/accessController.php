<?php 

/**
 * 用户访问权限控制器
*/
class accessController extends controller {

    function __construct() {
        parent::__construct();
    }

    public function accessControl() {       
        if ($this->AccessByEveryOne()) {
            return true;
        }

        return true;
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
    */
    public function Redirect($code) {
        $url = urlencode(Utils::URL());
        $url = "{<platform>passport/portal}&goto=$url";

        \Redirect($url);
    }   
}
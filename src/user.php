<?php

include __DIR__ . "/../.etc/bootstrap.php";

class App {

    /**
     * User login 
     * 
     * @access *
     * @method post
     * @uses api
    */
    public function login($email, $passwd) {
        $salt = DotNetRegistry::Read("password_salt");
        $passwd = md5($passwd . "|" . $salt);
        $check = (new Table(["registry_engine"=>"user"]))
            ->where(["email"=>$email,"password" => $passwd])
            ->find();

        if (Utils::isDbNull($check)) {
            controller::error("User not found or password incorrect!");
        } else if ($check["banned"] > 0) {
            controller::error("You has been banned from access this database resource!");        
        } else {
            # write session
            $_SESSION["user_id"] = $check["id"];
            $_SESSION["email"] = $check["email"];

            controller::success(1);
        }
    }

    /**
     * user logout
     * 
     * @access *
     * @method post
     * @uses api
    */
    public function logout() {
        unset($_SESSION["user_id"]);
        unset($_SESSION["email"]);
        session_destroy();

        controller::success(1);
    }
}
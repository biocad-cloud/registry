<?php

define("APP_PATH", dirname(__DIR__));
define("WEB_ROOT", dirname(APP_PATH));
define("APP_DEBUG", true);

define("YEAR", date("Y"));

session_start();

include "/opt/runtime/package.php";
include APP_PATH . "/framework/accessController.php";

dotnet::AutoLoad(__DIR__ . "/../.etc/config.ini.php");
dotnet::HandleRequest(new App());
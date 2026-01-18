<?php

define("APP_VIEWS", __DIR__ . "/../views/");

return [

    'DB_TYPE' => 'mysql',
    'DB_HOST' => '192.168.3.15',
    'DB_NAME' => 'cad_registry',
    'DB_USER' => 'xieguigang',
    'DB_PWD'  => '123456',
    'DB_PORT' => '3306',

    "cad_registry" => [
        'DB_TYPE' => 'mysql',
        'DB_HOST' => '192.168.3.15',
        'DB_NAME' => 'cad_registry',
        'DB_USER' => 'xieguigang',
        'DB_PWD'  => '123456',
        'DB_PORT' => '3306'
    ],

    "mzvault" => [
        'DB_TYPE' => 'mysql',
        'DB_HOST' => '192.168.3.15',
        'DB_NAME' => 'mzvault',
        'DB_USER' => 'xieguigang',
        'DB_PWD'  => '123456',
        'DB_PORT' => '3306'
    ],

    "ERR_HANDLER_DISABLE" => "FALSE",
	"RFC7231"       => APP_VIEWS . "/http_errors/",
    "CACHE" => FALSE,
    "CACHE.MINIFY" => FALSE,
    "APP_NAME" => "数据之源，洞见之始",
    "APP_TITLE" => "数据之源，洞见之始",
    "APP_VERSION" => "2.3.111.13-alpha",
	"MVC_VIEW_ROOT" => [
        "index" => APP_VIEWS
    ]
];
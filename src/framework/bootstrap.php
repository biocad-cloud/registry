<?php

include __DIR__ . "/../modules/php.NET/package.php";

dotnet::AutoLoad(__DIR__ . "/../../.etc/config.ini.php");
dotnet::HandleRequest(new App());
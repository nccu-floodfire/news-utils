<?php
use \Us\Utils\Engine\HotNews;
use \Us\Utils\Engine\AllNews;
use \Us\Utils\Engine\HttpHelper;
use \Us\Utils\Storage\InfluxDB;

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

// $url = $argv[1];

/* set parameters for develop purpose */
$config["app_id"] = '';
$config["app_secret"] = '';
$config["sleep"] = 0.5;
$config["date"] = '2014-11-25';

$config["db_username"] = "albert";
$config["db_password"] = "albert";

$Db = new InfluxDB($config["db_username"], $config["db_password"]);

/*$HotNews = new HotNews($config);
$HotNews->run();*/

$AllNews = new AllNews($Db, $config);
$AllNews->run();

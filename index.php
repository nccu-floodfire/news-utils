#!/usr/bin/env php
<?php

use \Us\Utils\NewsService\HotNews;
use \Us\Utils\NewsService\AllNews;
use \Us\Utils\NewsService\HttpHelper;
use \Us\Utils\Storage\InfluxDB;
use \Us\Utils\Facebook\FacebookSocialClient;

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

function help()
{
    fwrite(STDERR, "Usage: \n");
    fwrite(STDERR, "       --action={fetch-all|fetch-hot} \n");
    fwrite(STDERR, "       --date \n");
    fwrite(STDERR, "       --db-name \n");
    fwrite(STDERR, "       --db-user \n");
    fwrite(STDERR, "       --db-password \n");
    fwrite(STDERR, "       --app-id \n");
    fwrite(STDERR, "       --app-secret \n");
    fwrite(STDERR, "       --help \n");
}

$shortopts  = "";
$longopts  = array(
    "date:",
    "action:",
    "db-name:",
    "db-user:",
    "db-password:",
    "app-id:",
    "app-secret:",
    "help"
);

$options = getopt($shortopts, $longopts);

// default values
$config["app_id"] = '';
$config["app_secret"] = '';
$config["sleep"] = 1;
$config["date"] = date("Y-m-d", time());
$config["db_name"] = 'news_graph_dev';
$config["db_username"] = 'user';
$config["db_password"] = 'password';
$action = null;

foreach ($options as $k => $v) {
    switch ($k) {
        case 'app-id':
            $config["app_id"] = $v;
            break;
        case 'app-secret':
            $config["app_secret"] = $v;
            break;
        case 'date':
            $config['date'] = $v;
            break;
        case 'db-name':
            $config['db_name'] = $v;
            break;
        case 'db-user':
            $config['db_user'] = $v;
            break;
        case 'db-password':
            $config['db_password'] = $v;
            break;
        case 'action':
            $action = $v;
            break;
        case 'help':
            help();
            exit(0);
            break;
        default:
            break;
    }
}

$Db = new InfluxDB($config["db_name"], $config["db_username"], $config["db_password"]);

if ($action === null) {
    help();
    exit(0);
} else if ($action === 'fetch-hot') {
    $HotNews = new HotNews($Db, $config);
    $HotNews->run();
    exit();
} else if ($action === 'fetch-all') {
    $AllNews = new AllNews($Db, $config);
    $AllNews->run();
    exit();
} else if ($action === 'fetch-count-for-link') {

    $Fb = FacebookSocialClient::getInstance($config);
    $Dbh = new PDO('mysql:host=127.0.0.1;dbname=newsdiff', 'root', '');
    $Dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $date = $config['date'];
    $ts_start = strtotime($date);
    $ts_end = $ts_start + 86400 - 1;
    $stmt = $Dbh->prepare("select id, url from news where created_at between $ts_start and $ts_end;");
    $stmt->execute();
    $index = 0;
    while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
        usleep(800000); // 0.8 second
        $index++;
        echo "[{$date}] URL: {$rs->url} ($index/{$stmt->rowCount()}) ...";
        try {
            $GraphObj = $Fb->getLinkGraphObj($rs->url);
            $arr = $GraphObj->asArray();
            if (array_key_exists('og_object', $arr) && $arr['og_object']->type == 'website') {
                // skip website
                continue;
            }
            $ShareObj = $GraphObj->getProperty('share');
            if (!is_object($ShareObj)) {
                echo "Failed. Cannot find 'share'.\n";
                continue;
            }
            $input['share_count'] = $ShareObj->getProperty('share_count');
            $input['comment_count'] = $ShareObj->getProperty('comment_count');
            $stmt2 = $Dbh->prepare("update news set share_count = :share_count, comment_count = :comment_count where id = :id");
            $stmt2->bindValue(':share_count', $input['share_count'], PDO::PARAM_INT);
            $stmt2->bindValue(':comment_count', $input['comment_count'], PDO::PARAM_INT);
            $stmt2->bindValue(':id', $rs->id, PDO::PARAM_INT);
            $stmt2->execute();
            unset($stmt2);
            echo " Done! ({$input['share_count']}|{$input['comment_count']})\n";
        } catch (\Exception $e) {
            echo " Failed! - {$e->getMessage()}\n";
        }
    }
    unset($Dbh);
    exit();
    // TBD
}

help();
exit (1);

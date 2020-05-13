<?php


define("IN_SYS", TRUE);
header("Access-Control-Allow-Origin:*");
define("SITE_PATH", str_replace("\\", "/", realpath(dirname(__FILE__) . "/")) . "/");
define("APP_PATH", __DIR__ . "/application/");
define("RO_PATH", __DIR__);
define("CONF_PATH", APP_PATH);
define("WXAPP_TYPE", 10);
define("ATTACHMENT_ROOT", SITE_PATH);
$HTTP_HOST = explode(":", $_SERVER["HTTP_HOST"]);
$HTTP_HOST = $HTTP_HOST[0];
define("WEB_HOST", $HTTP_HOST);
error_reporting(0);
require "../../../framework/bootstrap.inc.php";
require "bootstrap.sys.inc.php";
error_reporting(0);
require __DIR__ . "/thinkphp/start.php";
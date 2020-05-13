<?php


defined("IN_IA") or define("IN_IA", true);
require __DIR__ . "/../../../../data/config.php";
$db = [];
if (empty($config["db"]["master"])) {
	$db = $config["db"];
} else {
	$db = $config["db"]["master"];
}
return ["type" => "mysql", "hostname" => $db["host"], "database" => $db["database"], "username" => $db["username"], "password" => $db["password"], "hostport" => $db["port"], "dsn" => '', "params" => [PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, PDO::ATTR_EMULATE_PREPARES => true], "charset" => "utf8", "prefix" => $db["tablepre"], "debug" => true, "deploy" => 0, "rw_separate" => false, "master_num" => 1, "slave_no" => '', "fields_strict" => true, "resultset_type" => "array", "auto_timestamp" => false, "datetime_format" => "Y-m-d H:i:s", "sql_explain" => false];
<?php


error_reporting(0);
define("APP_PATH", __DIR__ . "/application/");
require __DIR__ . "/thinkphp/base.php";
require __DIR__ . "/thinkphp/library/think/Session.php";
require __DIR__ . "/thinkphp/library/think/Request.php";
require __DIR__ . "/application/web/controller/User.php";
use think\Request;
use think\Config;
use think\Cache;
use think\Session;
use think\Cookie;
use think\Db;
use app\web\controller\User;
$databasePath = APP_PATH . "database.php";
Config::load($databasePath, "database");
$code = Request::instance()->param("code");
$mch_id = Request::instance()->param("mch_id");
$state = Request::instance()->param("state");
$do = Request::instance()->param("do");
if (Cookie::get("UserId_radar1" . $mch_id)) {
	$aa = Cookie::get("UserId_radar1" . $mch_id);
	$UserId = Cookie::get("UserId_radar1" . $mch_id);
	if (isset($state)) {
		$data2 = json_decode($state, true);
		$do = $data2["do"];
		$mch_id = $data2["mch_id"];
	}
	if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
		$host = $_SERVER["HTTP_X_REAL_HOST"];
	} else {
		$host = $_SERVER["HTTP_HOST"];
	}
	if (isset($_SERVER["PHP_SELF"])) {
		$child_path = $_SERVER["PHP_SELF"];
	} else {
		$child_path = $_SERVER["REQUEST_URI"];
	}
	$child_path = explode("/addons", $child_path);
	if ($do == "staffer_index") {
		$url = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web/index_ai.html?uid=" . $UserId . "&mch_id=" . $mch_id;
		header("Location:{$url}");
	} elseif ($do == "boss_index") {
		$url = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web/mychart_boss.html?uid=" . $UserId . "&mch_id=" . $mch_id;
		header("Location:{$url}");
	} else {
		echo "请在企业微信检查雷达链接是否填写正确";
		die;
	}
} else {
	Cookie::delete("UserId_radar1" . $mch_id);
	$data = ["mch_id" => $mch_id, "do" => $do];
	if (isset($code) && isset($state)) {
		$data2 = json_decode($state, true);
		$do = $data2["do"];
		$mch_id = $data2["mch_id"];
	}
	if (isset($_SERVER["PHP_SELF"])) {
		$child_path = $_SERVER["PHP_SELF"];
	} else {
		$child_path = $_SERVER["REQUEST_URI"];
	}
	$child_path = explode("/addons", $child_path);
	$we_chat = new User($mch_id);
	if (empty($code)) {
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		$url = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web.php";
		$we_chat->authorize($url, json_encode($data));
	} else {
		$we_chat->index($code, $do, $mch_id);
	}
}
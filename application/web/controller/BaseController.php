<?php


namespace app\web\controller;

use think\Controller;
use think\Session;
use think\Validate;
use think\Cache;
use think\Config;
use think\Request;
use think\Db;
use app\web\service\Send;
header("Access-Control-Allow-Origin: *");
class BaseController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$a = current($_SESSION["send_news"]);
		if (!empty($a)) {
			$t = $a["tt"];
			$send = new send($a["mch_id"]);
			$send->send_message($a["staff_id"], $a["user_id"], $a["post_message"]);
			unset($_SESSION["send_news"][$t]);
		}
	}
	protected function checkParam($rule, $data)
	{
		$validate = new Validate($rule);
		$result = $validate->check($data);
		if (!$result) {
			return $validate->getError();
		}
		return null;
	}
	public function getSId($uid)
	{
		$mch_id = Request::instance()->param("mch_id");
		if ($uid) {
			$rs = Db::name("ybmp_bus_card")->where("UserId", $uid)->where("mch_id", $mch_id)->where("radar", 1)->value("id");
			return $rs;
		} else {
			return null;
		}
	}
	public function getBId($uid)
	{
		if ($uid) {
			$rs = Db::name("ybmp_bus_card")->where("userid", $uid)->where("boss_radar", 1)->value("id");
			return $rs;
		} else {
			return null;
		}
	}
	public function getMchId($app_id)
	{
		$rs = Cache::get("app" . $app_id);
		if ($rs != false) {
			return $rs;
		}
		$info = Db::table("ims_ybmp_business")->field("id")->where("uniacid", $app_id)->find();
		$rs = $info["id"];
		Cache::set("app" . $app_id, $rs, CACHE_TIME);
		return $rs;
	}
	public function get_mchid($uid)
	{
		$mch_id = Request::instance()->param("mch_id");
		if (!empty($mch_id) && $mch_id != "undefined") {
			return $mch_id;
		}
		$re = db::name("ybmp_bus_card")->field("mch_id")->where("id", $uid)->order("id desc")->find();
		return $re["mch_id"];
	}
	public function get_mchid_cardid($cardid)
	{
		$re = db::name("ybmp_bus_card")->field("mch_id")->where("id", $cardid)->find();
		return $re["mch_id"];
	}
	protected function send_post($url, $post_data)
	{
		$options = array("http" => array("method" => "POST", "header" => "Content-type:application/json", "content" => $post_data, "timeout" => 60));
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
	public function data_uri($contents, $mime)
	{
		$base64 = base64_encode($contents);
		return "data:" . $mime . ";base64," . $base64;
	}
	public function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
	{
		if (empty($url)) {
			return array("code" => "100");
		}
		$_curl = curl_init();
		$_header = array("Accept-Language: zh-CN", "Connection: Keep-Alive", "Cache-Control: no-cache");
		if (!empty($hostIp)) {
			$urlInfo = parse_url($url);
			if (empty($urlInfo["host"])) {
				$urlInfo["host"] = substr(DOMAIN, 7, -1);
				$url = "http://{$hostIp}{$url}";
			} else {
				$url = str_replace($urlInfo["host"], $hostIp, $url);
			}
			$_header[] = "Host: {$urlInfo["host"]}";
		}
		if (!empty($params)) {
			curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($_curl, CURLOPT_POST, true);
		}
		if (substr($url, 0, 8) == "https://") {
			curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($_curl, CURLOPT_URL, $url);
		curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_curl, CURLOPT_USERAGENT, "API PHP CURL");
		curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);
		if ($expire > 0) {
			curl_setopt($_curl, CURLOPT_TIMEOUT, $expire);
			curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire);
		}
		if (!empty($extend)) {
			curl_setopt_array($_curl, $extend);
		}
		$result["result"] = curl_exec($_curl);
		$result["code"] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
		$result["info"] = curl_getinfo($_curl);
		if ($result["result"] === false) {
			$result["result"] = curl_error($_curl);
			$result["code"] = -curl_errno($_curl);
		}
		curl_close($_curl);
		return $result;
	}
	function fileToBase64($file)
	{
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		if (strpos($file, $host)) {
			return $file;
		}
		$base64_file = '';
		if ($file) {
			$base64_data = $this->get_url_content($file);
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$base64_file = $this->data_uri($base64_data, "image/" . $ext);
		}
		return $base64_file;
	}
	function get_url_content($url, $method = true)
	{
		global $_W;
		$site_root = $_W["siteroot"];
		$site_root = str_replace(["http://", "https://"], '', $site_root);
		$arr = ["jpg", "png", "jpeg", "gif"];
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		if (in_array($ext, $arr)) {
			$tmp_url = $url;
			$tmp_url = str_replace("http://" . $site_root . "addons/yb_mingpian/core/", SITE_PATH, $tmp_url);
			$tmp_url = str_replace("https://" . $site_root . "addons/yb_mingpian/core/", SITE_PATH, $tmp_url);
			if (file_exists($tmp_url)) {
				return file_get_contents($tmp_url);
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if ($method) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$cont = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($httpCode == "301") {
			$cont = file_get_contents($url);
		}
		return $cont;
	}
	public function get_skin($mch_id)
	{
		$s = 2;
		$skin = Db::name("ybmp_business_about")->where("mch_id", $mch_id)->find();
		if (!empty($skin["other"])) {
			$skin = json_decode($skin["other"], true);
			$s = $skin["zhuti"]["radar"];
		}
		if (empty($s) || $s == "undefined") {
			$s = 2;
		}
		$a = array(1 => "red", 2 => "blue", 3 => "green", 4 => "purple");
		return $a[$s];
	}
}
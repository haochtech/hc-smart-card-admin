<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/thinkphp/base.php";
require_once BASE_ROOT . "core/application/api/service/UserService.php";
require_once BASE_ROOT . "core/application/api/service/AddressService.php";
require_once BASE_ROOT . "core/application/api/service/GoodsService.php";
require_once BASE_ROOT . "core/application/api/service/CartService.php";
require_once BASE_ROOT . "core/application/api/service/AlbumService.php";
require_once BASE_ROOT . "core/application/api/service/ArticleService.php";
require_once BASE_ROOT . "core/application/api/service/OrderService.php";
require_once BASE_ROOT . "core/application/api/service/PayService.php";
require_once BASE_ROOT . "core/application/api/service/MarketService.php";
require_once BASE_ROOT . "core/application/api/service/IndexService.php";
require_once BASE_ROOT . "core/application/api/service/BargainService.php";
require_once BASE_ROOT . "core/application/api/service/PintuanService.php";
require_once BASE_ROOT . "core/application/api/service/MiaoshaService.php";
require_once BASE_ROOT . "core/application/api/service/DistribeService.php";
require_once BASE_ROOT . "core/application/api/service/CardService.php";
require_once BASE_ROOT . "core/application/api/service/OffwebService.php";
require_once BASE_ROOT . "core/application/api/service/ArlikiService.php";
use app\api\service\ArlikiService;
use think\Controller;
use think\Exception;
use think\Validate;
use think\Cache;
use think\config;
use think\Db;
class BaseController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$filename = BASE_ROOT . "core/application/database.php";
		Config::load($filename, "database");
		$a = Db::name("ybmp_red_push")->where("mch_id", $_SESSION["uniacid"])->where("create_time", 2)->find();
		$b = Db::name("ybmp_red_push")->where("create_time", 2)->find();
		$c = $a;
		if (empty($a)) {
			$c = $b;
		}
		if (!empty($c)) {
			Db::name("ybmp_red_push")->where("id", $c["id"])->update(["create_time" => "1"]);
			$send = new ArlikiService($c["mch_id"]);
			$e = $send->send_message($c["staff_id"], $c["user_id"], $c["msg"]);
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
	public function createOutTradeNo()
	{
		$time_str = date("Ymd");
		$order_no = $time_str . time() . rand(111, 999);
		return $order_no;
	}
	public function createOrderNo()
	{
		$no = time() . rand(111, 999);
		return $no;
	}
	public function getSId($uid)
	{
		if ($uid) {
			$rs = Db::name("ybmp_bus_card")->where("id", $uid)->value("UserId");
			return $rs;
		} else {
			return null;
		}
	}
	public function objectArray($obj)
	{
		$obj = (array) $obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == "resource") {
				return;
			}
			if (gettype($v) == "object" || gettype($v) == "array") {
				$obj[$k] = (array) $this->objectArray($v);
			}
		}
		return $obj;
	}
	public function getMchId($app_id)
	{
		$info = Db::name("ybmp_business")->field("id")->where("uniacid", $app_id)->find();
		$rs = $info["id"];
		return $rs;
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
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if ($method) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$cont = curl_exec($ch);
		curl_close($ch);
		$cont = mb_convert_encoding($cont, "UTF-8", "UTF-8,GBK,GB2312,BIG5");
		return $cont;
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
}
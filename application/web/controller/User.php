<?php


namespace app\web\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use think\Cache;
require_once APP_PATH . "api_common.php";
header("content-type: text/html; charset=utf-8");
class User extends Controller
{
	protected $CorpID = '';
	protected $agentid = '';
	protected $corpsecret = '';
	public function __construct($mch_id)
	{
		parent::__construct();
		$info = Db::name("ybmp_corp_conf")->where("mch_id", $mch_id)->find();
		if (!empty($info)) {
			$this->CorpID = $info["corp_id"];
			$this->agentid = $info["aid"];
			$this->corpsecret = $info["asecret"];
		}
	}
	public function index($code, $do, $mch_id)
	{
		$access_token = $this->getAccessToken();
		if (isset($access_token["access_token"]) && $access_token["errcode"] == 0) {
			$UserId = $this->getWeChatUserInfo($access_token["access_token"], $code, $mch_id);
		} else {
			$msg = check_work_err($access_token, false);
			if ($msg == $access_token) {
				$msg = json_encode($access_token);
			}
			echo $msg;
			echo "<script>alert('" . $msg . "');</script>";
			die;
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
	}
	public function authorize($redirect_url, $data)
	{
		$redirect_url = urlencode($redirect_url);
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?agentid={$this->agentid}&appid={$this->CorpID}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_base&state={$data}#wechat_redirect";
		header("Location:{$url}");
	}
	public function getAccessToken()
	{
		if (Cache::get("t_token" . $this->CorpID) && Cache::get("t_time_" . $this->CorpID) > time()) {
			return Cache::get("access_token");
		} else {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->CorpID}&corpsecret={$this->corpsecret}";
			$res = $this->get_url_content($url);
			$access_token = json_decode($res, true);
			if ($access_token["errcode"] == 0) {
				Cache::set("t_token_" . $this->CorpID, $access_token, 7000);
				Cache::set("t_time_" . $this->CorpID, time() + 7000);
			}
			return $access_token;
		}
	}
	public function getWeChatUserInfo($access_token, $code, $mch_id)
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
		$output = $this->get_url_content($url);
		$weChatUserInfo = json_decode($output, true);
		if ($weChatUserInfo["errcode"] == 0 && isset($weChatUserInfo["UserId"])) {
			Cookie::set("UserId_radar1" . $mch_id, $weChatUserInfo["UserId"], 3600);
			return $weChatUserInfo["UserId"];
		} else {
			$msg = check_work_err($weChatUserInfo, false);
			if ($msg == $weChatUserInfo) {
				$msg = json_encode($weChatUserInfo);
			}
			echo $msg;
			echo "<script>alert('" . $msg . "');</script>";
			die;
		}
	}
	public function get_url_content($url, $method = true)
	{
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
}
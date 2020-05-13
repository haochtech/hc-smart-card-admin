<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Request;
class Wechat extends Controller
{
	protected $CorpID = "wwbf62f70c2000d499";
	protected $agentid = "1000003";
	protected $corpsecret = "8_aQ-QR4pHfNylkgE4_y59Wfbd4ibvNyTe9n0-N7D9o";
	public function index($code)
	{
		$access_token = $this->getAccessToken();
		$UserId = $this->getWeChatUserInfo($access_token["access_token"], $code);
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		$url = "http://" . $host . "/addons/yb_mingpian/core/web/index.html?uid={$UserId}";
		header("Location:{$url}");
	}
	public function authorize($redirect_url)
	{
		$redirect_url = urlencode($redirect_url);
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?agentid={$this->agentid}&appid={$this->CorpID}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";
		header("Location:{$url}");
	}
	public function getAccessToken()
	{
		if (Session::get("access_token") && Session::get("expire_time") > time()) {
			return Session::get("access_token");
		} else {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->CorpID}&corpsecret={$this->corpsecret}";
			$res = get_url_content($url);
			$access_token = json_decode($res, true);
			Session::set("access_token", $access_token);
			Session::set("expire_time", time() + 7000);
			return $access_token;
		}
	}
	public function getWeChatUserInfo($access_token, $code)
	{
		$url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
		$output = get_url_content($url);
		$weChatUserInfo = json_decode($output, true);
		if ($weChatUserInfo["errcode"] == 0 && $weChatUserInfo["UserId"]) {
			return $weChatUserInfo["UserId"];
		} else {
			return null;
		}
	}
}
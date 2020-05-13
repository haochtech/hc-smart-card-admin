<?php


namespace app\api\service;

use think\Db;
use think\Cache;
class OffwebService
{
	private $user_corp = array("cid" => '', "aid" => '', "tsecret" => '', "asecret" => '');
	private $upda = array("add_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=", "edit_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=", "del_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=", "batch_depart" => "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceparty?access_token=", "add_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=", "edit_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=", "del_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=", "batch_user" => "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceuser?access_token=", "batchdel_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=", "upload" => "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=", "send_msg" => "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=");
	private $mch_id = '';
	function __construct($mch_id)
	{
		$this->mch_id = $mch_id;
		$d = Db::name("ybmp_corp_conf")->where("mch_id", $mch_id)->find();
		$this->user_corp["cid"] = $d["corp_id"];
		$this->user_corp["aid"] = $d["aid"];
		$this->user_corp["tsecret"] = $d["tsecret"];
		$this->user_corp["asecret"] = $d["asecret"];
	}
	public function re_a_token()
	{
		$corpid = $this->user_corp["cid"];
		$secret = $this->user_corp["asecret"];
		if (Cache::get("t_token")) {
			$token = Cache::get("t_token");
		} else {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$corpid}&corpsecret={$secret}";
			$token = json_decode($this->get_url_content($url), true);
			Cache::set("a_token", $token, 7200);
		}
		if ($token["errcode"] != 0) {
			return $token["errmsg"];
		}
		return $token["access_token"];
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
	public function sub_send($uid, $content, $type)
	{
		$a = Db::name("ybmp_business_about")->where("mch_id", $this->mch_id)->value("other");
		if (empty($a)) {
			return 0;
		}
		$a = json_decode($a, true);
		if (!isset($a["sub_send"])) {
			return 0;
		}
		if ($a["sub_send"][$type]["status"] == 0) {
			return 0;
		}
		$b = Db::name("ybmp_bus_card")->where(["mch_id" => $this->mch_id, "isleader" => 1])->field("UserId")->select();
		if (empty($b)) {
			return 0;
		}
		for ($i = 0; $i < count($b); $i++) {
			$e = $this->send_msg($b[$i]["UserId"], $content, $uid, 1, '', null, false);
		}
		return 1;
	}
	public function send_msg($user_id, $content1, $uid = null, $lead = 2, $href_content = "点击对话", $content2 = null, $return = true)
	{
		if (empty($user_id)) {
			return null;
		}
		$data["touser"] = $user_id;
		if (empty($this->user_corp["aid"])) {
			$res["code"] = 0;
			$res["message"] = "请设置应用ID";
			return $res;
		}
		if (empty($this->user_corp["asecret"])) {
			$res["code"] = 0;
			$res["message"] = "请设置应用secret";
			return $res;
		}
		$url = $this->upda["send_msg"] . $this->re_a_token();
		$data["msgtype"] = "text";
		$data["agentid"] = $this->user_corp["aid"];
		if (!empty($uid)) {
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
			$child_path = explode("/app", $child_path);
			$href = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web/news_ai.html?uid=" . $user_id . "&mch_id=" . $this->mch_id . "&user_id=" . $uid;
			if ($lead == 2) {
				$content = $content1 . "\n立即回复：<a href=\"" . $href . "\">" . $href_content . "</a>" . $content2;
			} else {
				$content = $content1;
			}
			$data["text"] = ["content" => $content];
		} else {
			$data["text"] = ["content" => $content1];
		}
		$cod = $this->post_data($url, $data);
		if ($lead == 1) {
			$type = 1;
			if (strpos($data["text"]["content"], "砍价") !== false) {
				$type = 2;
			}
			if (strpos($data["text"]["content"], "拼团") !== false) {
				$type = 3;
			}
			if (strpos($data["text"]["content"], "秒杀") !== false) {
				$type = 4;
			}
			Db::name("ybmp_synlog")->insert(["mch_id" => $this->mch_id, "media_id" => $data["text"]["content"], "filename" => $type, "create_time" => time()]);
		}
		if ($return) {
			return $cod["errmsg"];
		}
	}
	public function set_user_auth($uid, $token)
	{
		return true;
	}
	public function post_data($url, $param, $is_file = false, $return_array = true)
	{
		if (!$is_file && is_array($param)) {
			$param = json_encode($param, true);
		}
		if ($is_file) {
			$header[] = "content-type: multipart/form-data; charset=UTF-8";
		} else {
			$header[] = "content-type: application/json; charset=UTF-8";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		$return_array && ($res = json_decode($res, true));
		return $res;
	}
}
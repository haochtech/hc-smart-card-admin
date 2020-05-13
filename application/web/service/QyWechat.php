<?php


namespace app\web\service;

use think\Cache;
use think\Db;
require_once APP_PATH . "api_common.php";
class QyWechat
{
	protected $corpid = '';
	protected $corpsecret = '';
	protected $mch_id = 0;
	public function __construct($mch_id)
	{
		$this->mch_id = $mch_id;
		$conf = Db::name("ybmp_corp_conf")->where("mch_id", $mch_id)->find();
		if (!empty($conf)) {
			$this->corpid = $conf["corp_id"];
			$this->corpsecret = $conf["asecret"];
		}
	}
	public function getAccessToken()
	{
		if (Cache::get("t_token" . $this->corpid) && Cache::get("t_time_" . $this->corpid) > time()) {
			return Cache::get("access_token");
		} else {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->corpid}&corpsecret={$this->corpsecret}";
			$res = $this->get_url_content($url);
			$access_token = json_decode($res, true);
			if ($access_token["errcode"] == 0) {
				Cache::set("t_token_" . $this->corpid, $access_token, 7000);
				Cache::set("t_time_" . $this->corpid, time() + 7000);
			}
			return $access_token;
		}
	}
	public function GetJsapiTicket()
	{
		$token = $this->getAccessToken();
		if (isset($token["access_token"]) && $token["errcode"] == 0) {
			$access_token = $token["access_token"];
		} else {
			$msg = check_work_err($token, false);
			if ($msg == $token) {
				$msg = json_encode($token);
			}
			echo "<script>alert('" . $msg . "');</script>";
			die;
		}
		$ticket_qy = Cache::get("ticket_qy" . $this->corpid, '');
		if (empty($ticket_qy)) {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token={$access_token}";
			$res = $this->get_url_content($url);
			$res_obj = json_decode($res, true);
			$ticket_qy = $res_obj["ticket"];
			Cache::set("ticket_qy" . $this->corpid, $ticket_qy, $res_obj["expires_in"]);
		}
		return $ticket_qy;
	}
	public function GetSignPackage($url)
	{
		$ticket = $this->GetJsapiTicket();
		$timestamp = time();
		$nonceStr = $this->CreateNoncestr();
		$string = "jsapi_ticket={$ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
		$signature = sha1($string);
		$signPackage = array("appId" => $this->corpid, "nonceStr" => $nonceStr, "timestamp" => $timestamp, "signature" => $signature);
		return $signPackage;
	}
	private function CreateNoncestr($length = 16)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	function DownloadWeixinFile($media_id, $path, $filetype = "png")
	{
		$token = $this->getAccessToken();
		if (isset($token["access_token"]) && $token["errcode"] == 0) {
			$access_token = $token["access_token"];
		} else {
			$msg = check_work_err($token, false);
			if ($msg == $token) {
				$msg = json_encode($token);
			}
			echo "<script>alert('" . $msg . "');</script>";
			die;
		}
		$url = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		curl_close($ch);
		$imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . "." . $filetype;
		$date_path = date("Y-m-d", time());
		$file_path = $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $this->mch_id . "/" . $path . "/" . $date_path;
		if (!is_dir($file_path)) {
			mkdir($file_path, 0777, true);
		}
		$imageSrc = $file_path . "/" . $imageName;
		$local_file = fopen($imageSrc, "w");
		if (false !== $local_file) {
			if (false !== fwrite($local_file, $package)) {
				fclose($local_file);
			}
		}
		$host = get_child_url2(false);
		return $host . "attachment/upload/" . $this->mch_id . "/" . $path . "/" . $date_path . "/" . $imageName;
	}
	function get_url_content($url, $method = true)
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
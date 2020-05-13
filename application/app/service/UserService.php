<?php


namespace app\app\service;

use think\Db;
require EXTEND_PATH . "/Alisms/TopSdk.php";
class UserService
{
	public function login($user_name, $password)
	{
		$condition = array("phone" => $user_name, "password" => md5($password), "is_del" => 1);
		$info = Db::table("ims_ybmp_business")->field("id,nick_name,name,phone,head_img,payment_method,uniacid")->where($condition)->find();
		return $info;
	}
	public function ModifyPassword($phone, $data)
	{
		$rs = Db::table("ims_ybmp_business")->update(["password" => $data["password"]], ["phone" => $phone]);
		return $rs;
	}
	public function Sms($phone)
	{
		$condition = array("phone" => $phone, "is_del" => 1);
		$check = Db::table("ims_ybmp_business")->field("phone")->where($condition)->find();
		if (empty($check)) {
			return $check;
		}
		$rs = array("code" => 0, "info" => array());
		$c = new \TopClient();
		$TemplateCode = "SMS_41530401";
		$c->format = "json";
		$c->appkey = "24536084";
		$c->secretKey = "9c7d8e23b8a62bc8267ea2dd791d59f6";
		$req = new \AlibabaAliqinFcSmsNumSendRequest();
		$req->setSmsType("normal");
		$req->setSmsFreeSignName("注册验证");
		$num = rand(10000, 99999);
		$product = "洛阳易购";
		$req->setSmsParam("{\"code\":\"{$num}\",\"product\":\"{$product}\"}");
		$req->setRecNum($phone);
		$req->setSmsTemplateCode($TemplateCode);
		$resp = $c->execute($req);
		if ($resp->result) {
			$data["code"] = $num;
			$data["create_time"] = time();
			$data["phone"] = $phone;
			$data = array_filter($data);
			$info = Db::table("ims_ybmp_sms")->insert($data);
			if (empty($info)) {
				$rs["code"] = 1;
				$rs["msg"] = "验证码发送失败,请稍后再试";
				return $rs;
			}
			$rs["info"] = $num;
			return $rs;
		}
		$rs["code"] = 1;
		$rs["msg"] = "验证码发送失败,请稍后再试";
		return $rs;
	}
	public function resetPassword($phone, $code)
	{
		$condition = array("phone" => $phone, "code" => $code);
		$info = Db::table("ims_ybmp_sms")->where($condition)->find();
		return $info;
	}
	public function delCode($phone)
	{
		$info = Db::table("ims_ybmp_sms")->delete(["phone" => $phone]);
		return $info;
	}
	public function check()
	{
		$param = Db::table("ims_ybmp_config")->where("key", "APP")->where("is_use", 1)->value("value");
		$param = json_decode($param, true);
		return $param;
	}
	protected function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
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
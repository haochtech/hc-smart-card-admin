<?php


namespace app\api\service;

use think\Db;
require_once BASE_ROOT . "core/application/common.php";
class UserService
{
	protected $wa = '';
	protected $d = 1;
	protected $config = array("url" => "https://api.weixin.qq.com/sns/jscode2session", "appid" => "your appId", "secret" => "your secret", "grant_type" => "authorization_code");
	public function __construct()
	{
		$param = Db::name("account_wxapp")->where("uniacid", $GLOBALS["mch_id"])->field("key,secret,name")->find();
		$this->config["appid"] = $param["key"];
		$this->config["secret"] = $param["secret"];
	}
	public function checkUser($appId, $pid, $mch_id)
	{
		Db::name("ybmp_user")->where(["wx_openid" => $appId, "mch_id" => ["<>", $mch_id]])->delete();
		$this->wa = Db::name("ybmp_user")->where("uid", $pid)->value("pid");
		$rs = Db::name("ybmp_user")->where(["wx_openid" => $appId, "mch_id" => $mch_id])->find();
		$now_id = $rs["uid"];
		$arr = array();
		$d = 0;
		spLbH:
		$d++;
		if ($this->wa == 0 || $pid == 0) {
			$this->d = 0;
		} else {
			if ($now_id == $this->wa) {
				$this->d = 2;
			} else {
				array_push($arr, $this->wa);
				$this->d = 1;
				$this->wa = Db::name("ybmp_user")->where("uid", $this->wa)->value("pid");
				if ($this->wa != $now_id) {
					if (!in_array($this->wa, $arr)) {
						if ($this->d == 1) {
							goto spLbH;
						}
					} else {
						$this->d = 2;
						sort($arr);
						Db::name("ybmp_user")->where("uid", $arr[0])->update(["pid" => 0]);
					}
				} else {
					$this->d = 2;
				}
			}
		}
		if (!empty($rs) && $pid != 0 && $rs["pid"] == 0 && $pid != $rs["uid"] && $rs["is_distributor"] == 0 && $this->d == 0) {
			Db::name("ybmp_user")->where(["wx_openid" => $appId, "mch_id" => $mch_id])->update(["pid" => $pid]);
		}
		if (!empty($rs) && $rs["uid"]) {
			return $rs["uid"];
		} else {
			return null;
		}
	}
	public function addUser($data)
	{
		$info = Db::name("ybmp_user_share_setting")->where("mch_id", $data["mch_id"])->find();
		if (empty($info) || $info["level"] == 0) {
			$data["pid"] = 0;
		}
		$rs = Db::name("ybmp_user")->insertGetId($data);
		return $rs;
	}
	public function get_userinfo($data)
	{
		$u = Db::name("ybmp_user")->where("uid", $data["uid"])->find();
		return $u;
	}
	public function checkLogin($code)
	{
		$params = array("appid" => $this->config["appid"], "secret" => $this->config["secret"], "js_code" => $code, "grant_type" => $this->config["grant_type"]);
		$res = $this->makeRequest($this->config["url"], $params);
		return $res["result"];
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
	public function orderCount($data)
	{
		$rs = array();
		$u = Db::name("ybmp_user")->where("uid", $data["uid"])->find();
		$rs["user_level"] = $u["level_id"];
		if ($u["level_id"] != 0) {
			$a = Db::name("ybmp_user_level")->where(["id" => $u["level_id"], "mch_id" => $data["mch_id"]])->find();
			$rs["level_name"] = $a["level_name"];
		}
		$rs["pending_payment"] = Db::name("ybmp_order")->where("buyer_id", $data["uid"])->where("is_deleted", 0)->where("mch_id", $data["mch_id"])->where("order_status", 0)->count();
		$rs["pending_shipment"] = Db::name("ybmp_order")->where("buyer_id", $data["uid"])->where("is_deleted", 0)->where("mch_id", $data["mch_id"])->where("order_status", 1)->count();
		$rs["pending_receipt"] = Db::name("ybmp_order")->where("buyer_id", $data["uid"])->where("is_deleted", 0)->where("mch_id", $data["mch_id"])->where("order_status", 2)->count();
		$rs["refund"] = Db::name("ybmp_order")->where("buyer_id", $data["uid"])->where("is_deleted", 0)->where("mch_id", $data["mch_id"])->where("order_status", 4)->count();
		$rs["favorites"] = Db::name("ybmp_user_favorites")->where("uid", $data["uid"])->count();
		$copy = get_url_content("http://vip.ly100.wang/api/api/index/is_show_copyright?uniacid=" . $data["app_id"] . "&yuming=" . $_SERVER["HTTP_HOST"]);
		if (!empty($copy)) {
			if ($copy["code"] == 0) {
				$copyright = Db::name("ybmp_copyright")->where("uniacid", $data["mch_id"])->find();
				if (empty($copyright)) {
					$copyright = Db::name("ybmp_copyright")->where("uniacid", 0)->find();
				}
				if (!empty($copyright)) {
					$rs["copyright"] = $copyright["content"];
				}
			} else {
				$rs["copyright"] = "洛阳壹佰网络提供技术支持";
			}
		} else {
			$rs["copyright"] = '';
		}
		return $rs;
	}
	public function about($data)
	{
		$rs = Db::name("ybmp_business_about")->where("mch_id", $data["mch_id"])->find();
		$rs["logo"] = __IMG($rs["logo"]);
		if ($data["uid"] != 0) {
			$u = Db::name("ybmp_user")->where($data)->find();
			$rs["user_level"] = $u["level_id"];
			$rs["user_rebate"] = 10.0;
			if ($u["level_id"] != 0) {
				$a = Db::name("ybmp_user_level")->where(["id" => $u["level_id"], "mch_id" => $data["mch_id"]])->find();
				if ($a) {
					$rs["user_rebate"] = $a["rebate"];
				}
			}
		}
		return $rs;
	}
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
	public function decryptData($encryptedData, $sessionKey, $app_id, $iv, &$data)
	{
		if (strlen($sessionKey) != 24) {
			return self::$IllegalAesKey;
		}
		$aesKey = base64_decode($sessionKey);
		if (strlen($iv) != 24) {
			return self::$IllegalIv;
		}
		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($encryptedData);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$dataObj = json_decode($result);
		if ($dataObj == NULL) {
			return self::$IllegalBuffer;
		}
		if ($dataObj->watermark->appid != $app_id) {
			return self::$IllegalBuffer;
		}
		$data = $result;
		return self::$OK;
	}
}
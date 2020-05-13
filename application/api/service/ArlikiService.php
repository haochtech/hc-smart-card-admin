<?php


namespace app\api\service;

use think\Cache;
use think\Db;
class ArlikiService
{
	private $ali_id = '';
	private $ali_token = '';
	private $ali_name = '';
	private $ali_code = '';
	private $phone = '';
	private $status = "2";
	private $bus_id2 = '';
	private $mp3_id = "4ae8238553cf81b20374283b4356d57c";
	protected $config = array("url" => "https://api.weixin.qq.com/sns/jscode2session", "appid" => '', "secret" => '', "grant_type" => "authorization_code", "template_id" => '');
	function __construct($mch_id)
	{
		$this->bus_id2 = $mch_id;
		$re = Db::name("ybmp_smsconf")->where("mch_id", $mch_id)->where("type", 1)->find();
		$this->ali_id = $re["ali_id"];
		$this->ali_token = $re["ali_token"];
		$this->ali_name = $re["ali_name"];
		$this->ali_code = $re["ali_code"];
		$this->phone = $re["phone"];
		$this->status = $re["status"];
	}
	public function exp_load($no, $from = '')
	{
		if (Cache::get("exp_info" . $no)) {
			$res = Cache::get("exp_info" . $no);
		} else {
			if (empty($from)) {
				$from = $this->exp_from($no);
				for ($i = 0; $i < count($from); $i++) {
					$res = $this->exp_query($no, $from[$i]);
					if ($res["code"] == 1) {
						Cache::set("exp_info" . $no, $res, 3600);
						break;
					}
				}
				if (count($from) == 0) {
					$res["data"] = "单号异常,请稍后再试.";
					$res["code"] = $from;
				}
			} else {
				$res = $this->exp_query($no, $from);
			}
		}
		return $res;
	}
	public function exp_from($no)
	{
		$url = "http://m.kuaidi100.com/autonumber/auto?num=" . $no;
		$res = json_decode(get_url_content($url), true);
		$re = array();
		for ($i = 0; $i < count($res); $i++) {
			$re[$i] = $res[$i]["comCode"];
		}
		return $re;
	}
	private function exp_query($no, $from)
	{
		$state = array(0 => "在途中", 1 => "已揽收", 2 => "疑难", 3 => "已签收", 4 => "退签", 5 => "同城派送中", 6 => "退回", 7 => "转单");
		$url = "http://m.kuaidi100.com/query?type=" . $from . "&postid=" . $no . "&id=1&valicode=&temp=0.722990631975" . rand(100, 999);
		$res = json_decode(get_url_content($url), true);
		$msg["state"] = $res["state"];
		$msg["state"] = $state[$res["state"]];
		if ($res["message"] == "ok") {
			$msg["data"] = $res["data"];
			$msg["code"] = 1;
		} else {
			$msg["data"] = "获取失败,请稍后再试.";
			$msg["code"] = 0;
		}
		return $msg;
	}
	public function send_sms($data, $type = 1, $phone = '', $return = false)
	{
		if ($type != 1) {
			$re = Db::name("ybmp_smsconf")->where("mch_id", $this->bus_id2)->where("type", $type)->find();
			$this->ali_id = $re["ali_id"];
			$this->ali_token = $re["ali_token"];
			$this->ali_name = $re["ali_name"];
			$this->ali_code = $re["ali_code"];
			$this->phone = $re["phone"];
			$this->status = $re["status"];
		}
		if (empty($this->ali_id) || empty($this->ali_token)) {
			$d["Code"] = "0";
			$d["Message"] = "参数不完整";
			return $d;
		}
		if ($this->status == 2) {
			$d["Code"] = "0";
			$d["Message"] = "未开启短信通知";
			return $d;
		}
		if (empty($phone)) {
			$phone = explode(",", $this->phone);
		} else {
			$phone = array($phone);
		}
		if (!is_array($data)) {
			$pa["order"] = $data;
		} else {
			$pa = $data;
		}
		for ($i = 0; $i < count($phone); $i++) {
			if (strlen($phone[$i]) > 5) {
				$params = array();
				$params["PhoneNumbers"] = $phone[$i];
				$params["SignName"] = $this->ali_name;
				$params["TemplateCode"] = $this->ali_code;
				$params["TemplateParam"] = $pa;
				if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
					$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
				}
				$res[$i] = $this->aliyun_send($params);
				Db::name("ybmp_smsconf")->where("mch_id", $this->bus_id2)->setInc("ok_use");
			}
		}
		if ($return) {
			return $res;
		}
	}
	public function send_test($ali_id, $ali_token, $ali_name, $ali_code, $phone)
	{
		$params = array();
		$params["PhoneNumbers"] = $phone;
		$params["SignName"] = $ali_name;
		$params["TemplateCode"] = $ali_code;
		$params["TemplateParam"] = array("order" => "ABCDEF");
		if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
			$params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
		}
		$res = $this->aliyun_send($params, $ali_id, $ali_token);
		return $res;
	}
	private function aliyun_send($params, $ali_id = '', $ali_token = '')
	{
		$accessKeyId = $this->ali_id;
		$accessKeySecret = $this->ali_token;
		if (!empty($ali_id) && !empty($ali_token)) {
			$accessKeyId = $ali_id;
			$accessKeySecret = $ali_token;
		}
		$security = false;
		$content = $this->request($accessKeyId, $accessKeySecret, "dysmsapi.aliyuncs.com", array_merge($params, array("RegionId" => "cn-hangzhou", "Action" => "SendSms", "Version" => "2017-05-25")), $security);
		return $content;
	}
	public function amr2mp3($url, $staff_id, $sleep_time = 0, $call_back = false)
	{
		$endpoint = "https://api2.online-convert.com/jobs";
		$apikey = $this->mp3_id;
		$debug = false;
		$uu = get_child_url(false);
		$json_resquest = "{\n            \"input\": [{\n                \"type\": \"remote\",\n                \"source\": \"" . $url . "\"\n             }],\n            \"conversion\": [{\n                \"target\": \"mp3\"\n             }],\n            \"callback\": \"" . $uu . "addons/yb_mingpian/core/index.php?s=/admin/test/mp3api\",\n            \"notify_status\": true\n        }";
		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_resquest);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Oc-Api-Key: " . $apikey, "Content-Type: application/json", "cache-control: no-cache"));
		if ($debug) {
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
		}
		$response = json_decode(curl_exec($ch), true);
		$error = curl_error($ch);
		curl_close($ch);
		if (!empty($response["id"])) {
			$id = Db::name("ybmp_mp3log")->insertGetId(["staff_id" => $staff_id, "api_id" => $response["id"]]);
			if ($call_back) {
				if ($sleep_time >= 7) {
					$sleep_time = $sleep_time % 7;
				} else {
					$sleep_time = 3;
				}
				sleep($sleep_time);
				for ($i = 0; $i < 3; $i++) {
					$url = $this->get_mp3_url(0, $response["id"]);
					if (!empty($url)) {
						break;
					}
					sleep(1);
				}
				if (!empty($url)) {
					return $url;
				} else {
					return $id;
				}
			}
			return $id;
		} else {
			return $error;
		}
	}
	public function get_mp3_url($staff_id, $api_id = '')
	{
		if (!empty($api_id)) {
			$url = Db::name("ybmp_mp3log")->where("api_id", $api_id)->value("url");
		} else {
			$url = Db::name("ybmp_mp3log")->where("staff_id", $staff_id)->order("id", "desc")->value("url");
		}
		return $url;
	}
	public function request($accessKeyId, $accessKeySecret, $domain, $params, $security = false, $method = "POST")
	{
		$apiParams = array_merge(array("SignatureMethod" => "HMAC-SHA1", "SignatureNonce" => uniqid(mt_rand(0, 0xffff), true), "SignatureVersion" => "1.0", "AccessKeyId" => $accessKeyId, "Timestamp" => gmdate("Y-m-d\\TH:i:s\\Z"), "Format" => "JSON"), $params);
		ksort($apiParams);
		$sortedQueryStringTmp = '';
		foreach ($apiParams as $key => $value) {
			$sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
		}
		$stringToSign = "{$method}&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));
		$sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&", true));
		$signature = $this->encode($sign);
		$url = ($security ? "https" : "http") . "://{$domain}/";
		try {
			$content = $this->fetchContent($url, $method, "Signature={$signature}{$sortedQueryStringTmp}");
			return json_decode($content);
		} catch (\Exception $e) {
			return false;
		}
	}
	private function encode($str)
	{
		$res = urlencode($str);
		$res = preg_replace("/\\+/", "%20", $res);
		$res = preg_replace("/\\*/", "%2A", $res);
		$res = preg_replace("/%7E/", "~", $res);
		return $res;
	}
	private function fetchContent($url, $method, $body)
	{
		$ch = curl_init();
		if ($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		} else {
			$url .= "?" . $body;
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-sdk-client" => "php/2.0.0"));
		if (substr($url, 0, 5) == "https") {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$rtn = curl_exec($ch);
		if ($rtn === false) {
			trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
		}
		curl_close($ch);
		return $rtn;
	}
	public function insert_red($rid, $share_id, $user_id)
	{
		$code = 1;
		$msg = '';
		$conf = Db::name("ybmp_red")->where("id", $rid)->find();
		if ($conf["status"] == 2) {
			$re["code"] = 3;
			$re["msg"] = "活动已关闭";
			return json_encode($re);
		}
		$c_share = Db::name("ybmp_redlog")->where(["share_id" => $share_id, "status" => 0, "rid" => $rid])->order("create_time asc")->select();
		$data["rid"] = $rid;
		$data["share_id"] = $share_id;
		$data["split_id"] = $user_id;
		$data["create_time"] = time();
		$data["status"] = 0;
		if (empty($c_share)) {
			Db::name("ybmp_redlog")->insert($data);
			if ($conf["peo_num"] == 2) {
				$this->split_red();
				$msg = time();
				$code = 2;
				$e = Db::name("ybmp_user_coupon")->order("id", "desc")->where("user_id", $user_id)->where("rid", $rid)->find();
				$user_red["money"] = $e["rmoney"];
				$user_red["use_least"] = $conf["use_least"];
				$user_red["get_time"] = date("Y.m.d", $e["get_time"]);
				$user_red["end_time"] = date("Y.m.d", $e["rend_time"]);
				$re["user_red"] = $user_red;
			}
		} else {
			if ($c_share[0]["create_time"] + 3600 * $conf["split_time"] <= time()) {
				$code = 0;
				$msg = "红包已过期,无法帮拆";
			} else {
				if ($conf["peo_num"] > count($c_share) + 1) {
					Db::name("ybmp_redlog")->insert($data);
					if ($conf["peo_num"] == count($c_share) + 2) {
						$this->split_red();
						$e = Db::name("ybmp_user_coupon")->order("id", "desc")->where("user_id", $user_id)->where("rid", $rid)->find();
						$user_red["money"] = $e["rmoney"];
						$user_red["use_least"] = $conf["use_least"];
						$user_red["get_time"] = date("Y.m.d", $e["get_time"]);
						$user_red["end_time"] = date("Y.m.d", $e["rend_time"]);
						$code = 2;
						$msg = time();
						$re["user_red"] = $user_red;
					} else {
						$code = 1;
					}
				} else {
					$code = 4;
					$msg = "红包已拆开,无法帮拆";
					Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
				}
			}
		}
		$re["code"] = $code;
		$re["msg"] = $msg;
		return json_encode($re);
	}
	public function split_red()
	{
		$conf = Db::name("ybmp_red")->where("mch_id", $this->bus_id2)->find();
		if ($conf["status"] == 1) {
			$list = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0])->group("share_id")->field("*,count(share_id) num")->select();
			for ($i = 0; $i < count($list); $i++) {
				$check = $this->check_outtime($list[$i]["share_id"], $conf["id"]);
				if ($list[$i]["num"] == $conf["peo_num"] - 1 && $check == 1) {
					$a["status"] = 0;
					$a["is_del"] = 0;
					$a["coupon_id"] = 0;
					$a["get_time"] = time();
					$a["rid"] = $conf["id"];
					$a["mch_id"] = $conf["mch_id"];
					$a["rend_time"] = time() + $conf["vali_time"] * 86400;
					if ($conf["split_type"] == 1) {
						$pre = floor($conf["money_num"] * 100 / ($list[$i]["num"] + 1)) / 100;
						$share = $conf["money_num"] - ($list[$i]["num"] + 1) * $pre + $pre;
						$a["key"] = md5($conf["id"] . $list[$i]["share_id"] . $share . "Arliki");
						Db::startTrans();
						try {
							Db::name("ybmp_redshare")->where(["status" => 0, "rid" => $conf["id"], "share_id" => $list[$i]["share_id"]])->update(["status" => 1]);
							$a["user_id"] = $list[$i]["share_id"];
							$a["rmoney"] = $share;
							Db::name("ybmp_user_coupon")->insert($a);
							$user = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0, "share_id" => $list[$i]["share_id"]])->order("id", "asc")->select();
							$user_id = '';
							for ($k = 0; $k < count($user); $k++) {
								if ($k == 0) {
									$this->add_query($list[$i]["share_id"], $list[$i]["share_id"], "红包已拆分,获得金额:" . $share . "元，请到会员中心--我的优惠券查看");
								} else {
									$this->add_query($list[$i]["share_id"], $user[$k - 1]["split_id"], "红包已拆分,获得金额:" . $pre . "元，请到会员中心--我的优惠券查看");
								}
								$user_id .= $user[$k]["split_id"] . ",";
								Db::name("ybmp_redlog")->where(["status" => 0, "rid" => $conf["id"], "share_id" => $list[$i]["share_id"], "split_id" => $user[$k]["split_id"]])->update(["status" => 1]);
								$a["user_id"] = $user[$k]["split_id"];
								$a["rmoney"] = $pre;
								$a["key"] = md5($conf["id"] . $user[$k]["split_id"] . $pre . "Arliki");
								Db::name("ybmp_user_coupon")->insert($a);
								Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "share_id" => $list[$i]["share_id"], "split_id" => $user[$k]["split_id"]])->select();
							}
							$this->write_log($conf["id"] . "--分发红包:" . $user_id . $list[$i]["share_id"] . ";" . $pre . "," . $share);
							Db::commit();
						} catch (\Exception $e) {
							Db::rollback();
						}
						Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "share_id" => $list[$i]["share_id"]])->select();
					} else {
						$new_all = $conf["money_num"] * 100 - ($list[$i]["num"] + 1);
						$money_arr = array();
						$tmp = 0;
						for ($s = 0; $s < $list[$i]["num"] + 1; $s++) {
							$tmp = rand(0, $new_all);
							$new_all -= $tmp;
							if ($s == $list[$i]["num"]) {
								array_push($money_arr, ($new_all + $tmp + 1) / 100);
							} else {
								array_push($money_arr, ($tmp + 1) / 100);
							}
							shuffle($money_arr);
						}
						$user = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0, "share_id" => $list[$i]["share_id"]])->select();
						Db::startTrans();
						try {
							Db::name("ybmp_redshare")->where(["status" => 0, "rid" => $conf["id"], "share_id" => $list[$i]["share_id"]])->update(["status" => 1]);
							$a["user_id"] = $list[$i]["share_id"];
							$a["rmoney"] = $money_arr[0];
							$a["key"] = md5($conf["id"] . $list[$i]["share_id"] . $money_arr[0] . "Arliki");
							Db::name("ybmp_user_coupon")->insert($a);
							$user_id = '';
							for ($k = 0; $k < count($user); $k++) {
								if ($k == 0) {
									$this->add_query($list[$i]["share_id"], $list[$i]["share_id"], "红包已拆分,获得金额:" . $money_arr[0] . "元，请到会员中心--我的优惠券查看");
								} else {
									$this->add_query($list[$i]["share_id"], $user[$k - 1]["split_id"], "红包已拆分,获得金额:" . $money_arr[$k] . "元，请到会员中心--我的优惠券查看");
								}
								$user_id .= $user[$k]["split_id"] . ":" . $money_arr[$k + 1] . ",";
								Db::name("ybmp_redlog")->where(["status" => 0, "rid" => $conf["id"], "share_id" => $list[$i]["share_id"], "split_id" => $user[$k]["split_id"]])->update(["status" => 1]);
								$a["user_id"] = $user[$k]["split_id"];
								$a["rmoney"] = $money_arr[$k + 1];
								$a["key"] = md5($conf["id"] . $user[$k]["split_id"] . $money_arr[$k + 1] . "Arliki");
								Db::name("ybmp_user_coupon")->insert($a);
							}
							$this->write_log($conf["id"] . "--分发红包:" . $user_id . $list[$i]["share_id"] . ":" . $money_arr[0]);
							Db::commit();
						} catch (\Exception $e) {
							Db::rollback();
						}
					}
				}
			}
		}
	}
	public function add_query($staff_id, $user_id, $post_message)
	{
		Db::name("ybmp_red_push")->insert(["mch_id" => $this->bus_id2, "staff_id" => $staff_id, "user_id" => $user_id, "msg" => $post_message, "create_time" => 2]);
	}
	public function check_outtime($share_id, $rid, $change = true)
	{
		$conf = Db::name("ybmp_red")->where("id", $rid)->field("split_time,create_time")->find();
		$check = Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->order("id desc")->value("create_time");
		$new = intval($check + 3600 * $conf["split_time"]);
		if ($new > time()) {
			return 1;
		} else {
			if ($change) {
				Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0, "create_time" => [">", $new]])->update(["status" => 2]);
				Db::name("ybmp_redshare")->where(["status" => 0, "rid" => $rid, "share_id" => $share_id])->update(["status" => 2]);
			}
			return 2;
		}
	}
	private function write_log($do, $data = '')
	{
		Db::name("ybmp_synlog")->insert(["mch_id" => $this->bus_id2, "media_id" => $do . $data, "create_time" => date("Ymd/His", time())]);
	}
	public function check_data($peo_num, $split_time)
	{
		$conf = Db::name("ybmp_red")->where("mch_id", $this->bus_id2)->find();
		$a = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0])->group("share_id")->field("*,count(share_id) num")->order("num desc")->select();
		$res["code"] = 1;
		$res["msg"] = '';
		$b = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0])->order("create_time asc")->find();
		if ($b["create_time"] + 3600 * $split_time >= time()) {
			$res["code"] = 0;
			$res["msg"] = "拆分有效期过低,已存在成功分享团";
			return $res;
		}
		for ($i = 0; $i < count($a); $i++) {
			if ($a[$i]["num"] > $peo_num - 1) {
				$res["code"] = 0;
				$res["msg"] = "已存在成功分享" . ($a[$i]["num"] + 1) . "人团,修改人数不可低于此数";
				return $res;
			}
		}
		return $res;
	}
	public function send_message($staff_id, $uid, $msg)
	{
		if (empty($this->config["template_id"])) {
			$temp = Db::name("ybmp_tmpl_dope")->where("mch_id", $this->bus_id2)->value("temp");
			$temp = json_decode($temp, true);
			if ($temp["AT1186"]) {
				$this->config["template_id"] = $temp["AT1186"];
			}
		}
		$rs = array("code" => 0, "info" => array());
		$name = Db::name("ybmp_user")->where("uid", $uid)->value("nick_name");
		if (empty($name)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$openid = Db::name("ybmp_user")->where("uid", $uid)->value("wx_openid");
		if (empty($openid)) {
			$rs["code"] = 1;
			$rs["msg"] = "openid不存在";
			return json_encode($rs);
		}
		$list = Db::name("ybmp_user_formid")->where("open_id", $openid)->order("id", "asc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$time = time() - 60 * 60 * 24 * 7;
				if ($v["create_time"] < $time) {
					Db::name("ybmp_user_formid")->where("id", $v["id"])->delete();
					continue;
				}
				if (isset($list[0]["form_id"])) {
					$form_id = $list[0]["form_id"];
				} else {
					$rs["code"] = 1;
					$rs["msg"] = "openid不存在";
					return json_encode($rs);
				}
				$info = $this->form_data($openid,$name, $staff_id, $msg);
				if ($info["errcode"]) {
					Db::name("ybmp_user_formid")->where("form_id", $list[0]["form_id"])->delete();
				}
				$info2 = json_decode($info, true);
				$rs["info"] = $info2;
				$rs["infofo"] = $info;
				Db::name("ybmp_user_formid")->where("form_id", $list[0]["form_id"])->delete();
			}
		}
		Db::name("ybmp_synlog")->insert(["mch_id" => -999, "media_id" => "web:" . json_encode($rs), "create_time" => date("Ymd/His", time())]);
		return json_encode($rs);
	}
	public function form_data($openid, $name, $staff_id, $msg)
	{
		$time = date("Y-m-d H:i:s", time());
		$data_arr = array("date2" => array("value" => $time, "color" => "#949494"), "thing3" => array("value" => $msg, "color" => "#949494"));
		$data["template_id"] = $this->config["template_id"];
		$data["page"] = "yb_mingpian/pages/card/index";
		$data["openid"] = $openid;
		$result = $this->push($data, $data_arr);
		return $result;
	}
	public function Push($data, $data_arr)
	{
		$params = array("touser" => $data["openid"], "template_id" => $data["template_id"], "page" => $data["page"], "form_id" => $data["formid"], "data" => $data_arr);
		$access_token = getWxAccessToken($this->bus_id2);
		if ($access_token["errcode"] == 0) {
			$push_url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $access_token["access_token"];
			$data = json_encode($params, true);
			$result = post_data2($push_url, $data);
			return $result;
		} else {
			return null;
		}
	}
}
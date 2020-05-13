<?php


namespace app\admin\service;

use Aliyun\DySDKLite\SignatureHelper;
use think\Cache;
use think\Db;
use think\Exception;
require EXTEND_PATH . "Aliyun/SignatureHelper.php";
class ArlikiService extends Base
{
	private $ali_id = '';
	private $ali_token = '';
	private $ali_name = '';
	private $ali_code = '';
	private $phone = '';
	private $status = "2";
	private $bus_id2 = '';
	function __construct($mch_id)
	{
		$this->bus_id2 = $mch_id;
		parent::__construct();
		$re = Db::name("ybmp_smsconf")->where("mch_id", $this->bus_id2)->find();
		$this->ali_id = $re["ali_id"];
		$this->ali_token = $re["ali_token"];
		$this->ali_name = $re["ali_name"];
		$this->ali_code = $re["ali_code"];
		$this->phone = $re["phone"];
		$this->status = $re["status"];
	}
	public function exp_load($no, $from = '')
	{
		$host = "http://wuliu.market.alicloudapi.com";
		$path = "/kdi";
		$method = "GET";
		$a = Db::name("ybmp_business_about")->where("mch_id", $this->bus_id2)->value("other");
		$a = json_decode($a, true);
		if (!isset($a["exp"]) || empty($a["exp"])) {
			$re["status"] = 1;
			$rs["msg"] = "请配置阿里云快递api";
			return $re;
		}
		$b = Db::name("ybmp_order_express")->alias("a")->join("ybmp_express_company d", "a.express_company_id=d.co_id", "left")->where("a.express_no", $no)->find();
		if (!empty($b["code"])) {
			$from = $b["code"];
		} else {
			$from = '';
		}
		$appcode = $a["exp"];
		$headers = array();
		array_push($headers, "Authorization:APPCODE " . $appcode);
		$querys = "no=" . $no . "&type=" . $from;
		$bodys = '';
		$url = $host . $path . "?" . $querys;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		if (1 == strpos("\$" . $host, "https://")) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		$res = json_decode(curl_exec($curl), true);
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
	public function send_sms($data, $phone = '', $return = false)
	{
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
		$helper = new SignatureHelper();
		$content = $helper->request($accessKeyId, $accessKeySecret, "dysmsapi.aliyuncs.com", array_merge($params, array("RegionId" => "cn-hangzhou", "Action" => "SendSms", "Version" => "2017-05-25")), $security);
		return $content;
	}
	public function get_skin()
	{
		$s = 2;
		$a = array(0 => "blue", 1 => "red", 2 => "blue", 3 => "green", 4 => "purple");
		$d = Cache::get("skin" . $this->bus_id);
		if (empty($d)) {
			$skin = Db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->find();
			if (!empty($skin["other"])) {
				$skin = json_decode($skin["other"], true);
				$s = intval($skin["zhuti"]["back"]);
			}
			Cache::set("skin" . $this->bus_id, $s);
		} else {
			$s = Cache::get("skin" . $this->bus_id);
		}
		return $a[$s];
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
						$a["key"] = md5($conf["rid"] . $list[$i]["share_id"] . $share . "Arliki");
						Db::startTrans();
						try {
							Db::name("ybmp_redshare")->where(["status" => 0, "rid" => $conf["id"], "share_id" => $list[$i]["share_id"]])->update(["status" => 1]);
							$a["user_id"] = $list[$i]["share_id"];
							$a["rmoney"] = $share;
							Db::name("ybmp_user_coupon")->insert($a);
							$user = Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "status" => 0, "share_id" => $list[$i]["share_id"]])->select();
							$user_id = '';
							for ($k = 0; $k < count($user); $k++) {
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
	public function check_outtime($share_id, $rid, $change = true)
	{
		$conf = Db::name("ybmp_red")->where("id", $rid)->field("split_time,create_time")->find();
		$check = Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->order("id desc")->value("create_time");
		$new = intval($conf["create_time"] + 3600 * $conf["split_time"]);
		if ($new > $check) {
			return 1;
		} else {
			if ($change) {
				Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0, "create_time" => [">", $new]])->update(["status" => 2]);
				Db::name("ybmp_redshare")->where(["status" => 0, "rid" => $rid, "share_id" => $share_id])->update(["status" => 1]);
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
		if (!empty($b) && $b["create_time"] + 3600 * $split_time <= time()) {
			$res["code"] = 0;
			$res["msg"] = "拆分有效期过低,最低需设置:" . ceil((time() - $b["create_time"]) / 3600) . "小时";
			return $res;
		}
		if (!empty($a)) {
			for ($i = 0; $i < count($a); $i++) {
				if ($a[$i]["num"] > $peo_num - 1) {
					$res["code"] = 0;
					$res["msg"] = "已存在成功分享" . ($a[$i]["num"] + 1) . "人团,修改人数不可低于此数";
					break;
				}
			}
		}
		return $res;
	}
}
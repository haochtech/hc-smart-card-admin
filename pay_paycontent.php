<?php


error_reporting(0);
define("IN_IA", '');
date_default_timezone_set("Asia/chongqing");
define("APP_PATH", __DIR__ . "/application/");
define("CONF_PATH", APP_PATH);
define("EXTEND_PATH", __DIR__ . "/extend/");
require __DIR__ . "/thinkphp/base.php";
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
use think\config;
use think\Db;
use think\Log;
use think\Exception;
$filename = APP_PATH . "database.php";
Config::load($filename, "database");
error_reporting(0);
$notify_data = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : file_get_contents("php://input");
if (!$notify_data) {
	exit("未收到回调");
}
$doc = new \DOMDocument();
$doc->loadXML($notify_data);
$out_trade_no = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
$data = array("isPay" => 1, "payTime" => time());
$data_pay = array("pay_status" => 1, "pay_time" => time());
$info = Db::name("ybmp_paycontent_orders")->where(["out_trade_no" => $out_trade_no, "isPay" => 0])->find();
if ($info) {
	Db::startTrans();
	try {
		Db::name("ybmp_paycontent_payment")->where(["out_trade_no" => $out_trade_no, "pay_status" => 0])->update($data_pay);
		$res = Db::name("ybmp_paycontent_orders")->where(["out_trade_no" => $out_trade_no, "isPay" => 0])->update($data);
		if ($info["price_id"] > 0) {
			$user = Db::name("ybmp_paycontent_user")->where(["uid" => $info["uid"], "mch_id" => $info["mch_id"]])->find();
			$time = time();
			if (empty($user)) {
				$udata["uid"] = $info["uid"];
				$udata["mch_id"] = $info["mch_id"];
				$udata["expire"] = $time + $info["day"] * 24 * 60 * 60 * $info["num"];
				Db::name("ybmp_paycontent_user")->insert($udata);
			} else {
				$user["expire"] = $user["expire"] < $time ? $time : $user["expire"];
				$expire = $user["expire"] + $info["day"] * 24 * 60 * 60 * $info["num"];
				Db::name("ybmp_paycontent_user")->where(["uid" => $info["uid"], "mch_id" => $info["mch_id"]])->update(["expire" => $expire]);
			}
		}
		if ($info["group_id"] > 0) {
			$cids = explode(",", $info["pay_id"]);
			for ($i = 0; $i < count($cids); $i++) {
				if ($cids[$i] > 0) {
					Db::name("ybmp_paycontent")->where(["id" => $cids[$i], "mch_id" => $info["mch_id"]])->setInc("buy_count_real");
				}
			}
			Db::name("ybmp_paycontent_group")->where(["out_trade_no" => $out_trade_no, "isPay" => 1])->setInc("buy_count_real");
		}
		if ($info["content_id"] > 0) {
			Db::name("ybmp_paycontent")->where(["id" => $info["content_id"], "mch_id" => $info["mch_id"]])->setInc("buy_count_real");
		}
		Db::commit();
	} catch (\Exception $e) {
		Db::rollback();
		Log::write($out_trade_no . ",支付状态更改失败" . $e, "paycontent_pay_state_change_fail");
	}
}
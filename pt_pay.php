<?php


define("IN_IA", '');
date_default_timezone_set("Asia/chongqing");
define("APP_PATH", __DIR__ . "/application/");
define("CONF_PATH", APP_PATH);
define("EXTEND_PATH", __DIR__ . "/extend/");
require __DIR__ . "/thinkphp/base.php";
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
require __DIR__ . "/application/api/service/OffwebService.php";
use app\api\service\OffwebService;
use think\config;
use think\Db;
use think\Log;
use think\Exception;
$filename = APP_PATH . "database.php";
Config::load($filename, "database");
$notify_data = file_get_contents("php://input");
if (!$notify_data) {
	$notify_data = $GLOBALS["HTTP_RAW_POST_DATA"] ?: '';
}
if (!$notify_data) {
	exit("未收到回调");
}
$doc = new \DOMDocument();
$doc->loadXML($notify_data);
$out_trade_no = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
$data = array("order_status" => 2, "isPay" => 1, "payTime" => time());
$data_pay = array("pay_status" => 1, "pay_time" => time());
$info = Db::name("ybmp_pt_orders")->where(["orderNum" => $out_trade_no, "isPay" => 0])->find();
if ($info) {
	Db::startTrans();
	try {
		Db::name("ybmp_pt_order_payment")->where(["out_trade_no" => $out_trade_no, "pay_status" => 0])->update($data_pay);
		if ($info["isGroup"] == 0) {
			$data["order_status"] = 3;
			$res = Db::name("ybmp_pt_orders")->where(["orderNum" => $out_trade_no, "order_status" => 1, "isPay" => 0])->update($data);
			if ($res) {
				Log::write($out_trade_no . ",支付状态更改成功_last", "pt_pay_state_change_success2");
			}
		} else {
			if ($info["pid"] != 0) {
				$groupNum = Db::name("ybmp_pt_goods")->where("id", $info["gid"])->value("groupNum");
				if ($info["pid"] != 0 && $info["isGroup"] == 1) {
					$count = Db::name("ybmp_pt_orders")->where(["pid" => $info["pid"], "isPay" => 1])->count();
					if ($groupNum == $count + 2) {
						$aa = Db::name("ybmp_pt_orders")->where("id", $info["pid"])->whereOr("pid", $info["pid"])->update(["order_status" => 3, "groupTime" => time(), "isPay" => 1]);
						if ($aa) {
							Log::write($out_trade_no . ",拼团状态更改", "pt_pay_state_change_success1");
						}
					} else {
						$res = Db::name("ybmp_pt_orders")->where(["orderNum" => $out_trade_no, "order_status" => 1, "isPay" => 0])->update($data);
						if ($res) {
							Log::write($out_trade_no . ",支付状态更改成功_last", "pt_pay_state_change_success2");
						}
					}
				}
			} else {
				$res = Db::name("ybmp_pt_orders")->where(["orderNum" => $out_trade_no, "order_status" => 1, "isPay" => 0])->update($data);
				if ($res) {
					Log::write($out_trade_no . ",支付状态更改成功_last", "pt_pay_state_change_success2");
				}
			}
			$ser = new OffwebService($info["mch_id"]);
			$r = $ser->sub_send($info["uid"], "拼团订单支付成功:" . $out_trade_no, "pt_order_pay");
		}
		Db::commit();
	} catch (\Exception $e) {
		Db::rollback();
		Log::write($out_trade_no . ",支付状态更改失败" . $e, "pt_pay_state_change_fail");
	}
}
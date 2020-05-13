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
$data = array("order_status" => 1, "pay_status" => 1, "pay_type" => 1, "pay_time" => time());
$data_pay = array("pay_status" => 1, "pay_time" => time());
Db::startTrans();
try {
	$info = Db::name("ybmp_activity_order")->where(["out_trade_no" => $out_trade_no, "order_status" => 0])->find();
	$res = Db::name("ybmp_activity_order")->where(["out_trade_no" => $out_trade_no, "order_status" => 0])->update($data);
	Db::name("ybmp_activity_order_payment")->where(["out_trade_no" => $out_trade_no, "pay_status" => 0])->update($data_pay);
	Db::commit();
	Log::write($out_trade_no . ",支付状态更改成功", "ms_pay_state_change_success");
	$ser = new OffwebService($info["mch_id"]);
	$r = $ser->sub_send($info["buyer_id"], "秒杀订单支付成功:" . $info["order_no"], "ms_order_pay");
} catch (\Exception $e) {
	Db::rollback();
	Log::write($out_trade_no . ",支付状态更改失败" . $e, "ms_pay_state_change_fail");
}
<?php


define("IN_IA", '');
date_default_timezone_set("Asia/chongqing");
define("APP_PATH", __DIR__ . "/application/");
define("CONF_PATH", APP_PATH);
define("EXTEND_PATH", __DIR__ . "/extend/");
require __DIR__ . "/thinkphp/base.php";
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
require __DIR__ . "/application/admin/service/AliyunService.php";
require __DIR__ . "/application/api/service/ArlikiService.php";
require __DIR__ . "/application/api/service/OffwebService.php";
use app\admin\service\AliyunService;
use app\api\service\ArlikiService;
use app\api\service\OffwebService;
use think\config;
use think\Db;
use think\Log;
$filename = APP_PATH . "database.php";
Config::load($filename, "database");
$push_config = array("appid" => "LTAIA6equwSykSCK", "secret" => "2OTqoYSU2XVZe28cfiJpjk1FD3BEqY", "appkey" => "24965934");
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
$data_jf = array("time" => time(), "itype" => 1, "ctype" => 1, "explain" => "消费");
$a = Db::name("ybmp_order")->where(["out_trade_no" => $out_trade_no, "order_status" => 0])->find();
if ($a) {
	$b = Db::name("ybmp_integral_rule")->where("mch_id", $a["mch_id"])->value("data");
	$jifen = 0;
	if ($b) {
		$b = json_decode($b, true);
		if ($b["cons_status"] == 1) {
			$order_money = $a["order_money"];
			$jifen = floor(floatval($order_money) / floatval($b["cons_money"])) * floatval($b["cons_integral"]);
			$c = Db::name("ybmp_user")->where("uid", $a["buyer_id"])->find();
			$jf_all = round($jifen) + intval($c["consume"]);
			$d = Db::name("ybmp_user_level")->where("mch_id", $a["mch_id"])->order("hierarchy", "desc")->select();
			if ($d) {
				for ($i = 0; $i < count($d); $i++) {
					if ($jf_all >= $d[$i]["hierarchy"]) {
						$new_data["level_id"] = $d[$i]["id"];
						$new_data["consume"] = $jf_all;
						$new_data["integral"] = round($jifen) + intval($c["integral"]);
						Db::name("ybmp_user")->where("uid", $a["buyer_id"])->update($new_data);
						$data_jf["user_id"] = $a["buyer_id"];
						$data_jf["integral"] = $jifen;
						$data_jf["consume"] = $jifen;
						$data_jf["mch_id"] = $a["mch_id"];
						$data_jf["order_id"] = $a["order_id"];
						Db::name("ybmp_integral_detail")->insert($data_jf);
						break;
					}
				}
			}
		}
	}
}
Db::startTrans();
try {
	$res = Db::name("ybmp_order")->where(["out_trade_no" => $out_trade_no, "order_status" => 0])->update($data);
	Db::name("ybmp_order_payment")->where(["out_trade_no" => $out_trade_no, "pay_status" => 0])->update($data_pay);
	Db::commit();
	if (!empty($res) && $res > 0) {
		$order = Db::name("ybmp_order")->where(["out_trade_no" => $out_trade_no])->find();
		$mid = $order["mch_id"];
		$user = Db::name("ybmp_business")->where(["id" => $mid])->find();
		$un_data["url"] = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data["url"]);
		$data["url"] = $un_url[0];
		$data["username"] = $user["name"];
		$data["uniacid"] = $user["uniacid"];
		$alias = md5($data["url"] . $data["username"] . $data["uniacid"]);
		$aliyun = new AliyunService();
		$aliyun->push($push_config, $alias);
		$sms = new ArlikiService($a["mch_id"]);
		$r = $sms->send_sms($a["order_no"], 2);
		$ser = new OffwebService($a["mch_id"]);
		$r = $ser->sub_send($order["buyer_id"], "订单支付成功:" . $a["order_no"], "order_pay");
		Log::write($out_trade_no . ",支付状态更改成功", "shop_pay_state_change_success");
	}
} catch (\Exception $e) {
	Db::rollback();
	Log::write($out_trade_no . ",支付状态更改失败" . $e, "shop_pay_state_change_fail");
}
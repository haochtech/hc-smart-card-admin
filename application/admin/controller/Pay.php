<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\log;
use think\Request;
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
use app\admin\service\AliyunService;
class Pay extends Controller
{
	public function notice()
	{
		$out_trade_no = Request::instance()->param("id");
		$config = array("appid" => "LTAIA6equwSykSCK", "secret" => "2OTqoYSU2XVZe28cfiJpjk1FD3BEqY", "appkey" => "24965934");
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
		$info = $aliyun->push($config, $alias);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function PayCallback()
	{
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
		Log::write($out_trade_no . ",支付完成", "shop_pay_success");
		$info = $this->pay_set($out_trade_no, 1);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function pay_set($out_trade_no, $pay_type)
	{
		$data = array("order_status" => 1, "pay_status" => 1, "pay_type" => $pay_type, "pay_time" => time());
		$data_pay = array("pay_status" => 1, "pay_time" => time());
		Db::startTrans();
		try {
			$res = Db::name("ybmp_order")->where(["out_trade_no" => $out_trade_no, "order_status" => 0])->update($data);
			Db::name("ybmp_order_payment")->where(["out_trade_no" => $out_trade_no, "pay_status" => 0])->update($data_pay);
			Db::commit();
			if (!empty($res) && $res > 0) {
				$this->notice($out_trade_no);
			}
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return "success";
	}
}
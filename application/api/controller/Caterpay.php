<?php


namespace app\api\controller;

use think\Request;
use think\Log;
use think\db;
use app\api\service\CaterPayService;
use app\common\model\Business;
use app\common\model\ResOrder;
use app\common\model\BusinessStamping;
require EXTEND_PATH . "Netprint/printhelper.php";
class Caterpay extends BaseController
{
	public function OrderInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["order_id" => Request::instance()->param("order_id")];
		$rule = [["order_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$pay = new CaterPayService();
		$info = $pay->orderInfo($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单信息为空";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Pay()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["out_trade_no" => Request::instance()->param("out_trade_no"), "openid" => Request::instance()->param("openid"), "mch_id" => $mch_id];
		$rule = [["out_trade_no", "require"], ["openid", "require"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$pay = new CaterPayService();
		$info = $pay->orderPay($data);
		if ($info["code"] == 1) {
			$rs["code"] = 1;
			$rs["msg"] = $info["msg"];
			return json_encode($rs);
		}
		$pay_info = array();
		foreach ($this->objectArray($info["info"]) as $value) {
			$pay_info = $value;
		}
		$pay_info["paySign"] = $pay_info["sign"];
		unset($pay_info["sign"]);
		$rs["info"] = $pay_info;
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
		Log::write($out_trade_no . ",支付完成", "cater_pay_success");
		$pay = new CaterPayService();
		$info = $pay->payCallback($out_trade_no, 1);
		$this->printorder($out_trade_no);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function printorder($out_trade_no)
	{
		$resorder = new ResOrder();
		$info = $resorder->where("out_trade_no", $out_trade_no)->where("is_print", "0")->find();
		if (empty($info)) {
			exit;
		}
		$url = "https://vip.ly100.wang/api/app/Aliyun/push?mch_id=" . $info["mch_id"];
		file_get_contents($url);
		$helper = new \PrintHelper();
		$print = new BusinessStamping();
		$print_list = $print->where("mch_id", $info["mch_id"])->select();
		$res = array();
		foreach ($print_list as $item) {
			$res[] = $helper->printHtmlContent($item["uuid"], "https://vip.ly100.wang/wap/count/ResOrderDishList?app_id=ZPRZIJNPF2&order_id=" . $info["order_id"], $item["open_user_id"]);
		}
		$resorder->where("out_trade_no", $out_trade_no)->update(["is_print" => 1]);
	}
}
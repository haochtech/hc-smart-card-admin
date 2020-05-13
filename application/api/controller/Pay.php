<?php


namespace app\api\controller;

use think\Request;
use think\Log;
use think\Db;
use app\api\service\PayService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Pay extends BaseController
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
		$pay = new PayService();
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
		$pay = new PayService();
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
		$file_path = $_SERVER["DOCUMENT_ROOT"] . "/public/aaa.txt";
		$fp = fopen($file_path, "w+");
		fwrite($fp, "wwwwwwwwww");
		fclose($fp);
		echo 111;
		die;
		$notify_data = file_get_contents("php://input");
		$file_path = $_SERVER["DOCUMENT_ROOT"] . "/public/test.txt";
		$fp = fopen($file_path, "w+");
		fwrite($fp, $notify_data);
		fclose($fp);
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
		$pay = new PayService();
		$info = $pay->payCallback($out_trade_no, 1);
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
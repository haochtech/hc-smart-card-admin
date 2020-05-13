<?php


namespace app\api\service;

use think\Db;
require_once BASE_ROOT . "core/extend/Wxpay/WxPay.Api.php";
class PayService
{
	private $o = "ybmp_order";
	private $og = "ybmp_order_goods";
	private $uc = "ybmp_user_coupon";
	private $s = "ybmp_goods_sku";
	private $g = "ybmp_goods";
	private $op = "ybmp_order_payment";
	private $i = "ybmp_images";
	private $dq = "ybmp_area";
	private $cf = "ybmp_config";
	public function orderInfo($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		$res = Db::name($this->dq)->where("id", $info["receiver_area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$rs["province"] = $pro["name"];
		$rs["city"] = $city["name"];
		$rs["district"] = $res["name"];
		$info["address"] = $rs;
		return $info;
	}
	public function orderPay($data)
	{
		$rs = array("code" => 0, "info" => array());
		$info = Db::name($this->op)->where("out_trade_no", $data["out_trade_no"])->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单不存在";
			return $rs;
		}
		if ($info->pay_status != 0) {
			$rs["code"] = 1;
			$rs["msg"] = "订单主题已改变";
			return $rs;
		}
		$GLOBALS["mch_id"] = $data["mch_id"];
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($info["pay_body"]);
		$input->SetOpenid($data["openid"]);
		$input->SetDetail($info["pay_detail"]);
		$input->SetTotal_fee($info["pay_money"] * 100);
		$input->SetOut_trade_no($data["out_trade_no"]);
		$input->SetTrade_type("JSAPI");
		$unifiedorder = \WxPayApi::unifiedOrder($input);
		if ($unifiedorder["return_code"] == "FAIL") {
			$rs["code"] = 1;
			$rs["msg"] = $unifiedorder["return_msg"];
			return $rs;
		}
		if ($unifiedorder["result_code"] == "FAIL") {
			$rs["code"] = 1;
			$rs["msg"] = $unifiedorder["err_code_des"];
			return $rs;
		}
		$res = $this->weixinapp($unifiedorder);
		$rs["info"] = $res;
		return $rs;
	}
	private function weixinapp($unifiedorder)
	{
		$param = Db::name($this->cf)->where("key", "WXPAY")->where("mch_id", $GLOBALS["mch_id"])->where("is_use", 1)->value("value");
		$param = json_decode($param, true);
		$input = new \WxPayJsApiPay();
		$input->SetAppid($param["APP_ID"]);
		$input->SetTimeStamp('' . time() . '');
		$input->SetNonceStr($this->createNoncestr());
		$input->SetPackage("prepay_id=" . $unifiedorder["prepay_id"]);
		$input->SetSignType("MD5");
		$input->SetSign();
		return $input;
	}
	private function createNoncestr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function payCallback($out_trade_no, $pay_type)
	{
		$data = array("order_status" => 1, "pay_status" => 1, "pay_type" => $pay_type, "pay_time" => time());
		$data_pay = array("pay_status" => 1, "pay_time" => time());
		Db::startTrans();
		try {
			Db::name($this->o)->where(["out_trade_no" => $out_trade_no])->update($data);
			Db::name($this->op)->where(["out_trade_no" => $out_trade_no])->update($data_pay);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return "success";
	}
}
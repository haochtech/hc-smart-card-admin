<?php


namespace app\api\service;

use think\Db;
use think\log;
use think\Exception;
require_once BASE_ROOT . "core/extend/Wxpay/WxPay.Api.php";
class MiaoshaService
{
	private $g = "ybmp_activity";
	public function get_msGoodsList($data, $page, $type)
	{
		$time = time();
		$data["status"] = 1;
		$data["is_del"] = 2;
		$data["etime"] = [">", $time];
		if ($type == 1) {
			$data["stime"] = ["<", $time];
		} else {
			$data["stime"] = [">", $time];
		}
		$list = Db::name($this->g)->where($data)->page($page, 10)->order("sort", "desc")->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if ($type == 1) {
					$where = ["activity_order_type" => 3, "order_status" => ["in", [1, 2, 3]], "pay_status" => 1, "is_deleted" => 0, "mch_id" => $data["mch_id"], "bargain_id" => $v["id"]];
					$list[$k]["is_buy"] = Db::name("ybmp_activity_order")->where($where)->count();
					$list[$k]["rest_sell"] = intval($list[$k]["all_sell"]) - intval($list[$k]["is_buy"]);
					$list[$k]["progress"] = round(intval($list[$k]["rest_sell"]) / intval($list[$k]["all_sell"]) * 100, 2);
				} else {
					$list[$k]["all_sell"] = 0;
					$list[$k]["rest_sell"] = 0;
					$list[$k]["progress"] = 100;
				}
			}
		}
		return $list;
	}
	public function get_msGoodsmodel($data)
	{
		$data["status"] = 1;
		$data["is_del"] = 2;
		$time = time();
		$list = Db::name($this->g)->where($data)->order("sort", "desc")->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if (intval($v["etime"]) < $time) {
					$list[$k]["status"] = 0;
				} else {
					if (intval($v["stime"]) > $time) {
						$list[$k]["status"] = 1;
						$list[$k]["all_sell"] = 0;
						$list[$k]["rest_sell"] = 0;
						$list[$k]["progress"] = 100;
					} else {
						$list[$k]["status"] = 2;
						$where = ["activity_order_type" => 3, "order_status" => ["in", [1, 2, 3]], "pay_status" => 1, "is_deleted" => 0, "mch_id" => $data["mch_id"], "bargain_id" => $v["id"]];
						$list[$k]["is_buy"] = Db::name("ybmp_activity_order")->where($where)->count();
						$list[$k]["rest_sell"] = intval($list[$k]["all_sell"]) - intval($list[$k]["is_buy"]);
						$list[$k]["progress"] = round(intval($list[$k]["rest_sell"]) / intval($list[$k]["all_sell"]) * 100, 2);
					}
				}
			}
		}
		return $list;
	}
	public function get_msGoodsDetail($data)
	{
		$data["status"] = 1;
		$data["is_del"] = 2;
		$time = time();
		$goodsInfo = Db::name($this->g)->where($data)->find();
		if (!$goodsInfo) {
			return null;
		}
		if (!empty($goodsInfo["img"])) {
			$goodsInfo["img"] = json_decode($goodsInfo["img"], true);
		} else {
			$goodsInfo["img"] = [];
		}
		$where = ["activity_order_type" => 3, "order_status" => ["in", [1, 2, 3]], "pay_status" => 1, "is_deleted" => 0, "mch_id" => $data["mch_id"], "bargain_id" => $goodsInfo["id"]];
		$goodsInfo["description"] = Db::name("ybmp_goods")->where(["goods_id" => $goodsInfo["goods_id"]])->value("description");
		$goodsInfo["is_buy"] = Db::name("ybmp_activity_order")->where($where)->count();
		$goodsInfo["rest_sell"] = intval($goodsInfo["all_sell"]) - intval($goodsInfo["is_buy"]);
		if (intval($goodsInfo["etime"]) < $time) {
			$goodsInfo["status"] = 0;
		} else {
			if (intval($goodsInfo["stime"]) > $time) {
				$goodsInfo["status"] = 1;
			} else {
				$goodsInfo["status"] = 2;
			}
		}
		return $goodsInfo;
	}
	public function createOrder($data)
	{
		$info = Db::name("ybmp_activity")->where("id", $data["bargain_id"])->find();
		$data["bargain_name"] = $info["name"];
		$data["bargain_pic"] = $info["pic"];
		$data["original_price"] = $info["nprice"];
		Db::startTrans();
		try {
			$id = Db::name("ybmp_activity_order")->strict(true)->insertGetId($data);
			$pay_data = array("out_trade_no" => $data["out_trade_no"], "pay_type" => $data["pay_type"], "type_alis_id" => $id, "pay_body" => "平台支付", "pay_detail" => "平台购买商品", "pay_money" => $data["pay_money"], "create_time" => time());
			Db::name("ybmp_activity_order_payment")->insert($pay_data);
			$ser = new OffwebService($data["mch_id"]);
			$r = $ser->sub_send($data["buyer_id"], "秒杀订单下单成功" . $data["order_no"], "ms_order_create");
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $id;
	}
	public function orderList($data, $page = 1)
	{
		$order_list = null;
		$order_list = Db::name("ybmp_activity_order")->where("buyer_id", $data["buyer_id"])->where("is_deleted", 0)->where("order_status", $data["order_status"])->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$order_list[$key]["sign_time"] = __TIME($value["sign_time"]);
			$order_list[$key]["pay_time"] = __TIME($value["pay_time"]);
			$order_list[$key]["consign_time"] = __TIME($value["consign_time"]);
		}
		return $order_list;
	}
	public function getOrder($data)
	{
		$order_info = null;
		$order_info = Db::name("ybmp_activity_order")->where($data)->where("is_deleted", 0)->find();
		if (empty($order_info)) {
			return $order_info;
		}
		$order_info["sign_time"] = __TIME($order_info["sign_time"]);
		$order_info["pay_time"] = __TIME($order_info["pay_time"]);
		$order_info["consign_time"] = __TIME($order_info["consign_time"]);
		$order_info["create_time"] = __TIME($order_info["create_time"]);
		$res = Db::name("ybmp_area")->where("id", $order_info["receiver_area"])->find();
		$city = Db::name("ybmp_area")->where("id", $res["pid"])->find();
		$pro = Db::name("ybmp_area")->where("id", $city["pid"])->find();
		$address["province"] = $pro["name"];
		$address["city"] = $city["name"];
		$address["district"] = $res["name"];
		$order_info["address"] = $address;
		return $order_info;
	}
	public function signOrder($data)
	{
		$info = Db::name("ybmp_activity_order")->where($data)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该订单状态异常！";
			return json_encode($rs);
		}
		$new_data = array("order_status" => 3, "sign_time" => time());
		$res = Db::name("ybmp_activity_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
	public function cancelOrder($data)
	{
		$info = Db::name("ybmp_activity_order")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$bargain = Db::name("ybmp_bargain")->where("id=" . $info["bargain_id"])->find();
		if (!empty($bargain)) {
			Db::name("ybmp_bargain")->where("id=" . $info["bargain_id"])->setInc("bargain_inventory", $info["total"]);
		}
		Db::startTrans();
		try {
			$new_data = array("order_status" => -1);
			$res = Db::name("ybmp_activity_order")->where(["order_id" => $data["order_id"]])->update($new_data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return $e->getMessage();
		}
		return $res;
	}
	public function delOrder($data)
	{
		$info = Db::name("ybmp_activity_order")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("is_deleted" => 1);
		$res = Db::name("ybmp_activity_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
	public function refundOrder($data)
	{
		$info = Db::name("ybmp_activity_order")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 4, "refund_time" => time());
		$res = Db::name("ybmp_activity_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		$ser = new OffwebService($info["mch_id"]);
		$r = $ser->sub_send($data["buyer_id"], "秒杀订单申请退款:" . $info["order_no"], "ms_order_refund");
		return $res;
	}
	public function checkOrder($buyer_id, $bargain_id)
	{
		$rs = Db::name("ybmp_activity_order")->where("buyer_id", $buyer_id)->where("bargain_id", $bargain_id)->where("activity_order_type", 1)->where("order_status", "<>", -1)->count();
		return $rs;
	}
	public function checkmsOrder($buyer_id, $bargain_id)
	{
		$act = Db::name("ybmp_activity")->where("id", $bargain_id)->find();
		if (empty($act["max_pre"]) || $act["max_pre"] == 0) {
			return 0;
		}
		$rs = Db::name("ybmp_activity_order")->where("buyer_id", $buyer_id)->where("bargain_id", $bargain_id)->where("activity_order_type", 3)->where("order_status", "<>", -1)->count();
		if ($act["max_pre"] > $rs) {
			return 0;
		}
		$where = ["activity_order_type" => 3, "order_status" => ["in", [1, 2, 3]], "pay_status" => 1, "is_deleted" => 0, "bargain_id" => $bargain_id];
		$is_buy = Db::name("ybmp_activity_order")->where($where)->count();
		if ($act["all_sell"] < $is_buy || $act["all_sell"] == $is_buy) {
			return -1;
		}
		return $rs;
	}
	public function orderInfo($data)
	{
		$info = Db::name("ybmp_activity_order")->where($data)->find();
		$res = Db::name("ybmp_area")->where("id", $info["receiver_area"])->find();
		$city = Db::name("ybmp_area")->where("id", $res["pid"])->find();
		$pro = Db::name("ybmp_area")->where("id", $city["pid"])->find();
		$rs["province"] = $pro["name"];
		$rs["city"] = $city["name"];
		$rs["district"] = $res["name"];
		$info["address"] = $rs;
		return $info;
	}
	public function orderPay($data)
	{
		$rs = array("code" => 0, "info" => array());
		$info = Db::name("ybmp_activity_order_payment")->where("out_trade_no", $data["out_trade_no"])->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单不存在";
			return $rs;
		}
		if ($info["pay_status"] != 0) {
			$rs["code"] = 1;
			$rs["msg"] = "订单主题已改变";
			return $rs;
		}
		$GLOBALS["mch_id"] = $data["mch_id"];
		$GLOBALS["key"] = "miaosha";
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
		$param = Db::name("ybmp_config")->where("key", "WXPAY")->where("mch_id", $GLOBALS["mch_id"])->where("is_use", 1)->value("value");
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
}
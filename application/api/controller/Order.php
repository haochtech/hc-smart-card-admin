<?php


namespace app\api\controller;

use app\api\service\OffwebService;
use think\Request;
use think\Log;
use app\api\service\OrderService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
require_once BASE_ROOT . "core/application/api/service/ArlikiService.php";
use app\api\service\ArlikiService;
class Order extends BaseController
{
	public function CreateOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["order_no" => $this->createOrderNo(), "out_trade_no" => $this->createOutTradeNo(), "pay_type" => Request::instance()->param("pay_type", "1"), "shipping_type" => Request::instance()->param("shipping_type", "1"), "shipping_money" => Request::instance()->param("shipping_money", 0), "shipping_company_id" => Request::instance()->param("shipping_company_id", 0), "buyer_message" => Request::instance()->param("buyer_message", ''), "receiver_zip" => Request::instance()->param("receiver_zip"), "discount_money" => Request::instance()->param("discount_money", "0.00"), "coupon_id" => Request::instance()->param("coupon_id", 0), "coupon_money" => Request::instance()->param("coupon_money", "0.00"), "rebate_money" => Request::instance()->param("rebate_money", "0.00"), "activity_order_type" => Request::instance()->param("activity_order_type", 0), "create_time" => time(), "buyer_id" => Request::instance()->param("buyer_id"), "user_name" => Request::instance()->param("user_name"), "receiver_mobile" => Request::instance()->param("phone"), "receiver_area" => Request::instance()->param("area", 0), "receiver_address" => Request::instance()->param("address", 0), "receiver_name" => Request::instance()->param("consigner"), "order_money" => Request::instance()->param("order_money"), "sku_id" => Request::instance()->param("sku_id"), "mch_id" => $mch_id, "mailing_type" => Request::instance()->param("mailing_type", 1), "mention_time" => Request::instance()->param("mention_time")];
		$data["pay_money"] = round(floatval($data["order_money"]) - floatval($data["rebate_money"]) + floatval($data["shipping_money"]) - floatval($data["coupon_money"]) - floatval($data["discount_money"]), 2);
		if ($data["discount_money"] != "0.00" && $data["coupon_money"] != "0.00") {
			$data["activity_order_type"] = 4;
		}
		$rule = [["buyer_id", "require|number"], ["user_name", "require"], ["mailing_type", "require"], ["receiver_mobile", "require|number|length:11"], ["receiver_name", "require"], ["order_money", "require|number"], ["pay_money", "require|number"], ["sku_id", "require"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$staff_id = Request::instance()->param("staff_id", 0);
		$order = new OrderService();
		$info = $order->createOrder($data, $staff_id);
		if ($info["err_code"] == 0) {
			$ser = new ArlikiService($mch_id);
			$ser->send_sms($data["order_no"]);
			$off = new OffwebService($mch_id);
			$r = $off->sub_send($data["buyer_id"], "成功下单:" . $data["order_no"], "order_create");
			$rs["info"] = $info["info"];
			return json_encode($rs);
		} else {
			$rs["code"] = 1;
			$rs["msg"] = $info["msg"];
			return json_encode($rs);
		}
	}
	public function OrderList()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$status = Request::instance()->param("status", null);
		$page = Request::instance()->param("page", 1);
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["buyer_id", "require", "uid不能为空"], ["mch_id", "require", "不存在商户"]];
		$data = ["buyer_id" => $uid, "mch_id" => $mch_id];
		if ($status != null) {
			if ($status == 4) {
				$data["order_status"] = ["in", [4, 5]];
			} else {
				$data["order_status"] = $status;
			}
		} else {
			$data["order_status"] = ["<>", "-1"];
		}
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->orderList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["buyer_id", "require", "uid不能为空"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->getOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SignOrder()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["buyer_id", "require"], ["order_id", "require"], ["mch_id", "require"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => 2, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->signOrder($data);
		return $info;
	}
	public function CancelOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["buyer_id", "require"], ["order_id", "require"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => 0];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->cancelOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单取消失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["buyer_id", "require"], ["order_id", "require"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => ["in", [0, -1, 3, 5]]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->delOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单删除失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function RefundOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["buyer_id", "require"], ["order_id", "require"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => ["in", [1, 2, 3]]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->refundOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单退款失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
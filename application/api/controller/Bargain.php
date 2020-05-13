<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
use think\Request;
use think\log;
use app\api\service\BargainService;
class Bargain extends BaseController
{
	public function BarIndex()
	{
		$rs = array("code" => 0, "info" => array());
		$app_key = Request::instance()->param("i");
		$app_id = $this->getMchId($app_key);
		$data = ["agents_id" => $app_id];
		$rule = [["agents_id", "require", "不存在该应用"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new BargainService();
		$info = $index->barIndex($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Bargain()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$data = ["class_id" => Request::instance()->param("class_id"), "type" => Request::instance()->param("type", 1), "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$data = array_filter($data);
		$bargain = new BargainService();
		$info = $bargain->getBargainList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GoodsInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["id" => Request::instance()->param("id"), "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$data = array_filter($data);
		$bargain = new BargainService();
		$info = $bargain->getGoodsInfo($data);
		if (empty($info["bargain_info"])) {
			$rs["code"] = 1;
			$rs["msg"] = "该活动不存在或已被删除";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function BargainInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["id" => Request::instance()->param("id"), "user_id" => Request::instance()->param("user_id"), "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"], ["user_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$data = array_filter($data);
		$bargain = new BargainService();
		$info = $bargain->getBargainInfo($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function BargainCreate()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["user_id" => Request::instance()->param("user_id"), "bargain_id" => Request::instance()->param("bargain_id"), "mch_id" => $mch_id];
		$rule = [["user_id", "require|number"], ["bargain_id", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new BargainService();
		$info = $order->BargainCreate($data);
		if (empty($info)) {
			Log::write("发起砍价失败 --" . $data["user_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "发起砍价失败";
			return json_encode($rs);
		}
		if ($info == "exist") {
			$rs["code"] = 1;
			$rs["msg"] = "您已经砍过价了";
			return json_encode($rs);
		}
		if ($info == "lose") {
			$rs["code"] = 1;
			$rs["msg"] = "活动不存在,请再次尝试";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function BargainHelp()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["user_id" => Request::instance()->param("user_id"), "iInitiated_id" => Request::instance()->param("iInitiated_id")];
		$rule = [["user_id", "require|number"], ["iInitiated_id", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new BargainService();
		$info = $order->BargainHelp($data);
		if (empty($info)) {
			Log::write("帮砍价失败 --" . $data["user_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "帮砍价失败";
			return json_encode($rs);
		}
		if ($info == "exist") {
			$rs["code"] = 1;
			$rs["msg"] = "您已经帮助TA砍过价了";
			return json_encode($rs);
		}
		if ($info == "bargain_lose") {
			$rs["code"] = 1;
			$rs["msg"] = "砍价不存在,请再次尝试";
			return json_encode($rs);
		}
		if ($info == "lose") {
			$rs["code"] = 1;
			$rs["msg"] = "活动不存在,请再次尝试";
			return json_encode($rs);
		}
		if ($info == "max") {
			$rs["code"] = 1;
			$rs["msg"] = "帮砍人数已达上限";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function BargainRecord()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["iInitiated_id" => Request::instance()->param("iInitiated_id"), "mch_id" => $mch_id];
		$rule = [["iInitiated_id", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new BargainService();
		$info = $bargain->getBargainRecord($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function MyBargain()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$data = ["user_id" => Request::instance()->param("user_id"), "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在应用"], ["user_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new BargainService();
		$info = $bargain->getMyBargain($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CreateOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["order_no" => $this->createOrderNo(), "out_trade_no" => $this->createOutTradeNo(), "pay_type" => Request::instance()->param("pay_type", "1"), "shipping_type" => Request::instance()->param("shipping_type", "1"), "shipping_money" => Request::instance()->param("shipping_money", 0), "shipping_company_id" => Request::instance()->param("shipping_company_id", 0), "buyer_message" => Request::instance()->param("buyer_message", ''), "receiver_zip" => Request::instance()->param("receiver_zip"), "activity_order_type" => Request::instance()->param("activity_order_type", "0"), "create_time" => time(), "bargain_id" => Request::instance()->param("bargain_id"), "buyer_id" => Request::instance()->param("buyer_id"), "user_name" => Request::instance()->param("user_name"), "receiver_mobile" => Request::instance()->param("phone"), "receiver_area" => Request::instance()->param("area"), "receiver_address" => Request::instance()->param("address"), "receiver_name" => Request::instance()->param("consigner"), "order_money" => Request::instance()->param("order_money"), "pay_money" => Request::instance()->param("pay_money"), "total" => Request::instance()->param("total"), "mch_id" => $mch_id];
		$rule = [["buyer_id", "require|number"], ["user_name", "require"], ["receiver_mobile", "require|number|length:11"], ["receiver_area", "require|number"], ["receiver_address", "require"], ["receiver_name", "require"], ["order_money", "require|number"], ["pay_money", "require|number"], ["total", "require"], ["bargain_id", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new BargainService();
		if ($data["activity_order_type"] == 1) {
			$count = $order->checkOrder($data["buyer_id"], $data["bargain_id"]);
			if ($count > 0) {
				$rs["code"] = 1;
				$rs["msg"] = "您已参与该商品的优惠活动";
				return json_encode($rs);
			}
		} elseif ($data["activity_order_type"] == 3) {
			$count = $order->checkmsOrder($data["buyer_id"], $data["bargain_id"]);
			if ($count > 0) {
				$rs["code"] = 1;
				$rs["msg"] = "您参与活动的次数已达上限";
				return json_encode($rs);
			}
		}
		$info = $order->createOrder($data);
		if (empty($info)) {
			Log::write("生成订单失败 --" . $data["buyer_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "生成订单失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderList()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$status = Request::instance()->param("status", null);
		$page = Request::instance()->param("page", 1);
		$type = Request::instance()->param("mtype", 1);
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["buyer_id" => $uid, "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在应用"], ["buyer_id", "require", "uid不能为空"]];
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
		$order = new BargainService();
		$info = $order->orderList($data, $page, $type);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["buyer_id", "require", "uid不能为空"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new BargainService();
		$info = $order->getOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SignOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["buyer_id", "require"], ["order_id", "require"]];
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => 2];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new BargainService();
		$info = $order->signOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
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
		$order = new BargainService();
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
		$order = new BargainService();
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
		$order = new BargainService();
		$info = $order->refundOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单退款失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
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
		$pay = new BargainService();
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
		$rule = [["out_trade_no", "require"], ["openid", "require"], ["mch_id", "require", "不存在应用"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$pay = new BargainService();
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
}
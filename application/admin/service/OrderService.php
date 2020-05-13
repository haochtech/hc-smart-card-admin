<?php


namespace app\admin\service;

use app\common\model\ExpressCompany;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Images;
use app\common\model\Area;
use think\Db;
class OrderService extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->order = new Order();
	}
	public function orderDelivery($order_id, $express_name, $shipping_type, $express_company_id, $express_no)
	{
		$order_express = new OrderExpress();
		$retval = $order_express->delivey($order_id, $express_name, $shipping_type, $express_company_id, $express_no);
		if ($retval) {
			$params = ["order_id" => $order_id, "order_goods_id_array" => '', "express_name" => $express_name, "shipping_type" => $shipping_type, "express_company_id" => $express_company_id, "express_no" => $express_no];
		}
		return $retval;
	}
	public function updateOrderReceiveDetail($order_id, $receiver_mobile, $receiver_province, $receiver_city, $receiver_district, $receiver_address, $receiver_zip, $receiver_name)
	{
		$order = new Order();
		if ($receiver_district == "-1") {
			$receiver_district = $receiver_city;
		}
		$data = array("receiver_mobile" => $receiver_mobile, "receiver_area" => $receiver_district, "receiver_address" => $receiver_address, "receiver_zip" => $receiver_zip, "receiver_name" => $receiver_name);
		$retval = $order->save($data, ["order_id" => $order_id]);
		return $retval;
	}
	public function getOrderList($condition = '', $search_text = '', $order = '')
	{
		$order_model = new Order();
		$order_list = $order_model->getPageLisy($condition, $search_text, $order);
		if (!empty($order_list)) {
			foreach ($order_list as $k => $v) {
				$order_item = new OrderGoods();
				$order_item_list = $order_item->where(["order_id" => $v["order_id"]])->select();
				$county_name = '';
				$city_name = '';
				$province_name = '';
				$area = new Area();
				$county_info = $area->getInfo(array("id" => $v["receiver_area"]), "*");
				$city_info = $area->getInfo(array("id" => $county_info["pid"]), "*");
				$province_info = $area->getInfo(array("id" => $city_info["pid"]), "*");
				if (count($county_info) > 0) {
					$county_name = $county_info["name"];
					$city_name = $city_info["name"];
					$province_name = $province_info["name"];
				}
				if ($v["shipping_status"] == 1) {
					$a = Db::name("ybmp_order_express")->where("order_id", $v["order_id"])->field("express_no,express_company")->find();
					$order_list[$k]["express_no"] = $a["express_no"];
					$order_list[$k]["express_company"] = $a["express_company"];
				}
				$order_list[$k]["receiver_province_name"] = $province_name;
				$order_list[$k]["receiver_city_name"] = $city_name;
				$order_list[$k]["receiver_county_name"] = $county_name;
				foreach ($order_item_list as $key_item => $v_item) {
					$picture = new Images();
					$goods_picture = $picture->get($v_item["goods_images"]);
					if (empty($goods_picture)) {
						$goods_picture = array("img_cover" => '', "img_cover_big" => '', "img_cover_mid" => '', "img_cover_small" => '', "img_cover_micro" => '', "upload_type" => 1, "domain" => '');
					}
					$order_item_list[$key_item]["picture"] = $goods_picture;
				}
				$order_list[$k]["order_item_list"] = $order_item_list;
				$order_list[$k]["operation"] = '';
				$order_status = OrderService::getOrderStatus();
				foreach ($order_status as $k_status => $v_status) {
					if ($v_status["status_id"] == $v["order_status"]) {
						$order_list[$k]["status_name"] = $v_status["status_name"];
					}
				}
			}
			return $order_list;
		}
	}
	public function getOrderListRefund($condition = '', $search_text = '', $order = '')
	{
		$order_model = new Order();
		$order_list = $order_model->getPageLisy($condition, $search_text, $order);
		if (!empty($order_list)) {
			foreach ($order_list as $k => $v) {
				$order_item = new OrderGoods();
				$order_item_list = $order_item->where(["order_id" => $v["order_id"]])->select();
				$county_name = '';
				$city_name = '';
				$province_name = '';
				$area = new Area();
				$county_info = $area->getInfo(array("id" => $v["receiver_area"]), "*");
				$city_info = $area->getInfo(array("id" => $county_info["pid"]), "*");
				$province_info = $area->getInfo(array("id" => $city_info["pid"]), "*");
				if (count($county_info) > 0) {
					$county_name = $county_info["name"];
					$city_name = $city_info["name"];
					$province_name = $province_info["name"];
				}
				$order_list[$k]["receiver_province_name"] = $province_name;
				$order_list[$k]["receiver_city_name"] = $city_name;
				$order_list[$k]["receiver_county_name"] = $county_name;
				foreach ($order_item_list as $key_item => $v_item) {
					$picture = new Images();
					$goods_picture = $picture->get($v_item["goods_images"]);
					if (empty($goods_picture)) {
						$goods_picture = array("img_cover" => '', "img_cover_big" => '', "img_cover_mid" => '', "img_cover_small" => '', "img_cover_micro" => '', "upload_type" => 1, "domain" => '');
					}
					$order_item_list[$key_item]["picture"] = $goods_picture;
				}
				$order_list[$k]["order_item_list"] = $order_item_list;
				$order_list[$k]["operation"] = '';
				$order_status = OrderService::getOrderRefund();
				foreach ($order_status as $k_status => $v_status) {
					if ($v_status["status_id"] == $v["order_status"]) {
						$order_list[$k]["status_name"] = $v_status["status_name"];
					}
				}
			}
			return $order_list;
		}
	}
	public function addOrderSellerMemo($order_id, $memo)
	{
		$order = new Order();
		$data = array("seller_memo" => $memo);
		$retval = $order->save($data, ["order_id" => $order_id]);
		return $retval;
	}
	public function deleteOrder($order_id, $operator_type)
	{
		$order_model = new Order();
		$data = array("is_deleted" => 1);
		$order_id_array = explode(",", $order_id);
		if ($operator_type == 1) {
			$res = $order_model->save($data, ["order_status" => -1, "order_id" => ["in", $order_id_array]]);
		}
		return $res;
	}
	public function OrderTakeDelivery($orderid)
	{
		$this->order->startTrans();
		try {
			$data_take_delivery = array("shipping_status" => 2, "order_status" => 3, "sign_time" => time());
			$order_model = new Order();
			$order_model->save($data_take_delivery, ["order_id" => $orderid]);
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	public function orderTakeRefund($orderid)
	{
		$this->order->startTrans();
		try {
			$data_take_delivery = array("order_status" => 5, "finish_time" => time());
			$order_model = new Order();
			$order_model->save($data_take_delivery, ["order_id" => $orderid]);
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	public function orderGoodsDelivery($order_id)
	{
		$order_goods = new \app\admin\service\OrderGoods();
		$retval = $order_goods->orderGoodsDelivery($order_id, '');
		return $retval;
	}
	public function getOrderDetail($order_id)
	{
		$detail = $this->getDetail($order_id);
		if (empty($detail)) {
			return array();
		}
		$detail["shipping_status_name"] = $this->getShippingInfo($detail["shipping_status"])["status_name"];
		$order_goods_list = array();
		$order_goods_delive = array();
		$order_goods_exprss = array();
		foreach ($detail["order_goods"] as $order_goods_obj) {
			$shipping_status = $order_goods_obj["shipping_status"];
			if ($shipping_status == 0) {
				$order_goods_list[] = $order_goods_obj;
			} else {
				$order_goods_delive[] = $order_goods_obj;
			}
		}
		$detail["order_goods_no_delive"] = $order_goods_list;
		if (!empty($order_goods_delive) && count($order_goods_delive) > 0) {
			foreach ($order_goods_delive as $goods_obj) {
				$is_have = false;
				$order_goods_id = $goods_obj["order_goods_id"];
				if (!$is_have) {
					$order_goods_exprss[] = $goods_obj;
				}
			}
		}
		$goods_packet_list = array();
		if (count($order_goods_exprss) > 0) {
			$packet_obj = array("packet_name" => "无需物流", "express_name" => '', "express_code" => '', "express_id" => 0, "is_express" => 0, "order_goods_list" => $order_goods_exprss);
			$goods_packet_list[] = $packet_obj;
		}
		if (!empty($express_list) && count($express_list) > 0 && count($order_goods_delive) > 0) {
			$packet_num = 1;
			foreach ($express_list as $express_obj) {
				$packet_goods_list = array();
				$order_goods_id_array = $express_obj["order_goods_id_array"];
				$goods_id_str = explode(",", $order_goods_id_array);
				foreach ($order_goods_delive as $delive_obj) {
					$order_goods_id = $delive_obj["order_goods_id"];
					if (in_array($order_goods_id, $goods_id_str)) {
						$packet_goods_list[] = $delive_obj;
					}
				}
				$packet_obj = array("packet_name" => "包裹  + " . $packet_num, "express_name" => $express_obj["express_name"], "express_code" => $express_obj["express_no"], "express_id" => $express_obj["id"], "is_express" => 1, "order_goods_list" => $packet_goods_list);
				$packet_num = $packet_num + 1;
				$goods_packet_list[] = $packet_obj;
			}
		}
		$detail["goods_packet_list"] = $goods_packet_list;
		return $detail;
	}
	public function orderDoDelivery($orderid)
	{
		$this->order->startTrans();
		try {
			$data_delivery = array("shipping_status" => 1, "order_status" => 2, "consign_time" => time());
			$order_model = new Order();
			$order_model->save($data_delivery, ["order_id" => $orderid]);
			$this->order->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order->rollback();
			return $e->getMessage();
		}
	}
	public function getDetail($order_id)
	{
		$order_detail = $this->order->getInfo(["order_id" => $order_id, "is_deleted" => 0]);
		if (empty($order_detail)) {
			return array();
		}
		$express_company_name = '';
		if ($order_detail["shipping_type"] == 1) {
			$order_detail["shipping_type_name"] = "商家配送";
			$express_company = new ExpressCompany();
			$express_obj = $express_company->getInfo(["co_id" => $order_detail["shipping_company_id"]], "company_name");
			if (!empty($express_obj["company_name"])) {
				$express_company_name = $express_obj["company_name"];
			}
		} elseif ($order_detail["shipping_type"] == 2) {
			$order_detail["shipping_type_name"] = "门店自提";
		} else {
			$order_detail["shipping_type_name"] = '';
		}
		$order_detail["shipping_company_name"] = $express_company_name;
		$order_detail["order_goods"] = $this->getOrderGoods($order_id);
		$order_status = OrderService::getOrderStatus();
		$order_detail["order_pickup"] = '';
		foreach ($order_status as $k_status => $v_status) {
			if ($v_status["status_id"] == $order_detail["order_status"]) {
				$order_detail["status_name"] = $v_status["status_name"];
			}
		}
		$area = new Area();
		$county_info = $area->getInfo(array("id" => $order_detail["receiver_area"]), "*");
		$city_info = $area->getInfo(array("id" => $county_info["pid"]), "*");
		$province_info = $area->getInfo(array("id" => $city_info["pid"]), "*");
		$order_detail["address"] = $province_info["name"] . $city_info["name"] . $county_info["name"];
		return $order_detail;
	}
	public function getOrderGoods($order_id)
	{
		$order_goods = new OrderGoods();
		$order_goods_list = $order_goods->all(["order_id" => $order_id]);
		foreach ($order_goods_list as $k => $v) {
			$picture = new Images();
			$picture_info = $picture->get($v["goods_images"]);
			$order_goods_list[$k]["picture_info"] = $picture_info;
			$order_goods_list[$k]["refund_operation"] = '';
			$order_goods_list[$k]["status_name"] = '';
		}
		return $order_goods_list;
	}
	public function OrderDeliver($order_id)
	{
		$order = new Order();
		$data = array("order_status" => 2, "consign_time" => time());
		$res = $order->save($data, ["order_id" => $order_id]);
		return $res;
	}
	public static function getShippingInfo($shipping_status_id)
	{
		$shipping_status = OrderService::getShippingStatus();
		$info = null;
		foreach ($shipping_status as $shipping_info) {
			if ($shipping_status_id == $shipping_info["shipping_status"]) {
				$info = $shipping_info;
				break;
			}
		}
		return $info;
	}
	public static function getShippingStatus()
	{
		$shipping_status = array(array("shipping_status" => "0", "status_name" => "待发货"), array("shipping_status" => "1", "status_name" => "已发货"), array("shipping_status" => "2", "status_name" => "已收货"), array("shipping_status" => "3", "status_name" => "备货中"));
		return $shipping_status;
	}
	public static function getOrderRefund()
	{
		$status = array(array("status_id" => "4", "status_name" => "待退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()), array("status_id" => "5", "status_name" => "已退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()));
		return $status;
	}
	public static function getOrderStatus()
	{
		$status = array(array("status_id" => "0", "status_name" => "待支付", "is_refund" => 0, "operation" => array("0" => array("no" => "pay", "name" => "线下支付", "color" => "#FF9800"), "1" => array("no" => "close", "color" => "#E61D1D", "name" => "交易关闭"), "2" => array("no" => "adjust_price", "color" => "#4CAF50", "name" => "修改价格"), "3" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array("0" => array("no" => "pay", "name" => "去支付", "color" => "#F15050"), "1" => array("no" => "close", "name" => "关闭订单", "color" => "#999999"))), array("status_id" => "1", "status_name" => "待发货", "is_refund" => 1, "operation" => array("0" => array("no" => "delivery", "color" => "green", "name" => "发货"), "1" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注"), "2" => array("no" => "update_address", "color" => "#51A351", "name" => "修改地址")), "member_operation" => array()), array("status_id" => "2", "status_name" => "已发货", "is_refund" => 1, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注"), "1" => array("no" => "logistics", "color" => "#51A351", "name" => "查看物流")), "member_operation" => array("0" => array("no" => "getdelivery", "name" => "确认收货", "color" => "#FF6600"))), array("status_id" => "3", "status_name" => "已签收", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注"), "1" => array("no" => "logistics", "color" => "#51A351", "name" => "查看物流")), "member_operation" => array()), array("status_id" => "4", "status_name" => "待退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()), array("status_id" => "5", "status_name" => "已退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()), array("status_id" => "-1", "status_name" => "已取消", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()));
		return $status;
	}
	public static function getOrderStatus2()
	{
		$status = array(array("status_id" => "0", "status_name" => "待支付", "is_refund" => 0, "operation" => array("0" => array("no" => "pay", "name" => "线下支付", "color" => "#FF9800"), "1" => array("no" => "close", "color" => "#E61D1D", "name" => "交易关闭"), "2" => array("no" => "adjust_price", "color" => "#4CAF50", "name" => "修改价格"), "3" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array("0" => array("no" => "pay", "name" => "去支付", "color" => "#F15050"), "1" => array("no" => "close", "name" => "关闭订单", "color" => "#999999"))), array("status_id" => "1", "status_name" => "待提货", "is_refund" => 1, "operation" => array("0" => array("no" => "delivery", "color" => "green", "name" => "发货"), "1" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注"), "2" => array("no" => "update_address", "color" => "#51A351", "name" => "修改地址")), "member_operation" => array()), array("status_id" => "3", "status_name" => "已提货", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注"), "1" => array("no" => "logistics", "color" => "#51A351", "name" => "查看物流")), "member_operation" => array()), array("status_id" => "4", "status_name" => "待退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()), array("status_id" => "5", "status_name" => "已退款", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()), array("status_id" => "-1", "status_name" => "已取消", "is_refund" => 0, "operation" => array("0" => array("no" => "seller_memo", "color" => "#8e8c8c", "name" => "添加备注")), "member_operation" => array()));
		return $status;
	}
	public function getCity($area_id)
	{
		$rs = array();
		$res = Area::get($area_id);
		$city = Area::get($res["pid"]);
		$pro = Area::get($city["pid"]);
		$rs["province_id"] = $pro["id"];
		$rs["city_id"] = $city["id"];
		$rs["district_id"] = $res["id"];
		$rs["province_name"] = $pro["name"];
		$rs["city_name"] = $city["name"];
		$rs["district_name"] = $res["name"];
		return $rs;
	}
	public function getOrderSellerMemo($order_id)
	{
		$order = new Order();
		$res = $order->getInfo(["order_id" => $order_id], "*");
		return $res["seller_memo"];
	}
	public function getOrderReceiveDetail($order_id)
	{
		$order = new Order();
		$res = $order->getInfo(["order_id" => $order_id], "order_id,receiver_mobile,receiver_area,receiver_address,receiver_zip,receiver_name");
		return $res;
	}
}
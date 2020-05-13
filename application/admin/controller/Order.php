<?php


namespace app\admin\controller;

use app\admin\service\Area;
use think\Db;
use think\Log;
use think\Request;
use app\admin\service\OrderService;
header("content-type:text/html;charset=utf-8;");
require_once EXTEND_PATH . "Wxpay/WxPay.Api.php";
class Order extends Base
{
	public function __construct()
	{
		parent::__construct();
		$info = db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->find();
		if (!empty($info["other"])) {
			$info["exp"] = json_decode($info["other"], true)["exp"];
			if (!empty($info["exp"])) {
				$this->assign("show_exp", 1);
			}
		}
	}
	public function OrderList()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$order = new OrderService();
		$search_text = Request::instance()->post("order_no");
		$ttime = time();
		$outtimes = Db::name("ybmp_order")->where("refund_time", 0)->where("order_status", 2)->where("finish_time", 0)->where("consign_time > 0 AND consign_time < " . ($ttime - 10 * 24 * 60 * 60))->select();
		foreach ($outtimes as $out) {
			$order_id = $out["order_id"];
			$op = Db::name("ybmp_order")->where(["order_id" => $order_id, "order_status" => 2])->update(["finish_time" => $ttime, "order_status" => 3]);
			if ($op) {
				$order_info = Db::name("ybmp_order")->where(["order_id" => $order_id])->find();
				$us = Db::name("ybmp_order_share")->where(["user_id" => $order_info["buyer_id"], "order_id" => $order_id])->find();
				if ($us) {
					$share_data = ["mch_id" => $this->bus_id, "order_id" => $order_id, "create_time" => time(), "is_del" => 1];
					if ($us["parent_id_1"] && $us["parent_id_1"] != 0) {
						$share_data["user_id"] = $us["parent_id_1"];
						$share_data["money"] = $us["first_price"];
						$share_data["source"] = 1;
						Db::name("ybmp_user_share_money")->insert($share_data);
						Db::name("ybmp_user")->where("uid", $us["parent_id_1"])->setInc("total_price", $us["first_price"]);
						Db::name("ybmp_user")->where("uid", $us["parent_id_1"])->setInc("price", $us["first_price"]);
					}
					if ($us["parent_id_2"] && $us["parent_id_2"] != 0) {
						$share_data["user_id"] = $us["parent_id_2"];
						$share_data["money"] = $us["second_price"];
						$share_data["source"] = 2;
						Db::name("ybmp_user_share_money")->insert($share_data);
						Db::name("ybmp_user")->where("uid", $us["parent_id_2"])->setInc("total_price", $us["second_price"]);
						Db::name("ybmp_user")->where("uid", $us["parent_id_2"])->setInc("price", $us["second_price"]);
					}
					if ($us["parent_id_3"] && $us["parent_id_3"] != 0) {
						$share_data["user_id"] = $us["parent_id_3"];
						$share_data["money"] = $us["third_price"];
						$share_data["source"] = 3;
						Db::name("ybmp_user_share_money")->insert($share_data);
						Db::name("ybmp_user")->where("uid", $us["parent_id_3"])->setInc("total_price", $us["third_price"]);
						Db::name("ybmp_user")->where("uid", $us["parent_id_3"])->setInc("price", $us["third_price"]);
					}
				}
			}
		}
		$status = request()->param("status", '');
		$condition["order_no"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["activity_order_type"] = array("eq", 0);
		$star_time = input("param.star_time");
		$end_time = input("param.end_time");
		if (!empty($star_time)) {
			$star = strtotime($star_time);
			$condition["create_time"] = ["between", [$star, $star + 86400]];
		}
		if (!empty($star_time) && !empty($end_time)) {
			$star = strtotime($star_time);
			$end = strtotime($end_time);
			$condition["create_time"] = ["between", [$star, $end]];
		}
		if ($status != '') {
			$condition["order_status"] = array("=", $status);
		} else {
			$condition["order_status"] = array("in", "0,1,2,3,-1");
		}
		$condition["is_deleted"] = array("<>", "1");
		$list = $order->getOrderList($condition, $search_text, "create_time desc");
		$this->assign("order_status", $status);
		$all_status = OrderService::getOrderStatus();
		$child_menu_list = array();
		$child_menu_list[] = array("url" => "admin/Order/orderList", "menu_name" => "全部", "active" => $status == '' ? 1 : 0);
		foreach ($all_status as $k => $v) {
			$child_menu_list[] = array("url" => "admin/order/orderList&status=" . $v["status_id"], "menu_name" => $v["status_name"], "active" => $status == $v["status_id"] ? 1 : 0);
		}
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$this->assign("child_menu_list", $child_menu_list);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("order_no", $search_text);
		$this->assign("star_time", $star_time);
		$this->assign("end_time", $end_time);
		return view("order/order_list");
	}
	public function OrderList2()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$order = new OrderService();
		$search_text = Request::instance()->post("order_no");
		$search_text = str_replace(" ", '', $search_text);
		$status = request()->param("status", '');
		$condition["order_no"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["activity_order_type"] = array("eq", 0);
		$star_time = input("param.star_time");
		$end_time = input("param.end_time");
		if (!empty($star_time)) {
			$star = strtotime($star_time);
			$condition["create_time"] = ["between", [$star, $star + 86400]];
		}
		if (!empty($star_time) && !empty($end_time)) {
			$star = strtotime($star_time);
			$end = strtotime($end_time);
			$condition["create_time"] = ["between", [$star, $end]];
		}
		if ($status != '') {
			$condition["order_status"] = array("=", $status);
		} else {
			$condition["order_status"] = array("in", "0,1,2,3,4,5,-1");
		}
		$condition["is_deleted"] = array("<>", "1");
		$condition["mailing_type"] = 2;
		$list = $order->getOrderList($condition, $search_text, "create_time desc");
		$this->assign("order_status", $status);
		$all_status = OrderService::getOrderStatus2();
		$child_menu_list = array();
		$child_menu_list[] = array("url" => "admin/Order/orderList2", "menu_name" => "全部", "active" => $status == '' ? 1 : 0);
		$status_arr = array();
		foreach ($all_status as $k => $v) {
			$status_arr[$v["status_id"]] = $v["status_name"];
			$child_menu_list[] = array("url" => "admin/order/orderList2&status=" . $v["status_id"], "menu_name" => $v["status_name"], "active" => $status == $v["status_id"] ? 1 : 0);
		}
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$this->assign("status_arr", $status_arr);
		$this->assign("child_menu_list", $child_menu_list);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("order_no", $search_text);
		$this->assign("star_time", $star_time);
		$this->assign("end_time", $end_time);
		return view("order/order_list2");
	}
	public function bargain_order()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$status = input("param.status", "-2");
		$search_text = input("param.search_text");
		if ($status == '' || $status == "-2") {
			$condition["bo.order_status"] = array("in", "0,1,2,3,-1");
		} else {
			$condition["bo.order_status"] = array("eq", $status);
		}
		$condition["bo.bargain_name"] = array("like", "%" . $search_text . "%");
		$list = Db::name("ybmp_bargain_order")->alias("bo")->join("ybmp_images i", "i.img_id=bo.bargain_pic")->where($condition)->where("bo.mch_id", $mch_id)->field("bo.*,i.img_cover_small")->paginate(20, false, $config = ["query" => ["status" => $status, "search_text" => $search_text]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		$this->assign("status", $status);
		return view("order/bargain_order");
	}
	public function OrderRefund()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$order = new OrderService();
		$search_text = Request::instance()->post("order_no");
		$status = request()->param("status", '');
		$condition["order_no"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $mch_id);
		$star_time = input("param.star_time");
		$end_time = input("param.end_time");
		if (!empty($star_time)) {
			$star = strtotime($star_time);
			$condition["create_time"] = ["between", [$star, $star + 86400]];
		}
		if (!empty($star_time) && !empty($end_time)) {
			$star = strtotime($star_time);
			$end = strtotime($end_time);
			$condition["create_time"] = ["between", [$star, $end]];
		}
		if ($status != '') {
			$condition["order_status"] = array("=", $status);
			$condition["is_deleted"] = array("<>", "1");
		} else {
			$condition["order_status"] = array("in", "4,5");
		}
		$list = $order->getOrderListRefund($condition, $search_text, "create_time desc");
		$this->assign("order_status", $status);
		$all_status = OrderService::getOrderRefund();
		$child_menu_list = array();
		$child_menu_list[] = array("url" => "/admin/Order/OrderRefund", "menu_name" => "全部", "active" => $status == '' ? 1 : 0);
		foreach ($all_status as $k => $v) {
			$child_menu_list[] = array("url" => "/admin/order/OrderRefund&status=" . $v["status_id"], "menu_name" => $v["status_name"], "active" => $status == $v["status_id"] ? 1 : 0);
		}
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$this->assign("child_menu_list", $child_menu_list);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("order_no", $search_text);
		$this->assign("star_time", $star_time);
		$this->assign("end_time", $end_time);
		return view("order/order_refund_list");
	}
	public function OrderDetail()
	{
		$advert = new OrderService();
		$order_id = request()->get("order_id", 0);
		$info = $advert->getOrderDetail($order_id);
		$all_status = OrderService::getOrderRefund();
		$info["user_platform_money"] = 0;
		$info["tax_money"] = 0;
		$info["point"] = 0;
		$this->assign("re", \request()->param("re", "o"));
		foreach ($all_status as $k_status => $v_status) {
			if ($v_status["status_id"] == $info["order_status"]) {
				$info["status_name"] = $v_status["status_name"];
			}
		}
		$this->assign("order", $info);
		$list = Db::name("ybmp_order")->where("order_id", $order_id)->find();
		$this->assign("list", $list);
		return view("order/order_detail");
	}
	public function orderTakeDelivery()
	{
		$order_service = new OrderService();
		$order_id = request()->post("order_id", '');
		$res = $order_service->OrderTakeDelivery($order_id);
		return AjaxReturn($res);
	}
	public function orderTakeRefund()
	{
		$order_id = request()->post("order_id", '');
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$order_service = new OrderService();
			$res = $order_service->orderTakeRefund($order_id);
			return AjaxReturn($res);
		} else {
			$cert = authcode($refund_setting["cert"], "DECODE");
			$key = authcode($refund_setting["key"], "DECODE");
			$order_info = Db::name("ybmp_order")->where("order_id", $order_id)->find();
			$bus_config = Db::name("ybmp_config")->where("mch_id", $this->bus_id)->find();
			$temp = json_decode($bus_config["value"], true);
			$res = $temp["APP_MCHID"] . date("YmdHis") . rand(10000, 9999999);
			$input = new \WxPayRefund();
			$input->SetOut_trade_no($order_info["out_trade_no"]);
			$input->SetTotal_fee($order_info["pay_money"] * 100);
			$input->SetRefund_fee($order_info["pay_money"] * 100);
			$input->SetOut_refund_no($res);
			$input->SetOp_user_id($temp["APP_MCHID"]);
			$input->SetAppid($temp["APP_ID"]);
			$cert = $cert;
			$key = $key;
			$cert_path = ATTACHMENT_ROOT . "public/" . $this->bus_id . "_wechat_refund_cert.pem";
			$key_path = ATTACHMENT_ROOT . "public/" . $this->bus_id . "_wechat_refund_key.pem";
			file_put_contents($cert_path, $cert);
			file_put_contents($key_path, $key);
			$param = \WxPayApi::refund($input);
			if ($param["result_code"] == "SUCCESS") {
				$order_info = Db::name("ybmp_order")->where("order_id", $order_id)->update(["order_status" => 5, "refund_money" => $order_info["pay_money"], "finish_time" => time()]);
				return AjaxReturn($order_info);
			} else {
				$errmsg = empty($param["err_code_des"]) ? "退款失败" : $param["err_code_des"];
				return AjaxReturnMsg(0, $errmsg);
			}
		}
	}
	public function deleteOrder()
	{
		if (request()->isAjax() && request()->isPost()) {
			$order_service = new OrderService();
			$order_id = request()->post("order_id", '');
			$res = $order_service->deleteOrder($order_id, 1);
			return AjaxReturn($res);
		}
	}
	public function orderDelivery()
	{
		$order_service = new OrderService();
		$express_service = new \app\admin\service\Express();
		$address_service = new Area();
		$this->assign("re", \request()->param("re", "o"));
		$order_id = request()->get("order_id", '');
		$order_info = $order_service->getOrderDetail($order_id);
		$order_info["address"] = $address_service->getAddress($order_info["receiver_area"]);
		$express_company_list = $express_service->expressCompanyQuery();
		$order_goods_list = $order_service->getOrderGoods($order_id);
		foreach ($order_goods_list as $k => $v) {
			$order_goods_list[$k]["shipping_status_name"] = $order_service->getShippingInfo($v["shipping_status"])["status_name"];
		}
		$data["order_info"] = $order_info;
		$data["express_company_list"] = $express_company_list;
		$data["order_goods_list"] = $order_goods_list;
		$this->assign("data", $data);
		return view("order/order_delivery");
	}
	public function doOrderDelivery()
	{
		$order_service = new OrderService();
		$order_id = request()->post("order_id", '');
		$express_name = request()->post("express_name", '');
		$shipping_type = request()->post("shipping_type", '');
		$express_company_id = request()->post("express_company_id", '');
		$express_no = request()->post("express_no", '');
		if ($shipping_type == 1) {
			$res = $order_service->orderDelivery($order_id, $express_name, $shipping_type, $express_company_id, $express_no);
		} else {
			$res = $order_service->orderGoodsDelivery($order_id);
		}
		return AjaxReturn($res);
	}
	public function getOrderSellerMemo()
	{
		$order_service = new OrderService();
		$order_id = request()->get("order_id");
		$res = $order_service->getOrderSellerMemo($order_id);
		$this->assign("re", \request()->param("re", "o"));
		$this->assign("res", $res);
		$this->assign("order_id", $order_id);
		return view("order/order_msg");
	}
	public function addMemo()
	{
		$order_service = new OrderService();
		$order_id = request()->post("order_id");
		$memo = request()->post("memo");
		$result = $order_service->addOrderSellerMemo($order_id, $memo);
		return AjaxReturn($result);
	}
	public function getOrderUpdateAddress()
	{
		$order_service = new OrderService();
		$this->assign("re", \request()->param("re", "o"));
		$order_id = request()->get("order_id");
		$res = $order_service->getOrderReceiveDetail($order_id);
		$address_id = $order_service->getCity($res["receiver_area"]);
		$this->assign("order_id", $order_id);
		$this->assign("res", $res);
		$this->assign("address_id", $address_id);
		return view("order/order_address");
	}
	public function updateOrderAddress()
	{
		$order_service = new OrderService();
		$order_id = request()->post("order_id", '');
		$receiver_name = request()->post("receiver_name", '');
		$receiver_mobile = request()->post("receiver_mobile", '');
		$receiver_zip = request()->post("receiver_zip", '');
		$receiver_province = request()->post("seleAreaNext", '');
		$receiver_city = request()->post("seleAreaThird", '');
		$receiver_district = request()->post("seleAreaFouth", '');
		$receiver_address = request()->post("address_detail", '');
		$res = $order_service->updateOrderReceiveDetail($order_id, $receiver_mobile, $receiver_province, $receiver_city, $receiver_district, $receiver_address, $receiver_zip, $receiver_name);
		return $res;
	}
	public function getProvince()
	{
		$address = new Area();
		$province_list = $address->getAreaList();
		return $province_list;
	}
	public function getCity()
	{
		$address = new Area();
		$province_id = request()->post("province_id", 0);
		$city_list = $address->getCityList($province_id);
		return $city_list;
	}
	public function getDistrict()
	{
		$address = new Area();
		$city_id = request()->post("city_id", 0);
		$district_list = $address->getDistrictList($city_id);
		return $district_list;
	}
	public function add_mention()
	{
		$order_id = input("param.order_id");
		$res = Db::name("ybmp_order")->where("order_id", $order_id)->update(["order_status" => 3, "finish_time" => time(), "sign_time" => time()]);
		return AjaxReturn($res);
	}
	public function is_pay_back()
	{
		$data = array("order_status" => 1, "pay_status" => 1, "pay_type" => 3, "pay_time" => time());
		$data_pay = array("pay_status" => 1, "pay_time" => time());
		$no = \request()->param("no", '');
		Log::write($no . ",完成;后台操作:" . $this->uuid, "shop_pay_success");
		Db::name("ybmp_order")->where(["out_trade_no" => $no, "order_status" => 0])->update($data);
		Db::name("ybmp_order_payment")->where(["out_trade_no" => $no, "pay_status" => 0])->update($data_pay);
		return 1;
	}
}
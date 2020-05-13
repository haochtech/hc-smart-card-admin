<?php


namespace app\api\service;

use think\Db;
use think\log;
require_once BASE_ROOT . "core/application/common.php";
require_once BASE_ROOT . "core/extend/Wxpay/WxPay.Api.php";
class BargainService
{
	public function barIndex($data)
	{
		$rs = array();
		$rs["carousel"] = Db::name("ybmp_application_carousel")->where("is_use", 0)->where($data)->order("sort", "desc")->order("create_time", "desc")->select();
		$cate_info = Db::name("ybmp_application_activity_class")->where("is_use", 0)->where($data)->order("sort", "desc")->order("create_time", "desc")->select();
		$cate_info_obj["id"] = 0;
		$cate_info_obj["agents_id"] = $data["agents_id"];
		$cate_info_obj["class_name"] = "全部活动";
		$cate_info_obj["sort"] = 0;
		$cate_info_obj["create_time"] = time();
		$cate_info_obj["is_use"] = 0;
		$cate_info_obj["img_url"] = '';
		array_unshift($cate_info, $cate_info_obj);
		$rs["cate_info"] = $cate_info;
		return $rs;
	}
	public function getBargainList($data, $page)
	{
		if (isset($data["class_id"]) && $data["class_id"] != 0) {
			$new_data = ["class_id" => $data["class_id"]];
		} else {
			$new_data = [];
		}
		$new_data["mch_id"] = $data["mch_id"];
		if ($data["type"] == 1) {
			$bargain_list = Db::name("ybmp_bargain")->where($new_data)->where("bargain_type", "1")->whereTime("star_time", "<", time())->whereTime("end_time", ">", time())->order("sort", "desc")->order("create_time", "desc")->page($page, PAGE_NUM)->select();
		} elseif ($data["type"] == 2) {
			$bargain_list = Db::name("ybmp_bargain")->where($new_data)->where("bargain_type", "1")->whereTime("star_time", ">", time())->order("sort", "desc")->order("create_time", "desc")->page($page, PAGE_NUM)->select();
		} else {
			$bargain_list = Db::name("ybmp_bargain")->where($new_data)->where("bargain_type", "1")->whereTime("end_time", ">", time())->order("sort", "desc")->order("create_time", "desc")->page($page, PAGE_NUM)->select();
		}
		if (empty($bargain_list)) {
			return $bargain_list;
		}
		foreach ($bargain_list as $key => $value) {
			$bargain_user_list = Db::name("ybmp_bargain_user")->where("bargain_id", $value["id"])->select();
			$pic = Db::name("ybmp_images")->where("img_id", $value["bargain_picture"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$bargain_list[$key]["pic"] = $pic;
			}
			$user_list = array();
			foreach ($bargain_user_list as $user_key => $user_value) {
				$user_info = Db::name("ybmp_user")->field("user_headimg,nick_name")->where("uid", $user_value["user_id"])->find();
				$user_list[$user_key] = $user_info;
			}
			$bargain_list[$key]["user"] = $user_list;
		}
		return $bargain_list;
	}
	public function getGoodsInfo($data)
	{
		$rs = array();
		$bargain_info = Db::name("ybmp_bargain")->where($data)->where("bargain_type", "1")->find();
		Db::name("ybmp_bargain")->where($data)->setInc("check_number");
		$pic = Db::name("ybmp_images")->where("img_id", $bargain_info["bargain_picture"])->value("img_cover");
		if ($pic) {
			$bargain_info["pic"] = $pic;
		}
		$pic_arr = array();
		if ($bargain_info["img_id_array"]) {
			$pic_array = explode(",", $bargain_info["img_id_array"]);
			for ($i = 0; $i < count($pic_array); $i++) {
				$pic_arr[$i] = Db::name("ybmp_images")->where("img_id", $pic_array[$i])->value("img_cover");
			}
		}
		$bargain_info["pic_arr"] = $pic_arr;
		$rs["bargain_info"] = $bargain_info;
		$about_info = Db::name("ybmp_business_about")->where("mch_id", $data["mch_id"])->find();
		$rs["about_info"] = $about_info;
		return $rs;
	}
	public function getBargainInfo($data)
	{
		$rs = array();
		$bargain_info = Db::name("ybmp_bargain")->where("mch_id", $data["mch_id"])->where("id", $data["id"])->where("bargain_type", "1")->find();
		$pic = Db::name("ybmp_images")->where("img_id", $bargain_info["bargain_picture"])->value("img_cover");
		if ($pic) {
			$bargain_info["pic"] = $pic;
		}
		$pic_arr = array();
		if ($bargain_info["img_id_array"]) {
			$pic_array = explode(",", $bargain_info["img_id_array"]);
			for ($i = 0; $i < count($pic_array); $i++) {
				$pic_arr[$i] = Db::name("ybmp_images")->where("img_id", $pic_array[$i])->value("img_cover");
			}
		}
		$bargain_info["pic_arr"] = $pic_arr;
		$rs["bargain_info"] = $bargain_info;
		$user_info = Db::name("ybmp_bargain_user")->where("bargain_id", $data["id"])->where("user_id", $data["user_id"])->find();
		$user_data = Db::name("ybmp_user")->field("user_headimg,nick_name")->where("uid", $user_info["user_id"])->find();
		$user_info["user_headimg"] = $user_data["user_headimg"];
		$user_info["nick_name"] = $user_data["nick_name"];
		$rs["user_info"] = $user_info;
		return $rs;
	}
	public function BargainCreate($data)
	{
		$bargain_info = Db::name("ybmp_bargain_user")->where("user_id", $data["user_id"])->where("bargain_id", $data["bargain_id"])->find();
		if (!empty($bargain_info)) {
			return "exist";
		}
		$bargain_info = Db::name("ybmp_bargain")->where("id", $data["bargain_id"])->where("bargain_type", "1")->where("bargain_inventory", "<>", 0)->whereTime("star_time", "<", time())->whereTime("end_time", ">", time())->find();
		if (empty($bargain_info)) {
			return "lose";
		}
		$data["goods_id"] = $bargain_info["goods_id"];
		$data["original_price"] = $bargain_info["original_price"];
		$data["lowest_price"] = $bargain_info["lowest_price"];
		$bargain_price = ($bargain_info["original_price"] - $bargain_info["lowest_price"]) / $bargain_info["completed_number"];
		$data["current_price"] = $bargain_info["original_price"] - $bargain_price;
		$data["initiated_time"] = time();
		Db::startTrans();
		try {
			$in_id = Db::name("ybmp_bargain_user")->strict(true)->insertGetId($data);
			$help_data = array("user_id" => $data["user_id"], "iInitiated_id" => $in_id, "bargain_price" => $bargain_price, "create_time" => time(), "balance_price" => $data["current_price"]);
			$help_id = Db::name("ybmp_bargain_help")->strict(true)->insertGetId($help_data);
			Db::name("ybmp_bargain")->where("id", $data["bargain_id"])->setInc("participants_number");
			Db::name("ybmp_bargain")->where("id", $data["bargain_id"])->setInc("help_number");
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		$info = Db::name("ybmp_bargain_help")->where("id", $help_id)->find();
		return $info["bargain_price"];
	}
	public function randomFloat($min = 0, $max = 1)
	{
		$num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
		return sprintf("%.2f", $num);
	}
	public function BargainHelp($data)
	{
		$help_info = Db::name("ybmp_bargain_help")->where($data)->find();
		if (!empty($help_info)) {
			return "exist";
		}
		$user_info = Db::name("ybmp_bargain_user")->where("id", $data["iInitiated_id"])->find();
		if (empty($user_info)) {
			return "bargain_lose";
		}
		$bargain_info = Db::name("ybmp_bargain")->where("id", $user_info["bargain_id"])->where("bargain_type", "1")->whereTime("star_time", "<", time())->whereTime("end_time", ">", time())->find();
		if (empty($bargain_info)) {
			return "lose";
		}
		$help_count = Db::name("ybmp_bargain_help")->where("iInitiated_id", $data["iInitiated_id"])->count();
		if ($help_count == $bargain_info["completed_number"]) {
			return "max";
		} else {
			$help_price = Db::name("ybmp_bargain_help")->where("iInitiated_id", $data["iInitiated_id"])->sum("bargain_price");
			$data["original_price"] = $bargain_info["original_price"];
			$data["lowest_price"] = $bargain_info["lowest_price"];
			if ($bargain_info["completed_number"] - $help_count == 1) {
				$bargain_price = $user_info["current_price"] - $bargain_info["lowest_price"];
			} else {
				$bargain_price = ($bargain_info["original_price"] - $help_price - $bargain_info["lowest_price"]) / ($bargain_info["completed_number"] - $help_count);
				$bargain_price = $this->randomFloat($bargain_price - 1, $bargain_price + 1);
			}
			$data["balance_price"] = $user_info["current_price"] - $bargain_price;
			Db::startTrans();
			try {
				$help_data = array("user_id" => $data["user_id"], "iInitiated_id" => $data["iInitiated_id"], "bargain_price" => $bargain_price, "create_time" => time(), "balance_price" => $data["balance_price"]);
				$help_id = Db::name("ybmp_bargain_help")->strict(true)->insertGetId($help_data);
				Db::name("ybmp_bargain_user")->where("id", $data["iInitiated_id"])->setDec("current_price", $bargain_price);
				Db::name("ybmp_bargain")->where("id", $user_info["bargain_id"])->setInc("help_number");
				Db::commit();
			} catch (\Exception $e) {
				Db::rollback();
				return null;
			}
		}
		$info = Db::name("ybmp_bargain_help")->where("id", $help_id)->find();
		return $info["bargain_price"];
	}
	public function getBargainRecord($data)
	{
		$help_list = null;
		$help_list = Db::name("ybmp_bargain_help")->where("iInitiated_id", $data["iInitiated_id"])->select();
		foreach ($help_list as $key => $value) {
			$user_data = Db::name("ybmp_user")->field("user_headimg,nick_name")->where("uid", $value["user_id"])->find();
			$help_list[$key]["user_headimg"] = $user_data["user_headimg"];
			$help_list[$key]["nick_name"] = $user_data["nick_name"];
		}
		return $help_list;
	}
	public function getMyBargain($data, $page)
	{
		$user_list = Db::name("ybmp_bargain_user")->where("user_id", $data["user_id"])->order("initiated_time", "desc")->page($page, PAGE_NUM)->select();
		foreach ($user_list as $key => $value) {
			$bargain_info = Db::name("ybmp_bargain")->where("id", $value["bargain_id"])->find();
			$user_list[$key]["bargain_name"] = $bargain_info["bargain_name"];
			$user_list[$key]["star_time"] = $bargain_info["star_time"];
			$user_list[$key]["end_time"] = $bargain_info["end_time"];
			$user_list[$key]["consumption_time"] = $bargain_info["consumption_time"];
			$user_list[$key]["consumptiontime"] = date("Y-m-d H:i", $bargain_info["consumption_time"]);
			$order_info = Db::name("ybmp_bargain_order")->field("order_status")->where("buyer_id", $data["user_id"])->where("bargain_id", $value["bargain_id"])->where("activity_order_type", 1)->find();
			$user_list[$key]["order_status"] = $order_info["order_status"];
			if ($order_info["order_status"] === null) {
				$user_list[$key]["order_status"] = -2;
			}
			$pic = Db::name("ybmp_images")->where("img_id", $bargain_info["bargain_picture"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$user_list[$key]["pic"] = $pic;
			}
			$help_num = Db::name("ybmp_bargain_help")->where("iInitiated_id", $value["id"])->count();
			$user_list[$key]["help"] = $help_num;
		}
		return $user_list;
	}
	public function createOrder($data)
	{
		if ($data["activity_order_type"] != 3) {
			$info = Db::name("ybmp_bargain")->where("id", $data["bargain_id"])->find();
			$data["bargain_name"] = $info["bargain_name"];
			$data["bargain_pic"] = $info["bargain_picture"];
			$data["original_price"] = $info["original_price"];
		} else {
			$info = Db::name("ybmp_activity")->where("id", $data["bargain_id"])->find();
			$data["bargain_name"] = $info["name"];
			$data["bargain_pic"] = '';
			$data["original_price"] = $info["nprice"];
		}
		Db::startTrans();
		try {
			$id = Db::name("ybmp_bargain_order")->strict(true)->insertGetId($data);
			$pay_data = array("out_trade_no" => $data["out_trade_no"], "pay_type" => $data["pay_type"], "type_alis_id" => $id, "pay_body" => "平台支付", "pay_detail" => "平台购买商品", "pay_money" => $data["pay_money"], "create_time" => time());
			Db::name("ybmp_bargain_order_payment")->insert($pay_data);
			if (isset($data["bargain_id"]) && $data["activity_order_type"] == 1) {
				Db::name("ybmp_bargain")->where("id", $data["bargain_id"])->setDec("bargain_inventory");
			}
			$ser = new OffwebService($data["mch_id"]);
			$r = $ser->sub_send($data["buyer_id"], "砍价订单下单成功:" . $data["order_no"], "kj_order_create");
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $id;
	}
	public function orderList($data, $page = 1, $type)
	{
		if ($type == 1) {
			$where = ["activity_order_type" => ["<>", 3]];
		} else {
			$where = ["activity_order_type" => 3];
		}
		$order_list = null;
		$order_list = Db::name("ybmp_bargain_order")->where("buyer_id", $data["buyer_id"])->where($where)->where("is_deleted", 0)->where("order_status", $data["order_status"])->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$order_list[$key]["sign_time"] = __TIME($value["sign_time"]);
			$order_list[$key]["pay_time"] = __TIME($value["pay_time"]);
			$order_list[$key]["consign_time"] = __TIME($value["consign_time"]);
			if ($value["activity_order_type"] == 3) {
				$order_list[$key]["pic"]["img_cover"] = Db::name("ybmp_activity")->where(["id" => $value["bargain_id"]])->value("pic");
			} else {
				$pic = Db::name("ybmp_images")->where("img_id", $value["bargain_pic"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					$order_list[$key]["pic"] = $pic;
				}
			}
		}
		return $order_list;
	}
	public function getOrder($data)
	{
		$order_info = null;
		$order_info = Db::name("ybmp_bargain_order")->where($data)->where("is_deleted", 0)->find();
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
		if ($order_info["activity_order_type"] == 3) {
			$order_info["pic"]["img_cover"] = Db::name("ybmp_activity")->where(["id" => $order_info["bargain_id"]])->value("pic");
		} else {
			$pic = Db::name("ybmp_images")->where("img_id", $order_info["bargain_pic"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$order_info["pic"] = $pic;
			}
		}
		return $order_info;
	}
	public function signOrder($data)
	{
		$info = Db::name("ybmp_bargain_order")->where($data)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该订单状态异常！";
			return json_encode($rs);
		}
		$new_data = array("order_status" => 3, "sign_time" => time());
		$res = Db::name("ybmp_bargain_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
	public function cancelOrder($data)
	{
		$info = Db::name("ybmp_bargain_order")->where($data)->find();
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
			$res = Db::name("ybmp_bargain_order")->where(["order_id" => $data["order_id"]])->update($new_data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return $e->getMessage();
		}
		return $res;
	}
	public function delOrder($data)
	{
		$info = Db::name("ybmp_bargain_order")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("is_deleted" => 1);
		$res = Db::name("ybmp_bargain_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
	public function refundOrder($data)
	{
		$info = Db::name("ybmp_bargain_order")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 4, "refund_time" => time());
		$res = Db::name("ybmp_bargain_order")->where(["order_id" => $data["order_id"]])->update($new_data);
		$ser = new OffwebService($info["mch_id"]);
		$r = $ser->sub_send($data["buyer_id"], "砍价订单申请退款:" . $info["order_no"], "kj_order_refund");
		return $res;
	}
	public function checkOrder($buyer_id, $bargain_id)
	{
		$rs = Db::name("ybmp_bargain_order")->where("buyer_id", $buyer_id)->where("bargain_id", $bargain_id)->where("activity_order_type", 1)->where("order_status", "<>", -1)->count();
		return $rs;
	}
	public function checkmsOrder($buyer_id, $bargain_id)
	{
		$act = Db::name("ybmp_activity")->where("id", $bargain_id)->value("max_pre");
		if (empty($act) || $act == 0) {
			return 0;
		}
		$rs = Db::name("ybmp_bargain_order")->where("buyer_id", $buyer_id)->where("bargain_id", $bargain_id)->where("activity_order_type", 3)->where("order_status", "<>", -1)->count();
		if ($act > $rs) {
			return 0;
		}
		return $rs;
	}
	public function orderInfo($data)
	{
		$info = Db::name("ybmp_bargain_order")->where($data)->find();
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
		$info = Db::name("ybmp_bargain_order_payment")->where("out_trade_no", $data["out_trade_no"])->find();
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
		$GLOBALS["key"] = "bargain";
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
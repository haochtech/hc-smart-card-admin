<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use think\Log;
use app\api\service\CateringService;
use think\Session;
use think\Cache;
class Catering extends BaseController
{
	public function VoiceCall()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["desk_id" => Request::instance()->param("desk_id"), "mch_id" => $mch_id, "call_type" => Request::instance()->param("call_type")];
		$rule = [["desk_id", "require|number"], ["call_type", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if (Cache::get("expire_time_" . $data["desk_id"] . "_" . $data["call_type"]) && Cache::get("expire_time_" . $data["desk_id"] . "_" . $data["call_type"]) > time()) {
			$rs["code"] = 1;
			$rs["msg"] = "您操作太频繁，请于5分钟后重试";
			return json_encode($rs);
		} else {
			Cache::set("expire_time_" . $data["desk_id"] . "_" . $data["call_type"], time() + 300);
			$order = new CateringService();
			$info = $order->VoiceCall($data);
			return AjaxReturn($info);
		}
	}
	public function CreateOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["order_no" => $this->createOrderNo(), "out_trade_no" => $this->createOutTradeNo(), "buyer_message" => Request::instance()->param("buyer_message", ''), "discount_money" => Request::instance()->param("discount_money", "0.00"), "coupon_id" => Request::instance()->param("coupon_id", 0), "coupon_money" => Request::instance()->param("coupon_money", "0.00"), "create_time" => time(), "user_id" => Request::instance()->param("buyer_id"), "desk_id" => Request::instance()->param("desk_id"), "order_money" => Request::instance()->param("order_money"), "pay_money" => Request::instance()->param("pay_money"), "order_dish_json" => Request::instance()->param("order_dish_json"), "activity_order_type" => Request::instance()->param("activity_order_type", 0), "mch_id" => $mch_id, "pay_type" => Request::instance()->param("pay_type", 1), "total" => Request::instance()->param("total")];
		if ($data["discount_money"] != "0.00" && $data["coupon_money"] != "0.00") {
			$data["activity_order_type"] = 4;
		}
		$rule = [["user_id", "require|number"], ["order_money", "require|number"], ["pay_money", "require|number"], ["order_dish_json", "require"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->createOrder($data);
		if (empty($info)) {
			Log::write("生成订单失败 --" . $data["user_id"], "order_create_error");
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
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["user_id", "require", "uid不能为空"], ["mch_id", "require", "不存在商户"]];
		$data = ["user_id" => $uid, "mch_id" => $mch_id];
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
		$order = new CateringService();
		$info = $order->orderList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["user_id", "require", "uid不能为空"]];
		$data = ["user_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->getOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SignOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["user_id", "require"], ["order_id", "require"]];
		$data = ["user_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => 2];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->signOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CancelOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["user_id", "require"], ["order_id", "require"]];
		$data = ["user_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->cancelOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单取消失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function RefundOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["user_id", "require"], ["order_id", "require"]];
		$data = ["user_id" => Request::instance()->param("buyer_id"), "order_id" => Request::instance()->param("order_id"), "order_status" => ["in", [2, 3]]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->refundOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单退款失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Book()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["name", "require"], ["phone", "require"], ["mch_id", "require", "不存在商户"], ["booked_time", "require"], ["number_people", "require"], ["user_id", "require"]];
		$data = ["name" => Request::instance()->param("name"), "phone" => Request::instance()->param("phone"), "booked_time" => Request::instance()->param("booked_time"), "number_people" => Request::instance()->param("number_people"), "message" => Request::instance()->param("message"), "status" => 1, "mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "create_time" => time()];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->Book($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "提交失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Booklist()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"]];
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->Booklist($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CancelBook()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["user_id", "require"], ["id", "require"]];
		$data = ["user_id" => Request::instance()->param("user_id"), "id" => Request::instance()->param("id"), "status" => 0];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->cancelBook($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "预订取消失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ShopService()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->ShopService($data);
		if (!empty($info)) {
			$info = explode(",", $info);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function uploadFile()
	{
		$rs = array("code" => 0, "info" => array());
		$app_key = Request::instance()->param("app_key");
		$mch_id = $this->getMchId($app_key);
		$file_path = "public/upload/" . $mch_id . "/comment_pic/";
		if ($file_path == '') {
			return null;
		}
		if (!file_exists($file_path)) {
			$mode = intval("0777", 8);
			mkdir($file_path, $mode, true);
		}
		$file_name = $_FILES["file_upload"]["name"];
		$file_size = $_FILES["file_upload"]["size"];
		$file_type = $_FILES["file_upload"]["type"];
		if ($file_size == 0) {
			return null;
		}
		if ($file_type != "image/gif" && $file_type != "image/png" && $file_type != "image/jpeg" && $file_size > 5000000) {
			$rs["code"] = 1;
			$rs["msg"] = "文件上传失败,请检查您上传的文件类型,文件大小不能超过5MB";
			return json_encode($rs);
		}
		$guid = time() . rand(10000, 99999);
		$file_name_explode = explode(".", $file_name);
		$suffix = count($file_name_explode) - 1;
		$ext = "." . $file_name_explode[$suffix];
		$newfile = $guid . $ext;
		$ok = $this->moveUploadFile($_FILES["file_upload"]["tmp_name"], $file_path . $newfile);
		if ($ok["code"]) {
			@unlink($_FILES["file_upload"]);
			return $ok["path"];
		} else {
			return null;
		}
	}
	private $upload_type = 1;
	private function moveUploadFile($file_path, $key)
	{
		if ($this->upload_type == 1) {
			$ok = @move_uploaded_file($file_path, $key);
			$result = ["code" => $ok, "path" => $key, "domain" => '', "bucket" => ''];
			return $result;
		}
	}
	public function WriteComment()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["fraction", "require"], ["info", "require"], ["user_id", "require"]];
		$data = ["info" => Request::instance()->param("info"), "fraction" => Request::instance()->param("fraction"), "array_pic" => Request::instance()->post("array_pic/a", []), "mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "create_time" => time(), "del" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$array_pic = '';
		foreach ($data["array_pic"] as $v) {
			$array_pic .= $v . ",";
		}
		$data["array_pic"] = $array_pic;
		$info = $order->WriteComment($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "提交失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CommentList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id, "del" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->CommentList($data, $page);
		$info = json_decode($info, true);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addOrder()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"], ["order_id", "require"], ["add_price", "require"], ["total", "require"]];
		$data = ["mch_id" => $mch_id, "order_id" => Request::instance()->param("order_id"), "user_id" => Request::instance()->param("user_id"), "add_price" => Request::instance()->param("add_price"), "total" => Request::instance()->param("total")];
		$json = Request::instance()->param("order_dish_json");
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->addOrder($data, $json);
		return AjaxReturn($info);
	}
	public function tuiOrder()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"], ["order_id", "require"], ["reduce_price", "require"], ["total", "require"]];
		$data = ["mch_id" => $mch_id, "order_id" => Request::instance()->param("order_id"), "user_id" => Request::instance()->param("user_id"), "reduce_price" => Request::instance()->param("reduce_price"), "total" => Request::instance()->param("total")];
		$json = Request::instance()->param("order_dish_json");
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->tuiOrder($data, $json);
		return AjaxReturn($info);
	}
	public function tuigoodslist()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["order_id", "require"]];
		$data = ["mch_id" => $mch_id, "order_id" => Request::instance()->param("order_id")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CateringService();
		$info = $order->tuigoodslist($data);
		return AjaxReturn($info);
	}
	public function getWxcode()
	{
		$ACCESS_TOKEN = $this->getWxAccessToken();
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
		$post_data = array("page" => "pages/caregory/index", "scene" => "34,S853EE4QRP");
		$post_data = json_encode($post_data);
		$data = $this->send_post($url, $post_data);
		$result = data_uri($data, "image/png");
		return "<image src=" . $result . "></image>";
	}
	public function getWxAccessToken()
	{
	}
	protected function send_post($url, $post_data)
	{
		$options = array("http" => array("method" => "POST", "header" => "Content-type:application/json", "content" => $post_data, "timeout" => 60));
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}
}
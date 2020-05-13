<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
use think\Request;
use think\Db;
use app\api\service\IndexService;
use app\common\model\Config;
use think\Validate;
use think\Cache;
class Index extends BaseController
{
	public function ttt()
	{
		return $app_id = Request::instance()->param("i");
	}
	public function WriteBook()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["name", "require"], ["phone", "require"], ["user_id", "require"]];
		$data = ["name" => Request::instance()->param("name"), "phone" => Request::instance()->param("phone"), "content" => Request::instance()->param("content"), "mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new IndexService();
		$info = $order->WriteBook($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "提交失败";
			return json_encode($rs);
		}
		if ($info == -1) {
			$rs["code"] = 1;
			$rs["msg"] = "已添加成功";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function WriteComment()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["fraction", "require"], ["info", "require"], ["user_id", "require"]];
		$data = ["info" => Request::instance()->param("info"), "fraction" => Request::instance()->param("fraction"), "array_pic" => Request::instance()->post("array_pic/a", []), "mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "del" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new IndexService();
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
		if ($info == -1) {
			$rs["code"] = 1;
			$rs["msg"] = "已添加成功";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CommentList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$num = Request::instance()->param("num", 0);
		$page = Request::instance()->param("page", 1);
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id, "del" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new IndexService();
		$info = $order->CommentList($data, $page, $num);
		$info = json_decode($info, true);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Support()
	{
		$rs = array("code" => 0, "info" => array());
		$config = new Config();
		$info = $config->where("key", "TECHNICAL")->find();
		if (!empty($info)) {
			$value = json_decode($info["value"], true);
			if (!empty($value[0]["qrcode"])) {
				$value[0]["qrcode"] = IMG_PAH . $value[0]["qrcode"];
			}
			$rs["info"] = $value[0];
		}
		return json_encode($rs);
	}
	public function uploadFile()
	{
		$rs = array("code" => 0, "info" => array());
		$app_key = Request::instance()->param("i");
		$path = Request::instance()->param("path");
		$mch_id = $this->getMchId($app_key);
		$file_path = $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $path . "/acid_" . $mch_id . "/" . date("Y-m-d", time()) . "/";
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
			return null;
		}
		$guid = time() . rand(10000, 99999);
		$file_name_explode = explode(".", $file_name);
		$suffix = count($file_name_explode) - 1;
		$ext = "." . $file_name_explode[$suffix];
		$newfile = $guid . $ext;
		$ok = $this->moveUploadFile($_FILES["file_upload"]["tmp_name"], $file_path . $newfile);
		if ($ok["code"]) {
			@unlink($_FILES["file_upload"]);
			return "https://" . $_SERVER["HTTP_HOST"] . strstr($ok["path"], "/attachment");
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
	public function ModIndex()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->mod_index($mch_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "未选择官网模板";
			return json_encode($rs);
		}
		if ($info == "old") {
			$rs["code"] = 1;
			$rs["msg"] = "diy模板已升级，请在系统后台重新保存该页面";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function power()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$data = ["mch_id" => $mch_id, "id" => $id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"], ["id", "require", "未选择万能页面"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->get_power($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "请填充页面内容";
			return json_encode($rs);
		}
		if ($info == "old") {
			$rs["code"] = 1;
			$rs["msg"] = "diy模板已升级，请在系统后台重新保存该页面";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ModShop()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->mod_shop($mch_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "未选择模板";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function menu()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->get_menu($mch_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "未选择底部菜单";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function tabbar()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->get_tabbar($mch_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "未选择底部菜单";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function form()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$id = Request::instance()->param("id");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "id" => $id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"], ["id", "require", "未选择表单"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->get_form($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "表单不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function submitform()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$param = Request::instance()->param("data");
		$bus_form_id = Request::instance()->param("bus_form_id");
		$user_id = Request::instance()->param("user_id");
		$form = Request::instance()->param("form");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "param" => $param, "user_id" => $user_id, "bus_form_id" => $bus_form_id, "form" => $form];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"], ["param", "require", "内容不能为空"], ["bus_form_id", "require", "表单不存在"], ["form", "require", "表单不存在"], ["user_id", "require", "未获取到用户信息，请重试"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$limit_num = Db::name("ybmp_bus_form")->where("id", $bus_form_id)->value("limit_num");
		$num = Db::name("ybmp_user_form")->where(["mch_id" => $mch_id, "bus_form_id" => $bus_form_id, "user_id" => $user_id])->count();
		if ($limit_num != 0 && $num >= $limit_num) {
			$rs["code"] = 1;
			$rs["msg"] = "提交次数已达到上线";
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->submit_form($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		if ($info == -1) {
			$rs["code"] = 1;
			$rs["msg"] = "已添加成功";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ucenter()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new IndexService();
		$info = $index->get_ucenter($mch_id);
		if (empty($info)) {
			$rs = "{\"all_data\":[{\"img\":\"/public/images/member/cart.png\",\"type\":\"order\",\"status\":1,\"title\":\"我的订单\",\"time_key\":\"153474118797080\"},{\"img\":\"/public/images/member/like.png\",\"type\":\"follow\",\"status\":1,\"title\":\"我的关注\",\"time_key\":\"153474118882593\"},{\"img\":\"/public/images/member/coupon.png\",\"type\":\"coupon\",\"status\":1,\"title\":\"我的优惠券\",\"time_key\":\"153474118930570\"},{\"img\":\"/public/images/member/appointment.png\",\"type\":\"book\",\"status\":1,\"title\":\"我的预约\",\"time_key\":\"153474118919817\"},{\"img\":\"/public/images/member/kj_icon.png\",\"type\":\"kjm\",\"status\":1,\"title\":\"我的砍价\",\"time_key\":\"153474119087708\"},{\"img\":\"/public/images/member/kj_order_icon.png\",\"type\":\"kjo\",\"status\":1,\"title\":\"砍价订单\",\"time_key\":\"153474119043189\"},{\"img\":\"/public/images/member/group_icon.png\",\"type\":\"ptm\",\"status\":1,\"title\":\"我的拼团\",\"time_key\":\"153474119153315\"},{\"img\":\"/public/images/member/groupj_order_icon.png\",\"type\":\"pto\",\"status\":1,\"title\":\"拼团订单\",\"time_key\":\"153474119179761\"},{\"img\":\"/public/images/member/groupj_order_icon.png\",\"type\":\"miaosha\",\"status\":1,\"title\":\"秒杀订单\",\"time_key\":\"153474119179761\"},{\"img\":\"/public/images/member/service.png\",\"type\":\"kefu\",\"status\":1,\"title\":\"在线客服\",\"time_key\":\"15347411926469\"},{\"img\":\"/public/images/member/location.png\",\"type\":\"address\",\"status\":1,\"title\":\"收货地址\",\"time_key\":\"153474119365949\"},{\"img\":\"/public/images/member/about.png\",\"type\":\"about\",\"status\":1,\"title\":\"关于我们\",\"time_key\":\"153474119422866\"}],\"win_color\":\"#ffffff\",\"win_img\":\"\"}";
		} else {
			$rs = $info;
		}
		$data = json_decode($rs, true);
		if (!strpos($rs, "miaosha")) {
			$m = array(0 => array("img" => "http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/miaosha.png", "type" => "miaosha", "title" => "秒杀订单", "status" => 1, "time_key" => "153474119422866"));
			array_splice($data["all_data"], -3, 0, $m);
		}
		if (!strpos($rs, "dingyue")) {
			$m = array(0 => array("img" => "http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/dingyue.png", "type" => "dingyue", "title" => "我的订阅", "status" => 1, "time_key" => "153474119422866"));
			array_splice($data["all_data"], 4, 0, $m);
		}
		$rs = $data;
		return json_encode($rs);
	}
}
<?php


namespace app\web\controller;

use think\Controller;
use think\Db;
use think\Exception;
use think\Session;
use think\Request;
use app\web\service\WebindexService;
use app\web\service\Send;
use app\web\service\QyWechat;
class Webindex extends BaseController
{
	public function index_time()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = Request::instance()->param("mch_id");
		$page = Request::instance()->param("page", 1);
		$aa = Db::name("ybmp_bus_card")->where("UserId", $uid)->where("mch_id", $mch_id)->find();
		if (empty($aa)) {
			$rs["code"] = 1;
			$rs["msg"] = "该员工不存在，请确认后台是否添加该员工!";
			return json_encode($rs);
		}
		if ($aa["radar"] != 1) {
			$rs["code"] = 1;
			$rs["msg"] = "该名片已被禁用，请联系公司管理员!";
			return json_encode($rs);
		}
		$staff_id = $aa["id"];
		$data = ["staff_id" => $staff_id, "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$article = new WebindexService();
		$info = $article->get_index_time($data, $page);
		$rs["info"] = $info;
		exit(json_encode($rs, true));
	}
	public function index_detail()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$page = Request::instance()->param("page", 1);
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$str_time = Request::instance()->param("str_time", null);
		$end_time = Request::instance()->param("end_time", null);
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id, "op_type" => Request::instance()->param("op_type"), "op_name" => Request::instance()->param("op_name")];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require", "不存在用户"], ["op_type", "require"], ["op_name", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if (empty($str_time) || empty($end_time)) {
			$data["create_time"] = array(">", time() - 3600 * 24 * 7);
		} else {
			$data["create_time"] = array("BETWEEN", [strtotime($str_time), strtotime($end_time)]);
		}
		$article = new WebindexService();
		$info = $article->get_index_detail($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function index_detail_like()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$page = Request::instance()->param("page", 1);
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$str_time = Request::instance()->param("str_time", null);
		$end_time = Request::instance()->param("end_time", null);
		$data = ["mch_id" => $mch_id, "c_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["c_id", "require", "不存在用户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if (empty($str_time) || empty($end_time)) {
			$data["create_time"] = array(">", time() - 3600 * 24 * 7);
		} else {
			$data["create_time"] = array("BETWEEN", [strtotime($str_time), strtotime($end_time)]);
		}
		$article = new WebindexService();
		$info = $article->get_index_detail_like($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function index_behavior()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$str_time = Request::instance()->param("str_time");
		$end_time = Request::instance()->param("end_time");
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require", "不存在用户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if (empty($str_time) || empty($end_time)) {
			$data["create_time"] = array(">", time() - 3600 * 24 * 7);
		} else {
			$data["create_time"] = array("BETWEEN", [strtotime($str_time), strtotime($end_time)]);
		}
		$article = new WebindexService();
		$info = $article->get_index_behavior($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function index_people()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$page = Request::instance()->param("page", 1);
		$str_time = Request::instance()->param("str_time");
		$end_time = Request::instance()->param("end_time");
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require", "不存在用户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$article = new WebindexService();
		if (empty($str_time) || empty($end_time)) {
			$data["create_time"] = array(">", time() - 3600 * 24 * 7);
		} else {
			$data["create_time"] = array("BETWEEN", [strtotime($str_time), strtotime($end_time)]);
		}
		$info = $article->get_index_people($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function UserInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$UserId = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($UserId);
		$data = ["UserId" => $UserId, "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"], ["UserId", "require", "未获取用户信息"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$web = new WebindexService();
		$info = $web->get_userinfo($data);
		if (empty($info)) {
			$rs["code"] = 0;
			$rs["msg"] = "该用户不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function NewsList()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$for_back = Request::instance()->param("for_back");
		if ($for_back) {
			return true;
		}
		$staff_id = $this->getSId($uid);
		$data = ["staff_id" => $staff_id];
		$rule = [["staff_id", "require", "未获取用户信息"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$web = new WebindexService();
		$data["mch_id"] = $this->get_mchid($uid);
		$info = $web->get_NewsList($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function timelynews()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$data = ["user_id" => Request::instance()->param("user_id"), "staff_id" => $staff_id];
		$rule = [["user_id", "require", "未获取到客户信息"], ["staff_id", "require", "未获取用户信息"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->timely_news($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addnews()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "staff_id" => $staff_id, "post_message" => Request::instance()->param("post_message"), "post_type" => Request::instance()->param("post_type", 1)];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["staff_id", "require"], ["post_message", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if ($data["post_type"] == 2) {
			$qyWechat = new QyWechat($mch_id);
			$data["post_message"] = $qyWechat->DownloadWeixinFile($data["post_message"], "chat_pic");
		}
		$card = new WebindexService();
		$info = $card->addnews($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "消息发送失败";
		}
		if ($data["post_type"] == 1) {
			$post_message = $data["post_message"];
		} else {
			$post_message = "图片";
		}
		$ti = time();
		if ($_SESSION["send_news"]) {
			$arr_formid = $_SESSION["send_news"];
		} else {
			$arr_formid = [];
		}
		$arr_formid[$ti]["tt"] = $ti;
		$arr_formid[$ti]["mch_id"] = $mch_id;
		$arr_formid[$ti]["staff_id"] = $staff_id;
		$arr_formid[$ti]["user_id"] = $data["user_id"];
		$arr_formid[$ti]["post_message"] = $post_message;
		$_SESSION["send_news"] = $arr_formid;
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function send_test()
	{
		$mch_id = Request::instance()->param("mch_id");
		$uid = Request::instance()->param("uid");
		$staff_id = Request::instance()->param("staff_id");
		$send = new send($mch_id);
		$info = $send->send_message($staff_id, $uid, "测试消息");
		return $info;
	}
	public function upload_pic()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "pic_path" => Request::instance()->param("pic_path")];
		$rule = [["mch_id", "require", "不存在商户"], ["pic_path", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$qyWechat = new QyWechat($mch_id);
		$rs["info"] = $qyWechat->DownloadWeixinFile($data["pic_path"], "poster_div_pic");
		$rs["info"] = $this->fileToBase64($rs["info"]);
		return json_encode($rs);
	}
	public function wd_news()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->wd_news($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function wordpool()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->wordpool($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function add_wordpool()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "class_id" => Request::instance()->param("class_id", 0), "value" => Request::instance()->param("value")];
		$rule = [["mch_id", "require", "不存在商户"], ["value", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->add_wordpool($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "添加失败，请重试";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function del_wordpool()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "id" => Request::instance()->param("id")];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->del_wordpool($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "删除失败，请重试";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function suggest()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id, "phone" => Request::instance()->param("phone"), "sug_type" => Request::instance()->param("sug_type"), "content" => Request::instance()->param("content")];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require"], ["phone", "require"], ["sug_type", "require"], ["content", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->suggest($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "提交失败，请重试";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function my_chart()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$type = Request::instance()->param("type", 0);
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->my_chart($data, $type);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function user_chart()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$user_id = Request::instance()->param("user_id");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id, "user_id" => $user_id];
		$type = Request::instance()->param("type", 0);
		$card = new WebindexService();
		$info = $card->user_chart($data, $type);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetCards()
	{
		$rs = array("code" => 0, "info" => array());
		$mch_id = Request::instance()->param("mch_id");
		$uid = Request::instance()->param("uid");
		$staff_id = $this->getBId($uid);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		if (empty($staff_id)) {
			$rs["code"] = 2;
			$rs["msg"] = "没有权限";
			return json_encode($rs);
		}
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->get_card_list($mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function my_chart_boss()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = Request::instance()->param("mch_id");
		$type = Request::instance()->param("type", 0);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if (!empty($uid)) {
			$staff_id = $this->getSId($uid);
			$data["staff_id"] = $staff_id;
			$card = new WebindexService();
			$info = $card->my_chart($data, $type);
			$rs["info"] = $info;
		} else {
			$card = new WebindexService();
			$info = $card->my_chart_boss($data, $type);
			$rs["info"] = $info;
		}
		return json_encode($rs);
	}
	public function setting_card()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($uid);
		$is_relay = Request::instance()->param("is_relay", 1);
		$data = ["mch_id" => $mch_id, "UserId" => $uid];
		$rule = [["mch_id", "require", "不存在商户"], ["UserId", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new WebindexService();
		$info = $card->setting_card($data, $is_relay);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function user_name()
	{
		$uid = Request::instance()->param("uid");
		$info = Db::name("ybmp_user")->where("uid", $uid)->value("nick_name");
		if (empty($info)) {
			$info = "消息";
		}
		return $info;
	}
}
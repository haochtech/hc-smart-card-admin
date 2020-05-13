<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
use think\Request;
use think\Db;
use app\api\service\CardService;
use app\api\service\OffwebService;
use think\Validate;
use think\Cache;
use think\Session;
class Card extends BaseController
{
	public function CardList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$user_id = Request::instance()->param("uid");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$data = ["mch_id" => $mch_id, "user_id" => $user_id];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_CardList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CardInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$pid = Request::instance()->param("pid");
		$uid = Request::instance()->param("uid");
		$data = ["mch_id" => $mch_id, "id" => Request::instance()->param("id")];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		if ($pid > 0 && $uid) {
			$lxm = $card->add_share($data["mch_id"], $uid, $pid);
		}
		$info = $card->get_CardInfo($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "未获取名片，请重试";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SaveEffectTag()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "data" => Request::instance()->param("data"), "id" => Request::instance()->param("id")];
		$rule = [["mch_id", "require", "不存在商户"], ["data", "require", "数据不能为空"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_SaveEffectTag($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Zan()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "c_id" => Request::instance()->param("c_id"), "type" => Request::instance()->param("type_zan", 1)];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["c_id", "require", "未获取数据"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_Zan($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function isLike()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "c_id" => Request::instance()->param("c_id"), "type" => Request::instance()->param("type_zan", 1)];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["c_id", "require", "未获取数据"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_isLike($data);
		if ($info) {
			return json_encode(["code" => 0]);
		} else {
			return json_encode(["code" => 1]);
		}
	}
	public function message()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$staff_id = Request::instance()->param("staff_id");
		$data = ["mch_id" => $mch_id];
		if ($staff_id != 0) {
			$data["staff_id"] = ["in", ["0", $staff_id]];
		}
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_message($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Comment()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "information_id" => Request::instance()->param("in_id"), "details" => Request::instance()->param("details")];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["information_id", "require", "未获取到动态信息"], ["details", "require", "内容不能为空"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->get_Comment($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "评论失败";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Comment_Del()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "id" => Request::instance()->param("id")];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["details", "require", "主键id不能为空"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->delete_Comment($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "评论失败";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function behavior()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "staff_id" => Request::instance()->param("staff_id"), "op_type" => Request::instance()->param("op_type"), "op_name" => Request::instance()->param("op_name"), "de_id" => Request::instance()->param("de_id", 0)];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["op_type", "require"], ["op_name", "require"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->save_behavior($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
		} else {
			$rs["info"] = $info;
			if (Cache::get("user_id_" . $data["op_type"] . $data["user_id"]) && Cache::get("expire_time_" . $data["op_type"] . $data["user_id"]) > time()) {
				$rs["send"] = "001";
			} else {
				$offweb = new OffwebService($mch_id);
				$user_name = Db::name("ybmp_user")->where("uid", $data["user_id"])->value("nick_name");
				$id = $this->getSId($data["staff_id"]);
				if ($data["op_type"] == 6 && $data["de_id"] > 0) {
					if ($data["op_name"] == "商品") {
						$goods = Db::name("ybmp_goods")->where(["mch_id" => $data["mch_id"], "goods_id" => $data["de_id"]])->value("goods_name");
					} else {
						$goods = Db::name("ybmp_product")->where(["mch_id" => $data["mch_id"], "id" => $data["de_id"]])->value("title");
					}
					if ($goods) {
						$data["op_name"] = $data["op_name"] . ":" . $goods;
					} else {
						$data["op_name"] = $data["op_name"] . ":该" . $data["op_name"] . "已不存在";
					}
				}
				$msg = $user_name . " " . $this->status[$data["op_type"]] . "了你的 " . $data["op_name"];
				$rs["send"] = $offweb->send_msg($id, $msg, $data["user_id"]);
				if ($rs["send"] == "ok") {
					Cache::set("user_id_" . $data["op_type"] . $data["user_id"], $data["user_id"]);
					Cache::set("expire_time_" . $data["op_type"] . $data["user_id"], time() + 50);
				}
			}
		}
		return json_encode($rs);
	}
	private $status = array("1" => "查看", "2" => "转发", "3" => "复制", "4" => "保存", "5" => "拨打", "6" => "浏览");
	public function SaveCard()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$fenfa_id = Request::instance()->param("fenfa_id", 0);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "staff_id" => Request::instance()->param("staff_id"), "source" => Request::instance()->param("source")];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["source", "require"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->SaveCard($data, $fenfa_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function timelynews()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$data = ["user_id" => Request::instance()->param("user_id"), "staff_id" => Request::instance()->param("staff_id")];
		$rule = [["user_id", "require", "未获取到用户信息"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->timely_news($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addnews()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "staff_id" => Request::instance()->param("staff_id"), "post_message" => Request::instance()->param("post_message"), "post_type" => Request::instance()->param("post_type", 1)];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"], ["staff_id", "require"], ["post_message", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->addnews($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "消息发送失败";
		}
		$offweb = new OffwebService($mch_id);
		$id = $this->getSId($data["staff_id"]);
		if ($data["post_type"] == 1) {
			$post_message = $data["post_message"];
		} else {
			$post_message = "图片";
		}
		$send = $offweb->send_msg($id, "新留言:" . $post_message, $data["user_id"]);
		$rs["info"] = $info;
		$rs["send"] = $send;
		return json_encode($rs);
	}
	public function sendTest()
	{
		$id = Request::instance()->param("id");
		$user_id = Request::instance()->param("user_id");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$offweb = new OffwebService($mch_id);
		$id = $this->getSId($id);
		$info = $offweb->send_msg($id, "推送测试", $user_id);
		return json_encode($info);
	}
	public function wdnews()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$user_id = Request::instance()->param("user_id");
		$staff_id = Request::instance()->param("staff_id");
		$data = ["mch_id" => $mch_id, "user_id" => $user_id, "staff_id" => $staff_id];
		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"], ["staff_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->wd_news($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function zhaopin()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$data = ["mch_id" => $mch_id, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->zhaopin($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该招聘信息不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function getPhone()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$staff_id = Request::instance()->param("staff_id");
		$user_id = Request::instance()->param("user_id");
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id, "user_id" => $user_id];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require"], ["user_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->getPhone($data);
		if (empty($info["phone"]) || $info["phone"] == "未填写") {
			$rs["code"] = 1;
			$rs["info"] = $info["status"];
			$rs["msg"] = "未填写";
			return json_encode($rs);
		}
		$rs["info"] = $info["status"];
		return json_encode($rs);
	}
	public function savePhone()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$staff_id = Request::instance()->param("staff_id");
		$user_id = Request::instance()->param("user_id");
		$iv = Request::instance()->param("iv");
		$encryptedData = Request::instance()->param("encryptedData");
		$sessionKey = Request::instance()->param("sessionKey");
		$data = ["mch_id" => $mch_id, "staff_id" => $staff_id, "user_id" => $user_id, "iv" => $iv, "encryptedData" => $encryptedData, "sessionKey" => $sessionKey];
		$rule = [["mch_id", "require", "不存在商户"], ["staff_id", "require"], ["user_id", "require"], ["iv", "require"], ["encryptedData", "require"], ["sessionKey", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$card = new CardService();
		$info = $card->savePhone($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function sendcard()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$data = ["mch_id" => $mch_id, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = Db::name("ybmp_sendcard")->where($data)->where("status", 1)->value("user");
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "二维码已失效";
			return json_encode($rs);
		}
		$arr = explode(",", $user);
		$index = mt_rand(0, count($arr) - 1);
		$rs["info"] = $arr[$index];
		return json_encode($rs);
	}
}
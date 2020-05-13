<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Cache;
use app\api\service\DistribeService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
require_once BASE_ROOT . "core/application/api_common.php";
class Distribe extends BaseController
{
	public function userinfo()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$pid = Request::instance()->param("pid", 0);
		$rule = [["uid", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["uid" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->user_info($data, $pid);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该用户不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addman()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$uid = Request::instance()->param("uid");
		$pid = Request::instance()->param("pid");
		$rule = [["uid", "require"], ["pid", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["uid" => $uid, "mch_id" => $mch_id, "pid" => $pid];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->addman($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该用户不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function join()
	{
		$uid = Request::instance()->param("user_id");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["user_id", "require"], ["mch_id", "require", "不存在商户"], ["mobile", "require"], ["name", "require"]];
		$data = ["user_id" => $uid, "mch_id" => $mch_id, "mobile" => Request::instance()->param("mobile"), "name" => Request::instance()->param("name")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_join($data);
		return $info;
	}
	public function myteam()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$rule = [["uid", "require"], ["mch_id", "require", "不存在商户"], ["status", "require"]];
		$data = ["status" => Request::instance()->param("status", 1), "uid" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_myteam($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function shareOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$rule = [["user_id", "require"], ["mch_id", "require", "不存在商户"], ["status", "require"]];
		$data = ["status" => Request::instance()->param("status", -1), "user_id" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_shareOrder($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function shareSetting()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		if (empty($mch_id)) {
			$rs["code"] = 1;
			$rs["msg"] = "未获取到商家信息";
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_shareSetting($mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addCash()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["user_id", "require"], ["mch_id", "require", "不存在商户"], ["mobile", "require"], ["name", "require"], ["price", "require"], ["type", "require"]];
		$data = ["user_id" => Request::instance()->param("user_id"), "mch_id" => $mch_id, "mobile" => Request::instance()->param("mobile"), "name" => Request::instance()->param("name"), "bank_name" => Request::instance()->param("bank_name"), "price" => Request::instance()->param("price"), "status" => 0, "is_del" => 1, "create_time" => time(), "type" => Request::instance()->param("pay_type")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_addCash($data);
		return $info;
	}
	public function CashList()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$rule = [["user_id", "require"], ["mch_id", "require", "不存在商户"], ["status", "require"]];
		$data = ["status" => Request::instance()->param("status", -1), "user_id" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new DistribeService();
		$info = $user->get_CashList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function getShareCode()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["uid", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["uid" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$ACCESS_TOKEN = getWxAccessToken($mch_id);
		if ($ACCESS_TOKEN["errcode"] == 0) {
			$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
			$post_data = array("scene" => $uid, "page" => "yb_mingpian/pages/shop/index");
			$post_data = json_encode($post_data);
			$data = post_data2($url, $post_data, false);
			$data2 = json_decode($data, true);
			if (empty($data2)) {
				$result = $this->data_uri($data, "image/png");
				$rs["info"] = $result;
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "后台清理缓存后重试";
				return json_encode($rs);
			}
		} else {
			$rs["code"] = 1;
			$rs["msg"] = $ACCESS_TOKEN["msg"];
			return json_encode($rs);
		}
		return json_encode($rs);
	}
}
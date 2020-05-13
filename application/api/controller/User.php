<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
require_once BASE_ROOT . "core/application/api_common.php";
use think\Request;
use think\Log;
use app\api\service\UserService;
use app\api\service\AddressService;
use app\api\service\GoodsService;
class User extends BaseController
{
	public function OpenId()
	{
		$rs = array("code" => 0, "info" => array());
		$code = Request::instance()->param("wx_code");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["code", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["code" => $code, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$GLOBALS["mch_id"] = $mch_id;
		$user = new UserService();
		$info = $user->checkLogin($code);
		$rs["info"] = json_decode($info, true);
		if ($rs["info"]["errcode"]) {
			$msg = $rs["info"]["errmsg"];
			$rs["info"]["errmsg"] = getErrCode($rs["info"]["errcode"]);
			if ($rs["info"]["errmsg"] === $rs["info"]["errcode"]) {
				$rs["info"]["errmsg"] = $msg;
			}
		}
		return json_encode($rs);
	}
	public function Login()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$user_headimg = Request::instance()->param("user_headimg");
		$nick_name = Request::instance()->param("nick_name");
		$appid = Request::instance()->param("wx_openid");
		$pid = Request::instance()->param("pid", 0);
		$rule = [["user_headimg", "require"], ["nick_name", "require"], ["wx_openid", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["user_headimg" => $user_headimg, "nick_name" => $nick_name, "wx_openid" => $appid, "mch_id" => $mch_id, "is_distributor" => 0, "reg_time" => time(), "pid" => $pid];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->checkUser($appid, $pid, $mch_id);
		if (!empty($info)) {
			$rs["info"] = $info;
			return json_encode($rs);
		}
		$info = $user->addUser($data);
		if (empty($info)) {
			Log::write("用户添加失败 --" . $appid);
			$rs["code"] = 1;
			$rs["msg"] = "用户添加失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function UserInfo()
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
		$user = new UserService();
		$info = $user->get_userinfo($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DecryptData()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$encryptedData = Request::instance()->param("encryptedData");
		$sessionKey = Request::instance()->param("sessionKey");
		$iv = Request::instance()->param("iv");
		$rule = [["encryptedData", "require"], ["sessionKey", "require"], ["iv", "require"], ["app_id", "require"]];
		$data = ["encryptedData" => $encryptedData, "iv" => $iv, "sessionKey" => $sessionKey, "app_id" => $app_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->decryptData($encryptedData, $sessionKey, $app_id, $iv, $data);
		if ($info == 0) {
			$rs["info"] = json_decode($data, true);
			return json_encode($rs);
		} else {
			$rs["code"] = 1;
			$rs["msg"] = $info;
			return json_encode($rs);
		}
	}
	public function Index()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["uid", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["uid" => $uid, "mch_id" => $mch_id, "app_id" => $app_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->orderCount($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function About()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["uid", "require"]];
		$data = ["mch_id" => $mch_id, "uid" => Request::instance()->param("user_id", 0)];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$index = new UserService();
		$info = $index->about($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CreateAddress()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["uid" => Request::instance()->param("uid"), "consigner" => Request::instance()->param("consigner"), "phone" => Request::instance()->param("phone"), "area" => Request::instance()->param("areaid"), "address" => Request::instance()->param("address"), "zip_code" => Request::instance()->param("zip_code"), "is_default" => Request::instance()->param("is_default", 0)];
		$rule = [["uid", "require|number"], ["consigner", "require"], ["phone", "require|number|length:11", "手机号格式不正确"], ["area", "require|number", "请选择收货地址"], ["address", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$address = new AddressService();
		$info = $address->createAddress($data);
		if (empty($info)) {
			Log::write("用户地址添加失败 --" . $data["uid"]);
			$rs["code"] = 1;
			$rs["msg"] = "用户地址添加失败";
			return json_encode($rs);
		}
		if ($info == "fail") {
			$rs["code"] = 1;
			$rs["msg"] = "您已成功添加地址";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AddressList()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["uid" => Request::instance()->param("uid")];
		$rule = [["uid", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$page = Request::instance()->param("page", 1);
		$address = new AddressService();
		$info = $address->addressList($data, $page);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户无地址";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SingleAddress()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["id" => Request::instance()->param("id"), "uid" => Request::instance()->param("uid")];
		$rule = [["id", "require|number"], ["uid", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$address = new AddressService();
		$info = $address->singleAddress($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户无地址";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function UpdateAddress()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["id" => Request::instance()->param("id"), "uid" => Request::instance()->param("uid"), "consigner" => Request::instance()->param("consigner"), "phone" => Request::instance()->param("phone"), "area" => Request::instance()->param("areaid"), "address" => Request::instance()->param("address"), "zip_code" => Request::instance()->param("zip_code"), "is_default" => Request::instance()->param("is_default", 0)];
		$data = array_filter($data);
		$rule = [["id", "require|number"], ["uid", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$address = new AddressService();
		$info = $address->updateAddress($data);
		if (empty($info)) {
			Log::write("用户地址添加失败 --" . $data["uid"]);
			$rs["code"] = 1;
			$rs["msg"] = "用户地址添加失败";
			return json_encode($rs);
		}
		if ($info == "FAIL") {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在该地址";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelAddress()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["id" => Request::instance()->param("id")];
		$rule = [["id", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$address = new AddressService();
		$info = $address->delAddress($data);
		if (empty($info)) {
			Log::write("用户地址删除失败 --" . $data["id"]);
			$rs["code"] = 1;
			$rs["msg"] = "用户地址删除失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetFavorites()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["uid" => Request::instance()->param("uid")];
		$rule = [["uid", "require|number"]];
		$page = Request::instance()->param("page", 1);
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->getFavorites($data, $page);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "无收藏商品";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
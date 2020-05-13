<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
use think\Request;
use think\Db;
use think\Cache;
use app\api\service\PintuanService;
class Pintuan extends BaseController
{
	public function ptIndex()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在应用"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptIndex($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptGoodsList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$cate_id = Request::instance()->param("cate_id", 0);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在应用"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if ($cate_id != 0) {
			$data["cid"] = $cate_id;
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptGoodsList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptGoodsDetail()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "uid" => Request::instance()->param("uid"), "id" => Request::instance()->param("gid")];
		$rule = [["mch_id", "require", "不存在应用"], ["uid", "require"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptGoodsDetail($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该商品不存在或已被删除";
			return json_encode($data);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptCreateOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "uid" => Request::instance()->param("uid"), "pid" => Request::instance()->param("pid"), "isGroup" => Request::instance()->param("isGroup"), "gid" => Request::instance()->param("gid"), "goodsNum" => Request::instance()->param("goodsNum"), "address" => Request::instance()->param("address"), "limitTime" => Request::instance()->param("limitTime", 0), "totalPrice" => Request::instance()->param("totalPrice")];
		$rule = [["mch_id", "require", "不存在应用"], ["uid", "require"], ["pid", "require"], ["gid", "require"], ["isGroup", "require"], ["goodsNum", "require"], ["address", "require"], ["limitTime", "require"], ["totalPrice", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$data["orderNum"] = $this->createOutTradeNo();
		$bargain = new PintuanService();
		$info = $bargain->get_ptCreateOrder($data);
		if ($info["err_code"] == 1) {
			$rs["code"] = 1;
			$rs["msg"] = $info["msg"] ? $info["msg"] : "下单失败";
			return json_encode($rs);
		} else {
			$rs["info"] = $info["info"];
			return json_encode($rs);
		}
	}
	public function ptOrderList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$status = Request::instance()->param("status", 0);
		$data = ["mch_id" => $mch_id, "uid" => Request::instance()->param("uid")];
		$rule = [["mch_id", "require", "不存在应用"], ["uid", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if ($status != 0) {
			$data["order_status"] = $status;
		} else {
			$data["order_status"] = ["<>", -1];
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptOrderList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptOrderDetail()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "id" => Request::instance()->param("id")];
		$rule = [["mch_id", "require", "不存在应用"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptOrderDetail($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该订单不存在或已被删除";
			return json_encode($data);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptGroupList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$status = Request::instance()->param("status", 0);
		$data = ["mch_id" => $mch_id, "uid" => Request::instance()->param("uid")];
		$rule = [["mch_id", "require", "不存在应用"], ["uid", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bargain = new PintuanService();
		$info = $bargain->get_ptGroupList($data, $page, $status);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptGroupDetail()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "id" => Request::instance()->param("id"), "uid" => Request::instance()->param("uid")];
		$rule = [["mch_id", "require", "不存在应用"], ["id", "require"], ["uid", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$uid = $data["uid"];
		unset($data["uid"]);
		$bargain = new PintuanService();
		$info = $bargain->get_ptGroupDetail($data, $uid);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该拼团不存在或已被删除";
			return json_encode($data);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function SignOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["uid", "require"], ["id", "require"]];
		$data = ["uid" => Request::instance()->param("uid"), "id" => Request::instance()->param("id"), "order_status" => 4];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new PintuanService();
		$info = $order->signOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function RefundOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["uid", "require"], ["id", "require"]];
		$data = ["uid" => Request::instance()->param("uid"), "id" => Request::instance()->param("id"), "order_status" => ["in", "2,3,4"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new PintuanService();
		$info = $order->refundOrder($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单退款失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ptPay()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["id" => Request::instance()->param("oid"), "openid" => Request::instance()->param("openid"), "mch_id" => $mch_id];
		$rule = [["id", "require"], ["openid", "require"], ["mch_id", "require", "不存在应用"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$pay = new PintuanService();
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
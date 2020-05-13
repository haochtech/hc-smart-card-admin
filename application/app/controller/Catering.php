<?php


namespace app\app\controller;

use think\Db;
use think\Request;
use app\app\service\CaterService;
class Catering extends BaseController
{
	public function check_supplier()
	{
		$app_type = Request::instance()->param("app_type");
		$data = Db::table("ims_ybmp_app_check")->where("app_type", $app_type)->order("id", "desc")->find();
		$res["versioncode"] = $data["version"];
		$res["content"] = $data["content"];
		$res["versionname"] = $data["title"];
		$res["url"] = $data["url"];
		$res["new_time"] = date("Y-m-d H:i:s", $res["create_time"]);
		return json_encode($res);
	}
	public function count()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->count($mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CateGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$order = Request::instance()->param("order", 0);
		$by = Request::instance()->param("by", "desc");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->cateGoods($mch_id, $order, $by);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Booklist()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$status = Request::instance()->param("status", '');
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		if ($status == '') {
			$status = ["in", [1, 2]];
		}
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id, "status" => $status];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->Booklist($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CommentList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
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
		$order = new CaterService();
		$info = $order->CommentList($data, $page);
		$info = json_decode($info, true);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderList()
	{
		$rs = array("code" => 0, "info" => array());
		$status = Request::instance()->param("status", null);
		$page = Request::instance()->param("page", 1);
		$app_id = Request::instance()->param("app_id");
		$str_time = Request::instance()->param("str_time", null);
		$end_time = Request::instance()->param("end_time", null);
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		if ($str_time != null && $end_time != null && $str_time < $end_time) {
			$data["create_time"] = ["between", [$str_time, $end_time]];
		}
		if ($status != null) {
			$data["order_status"] = $status;
		} else {
			$data["order_status"] = ["<>", "-1"];
		}
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->orderList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "商户id不能为空"], ["order_id", "require"]];
		$data = ["order_id" => Request::instance()->param("order_id"), "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->getOrder($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function RefundOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$rule = [["order_id", "require"]];
		$data = ["order_id" => Request::instance()->param("order_id"), "order_status" => 4];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$time = time();
		$order = new CaterService();
		$info = $order->RefundOrder($data, $time);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = __TIME($time);
		return json_encode($rs);
	}
	public function storeinfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id, "del" => 1];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->StoreInfo($data);
		if (!empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "店铺信息为空";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function storemodify()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["name", "require", "商家名不能为空"], ["phone", "require"], ["address", "require"], ["lat", "require"], ["lng", "require"], ["job_time", "require"]];
		$data = ["mch_id" => $mch_id, "del" => 1, "name" => Request::instance()->param("name"), "english_name" => Request::instance()->param("english_name"), "phone" => Request::instance()->param("phone"), "address" => Request::instance()->param("address"), "lat" => Request::instance()->param("lat"), "lng" => Request::instance()->param("lng"), "job_time" => Request::instance()->param("job_time"), "logo" => Request::instance()->param("logo")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$store = new CaterService();
		$info = $store->StoreModify($data);
		if (!empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ReplyComment()
	{
		$rs = array("code" => 0, "info" => array());
		$reply = Request::instance()->param("reply");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"], ["reply", "require", "回复不能为空"]];
		$data = ["reply" => $reply, "mch_id" => $mch_id, "id" => Request::instance()->param("id")];
		$up_data = ["reply" => $reply, "reply_time" => time()];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		unset($data["reply"]);
		$order = new CaterService();
		$info = $order->ReplyComment($data, $up_data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "回复失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ModifyState()
	{
		$rs = array("code" => 0, "info" => array());
		$goods_id = Request::instance()->param("goods_id");
		$state = Request::instance()->param("state");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["goods_id", "require"], ["state", "require", "回复不能为空"]];
		$new_state = $state == 0 ? 1 : 0;
		$data = ["goods_id" => $goods_id, "mch_id" => $mch_id, "state" => $state];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->ModifyState($data, $new_state);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ManJian()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$Man = new CaterService();
		$info = $Man->ManJian($mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function BusCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->BusCoupon($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AddManJian()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id", 0);
		$activity_name = Request::instance()->param("activity_name");
		$satisfy_money = Request::instance()->param("satisfy_money");
		$discount_money = Request::instance()->param("discount_money");
		$star_time = Request::instance()->param("star_time");
		$end_time = Request::instance()->param("end_time");
		$is_use = Request::instance()->param("is_use");
		$data = ["mch_id" => $mch_id, "activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "is_use" => $is_use, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["activity_name", "require"], ["satisfy_money", "require"], ["discount_money", "require"], ["star_time", "require"], ["end_time", "require"], ["is_use", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->AddManJian($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AddCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id", 0);
		$activity_name = Request::instance()->param("activity_name");
		$satisfy_money = Request::instance()->param("satisfy_money");
		$discount_money = Request::instance()->param("discount_money");
		$star_time = Request::instance()->param("star_time");
		$end_time = Request::instance()->param("end_time");
		$count = Request::instance()->param("count");
		$together = Request::instance()->param("together");
		$is_use = Request::instance()->param("is_use");
		$data = ["mch_id" => $mch_id, "activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "count" => $count, "rem_count" => $count, "together" => $together, "is_use" => $is_use, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["activity_name", "require"], ["satisfy_money", "require"], ["discount_money", "require"], ["star_time", "require"], ["end_time", "require"], ["count", "require"], ["together", "require"], ["is_use", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->AddCoupon($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function TogetherCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$together = Request::instance()->param("together");
		$data = ["mch_id" => $mch_id, "together" => $together, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["together", "require"], ["id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new CaterService();
		$info = $goods->TogetherCoupon($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ModifyManjian()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("id");
		$state = Request::instance()->param("is_use");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"], ["is_use", "require"]];
		$new_state = $state == 1 ? 2 : 1;
		$data = ["id" => $id, "mch_id" => $mch_id, "is_use" => $state];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->ModifyManjian($data, $new_state);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ModifyCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("id");
		$state = Request::instance()->param("is_use");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"], ["is_use", "require"]];
		$new_state = $state == 1 ? 2 : 1;
		$data = ["id" => $id, "mch_id" => $mch_id, "is_use" => $state];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->ModifyCoupon($data, $new_state);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelManjian()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("id");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$data = ["id" => $id, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->DelManjian($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("id");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];
		$data = ["id" => $id, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new CaterService();
		$info = $order->DelCoupon($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
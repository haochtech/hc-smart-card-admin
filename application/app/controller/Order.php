<?php


namespace app\app\controller;

use think\Db;
use think\Request;
use think\Log;
use app\app\service\OrderService;
class Order extends BaseController
{
	public function count()
	{
		$mch_id = Request::instance()->param("app_id");
		$shop_info = Db::name("ybmp_business_about")->where("mch_id", $mch_id)->find();
		$data["name"] = $shop_info["name"];
		$data["head_img"] = $shop_info["logo"] ? $shop_info["logo"] : "http://vip.ly100.wang/public/static/h-ui.admin/images/admin_icon05.png";
		$time = strtotime(date("Y-m-d", time()));
		$today_str = date("Y-m-d");
		$stime = strtotime($today_str . " 00:00:00");
		$etime = strtotime($today_str . " 23:59:59");
		$data["order_money_today"] = Db::name("ybmp_order")->where("mch_id", $mch_id)->where("pay_status", "1")->where("pay_time", ">=", $stime)->sum("pay_money");
		$data["order_money_all"] = Db::name("ybmp_order")->where("mch_id", $mch_id)->where("pay_status", "1")->sum("pay_money");
		$data["order_today_count"] = Db::name("ybmp_order")->where("mch_id", $mch_id)->where("pay_status", "1")->where("create_time", ">=", $stime)->count();
		$data["order_today_pay_count"] = Db::name("ybmp_order")->where("mch_id", $mch_id)->where("pay_status", "1")->where("pay_time", ">=", $stime)->count();
		$data["order_pay_count"] = Db::name("ybmp_order")->where("mch_id", $mch_id)->where("pay_status", "1")->count();
		$data["manjian_all_count"] = Db::name("ybmp_business_activity")->where("mch_id", $mch_id)->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->count();
		$data["manjian_twoday_count"] = Db::name("ybmp_business_activity")->where("mch_id", $mch_id)->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->where("end_time", "<", time() + 172800)->count();
		$data["coupon_all_count"] = Db::name("ybmp_business_coupon")->where("mch_id", $data["mch_id"])->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->count();
		$data["coupon_twoday_count"] = Db::name("ybmp_business_coupon")->where("mch_id", $data["mch_id"])->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->where("end_time", "<", time() + 172800)->count();
		$manjian_count = Db::name("ybmp_business_activity")->where("mch_id", $mch_id)->count();
		$coupon_count = Db::name("ybmp_business_coupon")->where("mch_id", $mch_id)->count();
		$data["acti_count"] = $coupon_count + $manjian_count;
		$res["code"] = 0;
		$res["info"] = $data;
		exit(json_encode($res, true));
		return $data;
	}
	public function OrderList()
	{
		$rs = array("code" => 0);
		$status = Request::instance()->param("status", null);
		$page = Request::instance()->param("page", 1);
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
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
		$order = new OrderService();
		$info = $order->orderList($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$data = ["order_id" => Request::instance()->param("order_id"), "mch_id" => $app_id];
		$order_info = Db::name("ybmp_order")->where($data)->where("is_deleted", 0)->find();
		if (empty($order_info)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此订单";
			exit(json_encode($rs, true));
		}
		$order_info["pay_time"] = __TIME($order_info["pay_time"]);
		$order_info["finish_time"] = __TIME($order_info["finish_time"]);
		$order_info["create_time"] = __TIME($order_info["create_time"]);
		$order_info["refund_time"] = __TIME($order_info["refund_time"]);
		$a = Db::name("ybmp_order_goods")->where("order_id", $order_info["order_id"])->select();
		foreach ($a as $k => &$v) {
			$pic = Db::name("ybmp_images")->where("img_id", $v["img"])->field("img_cover")->find();
			$v["pic"] = $pic["img_cover"];
		}
		$order_info["goods_list"] = $a;
		$rs["info"] = $order_info;
		return json_encode($rs);
	}
	public function OrderHistory()
	{
		$rs = array("code" => 0);
		$page = Request::instance()->param("page", 1);
		$date = Request::instance()->param("date", date("Y-m-d", time()));
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->orderHistory($data, $date, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderSearch()
	{
		$rs = array("code" => 0);
		$page = Request::instance()->param("page", 1);
		$param = Request::instance()->param("param", '');
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->orderSearch($data, $param, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function OrderDelivery()
	{
		$rs = array("code" => 0);
		$order_id = Request::instance()->param("order_id");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["order_id", "require"]];
		$data = ["mch_id" => $mch_id, "order_id" => $order_id, "order_status" => 1, "is_deleted" => 0];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = new OrderService();
		$info = $order->orderDelivery($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单主体已更改";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ResOrderList()
	{
		$rs = array("code" => 0);
		$status = Request::instance()->param("status", '');
		$page = Request::instance()->param("page", 1);
		$keywords = Request::instance()->param("keywords", '');
		$app_id = input("param.app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		if (!empty($status)) {
			$where["o.order_status"] = $status;
		} else {
			$where["o.order_status"] = ["in", "1,2,3,4,5,6"];
		}
		$where["o.mch_id"] = ["eq", $mch_id];
		$condition["o.order_no"] = array("like", "%" . $keywords . "%");
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$list = Db::name("ybmp_res_order")->alias("o")->join("ybmp_res_desk d", "d.id=o.desk_id")->join("ybmp_user u", "u.uid=o.user_id")->field("o.*,d.name,u.nick_name")->where($where)->where($condition)->page($page, PAGE_NUM)->order("o.create_time desc")->select();
		foreach ($list as $k => $v) {
			$num = 0;
			$json = json_decode($v["order_dish_json"], true);
			foreach ($json as $a => $b) {
				$num += $b;
			}
			$list[$k]["goods_number"] = $num;
		}
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function ResOrderInfo()
	{
		$rs = array("code" => 0);
		$order_id = Request::instance()->param("order_id", '');
		$app_id = input("param.app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$desk_list = Db::name("ybmp_res_order")->where("order_id", $order_id)->find();
		$dish = json_decode($desk_list["order_dish_json"], true);
		$info = array();
		foreach ($dish as $k => $v) {
			$a = array();
			$res = Db::name("ybmp_goods")->where("goods_id", $k)->find();
			$a["goods_name"] = $res["goods_name"];
			$a["price"] = $res["price"];
			$a["number"] = $v;
			$info[] = $a;
		}
		$rs["info"] = $info;
		$rs["res"] = $desk_list;
		return json_encode($rs);
	}
	public function ResOrderRed()
	{
		$rs = array("code" => 0);
		$order_id = Request::instance()->param("order_id", '');
		$app_id = input("param.app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order_info = Db::name("ybmp_res_order")->where("order_id", $order_id)->find();
		$data["refund_ok_time"] = time();
		$data["refund_money"] = $order_info["pay_money"];
		$data["order_status"] = 5;
		$res = Db::name("ybmp_res_order")->where("id", $order_id)->update($data);
		if ($res) {
			$rs["code"] = 0;
			$rs["msg"] = "退款成功！";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "退款失败";
		}
		return json_encode($rs);
	}
	public function ResOrderSta()
	{
		$rs = array("code" => 0);
		$uuid = Request::instance()->param("uuid", '');
		$open_user_id = Request::instance()->param("open_user_id", '');
		$position = Request::instance()->param("position", '');
		$app_id = input("param.app_id");
		$mch_id = $this->getMchId($app_id);
		$rule = [["mch_id", "require", "不存在商户"], ["uuid", "require", "UUID不能为空"], ["open_user_id", "require", "OpenUserId不能为空"], ["position", "require", "位置不能为空"]];
		$data = ["mch_id" => $mch_id, "uuid" => $uuid, "open_user_id" => $open_user_id, "position" => $position];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$bus = Db::name("ybmp_business_stamping")->where("mch_id", $mch_id)->find();
		if ($bus) {
			if ($bus["position"] == $position) {
				$rs["code"] = 1;
				$rs["msg"] = "位置不能重复";
				return json_encode($rs);
			} else {
				$res = Db::name("ybmp_business_stamping")->where("mch_id", $mch_id)->update($data);
				if ($res) {
					$rs["code"] = 0;
					$rs["msg"] = "绑定成功";
				}
			}
		} else {
			$res = Db::name("ybmp_business_stamping")->insert($data);
			if ($res) {
				$rs["code"] = 0;
				$rs["msg"] = "绑定成功";
			}
		}
		return json_encode($rs);
	}
	public function ManJian()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$page = Request::instance()->param("page", 1);
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$info = Db::name("ybmp_business_activity")->where("mch_id", $app_id)->page($page)->select();
		foreach ($info as $act_k => &$act_v) {
			$act_v["star_time"] = __TIME($act_v["star_time"]);
			$act_v["end_time"] = __TIME($act_v["end_time"]);
		}
		$rs["info"] = $info;
		exit(json_encode($rs, true));
	}
	public function BusCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$page = Request::instance()->param("page", 1);
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$info = Db::name("ybmp_business_coupon")->where("mch_id", $app_id)->page($page)->order("id", "desc")->select();
		foreach ($info as $act_k => &$act_v) {
			$act_v["satisfy_money"] = intval($act_v["satisfy_money"]);
			$act_v["discount_money"] = intval($act_v["discount_money"]);
			$act_v["star_time"] = date("Y-m-d", $act_v["star_time"]);
			$act_v["end_time"] = date("Y-m-d", $act_v["end_time"]);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AddManJian()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$id = Request::instance()->param("id", 0);
		$activity_name = Request::instance()->param("activity_name");
		$satisfy_money = Request::instance()->param("satisfy_money");
		$discount_money = Request::instance()->param("discount_money");
		$star_time = Request::instance()->param("star_time");
		$end_time = Request::instance()->param("end_time");
		$is_use = Request::instance()->param("is_use");
		$data = ["mch_id" => $app_id, "activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "is_use" => $is_use, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["activity_name", "require"], ["satisfy_money", "require"], ["discount_money", "require"], ["star_time", "require"], ["end_time", "require"], ["is_use", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if ($data["id"] == 0) {
			unset($data["id"]);
			$info = Db::name("ybmp_business_activity")->insert($data);
		} else {
			$info = Db::name("ybmp_business_activity")->where("id", $data["id"])->update($data);
		}
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
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$id = Request::instance()->param("id", 0);
		$activity_name = Request::instance()->param("activity_name");
		$satisfy_money = Request::instance()->param("satisfy_money");
		$discount_money = Request::instance()->param("discount_money");
		$star_time = Request::instance()->param("star_time");
		$end_time = Request::instance()->param("end_time");
		$count = Request::instance()->param("count");
		$together = Request::instance()->param("together");
		$is_use = Request::instance()->param("is_use");
		$data = ["mch_id" => $app_id, "activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "count" => $count, "rem_count" => $count, "together" => $together, "is_use" => $is_use, "id" => $id];
		$rule = [["mch_id", "require", "不存在商户"], ["activity_name", "require"], ["satisfy_money", "require"], ["discount_money", "require"], ["star_time", "require"], ["end_time", "require"], ["count", "require"], ["together", "require"], ["is_use", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		if ($data["id"] == 0) {
			unset($data["id"]);
			$info = Db::name("ybmp_business_coupon")->insert($data);
		} else {
			$info = Db::name("ybmp_business_coupon")->where("id", $data["id"])->update($data);
		}
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
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$id = Request::instance()->param("id");
		$together = Request::instance()->param("together");
		$data = ["mch_id" => $app_id, "together" => $together, "id" => $id];
		$info = Db::name("ybmp_business_coupon")->where("id", $data["id"])->update(["together" => $data["together"]]);
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
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$new_state = $state == 1 ? 2 : 1;
		$data = ["id" => $id, "mch_id" => $app_id, "is_use" => $state];
		$info = Db::name("ybmp_business_activity")->where($data)->update(["is_use" => $new_state]);
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
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$new_state = $state == 1 ? 2 : 1;
		$data = ["id" => $id, "mch_id" => $app_id, "is_use" => $state];
		$info = Db::name("ybmp_business_coupon")->where($data)->update(["is_use" => $new_state]);
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
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$data = ["id" => $id, "mch_id" => $app_id];
		$info = Db::name("ybmp_business_activity")->where($data)->delete();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			exit(json_encode($rs, true));
		}
		$rs["info"] = $info;
		exit(json_encode($rs, true));
	}
	public function DelCoupon()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("id");
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$data = ["id" => $id, "mch_id" => $app_id];
		$info = Db::name("ybmp_business_coupon")->where($data)->delete();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			exit(json_encode($rs, true));
		}
		$rs["info"] = $info;
		exit(json_encode($rs, true));
	}
	public function OrderCount()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$date = Request::instance()->param("date");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$tsum = Db::name("ybmp_order")->where("mch_id", $app_id)->where("pay_status", "1")->count();
		$tpay = Db::name("ybmp_order")->where("mch_id", $app_id)->where("pay_status", "1")->sum("pay_money");
		if (empty($date) || $date == date("Y-m")) {
			$msum = Db::name("ybmp_order")->where("mch_id", $app_id)->whereTime("create_time", "month")->where("pay_status", "1")->count();
			$mpay = Db::name("ybmp_order")->where("mch_id", $app_id)->whereTime("create_time", "month")->where("pay_status", "1")->sum("pay_money");
		} else {
			$msum = Db::name("ybmp_order")->where("mch_id", $app_id)->whereTime("create_time", "between", [$date . "-1", $date . "-30"])->where("pay_status", "1")->count();
			$mpay = Db::name("ybmp_order")->where("mch_id", $app_id)->whereTime("create_time", "between", [$date . "-1", $date . "-30"])->where("pay_status", "1")->sum("pay_money");
		}
		$rs["mcount"] = $msum;
		$rs["mmoney"] = $mpay;
		$rs["tcount"] = $tsum;
		$rs["tmoney"] = $tpay;
		if (empty($msum)) {
			$rs["msg"] = "本月没有订单";
		} else {
			$rs["msg"] = '';
		}
		exit(json_encode($rs, true));
	}
}
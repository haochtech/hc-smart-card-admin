<?php


namespace app\admin\controller;

use app\admin\service\ArRedis;
use app\admin\service\BusActivity;
use think\Db;
class Activity extends Base
{
	protected $contr_share_set = array("1" => "可提现佣金", "2" => "已提现佣金", "3" => "推荐人", "4" => "未结算佣金", "5" => "分销佣金", "6" => "分销订单", "7" => "提现明细", "8" => "我的团队", "9" => "推广二维码", "10" => "发展下线", "11" => "待打款佣金", "12" => "分销中心", "13" => "分销商");
	protected $contr_pay_type = array("0" => "微信支付", "1" => "支付宝支付", "2" => "银行卡支付", "3" => "余额支付");
	protected $contr_order_status = array("-1" => "已取消", "0" => "待支付", "1" => "待发货", "2" => "待收货", "3" => "已完成", "4" => "退款中", "5" => "已退款");
	protected $contr_share_deep = 3;
	public function index()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$actn = request()->post("search_text", '');
		if ($actn) {
			$url = request()->query();
			$url = explode("=/", $url);
			$url = explode("&", $url[1]);
			$url = "/" . $url[0];
			$list = Db::name("ybmp_business_activity")->where("activity_name", "like", "%{$actn}%")->where("mch_id", $mch_id)->order("star_time", "desc")->paginate(20, false, ["query" => ["s" => $url]]);
		} else {
			$activity = new BusActivity();
			$condition["mch_id"] = array("eq", $mch_id);
			$list = $activity->getActivityAll($condition, '', "star_time desc");
		}
		$page = $list->render();
		$this->assign("search_text", $actn);
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view("activity/index");
	}
	public function addActivity()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$activity = new BusActivity();
		if (request()->isAjax() && request()->isPost()) {
			$activity_name = input("param.activity_name", '');
			$satisfy_money = input("param.satisfy_money", "0");
			$discount_money = input("param.discount_money", "0");
			$star_time = strtotime(input("param.star_time"), "0");
			$end_time = strtotime(input("param.end_time", "0"));
			$is_use = input("param.is_use");
			$res = $activity->addActivity($activity_name, $satisfy_money, $discount_money, $star_time, $end_time, $is_use, $mch_id);
			return AjaxReturn($res);
		}
		return view("activity/add_activity");
	}
	public function editActivity()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$activity = new BusActivity();
		if (request()->isAjax() && request()->isPost()) {
			$id = input("param.id", "0");
			$activity_name = input("param.activity_name", '');
			$satisfy_money = input("param.satisfy_money", "0");
			$discount_money = input("param.discount_money", "0");
			$star_time = strtotime(input("param.star_time"), "0");
			$end_time = strtotime(input("param.end_time", "0"));
			$is_use = input("param.is_use");
			$res = $activity->editActivity($id, $activity_name, $satisfy_money, $discount_money, $star_time, $end_time, $is_use, $mch_id);
			return AjaxReturn($res);
		}
		$id = input("param.id", "0");
		$info = $activity->getActivityInfo($id);
		$this->assign("info", $info);
		return view("activity/edit_activity");
	}
	public function delActivity()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$activity = new BusActivity();
		$id = input("param.id", "0");
		$res = $activity->delActivity($id, $mch_id);
		return AjaxReturn($res);
	}
	public function offActivity()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$activity = new BusActivity();
		$id = input("param.id", "0");
		$res = $activity->offActivity($id);
		return AjaxReturn($res);
	}
	public function onActivity()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$activity = new BusActivity();
		$id = input("param.id", "0");
		$res = $activity->onActivity($id);
		return AjaxReturn($res);
	}
	public function coupon()
	{
		$search_text = request()->param("search_text", '');
		$where = array();
		if (!empty($search_text)) {
			$where["activity_name"] = array("like", "%" . $search_text . "%");
		}
		$list = db::name("ybmp_business_coupon")->where("mch_id", $this->bus_id)->where($where)->order("end_time")->paginate(20);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return view("activity/coupon");
	}
	public function add_coupon()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data["activity_name"] = input("param.activity_name", '');
			$data["satisfy_money"] = input("param.satisfy_money", "0");
			$data["discount_money"] = input("param.discount_money", "0");
			$data["star_time"] = strtotime(input("param.star_time"), "0");
			$data["end_time"] = strtotime(input("param.end_time", "0"));
			$data["is_use"] = input("param.is_use");
			$data["mch_id"] = $this->bus_id;
			$data["count"] = input("param.count", "10");
			$data["rem_count"] = input("param.count", "10");
			$data["get_count"] = input("param.get_count", '');
			$data["together"] = input("param.together", "1");
			$res = db::name("ybmp_business_coupon")->insert($data);
			return AjaxReturn($res);
		}
		return view("activity/coupon_add");
	}
	public function edit_coupon()
	{
	}
	public function off_coupon()
	{
		$id = input("param.id", "0");
		$res = db::name("ybmp_business_coupon")->where("id", $id)->update(["is_use" => 2]);
		return AjaxReturn($res);
	}
	public function on_coupon()
	{
		$id = input("param.id", "0");
		$res = db::name("ybmp_business_coupon")->where("id", $id)->update(["is_use" => 1]);
		return AjaxReturn($res);
	}
	public function together_stop()
	{
		$id = input("param.id", "0");
		$res = db::name("ybmp_business_coupon")->where("id", $id)->update(["together" => 1]);
		return AjaxReturn($res);
	}
	public function together_on()
	{
		$id = input("param.id", "0");
		$res = db::name("ybmp_business_coupon")->where("id", $id)->update(["together" => 2]);
		return AjaxReturn($res);
	}
	public function together_user_list()
	{
		$id = input("param.id");
		$search_text = preg_replace("# #", '', input("param.search_text"));
		$where = array();
		if (!empty($search_text)) {
			$where["c.key"] = array("like", "%" . $search_text . "%");
		}
		$end_time = db::name("ybmp_business_coupon")->where("id", $id)->value("end_time");
		$this->assign("end_time", $end_time);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = db::name("ybmp_user_coupon")->alias("c")->join("ybmp_user u", "u.uid=c.user_id")->where("c.mch_id", $this->bus_id)->where("c.coupon_id", $id)->where($where)->field("u.nick_name,c.*")->order("c.get_time desc")->paginate(4, false, ["query" => ["s" => $url, "id" => $id]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("id", $id);
		$this->assign("search_text", $search_text);
		return view("activity/coupon_user_list");
	}
	public function coupon_user_hx()
	{
		$id = input("param.id");
		$res = db::name("ybmp_user_coupon")->where("id", $id)->update(["use_time" => time(), "status" => 1]);
		return AjaxReturn($res);
	}
	public function user_share()
	{
		@pdo_run("UPDATE " . \think\Config::get("database.prefix") . "ybmp_user SET pid=0 WHERE pid=uid AND is_distributor=1");
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$status = request()->get("status", '');
		$search_name = input("param.search_name", '');
		$condition["s.is_del"] = 1;
		$mch_id = $this->bus_id;
		$condition["s.mch_id"] = $mch_id;
		if (!empty($search_name)) {
			$condition["nick_name"] = ["like", "%" . $search_name . "%"];
		}
		if ($status == "0" || $status == "1") {
			$condition["status"] = $status;
		}
		$user = db::name("ybmp_user_share")->alias("s")->join("ybmp_user u", "s.user_id=u.uid")->field("s.user_id sid,mobile,name,status,create_time,seller_comments,nick_name,total_price,price,pid")->where($condition)->paginate(10, false, $config = ["query" => ["s" => $url]]);
		$d = db::name("ybmp_user_share_setting")->where("mch_id", $mch_id)->field("level")->find();
		$deep = $d["level"];
		$get_child = new BusActivity();
		$res = array();
		for ($i = 0; $i < count($user); $i++) {
			$res[$i] = $user[$i];
			if (strlen($user[$i]["seller_comments"]) > 10) {
				$res[$i]["seller_comments"] = substr($user[$i]["seller_comments"], 0, 9) . "...";
			}
			if ($user[$i]["pid"] == 0) {
				$res[$i]["father"] = "总店";
			} else {
				$pid = db::name("ybmp_user_share")->field("name")->where("user_id", $user[$i]["pid"])->find();
				if (!empty($pid)) {
					$res[$i]["father"] = $pid["name"];
				} else {
					$pid = db::name("ybmp_user")->field("nick_name")->where("uid", $user[$i]["pid"])->find();
					$res[$i]["father"] = $pid["nick_name"];
				}
			}
			$res[$i]["child"] = $get_child->get_child_share($user[$i]["sid"], "-1", $deep);
		}
		$this->assign("status", $status);
		$this->assign("search_name", $search_name);
		$this->assign("list", $res);
		$this->assign("deep", $deep);
		$this->assign("page", $user->render());
		return view();
	}
	public function user_share_edit()
	{
		$id = input("param.id", "0");
		$type = input("param.types", "0");
		switch ($type) {
			case "del_share":
				$sql = "update " . \think\Config::get("database.prefix") . "ybmp_user_share s INNER JOIN " . \think\Config::get("database.prefix") . "ybmp_user u on s.user_id=u.uid set s.is_del = 2,u.is_distributor=0 where s.user_id=" . $id;
				break;
			case "is_pass":
				$sql = "update " . \think\Config::get("database.prefix") . "ybmp_user_share s INNER JOIN " . \think\Config::get("database.prefix") . "ybmp_user u on s.user_id=u.uid set s.status = 1,u.is_distributor=1 where s.user_id=" . $id;
				break;
			case "no_pass":
				$sql = "update " . \think\Config::get("database.prefix") . "ybmp_user_share s INNER JOIN " . \think\Config::get("database.prefix") . "ybmp_user u on s.user_id=u.uid set s.status = 2,u.is_distributor=0 where s.user_id=" . $id;
				break;
			case "all_pass":
				$check = new BusActivity();
				$id = $check->screen_id("ybmp_user_share", $id, "user_id", 0, "status");
				if (strlen($id) == 0) {
					return 1;
				}
				$sql = "update " . \think\Config::get("database.prefix") . "ybmp_user_share s INNER JOIN " . \think\Config::get("database.prefix") . "ybmp_user u on s.user_id=u.uid set s.status = 1,u.is_distributor=1 where s.user_id in ( " . $id . " )";
				break;
		}
		if ($sql == '') {
			return 0;
		} else {
			@pdo_run($sql);
			return 1;
		}
	}
	public function comment_edit()
	{
		$id = request()->get("id", "0");
		$comment = db::name("ybmp_user_share")->where("user_id", $id)->field("seller_comments,user_id")->find();
		$this->assign("data", $comment);
		return view("activity/comment_edit");
	}
	public function update_comment()
	{
		$id = input("param.id");
		$com = request()->post("com");
		if (!empty($com)) {
			$sql = "update " . \think\Config::get("database.prefix") . "ybmp_user_share set seller_comments='" . $com . "' where user_id=" . $id;
			pdo_run($sql);
			return "2";
		} else {
			return "1";
		}
	}
	public function child_show()
	{
		$get_child = new BusActivity();
		$pid = request()->get("pid");
		$deep = request()->get("deep");
		$child = $get_child->get_child_share($pid, $deep);
		$this->assign("list", $child);
		return view();
	}
	public function recancel()
	{
		$pid = request()->post("id");
		$res = db::name("ybmp_user")->where("uid", $pid)->update(["pid" => 0]);
		return AjaxReturn($res);
	}
	public function share_set()
	{
		$id = $this->bus_id;
		$pay_type = request()->post("pay_type");
		$min_money = request()->post("min_money");
		$max_money = request()->post("max_money");
		$content = request()->post("content");
		$agree = request()->post("agree");
		$share_condition = request()->post("share_condition");
		$is_rebate = request()->post("is_rebate");
		$level = request()->post("level");
		$count = db::name("ybmp_user_share_setting")->where("mch_id", $id)->count();
		if (!empty($level) || $level == "0") {
			if ($count == 0) {
				$res = db::name("ybmp_user_share_setting")->insert(["level" => $level, "pay_type" => $pay_type, "min_money" => $min_money, "max_money" => $max_money, "content" => $content, "share_condition" => $share_condition, "agree" => $agree, "mch_id" => $this->bus_id, "is_rebate" => $is_rebate]);
			} else {
				$res = db::name("ybmp_user_share_setting")->where("mch_id", $id)->update(["level" => $level, "pay_type" => $pay_type, "min_money" => $min_money, "max_money" => $max_money, "content" => $content, "share_condition" => $share_condition, "agree" => $agree, "mch_id" => $this->bus_id, "is_rebate" => $is_rebate]);
			}
			$data = "2";
			return $data;
		}
		$share = db::name("ybmp_user_share_setting")->where("mch_id", $id)->find();
		if (count($share) == 0) {
			$share["pay_type"] = '';
			$share["level"] = 0;
			$share["is_rebate"] = 0;
			$share["min_money"] = "0.00";
			$share["max_money"] = "0.00";
			$share["content"] = '';
			$share["agree"] = '';
		}
		$share["pay_type"] = json_decode($share["pay_type"], true);
		$this->assign("share", $share);
		return view();
	}
	public function share_pay()
	{
		$id = $this->bus_id;
		if (request()->isAjax() && request()->isPost()) {
			$share = db::name("ybmp_user_share_setting")->where("mch_id", $id)->update(request()->post());
			return "0";
		} else {
			$share = db::name("ybmp_user_share_setting")->where("mch_id", $id)->field("id,level,first_name,second_name,third_name,first,second,third,price_type")->find();
			if (count($share) == 0) {
				$share["level"] = 0;
			}
			switch ($share["level"]) {
				case 0:
					break;
				case 1:
					$share["second"] = "-1";
					$share["third"] = "-1";
					break;
				case 2:
					$share["third"] = "-1";
					break;
			}
			if (!empty($share["price_type"]) && $share["price_type"] != 0) {
				$share["icon"] = "元";
			} else {
				$share["icon"] = "%";
				if ($share["level"] == 0) {
					$share["level"] = "6";
					$share["price_type"] = "0";
					$share["second"] = "-1";
					$share["third"] = "-1";
				}
			}
		}
		$this->assign("share", $share);
		return view();
	}
	public function share_other()
	{
		$id = $this->bus_id;
		if (request()->isAjax() && request()->isPost()) {
			$share = db::name("ybmp_user_share_setting")->where("mch_id", $id)->update(request()->post());
			return "0";
		}
		$share = db::name("ybmp_user_share_setting")->where("mch_id", $id)->field("other")->find();
		$share_default = $this->contr_share_set;
		if ($share["other"]) {
			$share = json_decode($share["other"], true);
		} else {
			$share = $share_default;
		}
		$this->assign("share", $share);
		$this->assign("share_default", $share_default);
		return view();
	}
	public function set_share_title()
	{
		$type = input("param.type");
		$val = input("param.val");
		$info = Db::name("ybmp_user_share_setting")->where("mch_id", $this->bus_id)->value("other");
		if (empty($info)) {
			$data = $this->contr_share_set;
		} else {
			$data = json_decode($info, true);
		}
		$data[$type] = $val;
		$new_data = json_encode($data);
		$res = Db::name("ybmp_user_share_setting")->where("mch_id", $this->bus_id)->update(["other" => $new_data]);
		return AjaxReturn($res);
	}
	public function share_cash()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$status = request()->get("status", '');
		if ($status == "-1") {
			$nn = "2";
		} else {
			$nn = '';
		}
		empty($status) ? $condition["status"] = array("in", "0,1,2,3") : ($condition["status"] = $status);
		if ($nn == "2") {
			$condition["status"] = "0";
		}
		$condition["c.is_del"] = 1;
		$condition["c.mch_id"] = $this->bus_id;
		$cash = db::name("ybmp_user_share_cash")->alias("c")->join("ybmp_user u", "u.uid=c.user_id")->field("c.id,c.type,u.nick_name,u.price total,c.price price,c.create_time,c.status,c.bank_name,c.mobile")->where($condition)->paginate(10, false, $config = ["query" => ["s" => $url]]);
		$type = $this->contr_pay_type;
		$res = array();
		for ($i = 0; $i < count($cash); $i++) {
			$res[$i] = $cash[$i];
			$res[$i]["type"] = $type[$cash[$i]["type"]];
			$res[$i]["create_time"] = date("Y-m-d H:i:s", $cash[$i]["create_time"]);
		}
		if ($nn == "2") {
			$condition["status"] = "-1";
		}
		empty($status) ? $condition["status"] = '' : '';
		$this->assign("cash", $res);
		$this->assign("page", $cash->render());
		$this->assign("status", $condition["status"]);
		return view();
	}
	public function share_cash_edit()
	{
		$id = request()->post("id");
		$status = request()->post("status");
		$info = 0;
		Db::startTrans();
		try {
			if ($status == 0 || $status == 1 || $status == 2 || $status == 3) {
				$info = Db::name("ybmp_user_share_cash")->where("id", $id)->update(["status" => $status]);
				if ($status == 3) {
					$u = Db::name("ybmp_user_share_cash")->where("id", $id)->find();
					$rs = Db::name("ybmp_user")->where("uid", $u["user_id"])->setInc("price", $u["price"]);
					if (empty($rs)) {
						throw new Exception("0");
					}
				}
			} else {
				$info = Db::name("ybmp_user_share_cash")->where("id", $id)->update(["is_del" => 2]);
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return 0;
		}
		return $info;
	}
	public function share_order()
	{
		$order = new BusActivity();
		$order_status = input("param.status", '');
		$stime = input("param.stime", '');
		$etime = input("param.etime", '');
		$order_no = input("param.order_no", '');
		$mch_id = $this->bus_id;
		$condition = array();
		$condition["s.mch_id"] = $mch_id;
		if (!empty($order_no)) {
			$condition["o.order_no"] = ["like", "%" . $order_no . "%"];
		}
		if (!empty($order_status)) {
			$condition["o.order_status"] = $order_status;
		}
		if (!empty($stime)) {
			$sstime = strtotime($stime);
			$condition["s.create_time"] = ["between", [$sstime, $sstime + 86400]];
		}
		if (!empty($etime)) {
			$eetime = strtotime($etime);
			$condition["s.create_time"] = ["between", [$eetime - 86400, $eetime]];
		}
		if (!empty($stime) && !empty($etime)) {
			$stime = strtotime($stime);
			$etime = strtotime($etime);
			$condition["s.create_time"] = ["between", [$stime, $etime]];
			$stime = date("Y-m-d", $stime);
			$etime = date("Y-m-d", $etime);
		}
		$list = $order->get_order_list($condition, $order_no);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		$this->assign("stime", $stime);
		$this->assign("etime", $etime);
		$this->assign("order_no", $order_no);
		return view();
	}
	public function order_share()
	{
		$user_name = request()->param("user_name", '');
		$stime = request()->param("star_time", '');
		$etime = request()->param("end_time", '');
		if (!empty($user_name)) {
			$where["s.user_name"] = ["like", "%" . $user_name . "%"];
		}
		if (!empty($stime)) {
			$where["a.create_time"] = ["gt", strtotime($stime)];
		}
		if (!empty($etime)) {
			$where["a.create_time"] = ["lt", strtotime($etime)];
		}
		$where["a.mch_id"] = $this->bus_id;
		$li = $this->get_list($where);
		$a = array();
		for ($i = 0; $i < count($li); $i++) {
			$a[$i] = $li[$i];
			$a[$i]["status"] = $this->contr_order_status[$li[$i]["order_status"]];
		}
		$this->assign("show", "staff");
		$this->assign("list", $a);
		$this->assign("user_name", $user_name);
		$this->assign("star_time", $stime);
		$this->assign("end_time", $etime);
		$this->assign("page", $li->render());
		return view("staff_share");
	}
	public function send_share()
	{
		$user_name = request()->param("user_name", '');
		$stime = request()->param("star_time", '');
		$etime = request()->param("end_time", '');
		if (!empty($user_name)) {
			$where["s.user_name"] = ["like", "%" . $user_name . "%"];
		}
		if (!empty($stime)) {
			$where["a.create_time"] = ["gt", strtotime($stime)];
		}
		if (!empty($etime)) {
			$where["a.create_time"] = ["lt", strtotime($etime)];
		}
		$where["a.mch_id"] = $this->bus_id;
		$where["d.order_status"] = 3;
		$li = $this->get_list($where);
		$a = array();
		for ($i = 0; $i < count($li); $i++) {
			$a[$i] = $li[$i];
			$a[$i]["status"] = $this->contr_order_status[$li[$i]["order_status"]];
		}
		$this->assign("show", "send");
		$this->assign("list", $a);
		$this->assign("user_name", $user_name);
		$this->assign("star_time", $stime);
		$this->assign("end_time", $etime);
		$this->assign("page", $li->render());
		return view("staff_share");
	}
	public function disend_share()
	{
		$user_name = request()->param("user_name", '');
		$stime = request()->param("star_time", '');
		$etime = request()->param("end_time", '');
		if (!empty($user_name)) {
			$where["s.user_name"] = ["like", "%" . $user_name . "%"];
		}
		if (!empty($stime)) {
			$where["a.create_time"] = ["gt", strtotime($stime)];
		}
		if (!empty($etime)) {
			$where["a.create_time"] = ["lt", strtotime($etime)];
		}
		$where["a.mch_id"] = $this->bus_id;
		$where["d.order_status"] = ["in", "0,1,2"];
		$li = $this->get_list($where);
		$a = array();
		for ($i = 0; $i < count($li); $i++) {
			$a[$i] = $li[$i];
			$a[$i]["status"] = $this->contr_order_status[$li[$i]["order_status"]];
		}
		$this->assign("show", "disend");
		$this->assign("list", $a);
		$this->assign("user_name", $user_name);
		$this->assign("star_time", $stime);
		$this->assign("end_time", $etime);
		$this->assign("page", $li->render());
		return view("staff_share");
	}
	public function show_order_dd()
	{
		$id = request()->param("order_id");
		$send = request()->param("send");
		$li = db::name("ybmp_order_goods")->alias("a")->join("ybmp_images s", "a.goods_images=s.img_id")->field("a.goods_name,a.price,a.num,a.goods_money,s.img_cover")->where("a.order_id", $id)->select();
		$a = db::name("ybmp_order")->where("order_id", $id)->field("order_money")->find();
		$this->assign("list", $li);
		$this->assign("send", $send);
		$this->assign("all", $a["order_money"]);
		return view();
	}
	public function get_list($where)
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$li = db::name("ybmp_order_staff")->alias("a")->join("ybmp_bus_card s", "a.staff_id=s.id", "left")->join("ybmp_order d", "a.order_id=d.order_id", "left")->field("a.money,s.user_name,s.position,s.head_photo,s.wxtel,d.order_status,d.order_id,a.create_time")->order("a.create_time", "desc")->where($where)->paginate(10, false, $config = ["query" => ["s" => $url]]);
		$a = array();
		for ($i = 0; $i < count($li); $i++) {
			$a[$i] = $li[$i];
			$a[$i]["status"] = $this->contr_order_status[$li[$i]["order_status"]];
		}
		return $li;
	}
	public function miaosha()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$where["mch_id"] = $this->bus_id;
		$where["type"] = 1;
		$where["is_del"] = 2;
		$name = str_replace(" ", '', request()->param("search_text"));
		if (!empty($name)) {
			$where["name"] = ["like", "%" . $name . "%"];
		}
		$res = Db::name("ybmp_activity")->where($where)->paginate(15, false, $config = ["query" => ["s" => $url, "name" => $name]]);
		$list = array();
		for ($i = 0; $i < count($res); $i++) {
			$list[$i] = $res[$i];
			$list[$i]["sell"] = Db::name("ybmp_activity_order")->where("activity_order_type", 3)->where("bargain_id", $list[$i]["id"])->count();
			$list[$i]["stime"] = date("Y-m-d H:i:s", $res[$i]["stime"]);
			$list[$i]["etime"] = date("Y-m-d H:i:s", $res[$i]["etime"]);
			$list[$i]["max_pre"] = $res[$i]["max_pre"] == 0 ? "无限" : $res[$i]["max_pre"];
			$list[$i]["img"] = json_decode($res[$i]["img"], true);
			if ($res[$i]["stime"] > time()) {
				$list[$i]["now"] = "未开始";
			}
			if ($res[$i]["stime"] < time() && $res[$i]["etime"] > time()) {
				$list[$i]["now"] = "进行中";
				$list[$i]["show_order"] = 1;
			}
			if ($res[$i]["etime"] < time()) {
				$list[$i]["now"] = "已截止";
				$list[$i]["show_order"] = 1;
			}
			if ($res[$i]["status"] == 2) {
				$list[$i]["now"] = "已停用";
			}
			$where = ["activity_order_type" => 3, "order_status" => ["in", [1, 2, 3]], "pay_status" => 1, "is_deleted" => 0, "mch_id" => $where["mch_id"], "bargain_id" => $res[$i]["id"]];
			$list[$i]["is_buy"] = Db::name("ybmp_activity_order")->where($where)->count();
			$list[$i]["kucun"] = intval($res[$i]["all_sell"] - $list[$i]["is_buy"]);
		}
		$this->assign("list", $list);
		$this->assign("page", $res->render());
		return view();
	}
	public function add_miaosha()
	{
		$type = request()->param("type", 1);
		if ($type == 1) {
			$show = "add_miaosha";
		}
		if (request()->isPost() && request()->isAjax()) {
			if ($type == 1) {
				$data = request()->post();
				$data["stime"] = strtotime($data["stime"]);
				$data["etime"] = strtotime($data["etime"]);
				$data["mch_id"] = $this->bus_id;
				$table = "ybmp_activity";
			}
			$res = Db::name($table)->insert($data);
			return AjaxReturn($res . json_decode($data));
		}
		return view($show);
	}
	public function edit_miaosha()
	{
		$type = request()->param("type", 1);
		$id = request()->param("id");
		if (request()->isPost() && request()->isAjax()) {
			if ($type == 1) {
				$data = request()->post();
				unset($data["id"]);
				$data["stime"] = strtotime($data["stime"]);
				$data["etime"] = strtotime($data["etime"]);
				$table = "ybmp_activity";
				$where["id"] = $id;
			}
			$res = Db::name($table)->where($where)->update($data);
			return AjaxReturn($res . json_decode($data));
		}
		if ($type == 1) {
			$info = Db::name("ybmp_activity")->where("id", $id)->find();
			$info["stime"] = date("Y-m-d H:i:s", $info["stime"]);
			$info["etime"] = date("Y-m-d H:i:s", $info["etime"]);
			$this->assign("info", $info);
			$show = "edit_miaosha";
		}
		return view($show);
	}
	public function order_send()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_activity_order")->where("order_id", $id)->update(["order_status" => 2]);
		return AjaxReturn($res);
	}
	public function ac_order()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$type = request()->param("type", 1);
		$status = request()->param("status", -2);
		$search_text = request()->param("search_text", '');
		if ($type == 1) {
			$show = "miaosha_order";
			$id = request()->param("acid", '');
			if (!empty($id)) {
				$where["s.bargain_id"] = $id;
			}
			$where["s.activity_order_type"] = 3;
			$where["s.is_deleted"] = 0;
			$where["s.mch_id"] = $this->bus_id;
			$where["s.order_status"] = $status;
			if ($status == -2) {
				$where["s.order_status"] = array("in", "0,1,2,3,4,5,-1");
			}
			if (!empty($search_text)) {
				$where["a.name"] = array("like", "%" . $search_text . "%");
			}
			$list = Db::name("ybmp_activity_order")->alias("s")->join("ybmp_activity a", "s.mch_id=a.mch_id AND s.bargain_id=a.id", "left")->where($where)->field("s.order_no,a.name,a.pic,s.user_name,s.receiver_mobile,s.receiver_area,s.receiver_address,s.order_money,s.order_status,s.order_id")->order("s.create_time desc")->paginate(15, false, $config = ["query" => ["s" => $url, "status" => $status, "search_text" => $search_text]]);
		}
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$this->assign("list", $list);
		$this->assign("status", $status);
		$this->assign("search_text", $search_text);
		$this->assign("page", $list->render());
		return view($show);
	}
	public function change()
	{
		$type = request()->param("type", 1);
		$id = request()->param("id");
		if ($type == 1) {
			$where["id"] = $id;
			$where["mch_id"] = $this->bus_id;
			$update["status"] = request()->param("status", 1);
			$table = "ybmp_activity";
		}
		$res = 0;
		if (!empty($where) && !empty($table)) {
			$res = Db::name($table)->where($where)->update($update);
		}
		return AjaxReturn($res);
	}
	public function del()
	{
		$type = request()->param("type", 1);
		$id = request()->param("id");
		if ($type == 1) {
			$where["id"] = $id;
			$where["mch_id"] = $this->bus_id;
			$table = "ybmp_activity";
			$update["is_del"] = 1;
		}
		$res = 0;
		if (!empty($where) && !empty($table)) {
			if (!empty($update)) {
				$res = Db::name($table)->where($where)->update($update);
			} else {
				$res = Db::name($table)->where($where)->delete();
			}
		}
		return AjaxReturn($res);
	}
}
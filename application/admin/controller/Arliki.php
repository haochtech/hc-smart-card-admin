<?php


namespace app\admin\controller;

use app\admin\service\ArlikiService;
use think\Db;
use think\Cache;
load()->model("wxapp");
load()->model("user");
class Arliki extends Base
{
	public function __construct()
	{
		parent::__construct();
	}
	public $op = array(1 => "查看", 2 => "转发", 3 => "复制", 4 => "保存", 5 => "拨打", 6 => "浏览");
	public $so = array(1 => "搜索", 2 => "扫码", 3 => "分享");
	public $red = 1;
	public function exp_load()
	{
		$no = request()->param("no", '');
		$json = request()->param("JSON", false);
		$show = request()->param("show", false);
		if (!empty($no)) {
			$ser = new ArlikiService($this->bus_id);
			$res = $ser->exp_load($no);
			if ($res["status"] != 0) {
				$data["code"] = 0;
				$data["data"] = $res["msg"];
			} else {
				$data["code"] = 1;
				$data["data"] = $res["list"];
			}
		} else {
			$data["data"] = "单号异常,请核实";
			$data["code"] = 0;
		}
		if ($show) {
			$this->assign("list", $data);
			$this->assign("code", $data["code"]);
			return view();
		}
		$json && ($data = json_encode($data));
		return $data;
	}
	public function send_sms()
	{
		$date = request()->param("content", '');
		$phone = request()->param("phone", '');
		$param["code"] = $date;
		$ser = new ArlikiService($this->bus_id);
		$res = $ser->send_sms($param, $phone, true);
		var_dump($res);
	}
	public function get_barcode($data, $width = 200, $height = 200)
	{
		$str = "<div id=\"bar_code\"></div><script>\$(function(){\$(\"#bar_code\").qrcode({width:" . $width . ",height:" . $height . ",text:\"" . $data . "\"});});</script>";
		return $str;
	}
	public function get_qrcode($data, $width = 1, $height = 20, $font_size = 10, $bg = "#fff", $line_color = "#000", $show_value = true, $txt_margin = 0)
	{
		if (!$show_value) {
			$show_value = "false";
		}
		$str = "<img src=\"\" id=\"qr_code\"><script>var options = {format: \"CODE128\",width:" . $width . ",height:" . $height . ",displayValue:" . $show_value . ",text:\"\",fontOptions:\"\",font:\"微软雅黑\",textAlign:\"center\",textPosition:\"bottom\",textMargin:" . $txt_margin . ",fontSize:" . $font_size . ",background:\"" . $bg . "\",lineColor:\"" . $line_color . "\",margin:0};\$(function(){\$(\"#qr_code\").JsBarcode(\"" . $data . "\",options);});</script>";
		return $str;
	}
	public function custom()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$staff_id = request()->param("staff_id", '');
		$stime = request()->param("stime", '');
		$etime = request()->param("etime", '');
		$depart_id = request()->param("depart", 0);
		$star_time = strtotime($stime);
		$end_time = strtotime($etime);
		$where["c.mch_id"] = $this->bus_id;
		$where["c.user_id"] = $where["c.staff_id"] = [">", 0];
		empty($staff_id) || ($where["staff_id"] = $staff_id);
		if (empty($stime) && !empty($etime)) {
			$where["c.create_time"] = ["<", $end_time];
		}
		if (!empty($stime) && empty($etime)) {
			$where["c.create_time"] = [">", $star_time];
		}
		if (!empty($stime) && !empty($etime)) {
			$where["c.create_time"] = ["between", [$star_time, $end_time]];
		}
		$staff = $depart_id > 0 ? Db::name("ybmp_bus_card")->where("did", $depart_id)->where("mch_id", $this->bus_id)->field("user_name,id")->select() : '';
		$res = Db::name("ybmp_customer")->alias("c")->join("ybmp_bus_card d", "c.staff_id=d.id", "left")->join("ybmp_user u", "c.user_id=u.uid", "left")->field("c.user_id,c.staff_id,u.user_headimg head,c.remark,u.nick_name,d.user_name,c.source,c.tel")->where("u.uid is not null")->where($where)->group("c.user_id")->order("c.create_time", "desc")->paginate(15, false, $config = ["query" => ["s" => $url, "staff_id" => $staff_id, "depart" => $depart_id, "stime" => $stime, "etime" => $etime]]);
		$list = array();
		for ($i = 0; $i < count($res); $i++) {
			$list[$i] = $res[$i];
			$list[$i]["source"] = $this->so[$res[$i]["source"]];
			if (empty($res[$i]["remark"]) || $res[$i]["remark"] == "昵称") {
				$list[$i]["remark"] = $res[$i]["nick_name"];
			}
			if (empty($res[$i]["tel"]) || $res[$i]["tel"] == "未填写") {
				$list[$i]["tel"] = "未授权";
			}
			$list[$i]["click"] = Db::name("ybmp_user_oplog")->where(["user_id" => $res[$i]["user_id"], "staff_id" => $res[$i]["staff_id"]])->count();
			$list[$i]["click"] == 0 ? $res[$i]["click"] = 1 : '';
			$list[$i]["last_time"] = Db::name("ybmp_user_oplog")->where(["user_id" => $res[$i]["user_id"], "staff_id" => $res[$i]["staff_id"]])->order("create_time", "desc")->value("create_time");
			$list[$i]["other"] = Db::name("ybmp_customer")->where("user_id", $res[$i]["user_id"])->where("mch_id", $this->bus_id)->whereNotIn("staff_id", $res[$i]["staff_id"])->group("staff_id")->field("staff_id")->count();
		}
		$this->assign("depart", Db::name("ybmp_depart")->where("mch_id", $this->bus_id)->order("id")->select());
		$this->assign("page", $res->render());
		$this->assign("list", $list);
		$this->assign("stime", $stime);
		$this->assign("etime", $etime);
		$this->assign("staff", $staff);
		$this->assign("staff_id", $staff_id);
		$this->assign("depart_id", $depart_id);
		return view();
	}
	public function show_other()
	{
		$where["c.mch_id"] = $this->bus_id;
		$where["c.user_id"] = request()->param("user_id");
		$staff_id = request()->param("staff_id");
		$res = Db::name("ybmp_customer")->alias("c")->join("ybmp_bus_card d", "c.staff_id=d.id", "left")->field("d.user_name,c.source,d.head_photo,c.create_time,c.staff_id,c.tel,c.user_id")->where($where)->whereNotIn("staff_id", $staff_id)->group("c.staff_id")->order("c.create_time", "desc")->select();
		$list = array();
		for ($i = 0; $i < count($res); $i++) {
			$list[$i] = $res[$i];
			$list[$i]["source"] = $this->so[$res[$i]["source"]];
			$list[$i]["click"] = Db::name("ybmp_user_oplog")->where(["user_id" => $where["c.user_id"], "staff_id" => $res[$i]["staff_id"]])->count();
			$list[$i]["last_time"] = Db::name("ybmp_user_oplog")->where(["user_id" => $res[$i]["user_id"], "staff_id" => $res[$i]["staff_id"]])->order("create_time", "desc")->value("create_time");
			$list[$i]["click"] == 0 ? $list[$i]["click"] = 1 : '';
		}
		$this->assign("list", $list);
		return view();
	}
	public function show_staff()
	{
		$id = request()->param("id");
		$res = Db::name("ybmp_bus_card")->where("did", $id)->where("mch_id", $this->bus_id)->field("user_name,id")->select();
		return json_encode($res);
	}
	public function ai_show()
	{
		$staff_id = request()->param("staff_id");
		$user_id = request()->param("user_id");
		$goods1 = request()->param("goods", 2);
		$mch_id = $this->bus_id;
		$arr = array();
		if ($goods1 == 1) {
			$goods = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "op_type" => 6])->group("de_id")->field("*,count(op_type) num")->select();
			for ($i = 0; $i < count($goods); $i++) {
				if ($goods[$i]["op_name"] == "商品") {
					$r = Db::name("ybmp_goods")->where("goods_id", $goods[$i]["de_id"])->value("goods_name");
				} else {
					$r = Db::name("ybmp_product")->where("id", $goods[$i]["de_id"])->value("title");
				}
				$goods[$i]["type"] = $this->op[$goods[$i]["op_type"]];
				$goods[$i]["goods_name"] = $r;
				array_push($arr, $goods[$i]);
			}
		} else {
			$shop = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "de_id" => 0, "op_name" => "商城"])->group("op_type")->field("*,count(op_type) num")->select();
			$web = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "de_id" => 0, "op_name" => "官网"])->group("op_type")->field("*,count(op_type) num")->select();
			$dy = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "de_id" => 0, "op_name" => "动态"])->group("op_type")->field("*,count(op_type) num")->select();
			$card = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "de_id" => 0, "op_name" => "名片"])->group("op_type")->field("*,count(op_type) num")->select();
			for ($i = 0; $i < count($shop); $i++) {
				$shop[$i]["type"] = $this->op[$shop[$i]["op_type"]];
				$la = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "op_name" => "商城", "op_type" => $shop[$i]["op_type"]])->order("create_time", "desc")->value("create_time");
				$shop[$i]["last_time"] = $la;
				array_push($arr, $shop[$i]);
			}
			for ($i = 0; $i < count($web); $i++) {
				$web[$i]["type"] = $this->op[$web[$i]["op_type"]];
				$la = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "op_name" => "官网", "op_type" => $web[$i]["op_type"]])->order("create_time", "desc")->value("create_time");
				$web[$i]["last_time"] = $la;
				array_push($arr, $web[$i]);
			}
			for ($i = 0; $i < count($dy); $i++) {
				$dy[$i]["type"] = $this->op[$dy[$i]["op_type"]];
				$la = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "op_name" => "动态", "op_type" => $dy[$i]["op_type"]])->order("create_time", "desc")->value("create_time");
				$dy[$i]["last_time"] = $la;
				array_push($arr, $dy[$i]);
			}
			for ($i = 0; $i < count($card); $i++) {
				$card[$i]["type"] = $this->op[$card[$i]["op_type"]];
				$la = Db::name("ybmp_user_oplog")->where(["staff_id" => $staff_id, "user_id" => $user_id, "mch_id" => $mch_id, "op_name" => "名片", "op_type" => $card[$i]["op_type"]])->order("create_time", "desc")->value("create_time");
				$card[$i]["last_time"] = $la;
				array_push($arr, $card[$i]);
			}
		}
		$this->assign("goods", $goods1);
		$this->assign("list", $arr);
		return view();
	}
	public function change_skin()
	{
		$mch_id = $this->bus_id;
		$id = request()->param("id", 2);
		$data["zhuti"]["back"] = $id;
		$other["other"] = json_encode($data);
		$re = Db::name("ybmp_business_about")->where("mch_id", $mch_id)->find();
		if (empty($re)) {
			$other["mch_id"] = $this->bus_id;
			$res = Db::name("ybmp_business_about")->insert($other);
		} else {
			$res = Db::name("ybmp_business_about")->where("mch_id", $mch_id)->update($other);
		}
		Cache::set("skin" . $this->bus_id, null);
		return AjaxReturn($res);
	}
	public function jiaren()
	{
		if (request()->isAjax() && request()->isPost()) {
			$da["owner_uid"] = 0;
			$da["groupid"] = 1;
			$da["founder_groupid"] = 0;
			$da["username"] = str_replace(" ", '', request()->param("username"));
			$da["salt"] = "arliki";
			$da["type"] = 1;
			$da["status"] = 2;
			$da["joindate"] = time();
			$da["starttime"] = time();
			$pass = str_replace(" ", '', request()->param("password"));
			$repass = str_replace(" ", '', request()->param("repassword"));
			if ($pass !== $repass || empty($pass) || empty($repass) || strlen($pass) < 8) {
				return AjaxReturnMsg(0, "密码格式错误");
			}
			if (WXAPP_TYPE == 10) {
				$da["password"] = user_hash($pass, "arliki");
			} else {
				$s = Db::name("ybsc_admin_role")->order("role_id asc")->find();
				$da["maxcount"] = 1;
				$da["isadmin"] = 1;
				$da["role_id_array"] = intval($s["role_id"]) > 0 ? $s["role_id"] : 1;
				$da["name"] = $da["username"];
				$da["status"] = 1;
				$da["password"] = md5($pass);
			}
			$res = Db::name("users")->insert($da);
			return AjaxReturn($res);
		} else {
			return view("user/add_user");
		}
	}
	public function redlist()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$mch_id = $this->bus_id;
		if ($this->red == 1) {
			$this->redirect("arliki/red_one");
		}
		$list = Db::name("ybmp_red")->where("mch_id", $mch_id)->order("id desc")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$this->assign("list", $list);
		$this->assign("page", $list->render());
		return view();
	}
	public function red_add_edit()
	{
		$mch_id = $this->bus_id;
		$id = request()->param("id", 0);
		if (request()->isPost() && request()->isAjax()) {
			$data = request()->post();
			if (round($data["money_num"], 2) < 0.02 || round($data["money_num"], 2) * 100 < intval($data["peo_num"])) {
				return AjaxReturnMsg(0, "红包金额不正确");
			}
			if ($data["id"] > 0) {
				$id = $data["id"];
				unset($data["id"]);
				Db::name("ybmp_red")->where("id", $id)->where("mch_id", $mch_id)->find();
				return AjaxReturn(1);
			} else {
				$data["mch_id"] = $mch_id;
				$data["create_time"] = time();
			}
		}
	}
	public function red_one()
	{
		$ser = new ArlikiService($this->bus_id);
		$mch_id = $this->bus_id;
		if (request()->isPost() && request()->isAjax()) {
			$data = request()->post();
			$data["split_time"] = intval($data["split_time"]);
			$data["vali_time"] = intval($data["vali_time"]);
			$data["peo_num"] = intval($data["peo_num"]);
			if ($data["peo_num"] > 16) {
				return AjaxReturnMsg(0, "人数设置过高");
			}
			if (round($data["money_num"], 2) < 0.02 || round($data["money_num"], 2) * 100 < $data["peo_num"]) {
				return AjaxReturnMsg(0, "红包金额不正确");
			}
			if ($data["use_least"] < 0) {
				return AjaxReturnMsg(0, "门槛金额不正确");
			}
			if ($data["vali_time"] < 1 || $data["split_time"] < 1) {
				return AjaxReturnMsg(0, "时间设置有误");
			}
			if ($data["status"] == 2) {
				$data["show_big"] = 2;
			}
			if ($data["id"] > 0) {
				$id = $data["id"];
				unset($data["id"]);
				$check = $ser->check_data($data["peo_num"], $data["split_time"]);
				if ($check["code"] == 0) {
					return AjaxReturnMsg(0, $check["msg"]);
				}
				$data["create_time"] = time();
				$new = explode("\n", $data["role"]);
				$res = Db::name("ybmp_red")->where("id", $id)->where("mch_id", $mch_id)->update($data);
				if ($data["status"] == 2) {
					Db::name("ybmp_redlog")->where(["rid" => $id, "status" => 0])->update(["status" => 2]);
					Db::name("ybmp_redshare")->where(["rid" => $id, "status" => 0])->update(["status" => 2]);
				} else {
					$ser->split_red();
				}
				return AjaxReturn($res);
			} else {
				unset($data["id"]);
				$data["mch_id"] = $mch_id;
				$data["create_time"] = time();
				$res = Db::name("ybmp_red")->insert($data);
				return AjaxReturn($res);
			}
		} else {
			$info = Db::name("ybmp_red")->where("mch_id", $mch_id)->find();
			if ($info["status"] == 1) {
				$ser->split_red();
			}
			$this->assign("info", $info);
			return view();
		}
	}
	public function red_share_log()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$mch_id = $this->bus_id;
		$red = Db::name("ybmp_red")->where("mch_id", $mch_id)->find();
		$list = Db::name("ybmp_redlog")->alias("a")->join("ybmp_user s", "a.share_id=s.uid", "left")->join("ybmp_user d", "a.split_id=d.uid", "left")->field("a.share_id,a.split_id,s.nick_name share_name,s.user_headimg share_img,d.nick_name split_name,d.user_headimg split_img,a.create_time,a.status")->where(["rid" => $red["id"]])->order("a.id desc")->paginate(20, false, $config = ["query" => ["s" => $url]]);
		$re = array();
		for ($i = 0; $i < count($list); $i++) {
			$re[$i] = $list[$i];
			if ($list[$i]["status"] == 1) {
				$re[$i]["status"] = "成功拆分";
				$re[$i]["css"] = "success";
			} elseif ($list[$i]["status"] == 2) {
				$re[$i]["status"] = "已过期";
				$re[$i]["css"] = "error";
			} else {
				$re[$i]["status"] = "未满足";
				$re[$i]["css"] = "wait";
			}
		}
		$this->assign("list", $re);
		$this->assign("page", $list->render());
		return view();
	}
	public function red_split_log()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$mch_id = $this->bus_id;
		$red = Db::name("ybmp_red")->where("mch_id", $mch_id)->find();
		$list = Db::name("ybmp_user_coupon")->alias("a")->join("ybmp_user s", "a.user_id=s.uid", "left")->field("a.*,s.user_headimg share_img,s.nick_name share_name")->where(["rid" => $red["id"]])->order("a.id desc")->paginate(20, false, $config = ["query" => ["s" => $url]]);
		$this->assign("list", $list);
		$this->assign("page", $list->render());
		return view();
	}
}
<?php


namespace app\admin\controller;

use think\Cache;
use think\Db;
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
class Bargain extends Base
{
	public function index()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$search_text = input("param.search_text", '');
		$condition["b.bargain_name"] = array("like", "%" . $search_text . "%");
		$seart = '';
		if ($search_text != '') {
			$seart = $search_text;
		}
		$list = Db::name("ybmp_bargain")->alias("b")->join("ybmp_images i", "i.img_id=b.bargain_picture")->field("i.img_cover,b.*")->where($condition)->where("b.mch_id", $this->bus_id)->order("b.create_time desc")->paginate(15, false, $config = ["query" => ["s" => $url, "search_text" => $seart]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("search_text", $search_text);
		$this->assign("page", $page);
		return view("bargain/index");
	}
	public function add_bargain()
	{
		$bargain = new \app\admin\service\Bargain();
		if (request()->isAjax() && request()->isPost()) {
			$bargain_name = input("param.bargain_name", '');
			$bargain_picture = input("param.bargain_picture", '');
			$bargain_inventory = input("param.bargain_inventory", '');
			$original_price = input("param.original_price", '');
			$lowest_price = input("param.lowest_price", '');
			$star_time = input("param.star_time", '');
			$end_time = input("param.end_time", '');
			$consumption_time = input("param.consumption_time", '');
			$activity_rules = input("param.activity_rules", '');
			$completed_number = input("param.completed_number", '');
			$array_img = json_decode(input("param.array_img", ''), true);
			$class_id = input("param.class_id", '');
			$arr_str_img = '';
			foreach ($array_img as $k => $v) {
				$arr_str_img .= $v["id"] . ",";
			}
			$arr_str_img = substr($arr_str_img, 0, strlen($arr_str_img) - 1);
			$res = $bargain->addBargain($bargain_name, $bargain_picture, $bargain_inventory, $original_price, $lowest_price, $star_time, $end_time, $consumption_time, $activity_rules, $completed_number, $this->bus_id, $arr_str_img, $class_id);
			return AjaxReturn($res);
		}
		$class_list = Db::name("ybmp_application_activity_class")->where("agents_id", $this->bus_id)->select();
		$this->assign("class_list", $class_list);
		return view("bargain/add_bargain");
	}
	public function bargain_edit()
	{
		$bargain = new \app\admin\service\Bargain();
		if (request()->isAjax() && request()->isPost()) {
			$id = input("param.id", '');
			$bargain_name = input("param.bargain_name", '');
			$bargain_picture = input("param.bargain_picture", '');
			$bargain_inventory = input("param.bargain_inventory", '');
			$original_price = input("param.original_price", '');
			$lowest_price = input("param.lowest_price", '');
			$star_time = input("param.star_time", '');
			$end_time = input("param.end_time", '');
			$consumption_time = input("param.consumption_time", '');
			$activity_rules = input("param.activity_rules", '');
			$completed_number = input("param.completed_number", '');
			$array_img = json_decode(input("param.array_img", ''), true);
			$class_id = input("param.class_id", '');
			$arr_str_img = '';
			foreach ($array_img as $k => $v) {
				$arr_str_img .= $v["id"] . ",";
			}
			$arr_str_img = substr($arr_str_img, 0, strlen($arr_str_img) - 1);
			$res = $bargain->editBargain($id, $bargain_name, $bargain_picture, $bargain_inventory, $original_price, $lowest_price, $star_time, $end_time, $consumption_time, $activity_rules, $completed_number, $this->bus_id, $arr_str_img, $class_id);
			if ($res !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = input("param.id", '');
		$list = Db::name("ybmp_bargain")->alias("b")->join("ybmp_images i", "i.img_id=b.bargain_picture")->field("b.*,i.img_id,i.img_cover")->where("b.id", $id)->find();
		$list["consumption_time"] = date("Y-m-d H:i:s", $list["consumption_time"]);
		$list["star_time"] = date("Y-m-d H:i:s", $list["star_time"]);
		$list["end_time"] = date("Y-m-d H:i:s", $list["end_time"]);
		$img_id = explode(",", $list["img_id_array"]);
		$arr = [];
		for ($a = 0; $a < count($img_id); $a++) {
			$img_url = Db::name("ybmp_images")->where("img_id", $img_id[$a])->find();
			$arr[$a]["id"] = $img_id[$a];
			$arr[$a]["url"] = $img_url["img_cover"];
		}
		$list["array_img"] = json_encode($arr);
		$this->assign("list", $list);
		$class_list = Db::name("ybmp_application_activity_class")->where("agents_id", $this->bus_id)->select();
		$this->assign("class_list", $class_list);
		return view("bargain/edit_bargain");
	}
	public function select_goods()
	{
		$this->open_page();
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$goods = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "i.img_id=g.images", "left")->where("g.mch_id", $this->bus_id)->where("g.is_del", "0")->where("g.state", "1")->where(["g.stock" => [">", 0]])->field("g.*,i.img_cover,i.img_id")->paginate(15, false, ["query" => ["s" => $url]]);
		$page = $goods->render();
		$this->open_page(false);
		$this->assign("goods", $goods);
		$this->assign("page", $page);
		return view("bargain/select_goods");
	}
	public function dialogalbumlist()
	{
		$number = request()->get("number", 5);
		$type = request()->get("type", '');
		$this->assign("type", $type);
		$this->assign("number", $number);
		$album = new \app\admin\service\Images();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$default_album_detail = $album->GetDefAll($condition);
		$this->assign("default_album_id", $default_album_detail["group_id"]);
		return view("bargain/select_img");
	}
	public function bargain_status()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_bargain")->where("id", $id)->update(["bargain_type" => 2]);
		} else {
			$res = Db::name("ybmp_bargain")->where("id", $id)->update(["bargain_type" => 1]);
		}
		return AjaxReturn($res);
	}
	public function bargain_del()
	{
		if (request()->isAjax()) {
			$id = input("param.id");
			$u = Db::name("ybmp_bargain_user")->where("bargain_id", $id)->find();
			if (empty($u)) {
				$res = Db::name("ybmp_bargain")->where("id", $id)->delete();
				return AjaxReturn($res);
			} else {
				return AjaxReturnMsg(0, "已有用户参加此活动,无法删除");
			}
		}
	}
	public function bargain_order()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$status = input("param.status", "-2");
		$search_text = input("param.search_text");
		if ($status == '' || $status == "-2") {
			$condition["bo.order_status"] = array("in", "0,1,2,3,4,5,-1");
		} else {
			$condition["bo.order_status"] = array("eq", $status);
		}
		$seart = '';
		$condition["bo.bargain_name"] = array("like", "%" . $search_text . "%");
		if ($search_text != '') {
			$seart = $search_text;
		}
		$list = Db::name("ybmp_bargain_order")->alias("bo")->join("ybmp_images i", "i.img_id=bo.bargain_pic")->where($condition)->where("bo.mch_id", $this->bus_id)->field("bo.*,i.img_cover")->order("bo.create_time desc")->paginate(15, false, $config = ["query" => ["s" => $url, "status" => $status, "search_text" => $seart]]);
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		$this->assign("status", $status);
		return view("bargain/bargain_order");
	}
	public function bargain_order_send()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_bargain_order")->where("order_id", $id)->update(["order_status" => 2]);
		return AjaxReturn($res);
	}
	public function activity_carousel()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_application_carousel")->where("agents_id", $this->bus_id)->order("sort")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view("bargain/activity_carousel");
	}
	public function add_activity_carousel()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = [];
			$data["img"] = input("param.logo", '');
			$data["sort"] = input("param.sort", '');
			$data["create_time"] = time();
			$data["agents_id"] = $this->bus_id;
			$res = Db::name("ybmp_application_carousel")->insert($data);
			return AjaxReturn($res);
		}
		$this->assign("this_id", 1);
		$this->assign("p_id", 4);
		return view("bargain/add_activity_carousel");
	}
	public function edit_activity_carousel()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = [];
			$id = input("param.id");
			$data["img"] = input("param.logo", '');
			$data["sort"] = input("param.sort", '');
			$data["agents_id"] = $this->bus_id;
			$res = Db::name("ybmp_application_carousel")->where("id", $id)->update($data);
			return AjaxReturn($res);
		}
		$id = input("param.id");
		$info = Db::name("ybmp_application_carousel")->where("id", $id)->find();
		$this->assign("info", $info);
		return view("bargain/edit_activity_carousel");
	}
	public function activity_carousel_sort()
	{
		$id = input("param.id");
		$val = input("param.val");
		$res = Db::name("ybmp_application_carousel")->where("id", $id)->update(["sort" => $val]);
		return AjaxReturn($res);
	}
	public function activity_class_use()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_application_carousel")->where("id", $id)->update(["is_use" => 1]);
		} elseif ($key == "on") {
			$res = Db::name("ybmp_application_carousel")->where("id", $id)->update(["is_use" => 0]);
		} elseif ($key == "del") {
			$item = Db::name("ybmp_application_carousel")->where("id", $id)->find();
			$link = $item["img"];
			$link = str_replace("http://" . $_SERVER["HTTP_HOST"], $_SERVER["DOCUMENT_ROOT"], $link);
			$res = Db::name("ybmp_application_carousel")->where("id", $id)->delete();
			if (file_exists($link)) {
				unlink($link);
			}
		}
		return AjaxReturn($res);
	}
	public function activity_class()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$search_text = input("param.search_text", '');
		$condition["class_name"] = array("like", "%" . $search_text . "%");
		$seart = '';
		if ($search_text != '') {
			$seart = $search_text;
		}
		$list = Db::name("ybmp_application_activity_class")->where("agents_id", $this->bus_id)->where($condition)->order("sort")->paginate(15, false, $config = ["query" => ["s" => $url, "search_text" => $seart]]);
		$page = $list->render();
		$this->assign("search_text", $search_text);
		$this->assign("page", $page);
		$this->assign("list", $list);
		return view("bargain/activity_class");
	}
	public function add_activity_class()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = [];
			$data["class_name"] = request()->post("cate_name", '');
			$data["sort"] = request()->post("sort", '');
			$data["img_url"] = request()->post("cate_pic", '');
			$data["create_time"] = time();
			$data["agents_id"] = $this->bus_id;
			$res = Db::name("ybmp_application_activity_class")->insert($data);
			return AjaxReturn($res);
		}
		return view("bargain/add_activity_class");
	}
	public function edit_activity_class()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$data = [];
			$data["class_name"] = request()->post("cate_name", '');
			$data["sort"] = request()->post("sort", '');
			$data["img_url"] = request()->post("cate_pic", '');
			$data["create_time"] = time();
			$data["agents_id"] = $this->bus_id;
			$res = Db::name("ybmp_application_activity_class")->where("id", $id)->update($data);
			return AjaxReturn($res);
		}
		$id = input("param.id", '');
		$list = Db::name("ybmp_application_activity_class")->where("id", $id)->find();
		$this->assign("info", $list);
		return view("bargain/edit_activity_class");
	}
	public function activity_class_status()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_application_activity_class")->where("id", $id)->update(["is_use" => 1]);
		} else {
			$res = Db::name("ybmp_application_activity_class")->where("id", $id)->update(["is_use" => 0]);
		}
		return AjaxReturn($res);
	}
	public function activity_class_sort()
	{
		$id = input("param.id");
		$val = input("param.val");
		$res = Db::name("ybmp_application_activity_class")->where("id", $id)->update(["sort" => $val]);
		return AjaxReturn($res);
	}
	public function bargain_refund()
	{
		if (request()->isAjax() && request()->isPost()) {
			$order_id = input("param.order_id");
			$refund_money = input("param.refund_money");
			$res = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->update(["order_status" => 4, "refund_money" => $refund_money, "finish_time" => time()]);
			return AjaxReturn($res);
		}
		$order_id = input("param.order_id", "0");
		$info = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->find();
		$this->assign("info", $info);
		$this->assign("order_id", $order_id);
		return view("bargain/bargain_refund");
	}
	public function orderTakeRefund()
	{
		$order_id = request()->post("order_id", '');
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$item = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->find();
			$res = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->update(["order_status" => 5, "refund_money" => $item["pay_money"], "finish_time" => time()]);
			return AjaxReturn($res);
		} else {
			$cert = authcode($refund_setting["cert"], "DECODE");
			$key = authcode($refund_setting["key"], "DECODE");
			$order_info = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->find();
			$bus_config = Db::name("ybmp_config")->where("mch_id", $this->bus_id)->find();
			$temp = json_decode($bus_config["value"], true);
			$res = $temp["APP_MCHID"] . date("YmdHis") . rand(10000, 9999999);
			$input = new \WxPayRefund();
			$input->SetOut_trade_no($order_info["out_trade_no"]);
			$input->SetTotal_fee($order_info["pay_money"] * 100);
			$input->SetRefund_fee($order_info["pay_money"] * 100);
			$input->SetOut_refund_no($res);
			$input->SetOp_user_id($temp["APP_MCHID"]);
			$input->SetAppid($temp["APP_ID"]);
			$cert = $cert;
			$key = $key;
			$cert_path = ATTACHMENT_ROOT . "public/" . $this->bus_id . "_wechat_refund_cert.pem";
			$key_path = ATTACHMENT_ROOT . "public/" . $this->bus_id . "_wechat_refund_key.pem";
			file_put_contents($cert_path, $cert);
			file_put_contents($key_path, $key);
			$param = \WxPayApi::refund($input);
			if ($param["result_code"] == "SUCCESS") {
				$order_info = Db::name("ybmp_bargain_order")->where("order_id", $order_id)->update(["order_status" => 5, "refund_money" => $order_info["pay_money"], "finish_time" => time()]);
				return AjaxReturn($order_info);
			} else {
				$errmsg = empty($param["err_code_des"]) ? "退款失败" : $param["err_code_des"];
				return AjaxReturnMsg(0, $errmsg);
			}
		}
	}
	public function returnGoodsList()
	{
		Cache::set("is_load", 2, 20);
		if (request()->isAjax() && request()->method() == "POST") {
			$goods_id = request()->post("goods_id");
			$where["goods_id"] = $goods_id;
			$goods = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images =i.img_id")->where($where)->field("g.*,i.img_cover")->find();
			$img_id_array = $goods["img_id_array"];
			$imgs = Db::name("ybmp_images")->where("img_id", "in", $img_id_array)->field("img_id,img_cover")->select();
			$goods["img_arr"] = $imgs;
			return AjaxReturn($goods);
		}
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$art = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "m.img_id=g.images")->where("g.mch_id", $this->bus_id)->where("g.is_del", "0")->where("g.state", 1)->where(["g.stock" => [">", 0]])->field("g.goods_id,g.create_time,g.goods_name,g.price,m.img_cover")->order("g.create_time desc")->paginate(15, false, ["query" => ["s" => $url]]);
		$this->assign("goods", $art);
		$this->assign("page", $art->render());
		Cache::set("is_load", null);
		return view("return_goods_list");
	}
	public function deldel()
	{
		$id = request()->param("id");
		db::name("ybmp_bargain")->where("id", $id)->delete();
		return AjaxReturnMsg(1);
	}
}
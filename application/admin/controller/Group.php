<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
require EXTEND_PATH . "Wxpay/WxPay.Api.php";
class Group extends Base
{
	public function index()
	{
		return view();
	}
	public function type()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$soname = request()->param("soname");
		if ($soname != '' && !empty($soname)) {
			$search = "%" . $soname . "%";
			$list = Db::name("ybmp_pt_category")->where("name", "like", $search)->where("mch_id", $this->bus_id)->order("sort asc")->paginate(15, false, $config = ["query" => ["s" => $url, "soname" => $soname]]);
		} else {
			$list = Db::name("ybmp_pt_category")->where("mch_id", $this->bus_id)->order("sort asc")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		}
		$this->assign("list", $list);
		$this->assign("soname", $soname);
		return view();
	}
	public function type_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["mch_id"] = $this->bus_id;
			$result = Db::name("ybmp_pt_category")->insert($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		return view();
	}
	public function type_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$result = Db::name("ybmp_pt_category")->where("id", $data["id"])->where("mch_id", $this->bus_id)->update($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = request()->get("id");
		if ($id != '' && !empty($id)) {
			$list = Db::name("ybmp_pt_category")->where("id", $id)->where("mch_id", $this->bus_id)->find();
			$this->assign("list", $list);
			return view();
		}
	}
	public function type_del()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_pt_category")->where("id", $id)->delete();
		return AjaxReturn($res);
	}
	public function group_list()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data = Request::instance()->param();
		$where = [];
		empty($data["group_name"]) || ($where["name"] = ["like", "%" . $data["group_name"] . "%"]);
		$data["group_name"] = empty($data["group_name"]) ? '' : $data["group_name"];
		$list = Db::name("ybmp_pt_goods")->where("mch_id", $this->bus_id)->where($where)->paginate(15, false, $config = ["query" => ["s" => $url, "group_name" => $data["group_name"]]]);
		$this->assign("list", $list);
		$this->assign("group_name", $data["group_name"]);
		return view();
	}
	public function group_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data["name"] = input("param.group_name");
			$data["img"] = input("param.img");
			$data["cid"] = input("param.type");
			$data["intro"] = input("param.details");
			$data["gprice"] = input("param.group_money");
			$data["price"] = input("param.price");
			$data["groupNum"] = input("param.group_num");
			$data["limitTime"] = input("param.group_time");
			$data["stock"] = input("param.stock");
			$data["mch_id"] = $this->bus_id;
			$data["saleNum"] = input("param.sales");
			$data["brief"] = input("param.brief");
			$data["unit"] = input("param.unit");
			$array_img = json_decode(input("param.img_arr", ''), true);
			$data["indexImg"] = $array_img[0]["id"];
			$arr_str_img = '';
			foreach ($array_img as $k => $v) {
				$arr_str_img .= $v["id"] . ",";
			}
			$arr_str_img = substr($arr_str_img, 0, strlen($arr_str_img) - 1);
			$data["album"] = $arr_str_img;
			$res = Db::name("ybmp_pt_goods")->insert($data);
			return AjaxReturn($res);
		}
		$group_type = Db::name("ybmp_pt_category")->where("mch_id", $this->bus_id)->select();
		$this->assign("group_type", $group_type);
		return view();
	}
	public function group_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = input("param.id");
			$data["name"] = input("param.group_name");
			$data["img"] = input("param.img");
			$data["cid"] = input("param.type");
			$data["intro"] = input("param.details");
			$data["gprice"] = input("param.group_money");
			$data["price"] = input("param.price");
			$data["groupNum"] = input("param.group_num");
			$data["limitTime"] = input("param.group_time");
			$data["stock"] = input("param.stock");
			$data["mch_id"] = $this->bus_id;
			$data["saleNum"] = input("param.sales");
			$data["brief"] = input("param.brief");
			$data["unit"] = input("param.unit");
			$array_img = json_decode(input("param.img_arr", ''), true);
			$data["indexImg"] = $array_img[0]["id"];
			$arr_str_img = '';
			foreach ($array_img as $k => $v) {
				$arr_str_img .= $v["id"] . ",";
			}
			$arr_str_img = substr($arr_str_img, 0, strlen($arr_str_img) - 1);
			$data["album"] = $arr_str_img;
			$res = Db::name("ybmp_pt_goods")->where("id", $id)->update($data);
			if ($res !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = input("param.id", '');
		$list = Db::name("ybmp_pt_goods")->where("id", $id)->find();
		$img_id = explode(",", $list["album"]);
		$arr = [];
		for ($a = 0; $a < count($img_id); $a++) {
			$img_url = Db::name("ybmp_images")->where("img_id", $img_id[$a])->find();
			$arr[$a]["id"] = $img_id[$a];
			$arr[$a]["url"] = $img_url["img_cover"];
		}
		$list["array_img"] = json_encode($arr);
		$this->assign("list", $list);
		$group_type = Db::name("ybmp_pt_category")->where("mch_id", $this->bus_id)->select();
		$this->assign("group_type", $group_type);
		return view();
	}
	public function group_status()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_pt_goods")->where("id", $id)->update(["isShow" => 2]);
		} else {
			$res = Db::name("ybmp_pt_goods")->where("id", $id)->update(["isShow" => 1]);
		}
		return AjaxReturn($res);
	}
	public function group_carousel()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_pt_advert")->where("mch_id", $this->bus_id)->order("sort")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view();
	}
	public function group_carousel_sort()
	{
		$id = input("param.id");
		$val = input("param.val");
		$res = Db::name("ybmp_pt_advert")->where("id", $id)->update(["sort" => $val]);
		return AjaxReturn($res);
	}
	public function group_class_use()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_pt_advert")->where("id", $id)->update(["enabled" => 2]);
		} elseif ($key == "on") {
			$res = Db::name("ybmp_pt_advert")->where("id", $id)->update(["enabled" => 1]);
		} else {
			$res = Db::name("ybmp_pt_advert")->where("id", $id)->delete();
		}
		return AjaxReturn($res);
	}
	public function group_carousel_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = [];
			$data["img"] = input("param.logo", '');
			$data["sort"] = input("param.sort", '');
			$data["mch_id"] = $this->bus_id;
			$res = Db::name("ybmp_pt_advert")->insert($data);
			return AjaxReturn($res);
		}
		return view();
	}
	public function group_carousel_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = [];
			$id = input("param.id");
			$data["img"] = input("param.logo", '');
			$data["sort"] = input("param.sort", '');
			$data["mch_id"] = $this->bus_id;
			$res = Db::name("ybmp_pt_advert")->where("id", $id)->update($data);
			return AjaxReturn($res);
		}
		$id = input("param.id");
		$info = Db::name("ybmp_pt_advert")->where("id", $id)->find();
		$this->assign("info", $info);
		return view();
	}
	public function group_order()
	{
		$ttime = time();
		$outtimes = Db::name("ybmp_pt_orders")->where("isPay", 1)->where("order_status", 2)->where("groupTime", 0)->where("endTime", "<", $ttime)->where("mch_id", $this->bus_id)->select();
		foreach ($outtimes as $out) {
			$order_id = $out["id"];
			try {
				$this->orderTakeRefund($order_id);
			} catch (\Exception $e) {
			}
		}
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$status = Request::instance()->param("status", -2);
		$this->assign("status", $status);
		$data = Request::instance()->param();
		$where = [];
		empty($data["order_num"]) || ($where["o.orderNum"] = ["like", "%" . $data["order_num"] . "%"]);
		if ($status != -2) {
			$where["o.order_status"] = $status;
		}
		$data["order_num"] = empty($data["order_num"]) ? '' : $data["order_num"];
		$list = Db::name("ybmp_pt_orders")->alias("o")->join("ybmp_pt_goods g", "o.gid=g.id")->where("o.mch_id", $this->bus_id)->field("o.*,g.name,g.img")->order("o.createTime desc")->where($where)->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$page = $list->render();
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$this->assign("refund_type", 0);
		} else {
			$this->assign("refund_type", 1);
		}
		$this->assign("order_num", $data["order_num"]);
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view();
	}
	public function OrderDetail()
	{
		$id = input("param.order_id");
		$list = Db::name("ybmp_pt_orders")->alias("o")->join("ybmp_pt_goods g", "o.gid=g.id")->where("o.mch_id", $this->bus_id)->where("o.id", $id)->field("o.*,g.name,g.img")->find();
		$this->assign("info", $list);
		return view("group/group_order_info");
	}
	public function collage()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data = Request::instance()->param();
		$where = [];
		empty($data["goods_name"]) || ($where["g.name"] = ["like", "%" . $data["goods_name"] . "%"]);
		$data["goods_name"] = empty($data["goods_name"]) ? '' : $data["goods_name"];
		$list = Db::name("ybmp_pt_orders")->alias("o")->join("ybmp_pt_goods g", "o.gid=g.id")->where("o.mch_id", $this->bus_id)->where("o.pid=0")->where("o.isGroup=1")->field("o.*,g.name,g.img")->order("o.createTime desc")->where($where)->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("time", time());
		$this->assign("goods_name", $data["goods_name"]);
		return view();
	}
	public function group_user_list()
	{
		$id = input("param.id");
		$list = Db::name("ybmp_pt_orders")->alias("o")->join("ybmp_pt_goods g", "o.gid=g.id")->where("o.mch_id", $this->bus_id)->where("o.id|o.pid", $id)->field("o.*,g.name,g.img")->select();
		$this->assign("info", $list);
		return view("group/collage_list");
	}
	public function deleteOrder()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$res = Db::name("ybmp_pt_orders")->where(["id" => $id, "order_status" => -1])->delete();
			return AjaxReturn($res);
		}
	}
	public function group_order_send()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_pt_orders")->where("id", $id)->update(["order_status" => 4]);
		return AjaxReturn($res);
	}
	public function orderTakeRefund($id)
	{
		$order_id = $id ? $id : request()->post("id", '');
		global $_W;
		$setting = uni_setting_load("payment", $_W["uniacid"]);
		$refund_setting = $setting["payment"]["wechat_refund"];
		if ($refund_setting["switch"] != 1 || empty($refund_setting["key"]) || empty($refund_setting["cert"])) {
			$res = Db::name("ybmp_pt_orders")->where("id", $order_id)->update(["order_status" => 7, "refund_time" => time()]);
			return AjaxReturn($res);
		} else {
			$cert = authcode($refund_setting["cert"], "DECODE");
			$key = authcode($refund_setting["key"], "DECODE");
			$order_info = Db::name("ybmp_pt_orders")->where("id", $order_id)->find();
			$bus_config = Db::name("ybmp_config")->where("mch_id", $this->bus_id)->find();
			$temp = json_decode($bus_config["value"], true);
			$res = $temp["APP_MCHID"] . date("YmdHis") . rand(10000, 9999999);
			$input = new \WxPayRefund();
			$input->SetOut_trade_no($order_info["orderNum"]);
			$input->SetTotal_fee($order_info["totalPrice"] * 100);
			$input->SetRefund_fee($order_info["totalPrice"] * 100);
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
				$order_info = Db::name("ybmp_pt_orders")->where("id", $order_id)->update(["order_status" => 7, "refund_time" => time()]);
				return AjaxReturn($order_info);
			} else {
				$errmsg = empty($param["err_code_des"]) ? "退款失败" : $param["err_code_des"];
				return AjaxReturnMsg(0, $errmsg);
			}
		}
	}
}
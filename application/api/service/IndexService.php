<?php


namespace app\api\service;

use think\Db;
use think\Cache;
use think\Exception;
use think\log;
class IndexService
{
	private $bb = "ybmp_bus_book";
	private $bc = "ybmp_bus_comment";
	private $u = "ybmp_user";
	private $ut = "ybmp_user_tmpl";
	private $bt = "ybmp_bus_tmpl";
	private $bf = "ybmp_bus_form";
	public function WriteBook($data)
	{
		$a = Db::name($this->bb)->where($data)->count();
		if ($a > 0) {
			return -1;
		}
		$data["create_time"] = time();
		$info = Db::name($this->bb)->insert($data);
		return $info;
	}
	public function WriteComment($data)
	{
		$a = Db::name($this->bc)->where($data)->count();
		if ($a > 0) {
			return -1;
		}
		$data["create_time"] = time();
		$info = Db::name($this->bc)->update($data);
		return $info;
	}
	public function CommentList($data, $page, $num)
	{
		if ($num == 0) {
			$info = Db::name($this->bc)->where($data)->page($page, PAGESIZE)->order("create_time", "desc")->select();
		} else {
			$info = Db::name($this->bc)->where($data)->limit($num)->order("create_time", "desc")->select();
		}
		if (!empty($info)) {
			foreach ($info as $key => $value) {
				if ($value->array_pic != '') {
					$pic = explode(",", rtrim($value->array_pic, ","));
					for ($i = 0; $i < count($pic); $i++) {
						$pic[$i] = "https://" . $_SERVER["HTTP_HOST"] . "/api/" . $pic[$i];
					}
					$value->pic = $pic;
				}
				$value->create_time = date("Y-m-d", $value->create_time);
				$username = $this->getUserInfo($value->user_id);
				$value->username = $username["nick_name"];
				$value->user_headimg = $username["user_headimg"];
			}
			$rs["info"] = $info;
		} else {
			$rs["info"] = array();
		}
		$rs["count"] = Db::name($this->bc)->where($data)->count();
		$num = Db::name($this->bc)->where($data)->avg("fraction") * 2;
		$rs["sroce"] = round($num, 2);
		return json_encode($rs);
	}
	public function getUserInfo($uid)
	{
		$info = Db::name($this->u)->where("uid", $uid)->find();
		return $info;
	}
	public function mod_index($uid = '')
	{
		$m = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $uid, "type" => 1, "default" => 1])->order("id desc")->find();
		if (empty($m)) {
			$m = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $uid, "type" => 1])->order("id desc")->find();
		}
		$rs = array();
		if (empty($m)) {
			return $m;
		}
		if ($m["index_values"]) {
			$rs = json_decode($m["index_values"], true);
		} else {
			$rs = [];
		}
		$rs["name"] = $m["name"];
		$rs["type"] = $m["type"];
		$rs["page_type"] = $m["page_type"];
		$rs["remark"] = $m["remark"];
		$rs["appid"] = $m["appid"];
		return $rs;
	}
	public function mod_shop($uid = '')
	{
		$m = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $uid, "default" => 1])->where("type!=1")->find();
		if (empty($m)) {
			$m = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $uid])->where("type!=1")->order("id desc")->find();
		}
		$rs = array();
		if (empty($m)) {
			return $m;
		}
		if ($m["index_values"]) {
			$rs = json_decode($m["index_values"], true);
		} else {
			$rs = [];
		}
		$conf = Db::name("ybmp_config")->where("mch_id", $uid)->find();
		$conf = json_decode($conf["value"], true);
		$rs["shop_site"] = empty($conf["shop_site"]) ? 1 : $conf["shop_site"];
		$rs["name"] = $m["name"];
		$rs["type"] = $m["type"];
		$rs["page_type"] = $m["page_type"];
		$rs["remark"] = $m["remark"];
		$rs["appid"] = $m["appid"];
		return $rs;
	}
	public function get_power($data)
	{
		$m = Db::name($this->bt)->where($data)->where("is_del", 1)->find();
		if (empty($m)) {
			return $m;
		}
		$rs = json_decode($m["index_values"], true);
		$rs["name"] = $m["name"];
		return $rs;
	}
	public function get_tabbar($uid = '')
	{
		$m = Db::name($this->ut)->where("mch_id=" . $uid)->find();
		if (empty($m)) {
			return $m;
		}
		$rs = array();
		$index_values = $m["index_values"];
		$index_values = json_decode($index_values, true);
		if (!empty($index_values["page"])) {
			$page = $index_values["page"];
			$tabbar = $index_values["tabbar"];
			$rs["page"] = $page;
			$rs["tabbar"] = $tabbar;
		}
		return $rs;
	}
	public function get_menu($uid = '')
	{
		$rs = array();
		$m = Db::name($this->ut)->where("mch_id=" . $uid)->find();
		if (empty($m)) {
			return $m;
		}
		$rs = $m["values"];
		if (strpos($rs, "no_select") !== false && strpos($rs, "is_select") !== false) {
			return json_decode($rs, true);
		} else {
			return null;
		}
	}
	public function get_form($data)
	{
		$data["is_del"] = 1;
		$m = Db::name($this->bf)->where($data)->find();
		if (empty($m)) {
			return $m;
		}
		$rs["list"] = json_decode($m["value"]);
		$rs["title"] = $m["title"];
		return $rs;
	}
	function in_form($form, $name)
	{
		$title = '';
		for ($i = 0; $i < count($form); $i++) {
			if ($form[$i]["name"] == $name) {
				$title = $form[$i]["title"];
				break;
			}
		}
		return $title;
	}
	public function submit_form($data)
	{
		$time = time();
		$form = json_decode($data["form"], true);
		$param = json_decode($data["param"], true);
		if (count($param) == 0) {
			return null;
		}
		$arr = array();
		$n = 0;
		foreach ($param as $k => $v) {
			$arr[$n]["name"] = $k;
			$arr[$n]["value"] = $v;
			$arr[$n]["title"] = $this->in_form($form, $k);
			$n++;
		}
		$where = ["bus_form_id" => $data["bus_form_id"], "user_id" => $data["user_id"], "mch_id" => $data["mch_id"], "param" => json_encode($arr)];
		$a = Db::name("ybmp_user_form")->where($where)->count();
		if ($a > 0) {
			return -1;
		}
		$where["create_time"] = $time;
		$form_id = Db::name("ybmp_user_form")->insertGetId($where);
		return $form_id;
	}
	public function get_ucenter($mch_id)
	{
		$m = Db::name($this->ut)->where("mch_id=" . $mch_id)->find();
		if (empty($m)) {
			return $m;
		}
		$rs = $m["center_values"];
		return $rs;
	}
}
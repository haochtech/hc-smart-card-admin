<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
class Sappl extends Base
{
	public function index()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data = Request::instance()->param();
		$where = [];
		empty($data["sapp_name"]) || ($where["sapp_name"] = ["like", "%" . $data["sapp_name"] . "%"]);
		$data["sapp_name"] = empty($data["sapp_name"]) ? '' : $data["sapp_name"];
		$list = Db::name("ybmp_sapp")->where("mch_id", $this->bus_id)->where($where)->order("id", "desc")->paginate(10, false, ["query" => ["s" => $url, "sapp_name" => $data["sapp_name"]]]);
		$this->assign("list", $list);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("sapp_name", $data["sapp_name"]);
		return view("sappl/index");
	}
	public function sappl_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data["sapp_name"] = request()->post("sapp_name");
			$data["appid"] = request()->post("appid");
			$data["url"] = request()->post("url");
			$data["mch_id"] = $this->bus_id;
			$result = Db::name("ybmp_sapp")->insert($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		return view("sappl/sappl_add");
	}
	public function sappl_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id");
			$data["sapp_name"] = request()->post("sapp_name");
			$data["appid"] = request()->post("appid");
			$data["url"] = request()->post("url");
			$result = Db::name("ybmp_sapp")->where("id", $id)->update($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = input("param.id");
		$info = Db::name("ybmp_sapp")->where("id", $id)->find();
		$this->assign("info", $info);
		return view("sappl/sappl_edit");
	}
	public function sappl_del()
	{
		if (request()->isAjax() && request()->isPost()) {
			$result = Db::name("ybmp_sapp")->where("id", request()->post("id"))->where("mch_id", $this->bus_id)->delete();
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
	public function applink()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data = Request::instance()->param();
		$where = [];
		empty($data["name"]) || ($where["name"] = ["like", "%" . $data["name"] . "%"]);
		$data["name"] = empty($data["name"]) ? '' : $data["name"];
		$where["mch_id"] = $this->bus_id;
		$list = Db::name("ybmp_applink")->where($where)->order("id", "desc")->paginate(15, false, ["query" => ["s" => $url, "name" => $data["name"]]]);
		$this->assign("list", $list);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		$this->assign("name", $data["name"]);
		return view("sappl/applink");
	}
	public function applink_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data["name"] = input("param.name");
			$data["url"] = input("param.url");
			$data["mch_id"] = $this->bus_id;
			$res = Db::name("ybmp_applink")->insert($data);
			if ($res !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		return view("sappl/applink_add");
	}
	public function applink_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = input("param.id");
			$data["name"] = input("param.name");
			$data["url"] = input("param.url");
			$res = Db::name("ybmp_applink")->where("id", $id)->update($data);
			if ($res !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = input("param.id");
		$info = Db::name("ybmp_applink")->where("id", $id)->find();
		$this->assign("info", $info);
		return view("sappl/applink_edit");
	}
	public function applink_del()
	{
		$id = input("param.id");
		$info = Db::name("ybmp_applink")->where("id", $id)->delete();
		if ($info !== false) {
			return AjaxReturn(1);
		} else {
			return AjaxReturn(0);
		}
	}
}
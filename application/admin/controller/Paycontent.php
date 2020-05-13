<?php


namespace app\admin\controller;

use think\Db;
class Paycontent extends Base
{
	public function index()
	{
		$search_text = request()->param("search_text", '');
		$class_id = request()->param("class_id", '');
		$group_id = request()->param("group_id", '');
		$this->assign("search_text", $search_text);
		$this->assign("class_id", $class_id);
		$this->assign("group_id", $group_id);
		$where = array();
		if (!empty($search_text)) {
			$where["p.title"] = array("like", "%{$search_text}%");
		}
		if (!empty($class_id)) {
			$where["p.class_id"] = $class_id;
		}
		if (!empty($group_id)) {
			$where["p.group_id"] = $group_id;
		}
		$where["p.mch_id"] = $this->bus_id;
		$querys = urlQueryToArr();
		$list = Db::name("ybmp_paycontent")->alias("p")->join("ybmp_paycontent_class c", "p.class_id = c.id")->join("ybmp_paycontent_group g", "p.group_id = g.id", "LEFT")->where($where)->field("p.*,c.name as class_name,g.name as group_name")->order("p.sort asc,p.id desc")->paginate(15, false, ["query" => ["s" => $querys["s"], "search_text" => $search_text, "class_id" => $class_id, "group_id" => $group_id]]);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		$class_list = Db::name("ybmp_paycontent_class")->where(["mch_id" => $this->bus_id])->select();
		$group_list = Db::name("ybmp_paycontent_group")->where(["mch_id" => $this->bus_id])->select();
		$this->assign("class_list", $class_list);
		$this->assign("group_list", $group_list);
		$types = ["0" => "文章", "1" => "音频", "2" => "视频"];
		$this->assign("types", $types);
		return view();
	}
	public function add_paycontent()
	{
		$data = request()->param();
		if (request()->isAjax() && request()->isPost()) {
			$data = $_POST;
			unset($data["editorValue"]);
			switch ($data["free"]) {
				case 0:
					$data["group_id"] = -1;
					break;
				case 1:
					$data["price"] = 0;
					break;
				case 2:
					$data["group_id"] = -1;
					$data["price"] = 0;
					break;
			}
			if (!empty($data["id"])) {
				$id = $data["id"];
				unset($data["id"]);
				$res = Db::name("ybmp_paycontent")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["sort"] = 0;
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_paycontent")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_paycontent")->where(["id" => $data["id"]])->find();
			$this->assign("info", $info);
		}
		$class_list = Db::name("ybmp_paycontent_class")->where(["mch_id" => $this->bus_id])->select();
		$group_list = Db::name("ybmp_paycontent_group")->where(["mch_id" => $this->bus_id])->select();
		$this->assign("class_list", $class_list);
		$this->assign("group_list", $group_list);
		return view();
	}
	public function del_paycontent()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$res = Db::name("ybmp_paycontent")->where(["id" => $id])->delete();
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function paycontent_status()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$info = Db::name("ybmp_paycontent")->where(["id" => $id])->find();
			$status = $info["status"] == 1 ? 2 : 1;
			$res = Db::name("ybmp_paycontent")->where(["id" => $id])->update(["status" => $status]);
			return AjaxReturn($res);
		}
	}
	public function paycontent_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_paycontent")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function class_list()
	{
		$list = Db::name("ybmp_paycontent_class")->where(["mch_id" => $this->bus_id])->order("sort asc")->select();
		$this->assign("list", $list);
		return view();
	}
	public function del_class()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$pp = Db::name("ybmp_paycontent")->where(["class_id" => $id])->find();
			if (!empty($pp)) {
				return AjaxReturnMsg(0, "此分类下还有内容,无法删除");
			}
			$res = Db::name("ybmp_paycontent_class")->where(["id" => $id])->delete();
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function add_class()
	{
		$data = request()->param();
		if (request()->isAjax() && request()->isPost()) {
			if (!empty($data["id"])) {
				$id = $data["id"];
				unset($data["id"]);
				$res = Db::name("ybmp_paycontent_class")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["pid"] = 0;
				$data["level"] = 1;
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_paycontent_class")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_paycontent_class")->where(["id" => $data["id"]])->find();
			$this->assign($info);
		}
		return view();
	}
	public function class_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_paycontent_class")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function groups()
	{
		$list = Db::name("ybmp_paycontent_group")->where(["mch_id" => $this->bus_id])->order("sort asc")->select();
		$this->assign("list", $list);
		return view();
	}
	public function add_group()
	{
		$data = request()->param();
		if (request()->isAjax() && request()->isPost()) {
			if (!empty($data["id"])) {
				$id = $data["id"];
				unset($data["id"]);
				$res = Db::name("ybmp_paycontent_group")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["pid"] = 0;
				$data["level"] = 1;
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_paycontent_group")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_paycontent_group")->where(["id" => $data["id"]])->find();
			$this->assign($info);
		}
		return view();
	}
	public function del_group()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$pp = Db::name("ybmp_paycontent")->where(["group_id" => $id])->find();
			if (!empty($pp)) {
				return AjaxReturnMsg(0, "此合集下还有内容,无法删除");
			}
			$res = Db::name("ybmp_paycontent_group")->where(["id" => $id])->delete();
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function group_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_paycontent_group")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function prices()
	{
		$list = Db::name("ybmp_paycontent_prices")->where(["mch_id" => $this->bus_id])->order("sort asc")->select();
		$this->assign("list", $list);
		return view();
	}
	public function add_price()
	{
		$data = request()->param();
		if (request()->isAjax() && request()->isPost()) {
			if (!empty($data["id"])) {
				$id = $data["id"];
				unset($data["id"]);
				$res = Db::name("ybmp_paycontent_prices")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_paycontent_prices")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_paycontent_prices")->where(["id" => $data["id"]])->find();
			$this->assign($info);
		}
		return view();
	}
	public function del_price()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$res = Db::name("ybmp_paycontent_prices")->where(["id" => $id])->delete();
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function price_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_paycontent_prices")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function orders()
	{
		$search_text = request()->post("search_text", '');
		$this->assign("search_text", $search_text);
		$where = array();
		if (!empty($search_text)) {
			$where["p.title|o.price_name|u.nick_name"] = array("like", "%{$search_text}%");
		}
		$where["o.mch_id"] = $this->bus_id;
		$querys = urlQueryToArr();
		$list = Db::name("ybmp_paycontent_orders")->alias("o")->join("ybmp_user u", "u.uid = o.uid")->join("ybmp_paycontent p", "p.id = o.content_id", "LEFT")->where($where)->field("o.*,p.title,u.nick_name")->order("o.id desc")->paginate(15, false, ["query" => ["s" => $querys["s"], "search_text" => $search_text]]);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		return view();
	}
}
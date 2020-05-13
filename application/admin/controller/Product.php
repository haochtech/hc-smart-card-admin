<?php


namespace app\admin\controller;

use think\Db;
class Product extends Base
{
	public function __construct()
	{
		parent::__construct();
		$class = Db::name("ybmp_pro_class")->where("mch_id", $this->bus_id)->select();
		if (!empty($class)) {
			for ($i = 0; $i < count($class); $i++) {
				$pid = empty($class[$i]["pid"]) ? 0 : $class[$i]["pid"];
				$id = Db::name("ybmp_product_class")->insertGetId(["pid" => $pid, "mch_id" => $this->bus_id, "name" => $class[$i]["name"], "create_time" => $class[$i]["ctime"], "level" => 1]);
				$list = Db::name("ybmp_pro")->where("mch_id", $this->bus_id)->where("cid", $class[$i]["id"])->select();
				if (!empty($list)) {
					for ($q = 0; $q < count($list); $q++) {
						Db::name("ybmp_product")->insert(["title" => $list[$q]["name"], "class_id" => $id, "content" => $list[$q]["content"], "image" => $list[$q]["pic"], "create_time" => $list[$q]["create_time"], "mch_id" => $this->bus_id]);
					}
					Db::name("ybmp_pro")->where("mch_id", $this->bus_id)->where("cid", $id)->delete();
				}
				Db::name("ybmp_pro_class")->where("id", $class[$i]["id"])->delete();
			}
		}
	}
	public function index()
	{
		$search_text = request()->post("search_text", '');
		$class_id = request()->post("class_id", '');
		$this->assign("search_text", $search_text);
		$this->assign("class_id", $class_id);
		$where = array();
		if (!empty($search_text)) {
			$where["p.title"] = array("like", "%{$search_text}%");
		}
		if (!empty($class_id)) {
			$where["p.class_id"] = $class_id;
		}
		$where["p.mch_id"] = $this->bus_id;
		$querys = urlQueryToArr();
		$list = Db::name("ybmp_product")->alias("p")->join("ybmp_product_class c", "p.class_id = c.id")->where($where)->field("p.*,c.name as class_name")->order("p.sort asc")->paginate(15, false, ["query" => ["s" => $querys["s"], "search_text" => $search_text, "class_id" => $class_id]]);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		$class_list = Db::name("ybmp_product_class")->where(["mch_id" => $this->bus_id])->select();
		$this->assign("class_list", $class_list);
		return view();
	}
	public function add_product()
	{
		$data = request()->param();
		if (request()->isAjax() && request()->isPost()) {
			if (!empty($data["id"])) {
				$id = $data["id"];
				unset($data["id"]);
				$res = Db::name("ybmp_product")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["sort"] = 0;
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_product")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_product")->where(["id" => $data["id"]])->find();
			$this->assign("info", $info);
		}
		$class_list = Db::name("ybmp_product_class")->where(["mch_id" => $this->bus_id])->select();
		$this->assign("class_list", $class_list);
		return view();
	}
	public function del_product()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$res = Db::name("ybmp_product")->where(["id" => $id])->delete();
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function product_status()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$info = Db::name("ybmp_product")->where(["id" => $id])->find();
			$status = $info["status"] == 1 ? 2 : 1;
			$res = Db::name("ybmp_product")->where(["id" => $id])->update(["status" => $status]);
			return AjaxReturn($res);
		}
	}
	public function product_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_product")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function class_list()
	{
		$list = Db::name("ybmp_product_class")->where(["mch_id" => $this->bus_id])->order("sort asc")->select();
		$this->assign("list", $list);
		return view();
	}
	public function del_class()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$pp = Db::name("ybmp_product")->where(["class_id" => $id])->find();
			if (!empty($pp)) {
				return AjaxReturnMsg(0, "此分类下还有产品,无法删除");
			}
			$res = Db::name("ybmp_product_class")->where(["id" => $id])->delete();
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
				$res = Db::name("ybmp_product_class")->where(["id" => $id])->update($data);
			} else {
				unset($data["id"]);
				$data["pid"] = 0;
				$data["level"] = 1;
				$data["mch_id"] = $this->bus_id;
				$data["create_time"] = time();
				$data["status"] = 1;
				$res = Db::name("ybmp_product_class")->insert($data);
			}
			return AjaxReturn($res);
		}
		if (!empty($data["id"])) {
			$info = Db::name("ybmp_product_class")->where(["id" => $data["id"]])->find();
			$this->assign($info);
		}
		return view();
	}
	public function class_sort()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = request()->post("id", '');
			$sort = request()->post("sort", "0");
			$res = Db::name("ybmp_product_class")->where(["id" => $id])->update(["sort" => $sort]);
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
}
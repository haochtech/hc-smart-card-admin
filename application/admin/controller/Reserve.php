<?php


namespace app\admin\controller;

use think\Db;
class Reserve extends Base
{
	public function assort()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$soname = request()->param("soname");
		if ($soname != '' && !empty($soname)) {
			$search = "%" . $soname . "%";
			$list = Db::name("ybmp_reserve_assort")->where("name", "like", $search)->where("mch_id", $this->bus_id)->paginate(15, false, $config = ["query" => ["s" => $url, "soname" => $soname]]);
		} else {
			$list = Db::name("ybmp_reserve_assort")->where("mch_id", $this->bus_id)->paginate(15, false, $config = ["query" => ["s" => $url]]);
		}
		$this->assign("list", $list);
		$this->assign("soname", $soname);
		return view();
	}
	public function assort_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["time"] = time();
			$data["mch_id"] = $this->bus_id;
			$result = Db::name("ybmp_reserve_assort")->insert($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		return view();
	}
	public function assort_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["time"] = time();
			$result = Db::name("ybmp_reserve_assort")->where("id", $data["id"])->where("mch_id", $this->bus_id)->update($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = request()->get("id");
		if ($id != '' && !empty($id)) {
			$list = Db::name("ybmp_reserve_assort")->where("id", $id)->where("mch_id", $this->bus_id)->find();
			$this->assign("list", $list);
			return view();
		}
	}
	public function assort_del()
	{
		$id = request()->post("id");
		if ($id != '' && !empty($id)) {
			$result = Db::name("ybmp_reserve_assort")->where("id", $id)->where("mch_id", $this->bus_id)->delete();
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
	public function thing()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$soname = request()->param("soname");
		if ($soname != '' && !empty($soname)) {
			$search = "%" . $soname . "%";
			$list = Db::name("ybmp_reserve_thing")->alias("t")->join("ybmp_bus_form f", "t.form_id = f.id", "left")->where("t.name", "like", $search)->where("t.mch_id", $this->bus_id)->field("t.*,f.title")->paginate(15, false, $config = ["query" => ["s" => $url, "soname" => $soname]]);
		} else {
			$list = Db::name("ybmp_reserve_thing")->alias("t")->join("ybmp_bus_form f", "t.form_id = f.id", "left")->where("t.mch_id", $this->bus_id)->field("t.*,f.title")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		}
		$this->assign("list", $list);
		$this->assign("soname", $soname);
		return view();
	}
	public function thing_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["time"] = time();
			$data["mch_id"] = $this->bus_id;
			$result = Db::name("ybmp_reserve_thing")->insert($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$ass_form = Db::name("ybmp_bus_form")->where("mch_id", $this->bus_id)->where("type", 2)->select();
		$this->assign("ass_form", $ass_form);
		return view("reserve/thing_add");
	}
	public function thing_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["time"] = time();
			$result = Db::name("ybmp_reserve_thing")->where("id", $data["id"])->where("mch_id", $this->bus_id)->update($data);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
		$id = request()->get("id");
		if ($id != '' && !empty($id)) {
			$ass_form = Db::name("ybmp_bus_form")->where("mch_id", $this->bus_id)->where("type", 2)->select();
			$this->assign("ass_form", $ass_form);
			$list = Db::name("ybmp_reserve_thing")->where("mch_id", $this->bus_id)->where("id", $id)->find();
			$this->assign("list", $list);
			return view("reserve/thing_edit");
		}
	}
	public function thing_del()
	{
		$id = request()->post("id");
		if ($id != '' && !empty($id)) {
			$result = Db::name("ybmp_reserve_thing")->where("id", $id)->where("mch_id", $this->bus_id)->delete();
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
	public function order()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$soname = request()->param("soname");
		if ($soname != '' && !empty($soname)) {
			$search = "%" . $soname . "%";
			$list = Db::name("ybmp_reserve_point")->alias("p")->join("ybmp_user u", "p.user_id = u.uid", "left")->join("ybmp_reserve_thing t", "p.thing_id = t.id", "left")->where("t.name", "like", $search)->where("p.mch_id", $this->bus_id)->field("p.*,u.nick_name,t.name tname")->paginate(15, false, $config = ["query" => ["s" => $url, "soname" => $soname]]);
		} else {
			$list = Db::name("ybmp_reserve_point")->alias("p")->join("ybmp_user u", "p.user_id = u.uid", "left")->join("ybmp_reserve_thing t", "p.thing_id = t.id", "left")->where("p.mch_id", $this->bus_id)->field("p.*,u.nick_name,t.name tname")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		}
		$this->assign("list", $list);
		$this->assign("soname", $soname);
		return view();
	}
	public function order_del()
	{
		$id = request()->post("id");
		if ($id != '' && !empty($id)) {
			$result = Db::name("ybmp_reserve_point")->where("id", $id)->where("mch_id", $this->bus_id)->delete();
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
	public function get_form_info()
	{
		$id = input("param.id");
		$user_info = Db::name("ybmp_reserve_point")->where("id", $id)->where("mch_id", $this->bus_id)->find();
		$user_info["param"] = str_replace("\\n", "<br>", $user_info["param"]);
		$user_info["param"] = json_decode($user_info["param"], true);
		foreach ($user_info["param"] as $k => $v) {
			$string_arr = explode("-", $v["name"]);
			$user_info["param"][$k]["key"] = $string_arr[1];
		}
		$this->assign("user_info", $user_info);
		return view("user/get_form_info");
	}
}
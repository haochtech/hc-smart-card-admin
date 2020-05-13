<?php


namespace app\admin\controller;

use think\Db;
class Message extends Base
{
	public function index()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$id = $data["id"];
			unset($data["id"]);
			$data = json_encode($data, true);
			if (!empty($id) && $id != '') {
				$t_count = Db::name("ybmp_tmpl_dope")->where("id", $id)->count();
			} else {
				$t_count = 0;
			}
			if ($t_count > 0) {
				$result = Db::name("ybmp_tmpl_dope")->where("id", $id)->where("mch_id", $this->bus_id)->update(["temp" => $data]);
				if ($result !== false) {
					return AjaxReturn(1);
				} else {
					return AjaxReturn(0);
				}
			} else {
				$result = Db::name("ybmp_tmpl_dope")->insert(["temp" => $data, "mch_id" => $this->bus_id]);
				if ($result !== false) {
					return AjaxReturn(1);
				} else {
					return AjaxReturn(0);
				}
			}
		}
		$mess = Db::name("ybmp_tmpl_dope")->where("mch_id", $this->bus_id)->find();
		if (count($mess) > 0) {
			$list = json_decode($mess["temp"], true);
			$this->assign("lone", $mess["id"]);
		} else {
			$list = [];
		}
		$this->assign("list", $list);
		return view("message/index");
	}
}
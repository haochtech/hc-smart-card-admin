<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
class Getip extends Controller
{
	public function get_wx_log()
	{
		$param = request()->param();
		Db::name("ybmp_user_log")->insert($param);
	}
	public function get_all_wx_log()
	{
		$bus_id = Session::get("bus_id");
		$list = Db::name("ybmp_user_log")->where("mch_id", $bus_id)->limit(10)->select();
		$this->assign("list", $list);
		return view();
	}
}
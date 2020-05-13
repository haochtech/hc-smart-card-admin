<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
class Merchant extends Controller
{
	public function contact()
	{
		$unid = request()->get("unid");
		$list = Db::name("ybmp_contact")->where("id", 1)->find();
		$this->assign("list", $list);
		$snap = $_SERVER["PHP_SELF"];
		$cuff = explode("addons", $snap);
		if ($_SERVER["HTTPS"]) {
			$cure = "https://" . WEB_HOST . $cuff[0];
		} else {
			$cure = "http://" . WEB_HOST . $cuff[0];
		}
		$this->assign("cure", $cure);
		return view();
	}
	public function about()
	{
		$unid = request()->get("unid");
		$list = Db::name("ybmp_contact")->where("id", 1)->find();
		$this->assign("list", $list);
		$snap = $_SERVER["PHP_SELF"];
		$cuff = explode("addons", $snap);
		if ($_SERVER["HTTPS"]) {
			$cure = "https://" . WEB_HOST . $cuff[0];
		} else {
			$cure = "http://" . WEB_HOST . $cuff[0];
		}
		$this->assign("cure", $cure);
		return view();
	}
}
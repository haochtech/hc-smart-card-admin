<?php


namespace app\admin\controller;

use think\Db;
class Book extends Base
{
	public function index()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_bus_book")->alias("book")->join("ybmp_user user", "user.uid = book.user_id", "left")->where("book.mch_id", $this->bus_id)->field("book.*,user.nick_name")->order("create_time")->paginate(12, false, ["query" => ["s" => $url]]);
		$a = array();
		for ($i = 0; $i < count($list); $i++) {
			$a[$i] = $list[$i];
			$a[$i]["contents"] = str_replace("\n", '', $list[$i]["content"]);
		}
		$this->assign("list", $a);
		$this->assign("page", $list->render());
		return view();
	}
	public function rebook()
	{
		if (request()->isAjax() && request()->isPost()) {
			$result = Db::name("ybmp_bus_book")->where("id", request()->post("id"))->where("mch_id", $this->bus_id)->delete();
			return AjaxReturn($result);
		}
	}
}
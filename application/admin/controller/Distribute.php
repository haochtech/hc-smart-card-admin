<?php


namespace app\admin\controller;

use think\Db;
class Distribute extends Base
{
	public function index()
	{
		$where["g.is_del"] = 0;
		$search_text = input("param.search_text");
		if (!empty($search_text)) {
			$where["g.goods_name"] = ["like", "%" . $search_text . "%"];
		}
		$star_time = input("param.star_time");
		$end_time = input("param.end_time");
		if (!empty($star_time)) {
			$star = strtotime($star_time);
			$where["s.create_time"] = ["between", [$star, $star + 86400]];
		}
		if (!empty($star_time) && !empty($end_time)) {
			$star = strtotime($star_time);
			$end = strtotime($end_time);
			$where["s.create_time"] = ["between", [$star, $end]];
		}
		$where["s.mch_id"] = array("eq", $this->bus_id);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&page", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_goods_share_setting")->alias("s")->join("ybmp_goods g", "g.goods_id=s.goods_id")->join("ybmp_images images", "g.images = images.img_id", "left")->field("s.*,g.goods_name,g.price,g.images,images.img_cover")->where($where)->paginate(20, false, ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("result", $list);
		$this->assign("search_text", $search_text);
		$this->assign("star_time", $star_time);
		$this->assign("end_time", $end_time);
		$share_level = db::name("ybmp_user_share_setting")->where("mch_id", $this->bus_id)->field("level")->find();
		if (empty($share_level)) {
			$share["level"] = 0;
		} else {
			$share = $share_level;
		}
		switch ($share_level["level"]) {
			case 0:
				break;
			case 1:
				$share["second"] = "-1";
				$share["third"] = "-1";
				break;
			case 2:
				$share["third"] = "-1";
				break;
		}
		$this->assign("share", $share);
		return view();
	}
	public function share_type_all()
	{
		$id = input("param.id");
		$status = input("param.key");
		$res = Db::name("ybmp_goods_share_setting")->where("id", $id)->update(["status" => $status]);
		return AjaxReturn($res);
	}
	public function share_del()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_goods_share_setting")->where("id", $id)->delete();
		return AjaxReturn($res);
	}
	public function add_share()
	{
		$id = input("param.goods_id", 0);
		if ($id > 0) {
			$share = db::name("ybmp_goods_share_setting")->alias("s")->join("ybmp_goods g", "g.goods_id=s.goods_id")->join("ybmp_images images", "g.images = images.img_id", "left")->field("s.*,g.goods_name,g.price,g.images,images.img_cover")->where("s.goods_id", $id)->find();
		} else {
			$share["price_type"] = 0;
		}
		$share2 = db::name("ybmp_user_share_setting")->where("mch_id", $this->bus_id)->field("level")->find();
		if (empty($share2)) {
			$share["level"] = 0;
		}
		switch ($share2["level"]) {
			case 0:
				break;
			case 1:
				$share["second"] = "-1";
				$share["third"] = "-1";
				break;
			case 2:
				$share["third"] = "-1";
				break;
		}
		if ($share["price_type"] == 0) {
			$share["icon"] = "%";
		} else {
			$share["icon"] = "元";
		}
		$this->assign("share", $share);
		$this->assign("id", $id);
		return view();
	}
	public function share_pay()
	{
		$data["goods_id"] = input("param.goods_id", 0);
		if ($data["goods_id"] == 0) {
			return 1;
		}
		$data["mch_id"] = $this->bus_id;
		$data["first"] = input("param.first", 0.0);
		$data["second"] = input("param.second", 0.0);
		$data["third"] = input("param.third", 0.0);
		$data["price_type"] = input("param.price_type", 0);
		$count = Db::name("ybmp_goods_share_setting")->where(["goods_id" => $data["goods_id"], "mch_id" => $data["mch_id"]])->count();
		if ($count > 0) {
			$rs = Db::name("ybmp_goods_share_setting")->where(["goods_id" => $data["goods_id"], "mch_id" => $data["mch_id"]])->update($data);
		} else {
			$data["create_time"] = time();
			$data["status"] = 1;
			$rs = Db::name("ybmp_goods_share_setting")->insert($data);
		}
		if ($rs) {
			return 0;
		} else {
			return 1;
		}
	}
}
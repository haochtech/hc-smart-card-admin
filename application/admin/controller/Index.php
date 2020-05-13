<?php


namespace app\admin\controller;

use app\admin\service\ConfigService;
use think\Cache;
use think\Db;
class Index extends Base
{
	public function index()
	{
		$card_sum = Db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("is_del", 0)->count();
		$is_card = Db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("radar", 1)->count();
		$corp = Db::name("ybmp_user_permission")->where("user_id", $this->uuid)->find();
		$dtime = Db::name("users")->where("uid", $this->uuid)->value("starttime");
		if (empty($dtime) || $dtime < 10) {
			$dtime = $corp["create_time"];
		}
		$sur_card = $corp["card_num"] - $is_card;
		$diff = floor((time() - $dtime) / 86400);
		$sheng = ceil(($corp["card_etime"] - time()) / 86400);
		if ($sheng < 0) {
			$sheng = 0;
		}
		if ($sur_card < 0) {
			$sur_card = 0;
		}
		if (empty($corp["card_etime"]) || $corp["card_etime"] < 1000) {
			$corp["card_etime"] = -1;
		}
		if ($corp["card_etime"] < $corp["create_time"]) {
			$corp["card_etime"] = $corp["create_time"];
		}
		$cus = db::name("ybmp_customer")->alias("a")->join("ybmp_user c", "a.user_id=c.uid", "left")->where("a.mch_id", $this->bus_id)->where("c.uid is not null")->group("a.user_id")->count();
		$this->assign("info", $this->count_info());
		$this->assign("card_sum", $card_sum);
		$this->assign("is_card", $is_card);
		$this->assign("sur_card", $sur_card);
		$this->assign("corp", $corp);
		$this->assign("diff", $diff);
		$this->assign("dtime", $dtime);
		$this->assign("sheng", $sheng);
		$this->assign("cus", $cus);
		$in = $this->check_permition(233);
		$ii = !$in ? 1 : 2;
		$this->assign("redi", $ii);
		$this->assign("uuid", $this->uuid);
		return $this->fetch("index/index");
	}
	public function clear()
	{
		Cache::clear();
	}
	public function ttt()
	{
		$where["pid"] = 0;
		$where["level"] = 1;
		$res1 = Db::name("ybmp_bus_module")->where($where)->select();
		foreach ($res1 as &$item1) {
			$where1["pid"] = $item1["module_id"];
			$where1["level"] = 2;
			$res2 = Db::name("ybmp_bus_module")->where($where1)->select();
			foreach ($res2 as &$item2) {
				$where2["pid"] = $item2["module_id"];
				$where2["level"] = 3;
				$res3 = Db::name("ybmp_bus_module")->where($where2)->select();
				$item2["sub"] = $res3;
			}
			$item1["sub"] = $res2;
		}
		exit(json_encode($res1, true));
	}
	public function count_info()
	{
		$date = request()->param("date", "8384");
		$type = request()->param("type", "8384");
		$mch = $this->bus_id;
		$date = $date == "8384" ? 0 : time() - $date * 86400;
		$res["customer"] = db::name("ybmp_customer")->alias("a")->join("ybmp_user c", "a.user_id=c.uid", "left")->where("a.mch_id", $this->bus_id)->where("c.uid is not null")->group("a.user_id")->where(["a.create_time" => [">", $date]])->count();
		$res["click"] = db::name("ybmp_information")->where("mch_id", $mch)->where(["create_time" => [">", $date]])->sum("click");
		$res["forward"] = db::name("ybmp_user_oplog")->where("mch_id", $mch)->where("op_type", 2)->where(["create_time" => [">", $date], "staff_id" => [">", 0]])->group("user_id")->count();
		$res["likes"] = db::name("ybmp_bus_card_likes")->where("mch_id", $mch)->where(["create_time" => [">", $date]])->count();
		if ($type == "8384") {
			return $res;
		} else {
			return json_encode($res, true);
		}
	}
}
<?php


namespace app\api\service;

use think\Db;
use think\Exception;
class DistribeService
{
	private $i = "ybmp_images";
	private $og = "ybmp_order_goods";
	private $u = "ybmp_user";
	private $us = "ybmp_user_share";
	public function user_info($data, $pid)
	{
		$u = Db::name($this->u)->where($data)->find();
		if (empty($u)) {
			return null;
		}
		if ($pid <= 0) {
			$u["parent"] = "总店";
		} else {
			$parent = Db::name($this->us)->where(["mch_id" => $data["mch_id"], "user_id" => $pid, "is_del" => 1])->value("name");
			if (empty($parent)) {
				$parent = Db::name($this->u)->where(["mch_id" => $data["mch_id"], "uid" => $pid])->value("nick_name");
			}
			$u["parent"] = $parent ? $parent : "总店";
		}
		$u["today_price"] = Db::name("ybmp_user_share_cash")->where(["mch_id" => $data["mch_id"], "user_id" => $data["uid"], "is_del" => 1])->whereTime("create_time", "d")->sum("price");
		$u["order_share_money"] = $this->get_share_money($data["uid"], $data["mch_id"], ["in", "0,1,2,3"]);
		$u["order_money_yj"] = $this->get_share_money($data["uid"], $data["mch_id"], ["in", "0,1,2"]);
		$u["order_money_un"] = $this->get_share_money($data["uid"], $data["mch_id"], ["in", "1,2"]);
		$u["un_pay"] = Db::name("ybmp_user_share_cash")->where(["mch_id" => $data["mch_id"], "status" => "1", "user_id" => $data["uid"], "is_del" => 1])->sum("price");
		$u["get_price"] = sprintf("%.2f", $u["total_price"] - $u["price"]);
		$a = $this->InfiniteClass([$data["uid"]], $data["mch_id"], 1);
		$info["first"] = $a["count"];
		$info["second"] = 0;
		$info["third"] = 0;
		if (count($a["pid_arr"]) > 0) {
			$b = $this->InfiniteClass($a["pid_arr"], $data["mch_id"], 1);
			$info["second"] = $b["count"];
			if (count($b["pid_arr"]) > 0) {
				$c = $this->InfiniteClass($b["pid_arr"], $data["mch_id"], 1, 1);
				$info["third"] = $c["count"];
			}
		}
		$share = Db::name("ybmp_user_share_setting")->where(["mch_id" => $data["mch_id"]])->value("level");
		if ($share == 1) {
			$u["team_count"] = $info["first"];
		} elseif ($share == 2) {
			$u["team_count"] = $info["first"] + $info["second"];
		} elseif ($share == 3) {
			$u["team_count"] = $info["first"] + $info["second"] + $info["third"];
		} else {
			$u["team_count"] = 0;
		}
		return $u;
	}
	public function addman($data)
	{
		$info = null;
		$rs = Db::name("ybmp_user")->where(["uid" => $data["uid"], "mch_id" => $data["mch_id"]])->find();
		if (!empty($rs) && $data["pid"] != 0 && $rs["pid"] == 0 && $data["uid"] != $data["pid"] && $rs["is_distributor"] == 0) {
			$info = Db::name("ybmp_user")->where("uid", $data["uid"])->update(["pid" => $data["pid"]]);
		}
		return $info;
	}
	public function get_share_money($uid, $mch_id, $status, $type = 0)
	{
		$fir = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_1" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->sum("os.first_price");
		$sec = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_2" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->sum("os.second_price");
		$thi = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_3" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->sum("third_price");
		$reb = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.user_id" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->sum("rebate");
		$money = floatval($fir) + floatval($sec) + floatval($thi) + floatval($reb);
		if ($type == 1) {
			$fir2 = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_1" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->count();
			$sec2 = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_2" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->count();
			$thi2 = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.parent_id_3" => $uid, "os.mch_id" => $mch_id, "os.is_del" => 1, "o.order_status" => $status])->count();
			$reb2 = Db::name("ybmp_order_share")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->where(["os.user_id" => $uid, "os.rebate" => ["<>", "0.00"], "os.is_del" => 1, "os.mch_id" => $mch_id, "o.order_status" => $status])->count();
			$count = intval($fir2) + intval($sec2) + intval($thi2) + intval($reb2);
			return ["order_money" => sprintf("%.2f", $money), "order_count" => $count];
		}
		return sprintf("%.2f", $money);
	}
	public function get_join($data)
	{
		$rs = array("code" => 0, "info" => array());
		$count = Db::name($this->us)->where(["mch_id" => $data["mch_id"], "user_id" => $data["user_id"], "is_del" => 1])->count();
		if ($count > 0) {
			$rs["code"] = 1;
			$rs["msg"] = "用户已存在";
			return json_encode($rs);
		}
		$share_condition = Db::name("ybmp_user_share_setting")->where(["mch_id" => $data["mch_id"]])->value("share_condition");
		$rs["share_condition"] = $share_condition;
		if ($share_condition == 1) {
			$data["status"] = 0;
		} else {
			$data["status"] = 1;
		}
		$data["is_del"] = 1;
		$data["seller_comments"] = '';
		$data["create_time"] = time();
		Db::startTrans();
		try {
			$res = Db::name($this->us)->insertGetId($data);
			if (empty($res)) {
				throw new Exception("操作失败");
			}
			if ($share_condition == 1) {
				$is_distributor = 2;
			} else {
				$is_distributor = 1;
			}
			Db::name($this->u)->where(["mch_id" => $data["mch_id"], "uid" => $data["user_id"]])->update(["is_distributor" => $is_distributor]);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			$rs["code"] = 1;
			$rs["msg"] = $e->getMessage();
			return json_encode($rs);
		}
		$rs["info"] = $res;
		return json_encode($rs);
	}
	public function get_myteam($data, $page)
	{
		$a = $this->InfiniteClass([$data["uid"]], $data["mch_id"], $page);
		$info["first"] = $a["count"];
		$info["list"] = [];
		$info["second"] = 0;
		$info["third"] = 0;
		$b["list"] = [];
		$c["list"] = [];
		if (count($a["pid_arr"]) > 0) {
			$b = $this->InfiniteClass($a["pid_arr"], $data["mch_id"], $page);
			$info["second"] = $b["count"];
			if (count($b["pid_arr"]) > 0) {
				$c = $this->InfiniteClass($b["pid_arr"], $data["mch_id"], $page);
				$info["third"] = $c["count"];
			}
		}
		if ($data["status"] == 1) {
			$info["list"] = $a["list"];
		} elseif ($data["status"] == 2) {
			$info["list"] = $b["list"];
		} elseif ($data["status"] == 3) {
			$info["list"] = $c["list"];
		}
		return $info;
	}
	public function InfiniteClass($pid, $mch_id, $page)
	{
		$rs = array("count" => 0, "list" => [], "pid_arr" => []);
		$rs["count"] = Db::name($this->u)->where(["pid" => ["in", $pid], "mch_id" => $mch_id])->count();
		$li = Db::name($this->u)->where(["pid" => ["in", $pid], "mch_id" => $mch_id])->select();
		if ($li) {
			foreach ($li as $k1 => $v1) {
				array_push($rs["pid_arr"], $v1["uid"]);
			}
		}
		$list = Db::name($this->u)->where(["pid" => ["in", $pid], "mch_id" => $mch_id])->page($page, PAGE_NUM)->order("uid", "desc")->select();
		if ($list) {
			foreach ($list as $k => $v) {
				$a = $this->get_share_money($v["uid"], $mch_id, ["in", "0,1,2,3"], 1);
				$list[$k]["order_count"] = $a["order_count"];
				$list[$k]["order_money"] = $a["order_money"];
				$list[$k]["child_count"] = Db::name($this->u)->where(["pid" => $v["uid"], "mch_id" => $mch_id])->count();
				$list[$k]["reg_time"] = date("Y-m-d H:i:s", $list[$k]["reg_time"]);
			}
			$rs["list"] = $list;
		}
		return $rs;
	}
	public function get_shareOrder($data, $page)
	{
		$status = $data["status"];
		$where["os.mch_id"] = $data["mch_id"];
		$where["os.is_del"] = 1;
		if ($status == 0) {
			$where["o.order_status"] = 0;
		} elseif ($status == 1) {
			$where["o.order_status"] = ["in", "1,2"];
		} elseif ($status == 2) {
			$where["o.order_status"] = 3;
		} else {
			$where["o.order_status"] = ["in", "0,1,2,3"];
		}
		$where["os.parent_id_1|os.parent_id_2|os.parent_id_3|os.user_id"] = $data["user_id"];
		$list = Db::name("ybmp_order_share")->field("os.*,o.order_status,o.order_no,u.user_headimg,u.nick_name")->alias("os")->join("ybmp_order o", "os.order_id=o.order_id")->join("ybmp_user u", "u.uid=os.user_id")->where($where)->page($page, PAGE_NUM)->order("os.id", "desc")->select();
		if (empty($list)) {
			return [];
		}
		foreach ($list as $key => $value) {
			$text = '';
			$money = 0.0;
			if ($data["user_id"] == $value["user_id"]) {
				$money = $value["rebate"];
				$text = "自购返现";
			} elseif ($data["user_id"] == $value["parent_id_1"]) {
				$text = "一级";
				$money = $value["first_price"];
			} elseif ($data["user_id"] == $value["parent_id_2"]) {
				$text = "二级";
				$money = $value["second_price"];
			} elseif ($data["user_id"] == $value["parent_id_3"]) {
				$text = "三级";
				$money = $value["third_price"];
			}
			$list[$key]["share_type"] = $text;
			$list[$key]["share_money"] = $money;
			$list[$key]["create_time"] = date("Y-m-d H:i:s", $list[$key]["create_time"]);
			$goods = Db::name($this->og)->where("order_id", $value["order_id"])->select();
			foreach ($goods as $k => $v) {
				$pic = Db::name($this->i)->where("img_id", $v["goods_images"])->value("img_cover");
				if ($pic) {
					$goods[$k]["pic"] = $pic;
				}
			}
			$list[$key]["goods"] = $goods;
		}
		return $list;
	}
	public function get_shareSetting($mch_id)
	{
		$info = Db::name("ybmp_user_share_setting")->where("mch_id", $mch_id)->find();
		if ($info && !empty($info["pay_type"])) {
			$info["pay_type"] = json_decode($info["pay_type"], true);
		} else {
			$info["pay_type"] = [];
		}
		if ($info && !empty($info["other"])) {
			$info["other"] = json_decode($info["other"], true);
		} else {
			$info["other"] = ["1" => "可提现佣金", "2" => "已提现佣金", "3" => "推荐人", "4" => "未结算佣金", "5" => "分销佣金", "6" => "分销订单", "7" => "提现明细", "8" => "我的团队", "9" => "推广二维码", "10" => "发展下线", "11" => "待打款佣金", "12" => "分销中心", "13" => "分销商"];
		}
		if (!isset($info["level"])) {
			$info["level"] = 0;
		}
		return $info;
	}
	public function get_addCash($data)
	{
		$rs = array("code" => 1);
		Db::startTrans();
		try {
			$u = Db::name($this->u)->where(["mch_id" => $data["mch_id"], "uid" => $data["user_id"]])->find();
			if ($u) {
				if ($data["price"] > $u["price"]) {
					throw new Exception("余额不足");
				}
			}
			$today_price = Db::name("ybmp_user_share_cash")->where(["mch_id" => $data["mch_id"], "user_id" => $data["user_id"], "is_del" => 1])->whereTime("create_time", "d")->sum("price");
			$setting = $this->get_shareSetting($data["mch_id"]);
			if ($setting) {
				if ($data["price"] < $setting["min_money"]) {
					throw new Exception("提现金额不能低于最小提现额度");
				}
				if ($setting["max_money"] > 0 && $setting["max_money"] - $today_price < $data["price"]) {
					throw new Exception("提现金额不能高于今日额度");
				}
			}
			$res = Db::name("ybmp_user_share_cash")->insertGetId($data);
			if (empty($res)) {
				throw new Exception("提现操作失败");
			}
			$res2 = Db::name($this->u)->where(["mch_id" => $data["mch_id"], "uid" => $data["user_id"]])->setDec("price", $data["price"]);
			if (empty($res2)) {
				throw new Exception("提现操作失败");
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			$rs["msg"] = $e->getMessage();
			return json_encode($rs);
		}
		$rs["code"] = 0;
		$rs["info"] = $res;
		return json_encode($rs);
	}
	public function get_CashList($data, $page)
	{
		if ($data["status"] == -1) {
			unset($data["status"]);
		}
		$data["is_del"] = 1;
		$list = Db::name("ybmp_user_share_cash")->where($data)->page($page, PAGE_NUM)->order("id", "desc")->select();
		if ($list) {
			foreach ($list as $k => $v) {
				$list[$k]["create_time"] = date("Y-m-d H:i:s", $list[$k]["create_time"]);
			}
		}
		return $list;
	}
}
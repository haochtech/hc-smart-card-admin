<?php


namespace app\web\service;

use think\Db;
use think\Cache;
class WebindexService
{
	private $status = array("1" => "查看", "2" => "转发", "3" => "复制", "4" => "保存", "5" => "拨打", "6" => "浏览");
	public function get_userinfo($data)
	{
		$info = Db::name("ybmp_bus_card")->where($data)->find();
		if ($info) {
			$info["mch_name"] = '';
		}
		return $info;
	}
	public function get_index_time($data, $page)
	{
		$list = Db::name("ybmp_user_oplog")->where($data)->page($page, PAGE_NUM)->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$use = Db::name("ybmp_user")->where("uid", $v["user_id"])->field("user_headimg,nick_name")->find();
				$tmp = Db::name("ybmp_customer")->where("user_id", $v["user_id"])->where("staff_id", $data["staff_id"])->value("remark");
				if ($tmp != "昵称") {
					$use["nick_name"] = $tmp;
				}
				$list[$k]["user"] = $use;
				$list[$k]["op_type"] = $this->status[$v["op_type"]] ? $this->status[$v["op_type"]] : "查看";
				if ($v["op_type"] == 6) {
					if ($v["op_name"] == "商品") {
						$goods = Db::name("ybmp_goods")->where(["mch_id" => $data["mch_id"], "goods_id" => $v["de_id"]])->value("goods_name");
					} else {
						$goods = Db::name("ybmp_product")->where(["mch_id" => $data["mch_id"], "id" => $v["de_id"]])->value("title");
					}
					if ($goods) {
						$list[$k]["op_name"] = $v["op_name"] . ":" . $goods;
					} else {
						$list[$k]["op_name"] = $v["op_name"] . ":该" . $v["op_name"] . "已不存在";
					}
				}
				$list[$k]["time"] = date("H:i", $v["create_time"]);
				$list[$k]["date"] = timeToChzh($v["create_time"]);
			}
		} else {
			$list = [];
		}
		return $list;
	}
	public function get_index_detail($data, $page)
	{
		$list = Db::name("ybmp_user_oplog")->where($data)->page($page, PAGE_NUM)->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list[$k]["user"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->field("user_headimg,nick_name")->find();
				$list[$k]["op_type"] = $this->status[$v["op_type"]] ? $this->status[$v["op_type"]] : "查看";
				if ($v["op_type"] == 6) {
					$goods = Db::name("ybmp_goods")->where(["mch_id" => $data["mch_id"], "goods_id" => $v["de_id"]])->value("goods_name");
					if ($goods) {
						$list[$k]["op_name"] = "商品:" . $goods;
					} else {
						$list[$k]["op_name"] = "商品:该商品已不存在";
					}
				}
				$list[$k]["time"] = date("H:i", $v["create_time"]);
				$list[$k]["date"] = timeToChzh($v["create_time"]);
			}
		} else {
			$list = [];
		}
		$rs["info"] = $list;
		$rs["count"] = Db::name("ybmp_user_oplog")->where($data)->count();
		return $rs;
	}
	public function get_index_detail_like($data, $page)
	{
		$data["type"] = 1;
		$data["op_id"] = 1;
		$list = Db::name("ybmp_bus_card_likes")->where($data)->page($page, PAGE_NUM)->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list[$k]["user"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->field("user_headimg,nick_name")->find();
				$list[$k]["time"] = date("H:i", $v["create_time"]);
				$list[$k]["date"] = timeToChzh($v["create_time"]);
			}
		} else {
			$list = [];
		}
		$rs["info"] = $list;
		$rs["count"] = Db::name("ybmp_bus_card_likes")->where($data)->count();
		return $rs;
	}
	public function get_index_behavior($data)
	{
		$info["ck_mp"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["ck_gw"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "官网"])->count();
		$info["ck_dt"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "动态"])->count();
		$info["ck_sc"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "商城"])->count();
		$info["likes"] = Db::name("ybmp_bus_card_likes")->where(["c_id" => $data["staff_id"], "type" => 1, "op_id" => 1, "create_time" => $data["create_time"]])->count();
		$info["zf_mp"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
		$info["fz_wx"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 3, "op_name" => "微信"])->count();
		$info["fz_yx"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 3, "op_name" => "邮箱"])->count();
		$info["bc_dh"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 4, "op_name" => "电话"])->count();
		$info["bd_dh"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 5, "op_name" => "电话"])->count();
		$info["ll_sp"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 6])->count();
		return $info;
	}
	public function get_index_people($data, $page)
	{
		$list = Db::name("ybmp_user_oplog")->where($data)->field("*,count('user_id') as num")->group("user_id")->page($page, PAGE_NUM)->order("id")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$data["user_id"] = $v["user_id"];
				$list[$k]["user"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->field("user_headimg,nick_name")->find();
				$list[$k]["ck_mp"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
				$list[$k]["ck_gw"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "官网"])->count();
				$list[$k]["ck_dt"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "动态"])->count();
				$list[$k]["ck_sc"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "商城"])->count();
				$list[$k]["zf_mp"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
				$list[$k]["fz_wx"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 3, "op_name" => "微信"])->count();
				$list[$k]["fz_yx"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 3, "op_name" => "邮箱"])->count();
				$list[$k]["bc_dh"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 4, "op_name" => "电话"])->count();
				$list[$k]["bd_dh"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 5, "op_name" => "电话"])->count();
			}
		} else {
			$list = [];
		}
		return $list;
	}
	public function get_NewsList($data)
	{
		$list = Db::query("select * from (select * from ims_ybmp_messages where staff_id=" . $data["staff_id"] . " and mch_id=" . $data["mch_id"] . " order by id desc) as a group by a.user_id order by a.id desc limit 0,30");
		if (empty($list)) {
			return [];
		}
		foreach ($list as $k => $v) {
			$message = Db::name("ybmp_messages")->where(["staff_id" => $v["staff_id"], "user_id" => $v["user_id"]])->order("id desc")->find();
			$list[$k]["user"] = $list[$k]["user"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->field("user_headimg,nick_name")->find();
			if ($message) {
				if ($message["post_type"] == 2) {
					$list[$k]["message"] = "[图片]";
				} else {
					$list[$k]["message"] = $message["post_message"];
				}
				$list[$k]["time"] = timeToChzh($message["create_time"]);
			}
			$list[$k]["wd_num"] = Db::name("ybmp_messages")->where(["staff_id" => $v["staff_id"], "user_id" => $v["user_id"], "type" => 1, "status" => 0])->count();
		}
		return $list;
	}
	public function timely_news($data)
	{
		$a = Db::name("ybmp_messages")->where($data)->order("id desc")->limit(100)->select();
		$count = Db::name("ybmp_messages")->where($data)->count();
		$data["id"] = array(">", intval($a[$count - 1]["id"]) - 1);
		$list = Db::name("ybmp_messages")->where($data)->order("id")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list[$k]["time"] = timeToChzh($v["create_time"]);
				Db::name("ybmp_messages")->where(["id" => $v["id"], "type" => 1])->update(["status" => 1]);
				$list[$k]["user_pic"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->value("user_headimg");
				$pic = Db::name("ybmp_bus_card")->where("id", $v["staff_id"])->value("head_photo");
				$list[$k]["staff_pic"] = $pic ? $pic : "vue/img/defaultlogo.png";
			}
		}
		return $list;
	}
	public function addnews($data)
	{
		$data["type"] = 2;
		$data["status"] = 0;
		$data["create_time"] = time();
		$res = Db::name("ybmp_messages")->insert($data);
		return $res;
	}
	public function wd_news($data)
	{
		$count = Db::name("ybmp_messages")->where(["staff_id" => $data["staff_id"], "type" => 1, "status" => 0])->count();
		return $count;
	}
	public function wordpool($data)
	{
		$data["is_del"] = 1;
		$default = ["id" => 0, "name" => "自定义"];
		$list = Db::name("ybmp_wordpool_class")->where($data)->order("sort desc")->order("id desc")->select();
		array_push($list, $default);
		foreach ($list as $k => $v) {
			$list[$k]["child"] = Db::name("ybmp_wordpool")->where(["mch_id" => $data["mch_id"], "class_id" => $v["id"]])->order("id desc")->select();
		}
		return $list;
	}
	public function add_wordpool($data)
	{
		$data["create_time"] = time();
		$rs = Db::name("ybmp_wordpool")->insert($data);
		return $rs;
	}
	public function del_wordpool($data)
	{
		$rs = Db::name("ybmp_wordpool")->where($data)->delete();
		return $rs;
	}
	public function suggest($data)
	{
		$data["create_time"] = time();
		$rs = Db::name("ybmp_suggest")->insert($data);
		return $rs;
	}
	public function my_chart($data, $type)
	{
		$time = time();
		$time1 = date("y-m-d", $time);
		$time2 = strtotime($time1);
		if ($type == 1) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24, $time2]];
		} elseif ($type == 2) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24 * 7, $time2]];
		} elseif ($type == 3) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24 * 30, $time2]];
		}
		$info["khsum"] = Db::name("ybmp_customer")->where($data)->count();
		$info["gjsum"] = Db::name("ybmp_follow")->where(["staff_id" => $data["staff_id"]])->count();
		$info["viewsum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["zfsum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
		$info["savesum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
		$info["likesum"] = Db::name("ybmp_bus_card_likes")->where(["mch_id" => $data["mch_id"], "c_id" => $data["staff_id"], "type" => 1])->count();
		$info["kfxqzb"]["me"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["kfxqzb"]["product"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "商城"])->count();
		$info["kfxqzb"]["company"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "官网"])->count();
		$dianzan = $info["likesum"];
		$comment = Db::name("ybmp_information_comments")->where(["mch_id" => $data["mch_id"]])->count();
		$zixun = Db::name("ybmp_messages")->where($data)->group("user_id")->count();
		$yinxiang = Db::name("ybmp_customer")->where($data)->where(["is_like" => 0])->count();
		$bd = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 5, "op_name" => "电话"])->count();
		$bc = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 4, "op_name" => "电话"])->count();
		$info["khhd"] = [$bc, $bd, $yinxiang, $zixun, $comment, $dianzan];
		$date_arr = $this->get_days();
		$where_oplog["create_time"] = ["between", [$time2 - 3600 * 24 * 15, $time2]];
		$where_oplog["staff_id"] = $data["staff_id"];
		$oplog_arr = Db::name("ybmp_user_oplog")->where($where_oplog)->select();
		$khhycs = [];
		foreach ($date_arr as $v) {
			$oplog_count = 0;
			foreach ($oplog_arr as $oplog_v) {
				if (strtotime($v) < $oplog_v["create_time"] && $oplog_v["create_time"] < strtotime($v) + 3600 * 24) {
					$oplog_count++;
				}
			}
			$khhycs[date("m-d", strtotime($v))] = $oplog_count;
		}
		$info["khhycs"] = $khhycs;
		return $info;
	}
	public function user_chart($data, $type)
	{
		$time = time();
		$time1 = date("y-m-d", $time);
		$time2 = strtotime($time1);
		$info["likesum"] = Db::name("ybmp_bus_card_likes")->where(["mch_id" => $data["mch_id"], "c_id" => $data["staff_id"], "type" => 1])->count();
		$info["kfxqzb"]["me"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["kfxqzb"]["product"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "商城"])->count();
		$info["kfxqzb"]["company"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "官网"])->count();
		$dianzan = $info["likesum"];
		$comment = Db::name("ybmp_information_comments")->where(["mch_id" => $data["mch_id"]])->count();
		$zixun = Db::name("ybmp_messages")->where($data)->group("user_id")->count();
		$yinxiang = Db::name("ybmp_customer")->where($data)->where(["is_like" => 0])->count();
		$bd = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 5, "op_name" => "电话"])->count();
		$bc = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 4, "op_name" => "电话"])->count();
		$info["khhd"] = [$bc, $bd, $yinxiang, $zixun, $comment, $dianzan];
		$date_arr = $this->get_days();
		$where_oplog["create_time"] = ["between", [$time2 - 3600 * 24 * 15, $time2]];
		$where_oplog["staff_id"] = $data["staff_id"];
		$where_oplog["user_id"] = $data["user_id"];
		$oplog_arr = Db::name("ybmp_user_oplog")->where($where_oplog)->select();
		$khhycs = [];
		foreach ($date_arr as $v) {
			$oplog_count = 0;
			foreach ($oplog_arr as $oplog_v) {
				if (strtotime($v) < $oplog_v["create_time"] && $oplog_v["create_time"] < strtotime($v) + 3600 * 24) {
					$oplog_count++;
				}
			}
			$khhycs[date("m-d", strtotime($v))] = $oplog_count;
		}
		$info["khhycs"] = $khhycs;
		return $info;
	}
	public function setting_card($data, $is_relay)
	{
		$rs = Db::name("ybmp_bus_card")->where($data)->update(["is_relay" => $is_relay]);
		return $rs;
	}
	public function get_card_list($mch_id)
	{
		$data["mch_id"] = ["EQ", $mch_id];
		$data["boss_radar"] = ["EQ", 1];
		$list = Db::name("ybmp_bus_card")->field("id")->where($data)->select();
		if (empty($list)) {
			return $list = [];
		}
		return $list;
	}
	public function my_chart_boss($data, $type)
	{
		$time = time();
		$time1 = date("y-m-d", $time);
		$time2 = strtotime($time1);
		$mch_id = $data["mch_id"];
		if ($type == 1) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24, $time2]];
		} elseif ($type == 2) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24 * 7, $time2]];
		} elseif ($type == 3) {
			$data["create_time"] = ["between", [$time2 - 3600 * 24 * 30, $time2]];
		}
		$list = $this->get_card_list($mch_id);
		$staff_id = '';
		if (count($list) > 0) {
			for ($i = 0; $i < count($list); $i++) {
				$staff_id .= $list[$i]["id"] . ",";
			}
		}
		$info["khsum"] = Db::name("ybmp_customer")->where("mch_id ", $mch_id)->where($data)->group("user_id")->count("distinct user_id");
		$info["gjsum"] = Db::name("ybmp_follow")->where("staff_id", "in", substr($staff_id, 0, strlen($staff_id) - 1))->count();
		$info["viewsum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["zfsum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
		$info["savesum"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 2, "op_name" => "名片"])->count();
		$info["likesum"] = Db::name("ybmp_bus_card_likes")->where(["mch_id" => $data["mch_id"], "type" => 1])->count();
		$info["kfxqzb"]["me"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "名片"])->count();
		$info["kfxqzb"]["product"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "商城"])->count();
		$info["kfxqzb"]["company"] = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 1, "op_name" => "官网"])->count();
		$dianzan = $info["likesum"];
		$comment = Db::name("ybmp_information_comments")->where(["mch_id" => $mch_id])->count();
		$zixun = Db::name("ybmp_messages")->where($data)->group("user_id")->count();
		$yinxiang = Db::name("ybmp_customer")->where($data)->where(["is_like" => 0])->count();
		$bd = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 5, "op_name" => "电话"])->count();
		$bc = Db::name("ybmp_user_oplog")->where($data)->where(["op_type" => 4, "op_name" => "电话"])->count();
		$info["khhd"] = [$bc, $bd, $yinxiang, $zixun, $comment, $dianzan];
		$date_arr = $this->get_days();
		$where_oplog["create_time"] = ["between", [$time2 - 3600 * 24 * 15, $time2]];
		$where_oplog["mch_id"] = $mch_id;
		$oplog_arr = Db::name("ybmp_user_oplog")->where($where_oplog)->select();
		$khhycs = [];
		foreach ($date_arr as $v) {
			$oplog_count = 0;
			foreach ($oplog_arr as $oplog_v) {
				if (strtotime($v) < $oplog_v["create_time"] && $oplog_v["create_time"] < strtotime($v) + 3600 * 24) {
					$oplog_count++;
				}
			}
			$khhycs[date("m-d", strtotime($v))] = $oplog_count;
		}
		$info["khhycs"] = $khhycs;
		return $info;
	}
	function get_days($time = '', $format = "Y-m-d")
	{
		$time = $time != '' ? $time : time();
		$date = [];
		for ($i = 1; $i <= 15; $i++) {
			$date[$i] = date($format, strtotime("+" . $i - 15 . " days", $time));
		}
		return $date;
	}
}
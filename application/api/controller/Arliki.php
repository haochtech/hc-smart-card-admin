<?php


namespace app\api\controller;

use app\api\service\ArlikiService;
use think\Db;
use think\Request;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Arliki extends BaseController
{
	public function get_red_info()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i", 0);
		$user_id = Request::instance()->param("uid", 0);
		$share_id = Request::instance()->param("share_id", 0);
		$rid = Request::instance()->param("rid", 1);
		$turn_share = Request::instance()->param("turn_share", 1);
		$end_time = Request::instance()->param("end_time", 0);
		$mch_id = $this->getMchId($app_id);
		$conf = Db::name("ybmp_red")->where("id", $rid)->find();
		$che = Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->find();
		if (empty($che) && $share_id != -1) {
			$rs["code"] = 0;
			$rs["info"] = "红包已拆分";
			$rs["show_share"] = 1;
			return json_encode($rs);
		}
		if ($share_id == $user_id && $turn_share == 1) {
			$share_id = -1;
		}
		$rs["show_split"] = 0;
		$rs["show_share"] = 1;
		if (empty($conf) || $conf["status"] == 2) {
			$rs["info"] = "商家已关闭红包活动";
			$rs["code"] = -1;
			return json_encode($rs);
		}
		if ($share_id == -1) {
			$rs["code"] = 1;
			$conf["end_time_stam"] = date("Y-m-d H:i:s", $conf["end_time"]);
			$rs["info"] = $this->get_user_list($rid, $user_id, true);
			$rs["bug"] = 2;
			if (!empty($rs["info"])) {
				$rs["bug"] = 0;
				if (intval($rs["info"][0]["create_time"] + $conf["split_time"] * 3600) < time()) {
					$rs["show_share"] = 0;
					$rs["code"] = 1;
					$rs["info"] = "该红包已过期";
					Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $user_id, "status" => 0])->update(["status" => 2]);
					Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $user_id, "status" => 0])->update(["status" => 2]);
					return json_encode($rs);
				}
			}
			if (isset($rs["info"][0]["split_img"]) && !empty($rs["info"][0]["split_img"])) {
				if ($conf["peo_num"] - 1 == count($rs["info"])) {
					$rs["code"] = 1;
					$rs["info"] = "该红包已拆分,可前往优惠券查看";
					$rs["show_share"] = 0;
					$ser = new ArlikiService($mch_id);
					$ser->split_red();
					return json_encode($rs);
				}
			}
			return json_encode($rs);
		}
		if (empty($end_time) || time() > $end_time) {
			if (time() > $end_time && !empty($che)) {
				Db::name("ybmp_redshare")->where(["rid" => $conf["id"], "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
			}
			$rs["code"] = 0;
			$rs["info"] = "未找到该红包";
			$rs["show_share"] = 1;
			return json_encode($rs);
		}
		$share = $this->get_user_list($rid, $share_id);
		if (empty($share)) {
			if (empty($che)) {
				Db::name("ybmp_redshare")->insert(["rid" => $conf["id"], "share_id" => $share_id, "status" => 0, "create_time" => time()]);
				$rs["show_split"] = 0;
			} else {
				if ($che["share_id"] != $user_id) {
					$rs["show_split"] = 1;
				} else {
					$rs["show_split"] = 0;
				}
			}
			$t = Db::name("ybmp_user")->field("nick_name share_name,user_headimg share_img")->where("uid", $share_id)->find();
			$rs["code"] = 1;
			$rs["info"][0] = $t;
			return json_encode($rs);
		}
		if ($conf["peo_num"] - 1 == count($share)) {
			$rs["code"] = 0;
			$rs["info"] = "该红包已拆分,可前往优惠券查看";
			$rs["show_share"] = 1;
			$ser = new ArlikiService($mch_id);
			$ser->split_red();
			return json_encode($rs);
		}
		if ($conf["split_time"] * 3600 + $share[0]["create_time"] <= time()) {
			$rs["code"] = 0;
			$rs["info"] = "该红包已过期";
			$rs["show_share"] = 1;
			Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
			Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
			return json_encode($rs);
		}
		$tmp = array();
		for ($i = 0; $i < count($share); $i++) {
			array_push($tmp, $share[$i]["split_id"]);
		}
		if (!in_array($user_id, $tmp)) {
			$rs["show_split"] = 1;
		} else {
			$rs["show_split"] = 0;
		}
		if ($user_id == $share_id) {
			$rs["show_split"] = 0;
		}
		$rs["show_share"] = 1;
		$rs["code"] = 1;
		$rs["info"] = $share;
		return json_encode($rs);
	}
	public function do_split()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$user_id = Request::instance()->param("uid", 0);
		$mch_id = $this->getMchId($app_id);
		$share_id = Request::instance()->param("share_id", 0);
		$rid = Request::instance()->param("rid", '');
		if ($share_id == $user_id || $user_id <= 0 || $share_id <= 0) {
			$rs["code"] = 4;
			$rs["msg"] = "数据异常";
			return json_encode($rs);
		}
		$ser = new ArlikiService($mch_id);
		$a = $ser->insert_red($rid, $share_id, $user_id);
		return json_encode($a);
	}
	public function share_success()
	{
		$rs = array("code" => 1, "info" => array());
		$app_id = Request::instance()->param("i", 0);
		$user_id = Request::instance()->param("uid", 0);
		$share_id = Request::instance()->param("share_id", 0);
		$rid = Request::instance()->param("rid", 1);
		$che1 = Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->find();
		$che2 = Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $user_id, "status" => 0])->find();
		$mch_id = $this->getMchId($app_id);
		$ser = new ArlikiService($mch_id);
		$ser->split_red();
		if (!empty($che1)) {
			return json_encode($rs);
		}
		if (empty($che2) && $user_id > 0) {
			$rs["code"] = Db::name("ybmp_redshare")->insert(["rid" => $rid, "share_id" => $user_id, "status" => 0, "create_time" => time()]);
		}
		return json_encode($rs);
	}
	public function get_user_list($rid, $share_id, $is_share = false)
	{
		$share = Db::name("ybmp_redlog")->alias("a")->join("ybmp_user s", "a.share_id=s.uid", "left")->join("ybmp_user d", "a.split_id=d.uid", "left")->field("a.*,s.nick_name share_name,d.nick_name split_name,s.user_headimg share_img,d.user_headimg split_img")->where(["a.rid" => $rid, "a.share_id" => $share_id, "a.status" => 0])->order("a.id asc")->select();
		if (empty($share) && $is_share) {
			$share = Db::name("ybmp_redshare")->alias("a")->join("ybmp_user s", "a.share_id=s.uid", "left")->field("s.nick_name share_name,s.user_headimg share_img,a.rid,a.create_time")->where(["a.rid" => $rid, "share_id" => $share_id, "status" => 0])->order("a.id asc")->select();
		}
		return $share;
	}
	public function get_red_conf()
	{
		$share_id = Request::instance()->param("share_id", 0);
		$rid = Request::instance()->param("rid", 1);
		$end_time = Request::instance()->param("end_time", 0);
		$rs["code"] = 1;
		$rs["conf"] = '';
		$conf = Db::name("ybmp_red")->where("id", $rid)->find();
		$conf["role"] = explode("\n", $conf["role"]);
		$che1 = Db::name("ybmp_redshare")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->find();
		$ne = Db::name("ybmp_redlog")->where(["rid" => $rid, "share_id" => $share_id, "status" => 0])->count();
		$conf["need_peo"] = $conf["peo_num"] - $ne - 1;
		if (!empty($che1)) {
			$conf["end_time_steam"] = $che1["create_time"] + 3600 * $conf["split_time"];
			if (time() > $conf["end_time_steam"]) {
				Db::name("ybmp_redshare")->where(["rid" => $conf["id"], "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
				Db::name("ybmp_redlog")->where(["rid" => $conf["id"], "share_id" => $share_id, "status" => 0])->update(["status" => 2]);
				$rs["code"] = 0;
				$rs["bug"] = 2;
				$rs["info"] = "该红包已过期,请重新分享";
				$conf["end_time_steam"] = time() + 3600 * $conf["split_time"];
				$conf["need_peo"] = $conf["peo_num"];
			}
		} else {
			$conf["need_peo"] = $conf["peo_num"];
			$conf["end_time_steam"] = time() + 3600 * $conf["split_time"];
		}
		$conf["end_time"] = date("Y-m-d H:i:s", $conf["end_time_steam"]);
		$conf["all_money"] = $conf["money_num"];
		$conf["all_people"] = $conf["peo_num"];
		$rs["conf"] = $conf;
		return json_encode($rs);
	}
	public function re_js()
	{
		$rs["jsp"] = "<view>这是一个好天气</view>";
		return json_encode($rs);
	}
}
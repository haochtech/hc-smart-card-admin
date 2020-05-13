<?php





namespace app\api\service;



use think\Db;

require_once BASE_ROOT . "core/application/common.php";

class MarketService

{

	private $hd = "ybmp_business_activity";

	private $bus = "ybmp_business";

	private $uc = "ybmp_user_coupon";

	private $bc = "ybmp_business_coupon";

	private $rt = "ybmp_reserve_thing";

	public function ManJian($mch_id)

	{

		$info = Db::name($this->hd)->where("mch_id", $mch_id)->where("is_use", 1)->order("satisfy_money", "desc")->where("star_time", "<", time())->where("end_time", ">", time())->select();

		foreach ($info as $act_k => $act_v) {

			$act_v["star_time"] = date("Y-m-d H:i:s", $act_v["star_time"]);

			$act_v["end_time"] = date("Y-m-d H:i:s", $act_v["end_time"]);

		}

		return $info;

	}

	public function mchInfo($mch_id)

	{

		$info = Db::name($this->bus)->field("id,nick_name,name,phone,mod_class_id,payment_method")->where("id", $mch_id)->where("is_del", 1)->find();

		return $info;

	}

	public function BusCoupon($data, $page)

	{

		$info = Db::name($this->bc)->where("mch_id", $data["mch_id"])->where("is_use", 1)->page($page, PAGE_NUM)->order("id", "desc")->where("star_time", "<", time())->where("end_time", ">", time())->select();

		foreach ($info as $act_k => $act_v) {

			$user_data = ["user_id" => $data["user_id"], "coupon_id" => $act_v["id"]];

			$count = Db::name($this->uc)->where($user_data)->find();

			$coupon = Db::name("ybmp_business_coupon")->where("id", $act_v["id"])->find();

			if ($coupon["get_count"] == $count["get_count"]) {

				$info[$act_k]["is_get"] = 1;

			}

			$info[$act_k]["satisfy_money"] = intval($act_v["satisfy_money"]);

			$info[$act_k]["discount_money"] = intval($act_v["discount_money"]);

			$info[$act_k]["star_time"] = __TIME($act_v["star_time"]);

			$info[$act_k]["endtime"] = date("Y-m-d", $act_v["end_time"]);

		}

		return $info;

	}

	public function booklist($data, $page)

	{

		$info = Db::name($this->rt)->where("mch_id", $data["mch_id"])->page($page, PAGE_NUM)->order("id", "desc")->select();

		return $info;

	}

	public function bookinfo($data)

	{

		$info = Db::name($this->rt)->where($data)->find();

		return $info;

	}

	function in_form($form, $name)

	{

		$title = '';

		for ($i = 0; $i < count($form); $i++) {

			if ($form[$i]["name"] == $name) {

				$title = $form[$i]["title"];

				break;

			}

		}

		return $title;

	}

	public function UserBook($data, $page)

	{

		$list = Db::name("ybmp_reserve_point")->where($data)->page($page, PAGE_NUM)->order("id", "desc")->select();

		if (empty($list)) {

			return $list;

		}

		foreach ($list as $k => $v) {

			$list[$k]["create_time"] = date("Y-m-d H:i:s", $list[$k]["create_time"]);

			$u = Db::name("ybmp_reserve_thing")->where("id", $list[$k]["thing_id"])->find();

			if ($u) {

				$list[$k]["name"] = $u["name"];

				$list[$k]["img"] = $u["img"];

			}

			$list[$k]["param"] = str_replace("\\n", "<br>", $list[$k]["param"]);

			$list[$k]["param"] = json_decode($list[$k]["param"], true);

			foreach ($list[$k]["param"] as $k1 => $v1) {

				$string_arr = explode("-", $list[$k]["param"][$k1]["name"]);

				$list[$k]["param"][$k1]["key"] = $string_arr[1];

			}

		}

		return $list;

	}

	public function submit_form($data)

	{

		$time = time();

		$form = json_decode($data["form"], true);

		$param = json_decode($data["param"], true);

		if (count($param) == 0) {

			return null;

		}

		$arr = array();

		$n = 0;

		foreach ($param as $k => $v) {

			$arr[$n]["name"] = $k;

			$arr[$n]["value"] = $v;

			$arr[$n]["title"] = $this->in_form($form, $k);

			$n++;

		}

		$where = ["thing_id" => $data["thing_id"], "user_id" => $data["user_id"], "mch_id" => $data["mch_id"], "param" => json_encode($arr)];

		$a = Db::name("ybmp_reserve_point")->where($where)->count();

		if ($a > 0) {

			return -1;

		}

		$where["create_time"] = $time;

		$form_id = Db::name("ybmp_reserve_point")->insertGetId($where);

		return $form_id;

	}

	public function getCoupon($data)

	{

		$count = Db::name($this->uc)->where("coupon_id", $data["coupon_id"])->where("user_id", $data["user_id"])->where("mch_id", $data["mch_id"])->find();

		if ($count && intval($count["get_count"]) + 1 > $data["get_count"]) {

			return "exist";

		} else {

			$as = Db::name($this->bc)->where("id", $data["coupon_id"])->find();

			if ($as["rem_count"] > 0) {

				Db::name($this->bc)->where("id", $data["coupon_id"])->setDec("rem_count", 1);

				$info = Db::name($this->uc)->where("coupon_id", $data["coupon_id"])->where("user_id", $data["user_id"])->where("mch_id", $data["mch_id"])->find();

				if (empty($info)) {

					$data["get_count"] = 1;

					$info1 = Db::name($this->uc)->insert($data);

				} else {

					$data["get_count"] = $info["get_count"] + 1;

					$info1 = Db::name($this->uc)->where(["coupon_id" => $data["coupon_id"], "user_id" => $data["user_id"], "mch_id" => $data["mch_id"]])->update($data);

				}

				return $info1;

			} else {

				return "empty";

			}

		}

	}

	public function UserCoupon($data, $page)

	{

		/*$info = Db::name("ybmp_user_coupon")->where(function ($query) {

			$query->where("rid", 1)->where("status", 0)->where("user_id", $data["user_id"]);

		})->whereOr(function ($query) {

			$query->where("rid", 0)->where("get_count", "exp", ">`use_count`")->where("user_id", $data["user_id"]);

		})->select();*/
		
		$info = Db::name("ybmp_user_coupon")->where("`rid`=1 AND `status`=0 AND `user_id`=".$data['user_id'])
		        ->whereOr("`rid` = 0 AND ( `get_count` >`use_count` ) AND `user_id`=".$data['user_id'])->select();

		/*echo  Db::name("ybmp_user_coupon")->getlastsql();
		print_r($data['user_id']);
		die;*/
		foreach ($info as $act_k => $act_v) {

			if ($act_v["coupon_id"] > 0 && $act_v["rid"] == 0) {

				$a = Db::name($this->bc)->where("id", $act_v["coupon_id"])->find();

				if ($a) {

					$info[$act_k]["satisfy_money"] = intval($a["satisfy_money"]);

					$info[$act_k]["discount_money"] = intval($a["discount_money"]);

					$info[$act_k]["together"] = $a["together"];

					$info[$act_k]["endtime"] = date("Y-m-d", $a["end_time"]);

					$info[$act_k]["end_time"] = $a["end_time"];

				}

			} else {

				$a = Db::name("ybmp_red")->where("id", $act_v["rid"])->find();

				if ($act_v["key"] == md5($act_v["rid"] . $act_v["user_id"] . round($act_v["rmoney"], 2) . "Arliki") || $act_v["key"] == md5($act_v["user_id"] . round($act_v["rmoney"], 2) . "Arliki")) {

					$info[$act_k]["satisfy_money"] = round($a["use_least"], 2);

					$info[$act_k]["discount_money"] = round($act_v["rmoney"], 2);

					$info[$act_k]["together"] = 1;

					$info[$act_k]["endtime"] = date("Y-m-d", $act_v["rend_time"]);

					$info[$act_k]["end_time"] = $act_v["rend_time"];

				}

			}

			$info[$act_k]["get_time"] = date("Y-m-d H:i:s", $act_v["get_time"]);

		}

		return $info;

	}

	public function getFormid($data)

	{

		$info = Db::name("ybmp_user_formid")->insertGetId($data);

		return $info;

	}

}
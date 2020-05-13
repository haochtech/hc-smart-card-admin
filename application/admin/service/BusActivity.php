<?php


namespace app\admin\service;

use think\db;
class BusActivity extends Base
{
	private $contr_order_status = array("-1" => "已取消", "0" => "待支付", "1" => "待发货", "2" => "待收货", "3" => "已完成", "4" => "退款中", "5" => "已退款");
	function __construct()
	{
		parent::__construct();
		$this->bus_activity = new \app\common\model\BusActivity();
	}
	public function getActivityAll($condition = '', $search_text = '', $order = '')
	{
		$list = $this->bus_activity->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function addActivity($activity_name, $satisfy_money, $discount_money, $star_time, $end_time, $is_use, $mch_id)
	{
		$data = array("activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "is_use" => $is_use, "mch_id" => $mch_id);
		$res = $this->bus_activity->save($data);
		return $res;
	}
	public function editActivity($id, $activity_name, $satisfy_money, $discount_money, $star_time, $end_time, $is_use, $mch_id)
	{
		$data = array("activity_name" => $activity_name, "satisfy_money" => $satisfy_money, "discount_money" => $discount_money, "star_time" => $star_time, "end_time" => $end_time, "is_use" => $is_use, "mch_id" => $mch_id);
		$res = $this->bus_activity->save($data, ["id" => $id]);
		return $res;
	}
	public function getActivityInfo($id)
	{
		$list = $this->bus_activity->getInfo(["id" => $id]);
		$list["star_time"] = date("Y-m-d H:i:s", $list["star_time"]);
		$list["end_time"] = date("Y-m-d H:i:s", $list["end_time"]);
		return $list;
	}
	public function delActivity($id, $mch_id)
	{
		$list = $this->bus_activity->destroy(["id" => $id, "mch_id" => $mch_id]);
		return $list;
	}
	public function offActivity($id)
	{
		$data = array("is_use" => 2);
		$res = $this->bus_activity->save($data, ["id" => $id]);
		return $res;
	}
	public function onActivity($id)
	{
		$data = array("is_use" => 1);
		$res = $this->bus_activity->save($data, ["id" => $id]);
		return $res;
	}
	public function get_order_list($condition, $order_no = '')
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$status = $this->contr_order_status;
		$num_list = db::name("ybmp_order_goods")->alias("g")->join("ybmp_order_share s", "s.order_id=g.order_id")->field("g.order_id,count('s.order_id') nums")->where($condition)->group("s.order_id")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => $order_no]]);
		for ($s = 0; $s < count($num_list); $s++) {
			$condition["o.order_id"] = $num_list[$s]["order_id"];
			$list = db::name("ybmp_order")->alias("o")->join("ybmp_user u", "o.buyer_id=u.uid")->join("ybmp_order_share s", "s.order_id=o.order_id")->join("ybmp_order_goods g", "g.order_id=o.order_id")->join("ybmp_images i", "i.img_id=g.goods_images")->field("o.order_id,g.goods_name,i.img_cover img_path,o.order_no,o.out_trade_no,s.create_time order_time,g.price,g.num,u.nick_name,o.pay_money,o.order_status,s.parent_id_1 pid1,s.parent_id_2 pid2,s.parent_id_3 pid3,s.first_price fprice,s.second_price sprice,s.third_price tprice,s.rebate")->order("o.order_id desc")->where($condition)->select();
			for ($i = 0; $i < count($list); $i++) {
				$list[$i]["share_detail"][$i]["pid1"] = $list[$i]["pid1"];
				$list[$i]["share_detail"][$i]["pid2"] = $list[$i]["pid2"];
				$list[$i]["share_detail"][$i]["pid3"] = $list[$i]["pid3"];
				$list[$i]["share_detail"][$i]["fprice"] = $list[$i]["fprice"];
				$list[$i]["share_detail"][$i]["sprice"] = $list[$i]["sprice"];
				$list[$i]["share_detail"][$i]["tprice"] = $list[$i]["tprice"];
				$list[$i]["share_detail"] = $this->get_share_detail($list[$i]["share_detail"][$i]);
				unset($list[$i]["pid1"]);
				unset($list[$i]["pid2"]);
				unset($list[$i]["pid3"]);
				unset($list[$i]["fprice"]);
				unset($list[$i]["sprice"]);
				unset($list[$i]["tprice"]);
				$list[$i]["order_time"] = date("Y-m-d H:i:s", $list[$i]["order_time"]);
				$list[$i]["order_status"] = $status[strval($list[$i]["order_status"])];
			}
			$lists = $num_list[$s]["nums"];
			$num_list[$s] = $list[0];
			$a = array();
			if ($lists > 1) {
				$a = $num_list[$s];
				$a["goods_name"] = "-99";
				for ($j = 0; $j < count($list); $j++) {
					$a["goods__"][$j]["img_path"] = $list[$j]["img_path"];
					$a["goods__"][$j]["goods_name"] = $list[$j]["goods_name"];
					$a["goods__"][$j]["price"] = $list[$j]["price"];
					$a["goods__"][$j]["num"] = $list[$j]["num"];
				}
				$num_list[$s] = $a;
			}
		}
		return $num_list;
	}
	public function get_share_detail($arr)
	{
		$conid = $this->bus_id;
		$share = db::name("ybmp_user_share_setting")->field("first_name fname,second_name sname,third_name tname")->where("mch_id", $conid)->find();
		$i = 0;
		$res = array();
		if ($arr["pid1"] > 0) {
			$name = db::name("ybmp_user")->field("nick_name")->where("uid", $arr["pid1"])->find();
			$res[$i]["name"] = $name["nick_name"];
			$res[$i]["share"] = $share["fname"];
			$res[$i]["price"] = $arr["fprice"];
			$i++;
		}
		if ($arr["pid2"] > 0) {
			$name = db::name("ybmp_user")->field("nick_name")->where("uid", $arr["pid2"])->find();
			$res[$i]["name"] = $name["nick_name"];
			$res[$i]["share"] = $share["sname"];
			$res[$i]["price"] = $arr["sprice"];
			$i++;
		}
		if ($arr["pid3"] > 0) {
			$name = db::name("ybmp_user")->field("nick_name")->where("uid", $arr["pid3"])->find();
			$res[$i]["name"] = $name["nick_name"];
			$res[$i]["share"] = $share["tname"];
			$res[$i]["price"] = $arr["tprice"];
		}
		return $res;
	}
	public function get_child_share($pid, $chioce = "-1", $deep = 3)
	{
		$res = array();
		$old_id = $pid;
		for ($i = 0; $i < $deep; $i++) {
			$child = array();
			$condition = array();
			$condition["pid"] = ["in", $pid];
			$condition["mch_id"] = $this->bus_id;
			$child = db::name("ybmp_user")->field("nick_name,reg_time,total_price,pid,uid")->where($condition)->select();
			$res[$i + 1] = $child;
			$res[$i + 1]["cnum"] = count($child);
			$pids = db::name("ybmp_user")->field("uid")->where($condition)->select();
			$pid = '';
			for ($s = 0; $s < count($pids); $s++) {
				$pid = $pid . $pids[$s]["uid"] . ",";
			}
			$pid = substr($pid, 0, strlen($pid) - 1);
			if ($pid == 0) {
				break;
			}
		}
		if ($chioce >= 1 && $chioce != "-1") {
			unset($res[$chioce]["cnum"]);
			return $res[$chioce];
		}
		return $res;
	}
	public function screen_id($db_name, $id_arr, $id_name, $check_num, $check_name)
	{
		$res = array();
		is_array($id_arr) ? '' : ($id_arr = explode(",", $id_arr));
		for ($i = 0; $i < count($id_arr); $i++) {
			$verify = db::name($db_name)->field($check_name)->where($id_name, $id_arr[$i])->find();
			if ($verify[$check_name] == $check_num) {
				array_push($res, $id_arr[$i]);
			}
		}
		return implode(",", $res);
	}
}
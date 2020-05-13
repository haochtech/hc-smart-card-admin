<?php


namespace app\api\service;

use think\Db;
use think\Exception;
use think\log;
require_once BASE_ROOT . "core/application/common.php";
class OrderService
{
	private $o = "ybmp_order";
	private $og = "ybmp_order_goods";
	private $uc = "ybmp_user_coupon";
	private $s = "ybmp_goods_sku";
	private $g = "ybmp_goods";
	private $op = "ybmp_order_payment";
	private $i = "ybmp_images";
	private $dq = "ybmp_area";
	private $u = "ybmp_user";
	private $os = "ybmp_order_share";
	private $usm = "ybmp_user_share_money";
	public function createOrder($data, $staff_id)
	{
		Db::startTrans();
		try {
			$data_new = $data;
			unset($data_new["sku_id"]);
			$data_new["finish_time"] = 0;
			$data_new["refund_time"] = 0;
			$data_new["consign_time"] = 0;
			$data_new["seller_memo"] = '';
			$order_id = Db::name($this->o)->insertGetId($data_new);
			if ($data["coupon_id"] != 0) {
				$coupon_data = array("status" => 1, "use_time" => time());
				$coupon_info = Db::name($this->uc)->where("id", $data["coupon_id"])->where("rid", 0)->find();
				if ($coupon_info["get_count"] > 0 && $coupon_info["use_count"] + 1 <= $coupon_info["get_count"]) {
					Db::name($this->uc)->where("id", $data["coupon_id"])->where("coupon_id", "not in", ["0"])->setInc("use_count");
				}
				if ($coupon_info["use_count"] + 1 == $coupon_info["get_count"] && $coupon_info["get_count"] != 0) {
					Db::name($this->uc)->where("id", $data["coupon_id"])->update($coupon_data);
				} else {
					Db::name($this->uc)->where("id", $data["coupon_id"])->where("rid", 1)->update($coupon_data);
				}
			}
			$staff_money = 0;
			$sku_id = rtrim($data["sku_id"], ",");
			$sku = explode(",", $sku_id);
			$goods_num = 0;
			$share_goods_id = 0;
			if (count($sku) == 1) {
				$goods_num = 1;
			}
			$good_name_str = '';
			foreach ($sku as $value) {
				$value_data = explode(":", $value);
				$data_sku = Db::name($this->s)->where("sku_id", $value_data[0])->find();
				if ($data_sku["stock"] <= 0) {
					throw new Exception("库存不足");
				}
				$data_goods = Db::name($this->g)->where("goods_id", $data_sku["goods_id"])->find();
				if (count($sku) > 1) {
					$good_name_str .= mb_substr($data_goods["goods_name"], 0, 20, "utf-8") . "+";
				} else {
					$good_name_str = $data_goods["goods_name"] . "+";
				}
				if ($data_goods["user_share"] >= 0) {
					$staff_money = round(floatval($staff_money) + floatval($data_goods["user_share"]) * intval($value_data[1]), 2);
				} else {
					$ch1 = abs($data_goods["user_share"]) * $data_sku["price"];
					$staff_money = round(floatval($staff_money) + floatval($ch1) * intval($value_data[1]), 2);
				}
				if ($goods_num == 1) {
					$share_goods_id = $data_sku["goods_id"];
				}
				Db::name($this->g)->where("goods_id", $data_sku["goods_id"])->setInc("real_sales", $value_data[1]);
				Db::name($this->g)->where("goods_id", $data_sku["goods_id"])->setInc("sales", $value_data[1]);
				Db::name($this->g)->where("goods_id", $data_sku["goods_id"])->setDec("stock", $value_data[1]);
				Db::name($this->s)->where("sku_id", $value_data[0])->setDec("stock", $value_data[1]);
				if ($data_sku->images == 0) {
					$images = $data_goods["images"];
				} else {
					$images = $data_sku["images"];
				}
				$goods_data = ["order_id" => $order_id, "sku_id" => $value_data[0], "sku_name" => $data_sku["sku_name"], "goods_id" => $data_sku["goods_id"], "goods_name" => $data_goods["goods_name"], "price" => $value_data[1] * $data_sku["price"], "num" => $value_data[1], "goods_money" => $data_sku["price"], "goods_images" => $images, "buyer_id" => $data["buyer_id"]];
				Db::name($this->og)->insert($goods_data);
			}
			if (!empty($staff_money) && $staff_id != 0 && $staff_money != "0.00" && $staff_money != 0) {
				$staff_data = array("staff_id" => $staff_id, "order_id" => $order_id, "money" => $staff_money, "mch_id" => $data["mch_id"]);
				$count = Db::name("ybmp_order_staff")->where($staff_data)->count();
				if ($count == 0) {
					$staff_data["create_time"] = time();
					Db::name("ybmp_order_staff")->insert($staff_data);
				}
			}
			$pay_data = array("out_trade_no" => $data["out_trade_no"], "pay_type" => $data["pay_type"], "type_alis_id" => $order_id, "pay_body" => rtrim($good_name_str, "+"), "pay_detail" => "用户购买商品", "pay_money" => $data["pay_money"], "pay_status" => 0, "pay_time" => 0, "create_time" => time());
			Db::name($this->op)->insert($pay_data);
			$u = Db::name($this->u)->where("uid", $data["buyer_id"])->find();
			$pid = $u["pid"] ? $u["pid"] : 0;
			$pid_db = $this->is_distributor($pid);
			$pid1 = $pid;
			if ($pid_db != 1) {
				$pid1 = 0;
			}
			$setting = $this->get_shareSetting($data["mch_id"], $share_goods_id);
			$fx_data = array("user_id" => $data["buyer_id"], "order_id" => $order_id, "first_price" => 0, "second_price" => 0, "third_price" => 0, "parent_id_1" => $pid1, "parent_id_2" => 0, "parent_id_3" => 0, "is_del" => 1, "rebate" => 0, "mch_id" => $data["mch_id"], "create_time" => time());
			if ($setting && $setting["level"] != 0) {
				if ($setting["price_type"] == 0) {
					$fx_data["first_price"] = floatval($data["pay_money"]) * floatval($setting["first"]) / 100;
					$fx_data["second_price"] = floatval($data["pay_money"]) * floatval($setting["second"]) / 100;
					$fx_data["third_price"] = floatval($data["pay_money"]) * floatval($setting["third"]) / 100;
				} else {
					$fx_data["first_price"] = $setting["first"];
					$fx_data["second_price"] = $setting["second"];
					$fx_data["third_price"] = $setting["third"];
				}
				if ($u["is_distributor"] && $setting["is_rebate"] == 1) {
					$fx_data["rebate"] = $fx_data["first_price"];
				}
				if ($setting["level"] == 1) {
					if ($pid1 != 0 || $fx_data["rebate"] != 0) {
						Db::name($this->os)->insert($fx_data);
					}
				} elseif ($setting["level"] == 2) {
					$pid2 = $this->get_user_uid($pid);
					$pid_db2 = $this->is_distributor($pid2);
					if ($pid_db2 != 1) {
						$pid2 = 0;
					}
					if ($pid1 != 0 || $pid2 != 0 || $fx_data["rebate"] != 0) {
						$fx_data["parent_id_2"] = $pid2;
						Db::name($this->os)->insert($fx_data);
					}
				} elseif ($setting["level"] == 3) {
					$pid2 = $this->get_user_uid($pid);
					$pid_db2 = $this->is_distributor($pid2);
					$pid3 = $this->get_user_uid($pid2);
					$pid_db3 = $this->is_distributor($pid3);
					if ($pid_db2 != 1) {
						$pid2 = 0;
					}
					$fx_data["parent_id_2"] = $pid2;
					if ($pid_db3 != 1) {
						$pid3 = 0;
					}
					if ($pid1 != 0 || $pid2 != 0 || $pid3 != 0 || $fx_data["rebate"] != 0) {
						$fx_data["parent_id_3"] = $pid3;
						Db::name($this->os)->insert($fx_data);
					}
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			Log::write("生成订单失败 --(用户id：" . $data["buyer_id"] . ") 错误信息：" . $e->getMessage(), "order_error");
			return ["err_code" => 1, "msg" => $e->getMessage()];
		}
		return ["err_code" => 0, "info" => $order_id];
	}
	public function get_shareSetting($mch_id, $share_goods_id)
	{
		$info = Db::name("ybmp_user_share_setting")->where("mch_id", $mch_id)->find();
		if ($share_goods_id != 0) {
			$goods_share = Db::name("ybmp_goods_share_setting")->where(["mch_id" => $mch_id, "goods_id" => $share_goods_id, "status" => 1])->find();
			if ($goods_share) {
				$info["price_type"] = $goods_share["price_type"];
				$info["first"] = $goods_share["first"];
				$info["second"] = $goods_share["second"];
				$info["third"] = $goods_share["third"];
			}
		}
		return $info;
	}
	public function is_distributor($uid)
	{
		if ($uid == 0) {
			return 0;
		}
		$a = Db::name($this->u)->where(["uid" => $uid])->value("is_distributor");
		if ($a) {
			return $a;
		} else {
			return 0;
		}
	}
	public function get_user_uid($uid)
	{
		if ($uid == 0) {
			return 0;
		}
		$pid = Db::name($this->u)->where(["uid" => $uid])->value("pid");
		if ($pid) {
			return $pid;
		} else {
			return 0;
		}
	}
	public function orderList($data, $page = 1)
	{
		$order_list = null;
		$order_list = Db::name($this->o)->where($data)->where("is_deleted", 0)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$goods = Db::name($this->og)->where("order_id", $value["order_id"])->select();
			foreach ($goods as $k => $v) {
				$pic = Db::name($this->i)->where("img_id", $v["goods_images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					$goods[$k]["pic"] = $pic;
				}
			}
			$order_list[$key]["goods"] = $goods;
		}
		return $order_list;
	}
	public function getOrder($data)
	{
		$order_info = null;
		$order_info = Db::name($this->o)->where($data)->where("is_deleted", 0)->find();
		if (empty($order_info)) {
			return $order_info;
		}
		$order_info["sign_time"] = __TIME($order_info["sign_time"]);
		$order_info["pay_time"] = __TIME($order_info["pay_time"]);
		$order_info["consign_time"] = __TIME($order_info["consign_time"]);
		$order_info["create_time"] = __TIME($order_info["create_time"]);
		$order_info["mention_time"] = __TIME($order_info["mention_time"]);
		$res = Db::name($this->dq)->where("id", $order_info["receiver_area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$address["province"] = $pro["name"];
		$address["city"] = $city["name"];
		$address["district"] = $res["name"];
		$order_info["address"] = $address;
		$order_info["goods"] = Db::name($this->og)->where("order_id", $order_info["order_id"])->select();
		foreach ($order_info["goods"] as $k => $v) {
			$pic = Db::name($this->i)->where("img_id", $v["goods_images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$order_info["goods"][$k]["pic"] = $pic;
			}
		}
		return $order_info;
	}
	public function signOrder($data)
	{
		$rs = array("code" => 0, "info" => array());
		Db::startTrans();
		try {
			$new_data = array("order_status" => 3, "sign_time" => time());
			$info = Db::name($this->o)->where($data)->update($new_data);
			if (empty($info)) {
				throw new Exception("操作失败");
			}
			$us = Db::name($this->os)->where(["user_id" => $data["buyer_id"], "order_id" => $data["order_id"]])->find();
			if ($us) {
				$share_data = ["mch_id" => $data["mch_id"], "order_id" => $data["order_id"], "create_time" => time(), "is_del" => 1];
				if ($us["parent_id_1"] && $us["parent_id_1"] != 0) {
					$share_data["user_id"] = $us["parent_id_1"];
					$share_data["money"] = $us["first_price"];
					$share_data["source"] = 1;
					Db::name($this->usm)->insert($share_data);
					Db::name($this->u)->where("uid", $us["parent_id_1"])->setInc("total_price", $us["first_price"]);
					Db::name($this->u)->where("uid", $us["parent_id_1"])->setInc("price", $us["first_price"]);
				}
				if ($us["parent_id_2"] && $us["parent_id_2"] != 0) {
					$share_data["user_id"] = $us["parent_id_2"];
					$share_data["money"] = $us["second_price"];
					$share_data["source"] = 2;
					Db::name($this->usm)->insert($share_data);
					Db::name($this->u)->where("uid", $us["parent_id_2"])->setInc("total_price", $us["second_price"]);
					Db::name($this->u)->where("uid", $us["parent_id_2"])->setInc("price", $us["second_price"]);
				}
				if ($us["parent_id_3"] && $us["parent_id_3"] != 0) {
					$share_data["user_id"] = $us["parent_id_3"];
					$share_data["money"] = $us["third_price"];
					$share_data["source"] = 3;
					Db::name($this->usm)->insert($share_data);
					Db::name($this->u)->where("uid", $us["parent_id_3"])->setInc("total_price", $us["third_price"]);
					Db::name($this->u)->where("uid", $us["parent_id_3"])->setInc("price", $us["third_price"]);
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			$rs["code"] = 1;
			$rs["msg"] = $e->getMessage();
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function cancelOrder($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$order_goods_info = Db::name($this->og)->where("order_id", $data["order_id"])->select();
		Db::startTrans();
		try {
			$new_data = array("order_status" => -1);
			$res = Db::name($this->o)->where(["order_id" => $data["order_id"]])->update($new_data);
			$mch_id = Db::name($this->o)->where(["order_id" => $data["order_id"]])->value("mch_id");
			foreach ($order_goods_info as $key => $value) {
				Db::name($this->g)->where("goods_id", $value["goods_id"])->setInc("stock", $value["num"]);
				Db::name($this->s)->where("sku_id", $value["sku_id"])->setInc("stock", $value["num"]);
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return $e->getMessage();
		}
		return $res;
	}
	public function delOrder($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("is_deleted" => 1);
		$res = Db::name($this->o)->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
	public function refundOrder($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 4, "refund_time" => time());
		$ser = new OffwebService($info["mch_id"]);
		$r = $ser->sub_send($data["buyer_id"], "订单申请退款:" . $info["order_no"], "order_refund");
		$res = Db::name($this->o)->where(["order_id" => $data["order_id"]])->update($new_data);
		return $res;
	}
}
<?php


namespace app\api\service;

use think\Db;
use think\log;
use think\Exception;
require_once BASE_ROOT . "core/extend/Wxpay/WxPay.Api.php";
class PintuanService
{
	private $o = "ybmp_pt_orders";
	private $g = "ybmp_pt_goods";
	private $c = "ybmp_pt_category";
	private $a = "ybmp_pt_advert";
	private $i = "ybmp_images";
	private $dq = "ybmp_area";
	private $ad = "ybmp_user_address";
	public function get_ptIndex($data)
	{
		$data["enabled"] = 1;
		$rs["advert"] = Db::name($this->a)->where($data)->order("sort", "desc")->order("id", "desc")->select();
		$rs["cate"] = Db::name($this->c)->where($data)->order("sort", "desc")->order("id", "desc")->select();
		return $rs;
	}
	public function get_ptGoodsList($data, $page)
	{
		$data["isShow"] = 1;
		$time = time();
		$list = Db::name($this->g)->where($data)->page($page, 10)->order("sort", "desc")->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$list[$k]["img"] = $this->get_pic_path($list[$k]["img"]);
				$list[$k]["indexImg"] = $this->get_pic_path($list[$k]["indexImg"]);
				$pia_arr = $list[$k]["album"];
				if ($pia_arr) {
					$pia_arr = explode(",", $pia_arr);
					for ($i = 0; $i < count($pia_arr); $i++) {
						$pia_arr[$i] = $this->get_pic_path($pia_arr[$i]);
					}
				} else {
					$pia_arr = [];
				}
				$list[$k]["album"] = $pia_arr;
				$list[$k]["groupList"] = Db::name($this->o)->alias("o")->field("m.user_headimg")->join("ybmp_user m", "m.uid=o.uid")->where(["o.gid" => $v["id"], "o.endTime" => [">", $time], "o.pid" => 0, "o.isGroup" => 1, "o.isPay" => 1, "o.groupTime" => 0, "o.mch_id" => $data["mch_id"]])->order("o.endTime asc,o.id asc")->limit(2)->select();
			}
		}
		return $list;
	}
	public function get_ptGoodsDetail($data)
	{
		$data["isShow"] = 1;
		$goodsInfo = Db::name($this->g)->where(["id" => $data["id"], "mch_id" => $data["mch_id"], "isShow" => 1])->find();
		if (!$goodsInfo) {
			return null;
		}
		$goodsInfo["img"] = $this->get_pic_path($goodsInfo["img"]);
		$goodsInfo["indexImg"] = $this->get_pic_path($goodsInfo["indexImg"]);
		$pia_arr = $goodsInfo["album"];
		if ($pia_arr) {
			$pia_arr = explode(",", $pia_arr);
			for ($i = 0; $i < count($pia_arr); $i++) {
				$pia_arr[$i] = $this->get_pic_path($pia_arr[$i]);
			}
		} else {
			$pia_arr = [];
		}
		$goodsInfo["album"] = $pia_arr;
		$goodsInfo["intro"] = htmlspecialchars_decode($goodsInfo["intro"]);
		$time = time();
		$groupList = Db::name($this->o)->alias("o")->field("o.id as oid,o.endTime,m.nick_name,m.user_headimg")->join("ybmp_user m", "m.uid=o.uid")->where(["o.gid" => $data["id"], "o.pid" => 0, "o.isGroup" => 1, "o.isPay" => 1, "o.groupTime" => 0, "o.mch_id" => $data["mch_id"], "o.endTime" => [">", $time], "m.uid" => ["<>", $data["uid"]]])->limit(2)->select();
		if ($groupList) {
			foreach ($groupList as $key => $value) {
				$groupList[$key]["leftNum"] = Db::name($this->o)->where(["pid" => $value["id"]])->count();
				$groupList[$key]["leftTime"] = $value["endTime"] - $time;
			}
		}
		$goodsInfo["groupList"] = $groupList;
		$address = Db::name($this->ad)->field("consigner,phone,address,area")->where("uid", $data["uid"])->where("is_default ", 1)->find();
		if (empty($address)) {
			$address = Db::name($this->ad)->field("consigner,phone,address,area")->where("uid", $data["uid"])->order("create_time", "desc")->find();
		}
		$res = Db::name($this->dq)->where("id", $address["area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$aa["userName"] = $address["consigner"];
		$aa["telNumber"] = $address["phone"];
		$aa["address"] = $address["address"];
		$aa["province"] = $pro["name"];
		$aa["city"] = $city["name"];
		$aa["county"] = $res["name"];
		$goodsInfo["address"] = $aa;
		return $goodsInfo;
	}
	public function get_ptCreateOrder($data)
	{
		$time = time();
		if ($data["isGroup"] == 1) {
			$new_data["uid"] = $data["uid"];
			$new_data["gid"] = $data["gid"];
			$new_data["mch_id"] = $data["mch_id"];
			$new_data["endTime"] = [">", $time];
			$new_data["groupTime"] = 0;
			$new_data["pid"] = $data["pid"];
			$count = Db::name($this->o)->where($new_data)->count();
			if ($count > 0) {
				["err_code" => 1, "msg" => "您已参与该商品的团购，请前往我的拼团查看"];
			}
		}
		$limitTime = $data["limitTime"];
		unset($data["limitTime"]);
		$data["createTime"] = $time;
		$data["isPay"] = 0;
		$data["order_status"] = 1;
		if ($data["pid"] == 0) {
			$data["endTime"] = $time + $limitTime * 3600;
		} else {
			$data["endTime"] = Db::name($this->o)->where("id", $data["pid"])->value("endTime");
		}
		$pay_data = array("out_trade_no" => $data["orderNum"], "pay_type" => 1, "pay_body" => "用户支付", "pay_detail" => "用户购买商品", "pay_money" => $data["totalPrice"], "pay_status" => 0, "pay_time" => 0, "create_time" => time());
		Db::startTrans();
		try {
			$groupNum = Db::name($this->g)->where("id", $data["gid"])->value("groupNum");
			if ($data["pid"] != 0) {
				$count = Db::name($this->o)->where(["pid" => $data["pid"], "isPay" => 1])->count();
				if ($count + 1 >= $groupNum) {
					throw new Exception("创建订单失败");
				}
			}
			$id = Db::name($this->o)->insertGetId($data);
			if (empty($id)) {
				throw new Exception("创建订单失败");
			}
			$pay_data["type_alis_id"] = $id;
			Db::name("ybmp_pt_order_payment")->insert($pay_data);
			Db::name($this->g)->where("id", $data["gid"])->setInc("saleNum", $data["goodsNum"]);
			Db::name($this->g)->where("id", $data["gid"])->setDec("stock", $data["goodsNum"]);
			$ser = new OffwebService($data["mch_id"]);
			$r = $ser->sub_send($data["uid"], "拼团订单下单成功:" . $data["orderNum"], "pt_order_create");
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			Log::write("生成订单失败 --(用户id：" . $data["uid"] . ") 错误信息：" . $e->getMessage(), "pt_create_order_error");
			return ["err_code" => 1, "msg" => $e->getMessage()];
		}
		return ["err_code" => 0, "info" => $id];
	}
	public function get_ptOrderList($data, $page)
	{
		$list = Db::name($this->o)->where($data)->page($page, 10)->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$orderStatus = '';
				if ($v["order_status"] == 1) {
					$orderStatus = "待付款";
				} elseif ($v["order_status"] == 2) {
					if ($v["groupTime"] == 0 && $v["endTime"] < time()) {
						$orderStatus = "拼团失败";
					} else {
						$orderStatus = "待成团";
					}
				} elseif ($v["order_status"] == 3) {
					$orderStatus = "待发货";
				} elseif ($v["order_status"] == 4) {
					$orderStatus = "待收货";
				} elseif ($v["order_status"] == 5) {
					$orderStatus = "退款中";
				} else {
					if ($v["order_status"] == 6 && $v["isRefund"] == 0) {
						$orderStatus = "已完成";
					} else {
						if ($v["order_status"] == 6 && $v["isRefund"] == 1) {
							$orderStatus = "已完成(退款)";
						}
					}
				}
				$list[$k]["orderStatus"] = $orderStatus;
				$goods = Db::name($this->g)->where("id", $v["gid"])->field("name,img,unit,gprice")->find();
				if ($goods) {
					$goods["img"] = $this->get_pic_path($goods["img"]);
				} else {
					$goods = [];
				}
				$list[$k]["goods"] = $goods;
			}
		}
		return $list;
	}
	public function get_ptOrderDetail($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if ($info) {
			$orderStatus = '';
			if ($info["order_status"] == 1) {
				$orderStatus = "待付款";
			} elseif ($info["order_status"] == 2) {
				if ($info["groupTime"] == 0 && $info["endTime"] < time()) {
					$orderStatus = "拼团失败";
				} else {
					$orderStatus = "待成团";
				}
			} elseif ($info["order_status"] == -1) {
				$orderStatus = "已取消";
			} elseif ($info["order_status"] == 3) {
				$orderStatus = "待发货";
			} elseif ($info["order_status"] == 4) {
				$orderStatus = "待收货";
			} elseif ($info["order_status"] == 5) {
				$orderStatus = "退款中";
			} else {
				if ($info["order_status"] == 6 && $info["isRefund"] == 0) {
					$orderStatus = "已完成";
				} else {
					if ($info["order_status"] == 6 && $info["isRefund"] == 1) {
						if ($info["groupTime"] > 0) {
							$orderStatus = "已退款";
						} else {
							$orderStatus = "已退款(未成团)";
						}
					}
				}
			}
			$info["orderStatus"] = $orderStatus;
			$info["payTime"] = $this->get_time($info["payTime"]);
			$info["createTime"] = $this->get_time($info["createTime"]);
			$info["end_time"] = $this->get_time($info["endTime"]);
			$info["group_time"] = $this->get_time($info["groupTime"]);
			$info["completeTime"] = $this->get_time($info["completeTime"]);
			$info["deliveryTime"] = $this->get_time($info["deliveryTime"]);
			$goods = Db::name($this->g)->where("id", $info["gid"])->field("name,img,unit,gprice")->find();
			if ($goods) {
				$goods["img"] = $this->get_pic_path($goods["img"]);
			}
			$info["goods"] = $goods;
			if ($info["address"]) {
				$info["address"] = json_decode($info["address"], true);
			}
		}
		return $info;
	}
	public function get_ptGroupList($data, $page, $status)
	{
		$data["isPay"] = 1;
		$data["isGroup"] = 1;
		if ($status == 1) {
			$data["endTime"] = [">=", time()];
			$data["groupTime"] = 0;
		} elseif ($status == 2) {
			$data["groupTime"] = [">", 0];
		} elseif ($status == 3) {
			$data["endTime"] = ["<", time()];
			$data["groupTime"] = 0;
		}
		$list = Db::name($this->o)->where($data)->page($page, 10)->order("id", "desc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$orderStatus = '';
				if ($v["groupTime"] > 0) {
					$orderStatus = "已成团";
				} else {
					if ($v["endTime"] >= time()) {
						$orderStatus = "待成团";
					} else {
						$orderStatus = "拼团失败";
					}
				}
				$list[$k]["groupStatus"] = $orderStatus;
				$goods = Db::name($this->g)->where("id", $v["gid"])->field("name,img,unit,gprice,groupNum,limitTime,saleNum")->find();
				if ($goods) {
					$goods["img"] = $this->get_pic_path($goods["img"]);
				} else {
					$goods = [];
				}
				$list[$k]["goods"] = $goods;
			}
		}
		return $list;
	}
	public function get_ptGroupDetail($data, $uid)
	{
		$data["isPay"] = 1;
		$data["isGroup"] = 1;
		$info = Db::name($this->o)->where($data)->find();
		if ($info) {
			$orderStatus = '';
			if ($info["groupTime"] > 0) {
				$orderStatus = "拼团成功";
			} else {
				if ($info["endTime"] >= time()) {
					$orderStatus = "拼团中";
				} else {
					$orderStatus = "拼团失败";
				}
			}
			$info["groupStatus"] = $orderStatus;
			if ($info["pid"] == 0) {
				$map = "o.pid=" . $info["id"] . " or o.id=" . $info["id"];
			} else {
				$map = "o.pid=" . $info["pid"] . " or o.id=" . $info["pid"];
			}
			$info["groupMember"] = Db::name($this->o)->alias("o")->field("o.pid,o.id,m.uid,m.user_headimg")->join("ybmp_user m", "m.uid=o.uid")->where($map)->where(["o.isPay" => 1, "o.isGroup" => 1])->order("o.pid asc")->select();
			foreach ($info["groupMember"] as $val) {
				if ($val["uid"] == $uid) {
					$info["isSelf"] = 1;
				}
			}
			$info["leftNum"] = count($info["groupMember"]);
			$info["leftTime"] = $info["endTime"] - time();
			$info["payTime"] = $this->get_time($info["payTime"]);
			$info["createTime"] = $this->get_time($info["createTime"]);
			$info["end_time"] = $this->get_time($info["endTime"]);
			$info["group_time"] = $this->get_time($info["groupTime"]);
			$info["completeTime"] = $this->get_time($info["completeTime"]);
			$info["deliveryTime"] = $this->get_time($info["deliveryTime"]);
			$goods = Db::name($this->g)->where("id", $info["gid"])->field("id,name,img,unit,gprice,groupNum,limitTime,saleNum")->find();
			if ($goods) {
				$goods["img"] = $this->get_pic_path($goods["img"]);
				$address = Db::name($this->ad)->field("consigner,phone,address,area")->where("uid", $uid)->where("is_default ", 1)->find();
				if (empty($address)) {
					$address = Db::name($this->ad)->field("consigner,phone,address,area")->where("uid", $uid)->order("create_time", "desc")->find();
				}
				$res = Db::name($this->dq)->where("id", $address["area"])->find();
				$city = Db::name($this->dq)->where("id", $res["pid"])->find();
				$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
				$aa["userName"] = $address["consigner"];
				$aa["telNumber"] = $address["phone"];
				$aa["address"] = $address["address"];
				$aa["province"] = $pro["name"];
				$aa["city"] = $city["name"];
				$aa["county"] = $res["name"];
				$goods["address"] = $aa;
			}
			$info["goods"] = $goods;
			if ($info["address"]) {
				$info["address"] = json_decode($info["address"], true);
			}
		}
		return $info;
	}
	public function signOrder($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 6, "completeTime" => time());
		$res = Db::name($this->o)->where(["id" => $data["id"]])->update($new_data);
		return $res;
	}
	public function refundOrder($data)
	{
		$info = Db::name($this->o)->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 5);
		$res = Db::name($this->o)->where(["id" => $data["id"]])->update($new_data);
		$ser = new OffwebService($info["mch_id"]);
		$r = $ser->sub_send($data["uid"], "拼团订单申请退款:" . $info["orderNum"], "pt_order_refund");
		return $res;
	}
	public function orderPay($data)
	{
		$rs = array("code" => 0, "info" => array());
		$info = Db::name($this->o)->where(["id" => $data["id"], "order_status" => 1])->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "订单不存在";
			return $rs;
		}
		if ($info["isPay"] != 0) {
			$rs["code"] = 1;
			$rs["msg"] = "订单主题已改变";
			return $rs;
		}
		if ($info["isGroup"] == 1) {
			if ($info["isGroup"] > time()) {
				$rs["code"] = 1;
				$rs["msg"] = "团购已过期";
				return $rs;
			}
		}
		$groupNum = Db::name("ybmp_pt_goods")->where("id", $info["gid"])->value("groupNum");
		if ($info["pid"] != 0) {
			$count = Db::name("ybmp_pt_orders")->where(["pid" => $info["pid"], "isPay" => 1])->count();
			if ($count + 1 >= $groupNum) {
				$rs["code"] = 1;
				$rs["msg"] = "团购已满员";
				return $rs;
			}
		}
		$GLOBALS["mch_id"] = $data["mch_id"];
		$GLOBALS["key"] = "pintuan";
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("拼团商品");
		$input->SetOpenid($data["openid"]);
		$input->SetDetail("用户购买商品");
		$input->SetTotal_fee($info["totalPrice"] * 100);
		$input->SetOut_trade_no($info["orderNum"]);
		$input->SetTrade_type("JSAPI");
		$unifiedorder = \WxPayApi::unifiedOrder($input);
		if ($unifiedorder["return_code"] == "FAIL") {
			$rs["code"] = 1;
			$rs["msg"] = $unifiedorder["return_msg"];
			return $rs;
		}
		if ($unifiedorder["result_code"] == "FAIL") {
			$rs["code"] = 1;
			$rs["msg"] = $unifiedorder["err_code_des"];
			return $rs;
		}
		Db::name($this->o)->where("id", $data["id"])->update(["prepayId" => $unifiedorder["prepay_id"]]);
		$res = $this->weixinapp($unifiedorder);
		$rs["info"] = $res;
		return $rs;
	}
	private function weixinapp($unifiedorder)
	{
		$param = Db::name("ybmp_config")->where("key", "WXPAY")->where("mch_id", $GLOBALS["mch_id"])->where("is_use", 1)->value("value");
		$param = json_decode($param, true);
		$input = new \WxPayJsApiPay();
		$input->SetAppid($param["APP_ID"]);
		$input->SetTimeStamp('' . time() . '');
		$input->SetNonceStr($this->createNoncestr());
		$input->SetPackage("prepay_id=" . $unifiedorder["prepay_id"]);
		$input->SetSignType("MD5");
		$input->SetSign();
		return $input;
	}
	private function createNoncestr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function get_pic_path($id)
	{
		if ($id) {
			$img = Db::name($this->i)->where("img_id", $id)->value("img_cover");
			return $img;
		} else {
			return null;
		}
	}
	public function get_time($t)
	{
		if ($t) {
			return date("Y-m-d H:i:s", $t);
		} else {
			return null;
		}
	}
}
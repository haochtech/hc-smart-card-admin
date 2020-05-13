<?php


namespace app\api\service;

use app\common\model\ResOrderPayment;
use app\common\model\ResOrder;
use app\common\model\Goods;
use app\common\model\Images;
use app\common\model\ResBooked;
use app\common\model\ResService;
use app\common\model\ResComment;
use app\common\model\User;
use app\common\model\UserCoupon;
use app\common\model\ResOrderGoods;
use app\common\model\Resdesk;
use app\common\model\Business;
use think\Db;
use Exception;
class CateringService
{
	function __construct()
	{
		$this->UserOrder = new \app\common\model\ResOrder();
		$this->OrderGoods = new \app\common\model\ResOrderGoods();
	}
	public function VoiceCall($data)
	{
		$desk = new Resdesk();
		$where = ["id" => $data["desk_id"], "mch_id" => $data["mch_id"]];
		$a = $desk->where($where)->find();
		if (empty($a)) {
			return ["err_code" => 1, "msg" => "扫码异常，请重试"];
		}
		switch ($data["call_type"]) {
			case 1:
				$title = "催促上菜";
				$content = $a["name"] . "号桌催促上菜";
				break;
			case 2:
				$title = "呼叫服务员";
				$content = $a["name"] . "号桌呼叫服务员";
				break;
			case 3:
				$title = "加米饭";
				$content = $a["name"] . "号桌需要加米饭";
				break;
			case 4:
				$title = "加水";
				$content = $a["name"] . "号桌需要加水";
				break;
			default:
				return ["err_code" => 1, "msg" => "参数异常，请求失败"];
		}
		$url = "https://vip.ly100.wang/api/app/Aliyun/push?mch_id=" . $data["mch_id"] . "&title=" . $title . "&content=" . $content;
		file_get_contents($url);
		return "已通知服务员,请稍后";
	}
	public function createOrder($data)
	{
		Db::startTrans();
		try {
			$order = new ResOrder();
			$a = $order->data($data)->allowField(true)->save();
			if (empty($a)) {
				throw new \think\Exception("shibai");
			}
			if ($data["pay_type"] == 1) {
				$pay = new ResOrderPayment();
				$pay_data = array("out_trade_no" => $data["out_trade_no"], "type_alis_id" => $order->order_id, "pay_body" => "用户支付", "pay_detail" => "用户购买商品", "pay_money" => $data["pay_money"], "create_time" => time());
				$b = $pay->save($pay_data);
				if (empty($b)) {
					throw new Exception("shibai");
				}
			}
			if ($data["coupon_id"] != 0) {
				$UserCoupon = new UserCoupon();
				$coupon_data = array("status" => 1, "use_time" => time());
				$c = $UserCoupon->save($coupon_data, "id=" . $data["coupon_id"]);
				if (empty($c)) {
					throw new \think\Exception("shibai");
				}
			}
			$dish = json_decode($data["order_dish_json"], true);
			if (empty($dish)) {
				throw new \think\Exception("shibai");
			}
			$order_good = new Goods();
			$good = new ResOrderGoods();
			$time = time();
			foreach ($dish as $k => $v) {
				$order_good->where("goods_id", $k)->setInc("real_sales", $v);
				$info = $order_good->where("goods_id", $k)->find();
				if ($info) {
					$new_data = ["order_id" => $order->order_id, "goods_name" => $info["goods_name"], "price" => $info["price"], "mch_id" => $info["mch_id"], "user_id" => $data["user_id"], "img" => $info["images"], "goods_id" => $k, "number" => $v, "type" => 0, "create_time" => $time];
					$d = $good->insert($new_data);
					if (empty($d)) {
						throw new \think\Exception("shibai");
					}
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $order->order_id;
	}
	public function orderList($data, $page = 1)
	{
		$bus = new Business();
		$a = $bus->field("id,payment_method")->where("id", $data["mch_id"])->find();
		$info["pay_type"] = $a["payment_method"];
		$order = new ResOrder();
		$order_list = null;
		$order_list = $order->where($data)->where("is_deleted", 0)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		$info["info"] = $order_list;
		if (empty($order_list)) {
			return $info;
		}
		foreach ($order_list as $key => $value) {
			$Resdesk = new Resdesk();
			$desk = $Resdesk->where("id", $value->desk_id)->find();
			if ($desk) {
				$value->desk_name = $desk["name"];
			}
			$value->create_time = __TIME($value->create_time);
			$value->pay_time = __TIME($value->pay_time);
			$value->finish_time = __TIME($value->finish_time);
			$good = new ResOrderGoods();
			$a = $good->where("order_id", $value->order_id)->select();
			foreach ($a as $k => $v) {
				$imgas = new Images();
				$pic = $imgas->where("img_id", $v->img)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					foreach ($pic->toArray() as $p_k => $p_v) {
						$pic[$p_k] = __IMG($p_v);
					}
					$v["pic"] = $pic;
				}
			}
			$value->goods = $a;
		}
		return $info;
	}
	public function getOrder($data)
	{
		$order = new ResOrder();
		$order_info = null;
		$order_info = $order->where($data)->where("is_deleted", 0)->find();
		if (empty($order_info)) {
			return $order_info;
		}
		$Resdesk = new Resdesk();
		$desk = $Resdesk->where("id", $order_info["desk_id"])->find();
		if ($desk) {
			$order_info["desk_name"] = $desk["name"];
		}
		$order_info["pay_time"] = __TIME($order_info["pay_time"]);
		$order_info["finish_time"] = __TIME($order_info["finish_time"]);
		$order_info["create_time"] = __TIME($order_info["create_time"]);
		$order_info["refund_time"] = __TIME($order_info["refund_time"]);
		$order_info["refund_ok_time"] = __TIME($order_info["refund_ok_time"]);
		$good = new ResOrderGoods();
		$a = $good->where("order_id", $order_info["order_id"])->select();
		foreach ($a as $k => $v) {
			$imgas = new Images();
			$pic = $imgas->where("img_id", $v->img)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				foreach ($pic->toArray() as $p_k => $p_v) {
					$pic[$p_k] = __IMG($p_v);
				}
				$v["pic"] = $pic;
			}
		}
		$order_info["goods"] = $a;
		return $order_info;
	}
	public function signOrder($data)
	{
		$order = new ResOrder();
		$info = $order->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 3, "finish_time" => time());
		$res = $order->save($new_data, ["order_id" => $data["order_id"]]);
		return $res;
	}
	public function cancelOrder($data)
	{
		$order = new ResOrder();
		$info = $order->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => -1);
		$res = $order->save($new_data, ["order_id" => $data["order_id"]]);
		return $res;
	}
	public function delOrder($data)
	{
		$order = new ResOrder();
		$info = $order->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("is_deleted" => 1);
		$res = $order->save($new_data, ["order_id" => $data["order_id"]]);
		return $res;
	}
	public function refundOrder($data)
	{
		$order = new ResOrder();
		$info = $order->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 4, "refund_time" => time());
		$res = $order->save($new_data, ["order_id" => $data["order_id"]]);
		return $res;
	}
	public function Book($data)
	{
		$book = new ResBooked();
		$info = $book->save($data);
		return $info;
	}
	public function Booklist($data, $page)
	{
		$book = new ResBooked();
		$info = $book->where($data)->where("status", ["in", [1, 2]])->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($info)) {
			return $info;
		}
		foreach ($info as $key => $value) {
			$value->create_time = __TIME($value->create_time);
		}
		$rs = $info;
		return $rs;
	}
	public function cancelBook($data)
	{
		$book = new ResBooked();
		$info = $book->save($data, ["id" => $data["id"]]);
		return $info;
	}
	public function ShopService($data)
	{
		$book = new ResService();
		$info = $book->where($data)->find();
		if (isset($info["array_values"]) && $info["array_values"] != null) {
			return $info["array_values"];
		}
		return array();
	}
	public function WriteComment($data)
	{
		$comment = new ResComment();
		$info = $comment->save($data);
		return $info;
	}
	public function CommentList($data, $page)
	{
		$book = new ResComment();
		$info = $book->where($data)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (!empty($info)) {
			foreach ($info as $key => $value) {
				if ($value->array_pic != '') {
					$pic = explode(",", rtrim($value->array_pic, ","));
					for ($i = 0; $i < count($pic); $i++) {
						$pic[$i] = "https://" . $_SERVER["HTTP_HOST"] . "/api/" . $pic[$i];
					}
					$value->pic = $pic;
				}
				$value->create_time = date("Y-m-d", $value->create_time);
				$username = $this->getUserInfo($value->user_id);
				$value->username = $username["nick_name"];
				$value->user_headimg = $username["user_headimg"];
			}
			$rs["info"] = $info;
		} else {
			$rs["info"] = array();
		}
		$rs["count"] = $book->where($data)->count();
		$num = $book->where($data)->avg("fraction") * 2;
		$rs["sroce"] = round($num, 2);
		return json_encode($rs);
	}
	public function getUserInfo($uid)
	{
		$User = new User();
		$info = $User->where("uid", $uid)->find();
		return $info;
	}
	public function addOrder($data, $json)
	{
		$where["order_status"] = 1;
		$where["order_id"] = $data["order_id"];
		$where["user_id"] = $data["user_id"];
		$info = $this->UserOrder->where($where)->find();
		if (empty($info)) {
			return ["err_code" => 1, "msg" => "该订单不存在"];
		}
		$this->UserOrder->startTrans();
		try {
			$a = $this->UserOrder->where("order_id", $data["order_id"])->setInc("total", $data["total"]);
			if (empty($a)) {
				throw new Exception("订单创建失败001");
			}
			$b = $this->UserOrder->where("order_id", $data["order_id"])->setInc("add_price", $data["add_price"]);
			if (empty($b)) {
				throw new Exception("订单创建失败002");
			}
			$c = $this->UserOrder->where("order_id", $data["order_id"])->setInc("pay_money", $data["add_price"]);
			if (empty($c)) {
				throw new Exception("订单创建失败003");
			}
			$dish = json_decode($json, true);
			if (empty($dish)) {
				throw new Exception("菜单不能为空");
			}
			$time = time();
			$order_good = new Goods();
			foreach ($dish as $k => $v) {
				$info = $order_good->where("goods_id", $k)->find();
				if ($info) {
					$new_data = ["order_id" => $data["order_id"], "goods_name" => $info["goods_name"], "price" => $info["price"], "mch_id" => $info["mch_id"], "user_id" => $data["user_id"], "img" => $info["images"], "goods_id" => $k, "number" => $v, "type" => 1, "create_time" => $time];
					$d = $this->OrderGoods->insert($new_data);
					if (empty($d)) {
						throw new Exception("菜单写入失败");
					}
				}
			}
			$this->UserOrder->commit();
		} catch (Exception $e) {
			$this->UserOrder->rollback();
			return ["err_code" => 1, "msg" => $e->getMessage()];
		}
		return "加菜成功";
	}
	public function tuiOrder($data, $json)
	{
		$where["order_status"] = 1;
		$where["order_id"] = $data["order_id"];
		$where["user_id"] = $data["user_id"];
		$info = $this->UserOrder->where($where)->find();
		if (empty($info)) {
			return ["err_code" => 1, "msg" => "该订单不存在"];
		}
		$this->UserOrder->startTrans();
		try {
			$a = $this->UserOrder->where("order_id", $data["order_id"])->setDec("total", $data["total"]);
			if (empty($a)) {
				throw new Exception("订单创建失败001");
			}
			$b = $this->UserOrder->where("order_id", $data["order_id"])->setInc("reduce_price", $data["reduce_price"]);
			if (empty($b)) {
				throw new Exception("订单创建失败002");
			}
			$c = $this->UserOrder->where("order_id", $data["order_id"])->setDec("pay_money", $data["reduce_price"]);
			if (empty($c)) {
				throw new Exception("订单创建失败001");
			}
			$dish = json_decode($json, true);
			if (empty($dish)) {
				throw new Exception("菜单不能为空");
			}
			$time = time();
			$order_good = new Goods();
			foreach ($dish as $k => $v) {
				$info = $order_good->where("goods_id", $k)->find();
				if ($info) {
					$new_data = ["order_id" => $data["order_id"], "goods_name" => $info["goods_name"], "price" => $info["price"], "mch_id" => $info["mch_id"], "user_id" => $data["user_id"], "img" => $info["images"], "goods_id" => $k, "number" => $v, "type" => 2, "create_time" => $time];
					$d = $this->OrderGoods->insert($new_data);
					if (empty($d)) {
						throw new Exception("菜单写入失败");
					}
				}
			}
			$this->UserOrder->commit();
		} catch (Exception $e) {
			$this->UserOrder->rollback();
			return ["err_code" => 1, "msg" => $e->getMessage()];
		}
		return "退菜成功";
	}
	public function tuigoodslist($data)
	{
		$list = $this->OrderGoods->where($data)->where("type", "in", "0,1")->field("goods_id,goods_name,img,price,sum(number) as all_num")->group("goods_id")->select();
		foreach ($list as $k => $v) {
			$where = ["type" => 2, "goods_id" => $v->goods_id];
			$imgas = new Images();
			$pic = $imgas->where("img_id", $v->img)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				foreach ($pic->toArray() as $p_k => $p_v) {
					$pic[$p_k] = __IMG($p_v);
				}
				$v["pic"] = $pic["img_cover_mid"];
			}
			$a = $this->OrderGoods->where($data)->where($where)->field("goods_id,sum(number) as all_num")->group("goods_id")->find();
			if ($a) {
				$v->total = $v->all_num - $a["all_num"];
			} else {
				$v->total = $v->all_num;
			}
			$v->wri_total = $v->total;
		}
		return $list;
	}
}
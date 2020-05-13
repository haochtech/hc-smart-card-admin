<?php


namespace app\app\service;

use app\common\model\Business;
use app\common\model\Goods;
use app\common\model\Images;
use app\common\model\GoodsCate;
use app\common\model\ResBooked;
use app\common\model\ResOrder;
use app\common\model\ResComment;
use app\common\model\User;
use app\common\model\About;
use app\common\model\Activity;
use app\common\model\BusCoupon;
use app\common\model\Resdesk;
use app\common\model\ResOrderGoods;
use think\Db;
class CaterService
{
	public function count($mch_id)
	{
		$store = new Business();
		$info1 = $store->where("id", $mch_id)->find();
		$data["name"] = $info1["name"];
		$data["head_img"] = $info1["head_img"] ? IMG_VIP . $info1["head_img"] : "http://vip.ly100.wang/public/static/h-ui.admin/images/admin_icon05.png";
		$Comment = new ResComment();
		$data["comment_count"] = $Comment->where("mch_id", $mch_id)->where("reply", '')->count();
		$order = new ResOrder();
		$time = strtotime(date("Y-m-d", time()));
		$data["order_money_today"] = $order->where("mch_id", $mch_id)->where("pay_status", "2")->where("pay_time", ">", $time)->sum("pay_money");
		$data["order_money_all"] = $order->where("mch_id", $mch_id)->where("pay_status", "2")->sum("pay_money");
		$data["order_today_count"] = $order->where("mch_id", $mch_id)->where("pay_status", "2")->where("create_time", ">", $time)->count();
		$data["order_today_pay_count"] = $order->where("mch_id", $mch_id)->where("pay_status", "2")->count();
		$data["order_pay_count"] = $order->where("mch_id", $mch_id)->where("pay_status", "2")->count();
		$book = new ResBooked();
		$data["book_count"] = $book->where("mch_id", $mch_id)->where("booked_time", "> time", time())->count();
		$Activity = new Activity();
		$data["manjian_all_count"] = $Activity->where("mch_id", $mch_id)->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->count();
		$data["manjian_twoday_count"] = $Activity->where("mch_id", $mch_id)->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->where("end_time", "<", time() + 172800)->count();
		$BusCoupon = new BusCoupon();
		$data["coupon_all_count"] = $BusCoupon->where("mch_id", $data["mch_id"])->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->count();
		$data["coupon_twoday_count"] = $BusCoupon->where("mch_id", $data["mch_id"])->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->where("end_time", "<", time() + 172800)->count();
		$manjian_count = $Activity->where("mch_id", $mch_id)->count();
		$coupon_count = $BusCoupon->where("mch_id", $mch_id)->count();
		$data["acti_count"] = $coupon_count + $manjian_count;
		return $data;
	}
	public function cateGoods($mch_id, $order, $by)
	{
		$order_name = "sort";
		if ($order == 1) {
			$order_name = "real_sales";
		} elseif ($order == 2) {
			$order_name = "stock";
		} elseif ($order == 3) {
			$order_name = "state";
		}
		$cate = new GoodsCate();
		$cate_info = $cate->where("pid", 0)->where("mch_id", $mch_id)->select();
		foreach ($cate_info as $key => $value) {
			$value->cate_pic = __IMG($value->cate_pic);
			$goods = new Goods();
			$value["goods_list"] = $goods->field("goods_id,state,goods_name,price,introduction,images,real_sales,sales,min_buy,stock")->where("cate_id", $value->cate_id)->where("mch_id", $mch_id)->where("is_del", 0)->order($order_name, $by)->order("goods_id", "desc")->select();
			if (empty($value["goods_list"])) {
				continue;
			}
			foreach ($value["goods_list"] as $g_v) {
				$imgas = new Images();
				$pic = $imgas->where("img_id", $g_v->images)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					foreach ($pic->toArray() as $k => $v) {
						$pic[$k] = __IMG($v);
					}
					$g_v["pic"] = $pic;
				}
			}
		}
		return $cate_info;
	}
	public function Booklist($data, $page)
	{
		$book = new ResBooked();
		$info = $book->where($data)->where("booked_time", "> time", time())->order("create_time", "desc")->select();
		if (empty($info)) {
			return $info;
		}
		foreach ($info as $key => $value) {
			$value->create_time = __TIME($value->create_time);
			$username = $this->getUserInfo($value->user_id);
			$value->username = $username["nick_name"];
			$value->user_headimg = $username["user_headimg"];
		}
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
						$pic[$i] = "https://" . WEB_HOST . "/api/" . $pic[$i];
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
		$rs["sroce"] = $book->where($data)->avg("fraction") * 2;
		return json_encode($rs);
	}
	public function orderList($data, $page = 1)
	{
		$order = new ResOrder();
		$order_list = null;
		$order_list = $order->where($data)->where("is_deleted", 0)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$Resdesk = new Resdesk();
			$desk = $Resdesk->where("id", $value->desk_id)->find();
			if ($desk) {
				$value->desk_name = $desk["name"];
			}
			$value->userinfo = $this->getUserInfo($value->user_id);
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
					$v["pic"] = $pic["img_cover_mid"];
				}
			}
			$value->goods = $a;
		}
		return $order_list;
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
				$v["pic"] = $pic["img_cover_mid"];
			}
		}
		$order_info["goods"] = $a;
		return $order_info;
	}
	public function RefundOrder($data, $time)
	{
		$order = new ResOrder();
		$new_data = array("order_status" => 5, "refund_ok_time" => $time);
		$res = $order->where($data)->update($new_data);
		return $res;
	}
	public function getUserInfo($uid)
	{
		$User = new User();
		$info = $User->where("uid", $uid)->find();
		return $info;
	}
	public function StoreInfo($data)
	{
		$about = new About();
		$info = $about->where($data)->find();
		return $info;
	}
	public function StoreModify($data)
	{
		$about = new About();
		$count = $about->count($data["mch_id"]);
		if ($count == 0) {
			$info = $about->data($data)->allowField(true)->save();
		} else {
			unset($data["mch_id"]);
			$info = $about->where("mch_id", $data["mch_id"])->update($data);
		}
		return $info;
	}
	public function ReplyComment($data, $up_data)
	{
		$comment = new ResComment();
		$info = $comment->where($data)->update($up_data);
		return $info;
	}
	public function ModifyState($data, $new_state)
	{
		$Goods = new Goods();
		$info = $Goods->where($data)->update(["state" => $new_state]);
		return $info;
	}
	public function ManJian($mch_id)
	{
		$activity = new Activity();
		$info = $activity->where("mch_id", $mch_id)->select();
		foreach ($info as $act_k => $act_v) {
			$act_v->star_time = __TIME($act_v->star_time);
			$act_v->end_time = __TIME($act_v->end_time);
		}
		return $info;
	}
	public function BusCoupon($data, $page)
	{
		$BusCoupon = new BusCoupon();
		$info = $BusCoupon->where("mch_id", $data["mch_id"])->page($page, PAGE_NUM)->order("id", "desc")->select();
		foreach ($info as $act_k => $act_v) {
			$act_v->satisfy_money = intval($act_v->satisfy_money);
			$act_v->discount_money = intval($act_v->discount_money);
			$act_v->star_time = date("Y-m-d", $act_v->star_time);
			$act_v->end_time = date("Y-m-d", $act_v->end_time);
		}
		return $info;
	}
	public function AddManJian($data)
	{
		$activity = new Activity();
		if ($data["id"] == 0) {
			unset($data["id"]);
			$info = $activity->insert($data);
		} else {
			$info = $activity->where("id", $data["id"])->update($data);
		}
		return $info;
	}
	public function AddCoupon($data)
	{
		$activity = new BusCoupon();
		if ($data["id"] == 0) {
			unset($data["id"]);
			$info = $activity->insert($data);
		} else {
			$info = $activity->where("id", $data["id"])->update($data);
		}
		return $info;
	}
	public function TogetherCoupon($data)
	{
		$activity = new BusCoupon();
		$info = $activity->where("id", $data["id"])->update(["together" => $data["together"]]);
		return $info;
	}
	public function ModifyManjian($data, $new_state)
	{
		$Goods = new Activity();
		$info = $Goods->where($data)->update(["is_use" => $new_state]);
		return $info;
	}
	public function ModifyCoupon($data, $new_state)
	{
		$Goods = new BusCoupon();
		$info = $Goods->where($data)->update(["is_use" => $new_state]);
		return $info;
	}
	public function DelManjian($data)
	{
		$Goods = new Activity();
		$info = $Goods->where($data)->delete();
		return $info;
	}
	public function DelCoupon($data)
	{
		$Goods = new BusCoupon();
		$info = $Goods->where($data)->delete();
		return $info;
	}
}
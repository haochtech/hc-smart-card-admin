<?php


namespace app\app\service;

use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Images;
use app\common\model\Area;
use think\Db;
class OrderService
{
	public $order_status = array("待支付", "待发货", "已发货", "已签收", "待退款", "已退款", -1 => "已取消");
	public function orderList($data, $page = 1)
	{
		$order = new Order();
		$order_list = null;
		$order_list = $order->where($data)->where("is_deleted", 0)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$address = new Area();
			$address_info = $address->getCity($value->receiver_area);
			$value->province_name = $address_info["province"];
			$value->city_name = $address_info["city"];
			$value->district_name = $address_info["district"];
			$value->order_status_name = $this->order_status[$value->order_status];
			$order_good = new OrderGoods();
			$value["goods_list"] = $order_good->where("order_id", $value->order_id)->select();
			foreach ($value["goods_list"] as $k => $v) {
				$imgas = new Images();
				$pic = $imgas->where("img_id", $v->goods_images)->field("img_cover")->find();
				if ($pic) {
					$v["goods_picture"] = __IMG($pic->toArray()["img_cover"]);
				}
			}
		}
		return $order_list;
	}
	public function orderHistory($data, $date, $page = 1)
	{
		$start = strtotime($date);
		$end = strtotime($date . " 23:59:59");
		$order = new Order();
		$order_list = null;
		$order_list = $order->where($data)->where("is_deleted", 0)->whereTime("create_time", "between", [$start, $end])->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$address = new Area();
			$address_info = $address->getCity($value->receiver_area);
			$value->province_name = $address_info["province"];
			$value->city_name = $address_info["city"];
			$value->district_name = $address_info["district"];
			$value->order_status_name = $this->order_status[$value->order_status];
			$order_good = new OrderGoods();
			$value["goods_list"] = $order_good->where("order_id", $value->order_id)->select();
			foreach ($value["goods_list"] as $k => $v) {
				$imgas = new Images();
				$pic = $imgas->where("img_id", $v->goods_images)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					$v["goods_picture"] = __IMG($pic->toArray()["img_cover_big"]);
				}
			}
		}
		return $order_list;
	}
	public function orderSearch($data, $param, $page = 1)
	{
		$where = array("order_no", "receiver_mobile", "receiver_name");
		$order = new Order();
		$order_list = null;
		foreach ($where as $w_value) {
			$order_list = $order->where($data)->where($w_value, "like", "%" . $param . "%")->where("is_deleted", 0)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
			if (!empty($order_list)) {
				break;
			}
		}
		if (empty($order_list)) {
			return $order_list;
		}
		foreach ($order_list as $key => $value) {
			$address = new Area();
			$address_info = $address->getCity($value->receiver_area);
			$value->province_name = $address_info["province"];
			$value->city_name = $address_info["city"];
			$value->district_name = $address_info["district"];
			$value->order_status_name = $this->order_status[$value->order_status];
			$order_good = new OrderGoods();
			$value["goods_list"] = $order_good->where("order_id", $value->order_id)->select();
			foreach ($value["goods_list"] as $k => $v) {
				$imgas = new Images();
				$pic = $imgas->where("img_id", $v->goods_images)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					$v["goods_picture"] = __IMG($pic->toArray()["img_cover_big"]);
				}
			}
		}
		return $order_list;
	}
	public function orderDelivery($data)
	{
		$order = new Order();
		$info = $order->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$new_data = array("order_status" => 2, "consign_time" => time());
		$res = $order->save($new_data, ["order_id" => $data["order_id"]]);
		return $res;
	}
}
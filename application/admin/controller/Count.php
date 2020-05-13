<?php


namespace app\admin\controller;

use think\Db;
class Count extends Base
{
	public function OrderCount()
	{
		if (request()->isAjax() && request()->isPost()) {
			$star_time = request()->post("star_time", 0);
			$data = [];
			if ($star_time == 0) {
				$days = date("t");
				$firstday = date("Y-m", time());
				$order_month = Db::name("ybmp_order")->where("mch_id", $this->bus_id)->whereTime("create_time", "month")->count();
				$order_pay = Db::name("ybmp_order")->where("mch_id", $this->bus_id)->whereNotIn("order_status", 5)->whereTime("create_time", "month")->sum("order_money");
				$data[2] = $order_month;
				$data[3] = sprintf("%.2f", $order_pay);
			} else {
				$days = date("t", strtotime($star_time));
				$firstday = $star_time;
				$order_month = Db::name("ybmp_order")->where("mch_id", $this->bus_id)->whereTime("create_time", "between", [$firstday, $firstday . "-" . $days . " 23:59:59"])->count();
				$order_pay = Db::name("ybmp_order")->where("mch_id", $this->bus_id)->whereTime("create_time", "between", [$firstday, $firstday . "-" . $days . " 23:59:59"])->sum("order_money");
				$data[2] = $order_month;
				$data[3] = sprintf("%.2f", $order_pay);
			}
			for ($i = 1; $i <= $days; $i++) {
				$data[0][] = $i . "日";
				$data[1][] = Db::name("ybmp_order")->where("mch_id", $this->bus_id)->whereTime("create_time", "between", [$firstday . "-" . $i, $firstday . "-" . $i . " 23:59:59"])->count();
			}
			return $data;
		} else {
			$star_time = request()->param("star_time", '');
			$this->assign("star_time", $star_time);
			return view("count/order_count");
		}
	}
	public function UserCount()
	{
		if (request()->isAjax() && request()->isPost()) {
			$star_time = request()->post("star_time", 0);
			$data = [];
			if ($star_time == 0) {
				$days = date("t");
				$firstday = date("Y-m", time());
			} else {
				$days = date("t", strtotime($star_time));
				$firstday = $star_time;
			}
			for ($i = 1; $i <= $days; $i++) {
				$data[0][] = $i . "日";
				$data[1][] = Db::name("ybmp_user")->where("mch_id", $this->bus_id)->whereTime("reg_time", "between", [$firstday . "-" . $i, $firstday . "-" . $i . " 23:59:59"])->count();
			}
			return $data;
		} else {
			$star_time = request()->param("star_time", '');
			$this->assign("star_time", $star_time);
			return view("count/user_count");
		}
	}
}
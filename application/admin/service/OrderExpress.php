<?php


namespace app\admin\service;

use app\common\model\ExpressCompany;
use app\common\model\OrderGoods;
class OrderExpress extends Base
{
	function __construct()
	{
		parent::__construct();
	}
	public function delivey($order_id, $express_name, $shipping_type, $express_company_id, $express_no)
	{
		$user = new \app\common\model\Business();
		$user_name = $user->getInfo(["id" => $this->bus_id], "name");
		$order_express = new \app\common\model\OrderExpress();
		$order_express->startTrans();
		try {
			$express_company = new ExpressCompany();
			$express_company_info = $express_company->getInfo(["co_id" => $express_company_id], "company_name");
			$data_goods_delivery = array("order_id" => $order_id, "order_goods_id_array" => '', "express_name" => $express_name, "shipping_type" => $shipping_type, "express_company" => $express_company_info["company_name"], "express_company_id" => $express_company_id, "express_no" => $express_no, "shipping_time" => time(), "uid" => $this->bus_id, "user_name" => $user_name["name"]);
			$order_express->save($data_goods_delivery);
			$order_goods = new \app\admin\service\OrderGoods();
			$order_goods->orderGoodsDelivery($order_id, '');
			$order_express->commit();
			return 1;
		} catch (\Exception $e) {
			$order_express->rollback();
			return $e->getMessage();
		}
	}
}
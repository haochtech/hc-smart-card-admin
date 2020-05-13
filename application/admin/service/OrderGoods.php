<?php


namespace app\admin\service;

class OrderGoods extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->order_goods = new \app\common\model\OrderGoods();
	}
	public function orderGoodsDelivery($order_id, $order_goods_id_array)
	{
		$this->order_goods->startTrans();
		try {
			$data = array("shipping_status" => 1);
			$order_goods = new \app\common\model\OrderGoods();
			$retval = $order_goods->save($data, ["order_id" => $order_id]);
			$order = new OrderService();
			$order->orderDoDelivery($order_id);
			$this->order_goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->order_goods->rollback();
			return $e->getMessage();
		}
		return $retval;
	}
}
<?php


namespace app\admin\service;

class GoodsLabel extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->goods_label = new \app\common\model\GoodsLabel();
	}
	public function getGoodsGroupList($condition = '', $search_text = '', $order = '')
	{
		$list = $this->goods_label->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function addOrEditGoodsGroup($label_id, $label_name, $sort, $label_pic, $mch_id)
	{
		$data = array("label_name" => $label_name, "sort" => $sort, "label_pic" => $label_pic, "mch_id" => $mch_id);
		if ($label_id == 0) {
			$this->goods_label->save($data);
			$res = $this->goods_label->label_id;
		} else {
			$res = $this->goods_label->save($data, ["label_id" => $label_id]);
		}
		return $res;
	}
	public function getGoodsGroupDetail($label_id)
	{
		$info = $this->goods_label->get($label_id);
		return $info;
	}
}
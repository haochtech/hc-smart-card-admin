<?php


namespace app\admin\service;

class GoodsBrand extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->goods_brand = new \app\common\model\GoodsBrand();
	}
	public function getGoodsBrandList($condition = '', $search_text = '', $order = '')
	{
		$list = $this->goods_brand->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function getGoodsBrandAll($condition = '', $field = "*", $order = '')
	{
		$list = $this->goods_brand->getQuerys($condition, $field, $order);
		return $list;
	}
	public function addOrUpdateGoodsBrand($brand_id, $brand_name, $brand_initial, $brand_pic, $brand_recommend, $mch_id)
	{
		$data = array("brand_name" => $brand_name, "brand_initial" => $brand_initial, "brand_pic" => $brand_pic, "brand_recommend" => $brand_recommend, "sort" => 0, "create_time" => time(), "mch_id" => $mch_id);
		if ($brand_id == '') {
			$res = $this->goods_brand->save($data);
			return $this->goods_brand->brand_id;
		} else {
			$res = $this->goods_brand->save($data, ["brand_id" => $brand_id]);
			return $res;
		}
	}
	public function deleteGoodsBrand($brand_id_array)
	{
		$res = $this->goods_brand->destroy($brand_id_array);
		return $res;
	}
	public function getGoodsBrandInfo($brand_id, $field = "*")
	{
		$info = $this->goods_brand->getInfo(array("brand_id" => $brand_id), $field);
		return $info;
	}
}
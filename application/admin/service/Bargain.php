<?php


namespace app\admin\service;

class Bargain extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->bargain = new \app\common\model\Bargain();
	}
	public function addBargain($bargain_name, $bargain_picture, $bargain_inventory, $original_price, $lowest_price, $star_time, $end_time, $consumption_time, $activity_rules, $completed_number, $mch_id, $arr_str_img, $class_id)
	{
		$data = [];
		$data["bargain_name"] = $bargain_name;
		$data["bargain_picture"] = $bargain_picture;
		$data["bargain_inventory"] = $bargain_inventory;
		$data["original_price"] = $original_price;
		$data["lowest_price"] = $lowest_price;
		$data["star_time"] = strtotime($star_time);
		$data["end_time"] = strtotime($end_time);
		$data["consumption_time"] = strtotime($consumption_time);
		$data["activity_rules"] = $activity_rules;
		$data["completed_number"] = $completed_number;
		$data["create_time"] = time();
		$data["mch_id"] = $mch_id;
		$data["img_id_array"] = $arr_str_img;
		$data["class_id"] = $class_id;
		$res = $this->bargain->save($data);
		return $res;
	}
	public function editBargain($id, $bargain_name, $bargain_picture, $bargain_inventory, $original_price, $lowest_price, $star_time, $end_time, $consumption_time, $activity_rules, $completed_number, $mch_id, $arr_str_img, $class_id)
	{
		$data = [];
		$data["bargain_name"] = $bargain_name;
		$data["bargain_picture"] = $bargain_picture;
		$data["bargain_inventory"] = $bargain_inventory;
		$data["original_price"] = $original_price;
		$data["lowest_price"] = $lowest_price;
		$data["star_time"] = strtotime($star_time);
		$data["end_time"] = strtotime($end_time);
		$data["consumption_time"] = strtotime($consumption_time);
		$data["activity_rules"] = $activity_rules;
		$data["completed_number"] = $completed_number;
		$data["mch_id"] = $mch_id;
		$data["img_id_array"] = $arr_str_img;
		$data["class_id"] = $class_id;
		$res = $this->bargain->save($data, ["id" => $id]);
		return $res;
	}
	public function getBargainInfo($id)
	{
		$res = $this->bargain->getInfo(["id" => $id]);
		$res["consumption_time"] = date("Y-m-d H:i:s", $res["consumption_time"]);
		$res["star_time"] = date("Y-m-d H:i:s", $res["star_time"]);
		$res["end_time"] = date("Y-m-d H:i:s", $res["end_time"]);
		return $res;
	}
}
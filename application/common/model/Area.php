<?php


namespace app\common\model;

class Area extends Base
{
	protected $name = "ybmp_area";
	public function getCity($area_id)
	{
		$rs = array();
		$res = Area::get($area_id);
		$city = Area::get($res["pid"]);
		$pro = Area::get($city["pid"]);
		$rs["province"] = $pro["name"];
		$rs["city"] = $city["name"];
		$rs["district"] = $res["name"];
		return $rs;
	}
}
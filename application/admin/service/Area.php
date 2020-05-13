<?php


namespace app\admin\service;

class Area extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->area = new \app\common\model\Area();
	}
	public function getAddress($province)
	{
		$county_info = $this->area->getInfo(array("id" => $province), "*");
		$city_info = $this->area->getInfo(array("id" => $county_info["pid"]), "*");
		$province_info = $this->area->getInfo(array("id" => $city_info["pid"]), "*");
		return $order_detail = $province_info["name"] . "&nbsp;" . $city_info["name"] . "&nbsp;" . $county_info["name"];
	}
	public function getAreaList()
	{
		$area = new \app\common\model\Area();
		$list = $area->getQuerys("pid=1", "id,name", '');
		return $list;
	}
	public function getCityList($province_id)
	{
		$area = new \app\common\model\Area();
		$list = $area->getQuerys("pid=" . $province_id, "id,name", '');
		return $list;
	}
	public function getDistrictList($city_id)
	{
		$area = new \app\common\model\Area();
		$list = $area->getQuerys("pid=" . $city_id, "id,name", '');
		return $list;
	}
	public function getProvince($existing_address_list)
	{
		$area = new \app\common\model\Area();
		$select_district_id_array = [];
		if (!empty($existing_address_list)) {
			$select_district_id_array = $existing_address_list["province_id_array"];
		}
		$list = $area->getQuerys("pid=1", "id,name", '');
		foreach ($list as $k_province => $v_province) {
			$deal_array = $this->dealProvinceCityData($v_province["id"], $existing_address_list["province_id_array"]);
			$is_disabled = $deal_array["is_disabled"];
			$list[$k_province]["is_disabled"] = $is_disabled;
		}
		return $list;
	}
	private function dealProvinceCityData($province_id, $province_id_array)
	{
		$is_set = in_array($province_id, $province_id_array);
		if ($is_set) {
			$is_disabled = 1;
		} else {
			$is_disabled = 0;
		}
		return array("is_disabled" => $is_disabled);
	}
	public function getAddressListById($province_id_arr, $city_id_arr)
	{
		$province_condition = array("id" => array("in", $province_id_arr));
		$city_condition = array("id" => array("in", $city_id_arr));
		$province_list = $this->area->getQuerys($province_condition, "id,name", "id asc");
		$city_list = $this->area->getQuerys($city_condition, "id,name,pid", "id asc");
		foreach ($province_list as $k => $v) {
			$list["province_list"][$k] = $v;
			$children_list = array();
			foreach ($city_list as $city_k => $city_v) {
				if ($v["id"] == $city_v["pid"]) {
					$children_list[$city_k] = $city_v;
				}
			}
			$list["province_list"][$k]["city_list"] = $children_list;
		}
		return $list;
	}
}
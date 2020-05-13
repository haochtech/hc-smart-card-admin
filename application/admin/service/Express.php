<?php


namespace app\admin\service;

use app\common\model\Area;
use app\common\model\ExpressCompany;
use app\common\model\ExpressShipping;
use think\Db;
class Express extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->express = new ExpressCompany();
	}
	public function addExpressCompany($company_name, $express_logo, $express_no, $is_enabled, $phone, $is_default, $mch_id, $code)
	{
		$ns_express_company = new ExpressCompany();
		$ns_express_company->startTrans();
		try {
			if ($is_default == 1) {
				$this->defaultExpressCompany($mch_id);
			}
			$data = array("company_name" => $company_name, "express_logo" => $express_logo, "express_no" => $express_no, "is_enabled" => $is_enabled, "phone" => $phone, "code" => $code, "is_default" => $is_default, "create_time" => time(), "mch_id" => $mch_id);
			$ns_express_company->save($data);
			$ns_express_company->commit();
			return $ns_express_company->co_id;
		} catch (\Exception $e) {
			$ns_express_company->rollback();
			return $e->getCode();
		}
	}
	public function updateExpressCompany($co_id, $company_name, $express_logo, $express_no, $is_enabled, $phone, $is_default, $mch_id, $code)
	{
		$ns_express_company = new ExpressCompany();
		if ($is_default == 1) {
			$this->defaultExpressCompany($co_id);
		}
		$data = array("company_name" => $company_name, "express_logo" => $express_logo, "express_no" => $express_no, "is_enabled" => $is_enabled, "phone" => $phone, "code" => $code, "is_default" => $is_default, "mch_id" => $mch_id);
		$res = $ns_express_company->save($data, ["co_id" => $co_id, "mch_id" => $mch_id]);
		return $res;
	}
	public function expressCompanyQuery($where = '', $field = "*")
	{
		$ns_express_company = new ExpressCompany();
		return $ns_express_company->where($where)->field($field)->select();
	}
	public function expressCompanyDetail($co_id)
	{
		$ns_express_company = new ExpressCompany();
		return $ns_express_company->get($co_id);
	}
	public function defaultExpressCompany($co_id)
	{
		Db::name("ybmp_express_company")->whereNotIn("co_id", $co_id)->update(["is_default" => 0]);
	}
	public function getShippingFeeList($condition = '', $field = "*", $order = '')
	{
		$ns_order_shipping_fee = new ExpressShipping();
		$list = $ns_order_shipping_fee->getQuerys($condition, $field, $order);
		foreach ($list as $k => $v) {
			$address = new \app\admin\service\Area();
			$list[$k]["address_list"] = $address->getAddressListById($v["province_id_array"], $v["city_id_array"]);
		}
		return $list;
	}
	public function getExpressCompanyList($condition, $search_text, $order = '')
	{
		$ns_express_company = new ExpressCompany();
		$list = $ns_express_company->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function getExpressCompany($condition, $field = "*", $order)
	{
		$ns_express_company = new ExpressCompany();
		$list = $ns_express_company->getQuerys($condition, $field, $order);
		return $list;
	}
	public function expressCompanyDelete($co_id)
	{
		$ns_express_company = new ExpressCompany();
		$conditon = array("co_id" => array("in", $co_id));
		$ns_express_company_return = $ns_express_company->destroy($conditon);
		if ($ns_express_company_return > 0) {
			return 1;
		} else {
			return -1;
		}
	}
	public function addShippingFee($co_id, $is_default, $shipping_fee_name, $province_id_array, $city_id_array, $district_id_array, $weight_is_use, $weight_snum, $weight_sprice, $weight_xnum, $weight_xprice, $volume_is_use, $volume_snum, $volume_sprice, $volume_xnum, $volume_xprice, $bynum_is_use, $bynum_snum, $bynum_sprice, $bynum_xnum, $bynum_xprice)
	{
		$order_shipping_fee = new ExpressShipping();
		$order_shipping_fee->startTrans();
		try {
			$data = array("shipping_fee_name" => $shipping_fee_name, "co_id" => $co_id, "is_default" => $is_default, "province_id_array" => $province_id_array, "city_id_array" => $city_id_array, "district_id_array" => $district_id_array, "weight_is_use" => $weight_is_use, "weight_snum" => $weight_snum, "weight_xnum" => $weight_xnum, "weight_sprice" => $weight_sprice, "weight_xprice" => $weight_xprice, "volume_is_use" => $volume_is_use, "volume_snum" => $volume_snum, "volume_sprice" => $volume_sprice, "volume_xnum" => $volume_xnum, "volume_xprice" => $volume_xprice, "bynum_is_use" => $bynum_is_use, "bynum_snum" => $bynum_snum, "bynum_sprice" => $bynum_sprice, "bynum_xnum" => $bynum_xnum, "bynum_xprice" => $bynum_xprice, "create_time" => time());
			$order_shipping_fee->save($data);
			$order_shipping_fee->commit();
			return 1;
		} catch (\Exception $e) {
			$order_shipping_fee->rollback();
			return $e->getMessage();
		}
		return -1;
	}
	public function updateShippingFee($shipping_fee_id, $is_default, $shipping_fee_name, $province_id_array, $city_id_array, $district_id_array, $weight_is_use, $weight_snum, $weight_sprice, $weight_xnum, $weight_xprice, $volume_is_use, $volume_snum, $volume_sprice, $volume_xnum, $volume_xprice, $bynum_is_use, $bynum_snum, $bynum_sprice, $bynum_xnum, $bynum_xprice)
	{
		$order_shipping_fee = new ExpressShipping();
		$order_shipping_fee->startTrans();
		try {
			$data = array("shipping_fee_name" => $shipping_fee_name, "is_default" => $is_default, "province_id_array" => $province_id_array, "city_id_array" => $city_id_array, "district_id_array" => $district_id_array, "weight_is_use" => $weight_is_use, "weight_snum" => $weight_snum, "weight_xnum" => $weight_xnum, "weight_sprice" => $weight_sprice, "weight_xprice" => $weight_xprice, "volume_is_use" => $volume_is_use, "volume_snum" => $volume_snum, "volume_sprice" => $volume_sprice, "volume_xnum" => $volume_xnum, "volume_xprice" => $volume_xprice, "bynum_is_use" => $bynum_is_use, "bynum_snum" => $bynum_snum, "bynum_sprice" => $bynum_sprice, "bynum_xnum" => $bynum_xnum, "bynum_xprice" => $bynum_xprice, "update_time" => time());
			$order_shipping_fee->save($data, ["shipping_fee_id" => $shipping_fee_id]);
			$order_shipping_fee->commit();
			return 1;
		} catch (\Exception $e) {
			$order_shipping_fee->rollback();
			return $e->getMessage();
		}
		return -1;
	}
	public function isHasExpressCompanyDefaultTemplate($co_id)
	{
		$ns_order_shipping_fee = new ExpressShipping();
		$list = $ns_order_shipping_fee->getQuerys(["co_id" => $co_id], "is_default", '');
		$is_default = 1;
		foreach ($list as $v) {
			if ($v["is_default"]) {
				$is_default = 0;
				break;
			}
		}
		return $is_default;
	}
	public function shippingFeeDetail($shipping_fee_id)
	{
		$order_shipping_fee = new ExpressShipping();
		$order_shipping_fee_info = $order_shipping_fee->get($shipping_fee_id);
		$address = new \app\admin\service\Area();
		$area = $address->getAreaList();
		$address_name = '';
		$province_array = explode(",", $order_shipping_fee_info["province_id_array"]);
		foreach ($province_array as $e) {
			foreach ($area as $p) {
				if ($e == $p["id"]) {
					$address_name = $address_name . $p["name"] . ",";
				}
			}
		}
		$address_name = substr($address_name, 0, strlen($address_name) - 1);
		$order_shipping_fee_info["address_name"] = $address_name;
		return $order_shipping_fee_info;
	}
	public function getExpressCompanyProvincesAndCitiesById($co_id, $current_province_id_array, $current_city_id_array, $current_district_id_array)
	{
		$curr_province_id_array = [];
		if (!empty($current_province_id_array)) {
			if (!strstr($current_province_id_array, ",")) {
				array_push($curr_province_id_array, $current_province_id_array);
			} else {
				$curr_province_id_array = explode(",", $current_province_id_array);
			}
		}
		$ns_order_shipping_fee = new ExpressShipping();
		$list = $ns_order_shipping_fee->getQuerys(["co_id" => $co_id, "is_default" => 0], "province_id_array,city_id_array,district_id_array", '');
		$province_id_array = [];
		$res_list["province_id_array"] = [];
		foreach ($list as $k => $v) {
			if (!strstr($v["province_id_array"], ",")) {
				array_push($province_id_array, $v["province_id_array"]);
			} else {
				$temp_province_array = explode(",", $v["province_id_array"]);
				foreach ($temp_province_array as $temp_province_id) {
					array_push($province_id_array, $temp_province_id);
				}
			}
		}
		if (count($province_id_array)) {
			foreach ($province_id_array as $province_id) {
				$flag = true;
				foreach ($curr_province_id_array as $temp_province_id) {
					if ($province_id == $temp_province_id) {
						$flag = false;
					}
				}
				if ($flag) {
					array_push($res_list["province_id_array"], $province_id);
				}
			}
		}
		return $res_list;
	}
	public function shippingFeeDelete($shipping_fee_id)
	{
		$order_shipping_fee = new ExpressShipping();
		$condition = array("shipping_fee_id" => array(array("in", $shipping_fee_id)));
		$order_shipping_return = $order_shipping_fee->destroy($condition);
		if ($order_shipping_return > 0) {
			return 1;
		} else {
			return -1;
		}
	}
}
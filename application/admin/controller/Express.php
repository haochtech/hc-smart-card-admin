<?php


namespace app\admin\controller;

use app\admin\service\Area;
use think\Db;
class Express extends Base
{
	public function express_list()
	{
		$search_text = request()->param("search_text", '');
		$expressCompany = new \app\admin\service\Express();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["company_name"] = array("like", "%{$search_text}%");
		$retval = $expressCompany->getExpressCompanyList($condition, '', "create_time");
		$page = $retval->render();
		$this->assign("search_text", $search_text);
		$this->assign("retval", $retval);
		$this->assign("page", $page);
		return view("express_list");
	}
	public function express_add()
	{
		$expressCompany = new \app\admin\service\Express();
		if (request()->isAjax() && request()->isPost()) {
			$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
			$company_name = request()->post("company_name", '');
			$express_logo = request()->post("express_logo", '');
			$express_no = request()->post("express_no", '');
			$code = request()->post("code", '');
			$is_enabled = request()->post("is_enabled", '');
			$phone = request()->post("phone", '');
			$is_default = request()->post("is_default", "0");
			$retval = $expressCompany->addExpressCompany($company_name, $express_logo, $express_no, $is_enabled, $phone, $is_default, $mch_id, $code);
			return AjaxReturn($retval);
		}
		return view("express/express_add");
	}
	public function freightTemplateList()
	{
		$co_id = request()->get("co_id", 0);
		if ($co_id > 0) {
			$condition["co_id"] = $co_id;
		}
		$shippingfee_list = new \app\admin\service\Express();
		$express_list_pagequery = $shippingfee_list->getShippingFeeList($condition, "*", "is_default desc,create_time desc");
		$this->assign("co_id", $co_id);
		$this->assign("express_list_pagequery", $express_list_pagequery);
		return view("express/express_fee");
	}
	public function expressDelete()
	{
		if (request()->isAjax() && request()->isPost()) {
			$expressCompany = new \app\admin\service\Express();
			$co_id = request()->post("co_id", '');
			$retval = $expressCompany->expressCompanyDelete($co_id);
			return AjaxReturn($retval);
		}
	}
	public function express_edit()
	{
		$expressCompany = new \app\admin\service\Express();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$co_id = request()->post("co_id", '');
			$company_name = request()->post("company_name", '');
			$express_logo = request()->post("express_logo", '');
			$express_no = request()->post("express_no", '');
			$is_enabled = request()->post("is_enabled", '');
			$code = request()->post("code", '');
			$phone = request()->post("phone", '');
			$is_default = request()->post("is_default", "0");
			$retval = $expressCompany->updateExpressCompany($co_id, $company_name, $express_logo, $express_no, $is_enabled, $phone, $is_default, $mch_id, $code);
			return AjaxReturn($retval);
		}
		$co_id = request()->get("co_id", '');
		$expressCompanyinfo = $expressCompany->expressCompanyDetail($co_id);
		$this->assign("expressCompany", $expressCompanyinfo);
		return view("express/express_edit");
	}
	public function freightTemplateEdit()
	{
		$express = new \app\admin\service\Express();
		$address = new Area();
		if (request()->isAjax() && request()->isPost()) {
			$retval = -1;
			$data = request()->post("data", '');
			$json_data = json_decode($data, true);
			$shipping_fee_id = $json_data["shipping_fee_id"];
			$co_id = $json_data["co_id"];
			$is_default = $json_data["is_default"];
			$shipping_fee_name = $json_data["shipping_fee_name"];
			$province_id_array = $json_data["province_id_array"];
			$city_id_array = $json_data["city_id_array"];
			$district_id_array = $json_data["district_id_array"];
			$weight_is_use = $json_data["weight_is_use"];
			$weight_snum = $json_data["weight_snum"];
			$weight_sprice = $json_data["weight_sprice"];
			$weight_xnum = $json_data["weight_xnum"];
			$weight_xprice = $json_data["weight_xprice"];
			$volume_is_use = $json_data["volume_is_use"];
			$volume_snum = $json_data["volume_snum"];
			$volume_sprice = $json_data["volume_sprice"];
			$volume_xnum = $json_data["volume_xnum"];
			$volume_xprice = $json_data["volume_xprice"];
			$bynum_is_use = $json_data["bynum_is_use"];
			$bynum_snum = $json_data["bynum_snum"];
			$bynum_sprice = $json_data["bynum_sprice"];
			$bynum_xnum = $json_data["bynum_xnum"];
			$bynum_xprice = $json_data["bynum_xprice"];
			if ($shipping_fee_id) {
				$retval = $express->updateShippingFee($shipping_fee_id, $is_default, $shipping_fee_name, $province_id_array, $city_id_array, $district_id_array, $weight_is_use, $weight_snum, $weight_sprice, $weight_xnum, $weight_xprice, $volume_is_use, $volume_snum, $volume_sprice, $volume_xnum, $volume_xprice, $bynum_is_use, $bynum_snum, $bynum_sprice, $bynum_xnum, $bynum_xprice);
			} else {
				$retval = $express->addShippingFee($co_id, $is_default, $shipping_fee_name, $province_id_array, $city_id_array, $district_id_array, $weight_is_use, $weight_snum, $weight_sprice, $weight_xnum, $weight_xprice, $volume_is_use, $volume_snum, $volume_sprice, $volume_xnum, $volume_xprice, $bynum_is_use, $bynum_snum, $bynum_sprice, $bynum_xnum, $bynum_xprice);
			}
			return AjaxReturn($retval);
		} else {
			$co_id = request()->get("co_id", 0);
			$shipping_fee_id = request()->get("shipping_fee_id", 0);
			$this->assign("co_id", $co_id);
			$this->assign("shipping_fee_id", $shipping_fee_id);
			$current_province_id_array = '';
			$is_default = $express->isHasExpressCompanyDefaultTemplate($co_id);
			if ($shipping_fee_id) {
				$shipping_fee_detail = $express->shippingFeeDetail($shipping_fee_id);
				if ($shipping_fee_detail["is_default"]) {
					$is_default = $shipping_fee_detail["is_default"];
				}
				$current_province_id_array = $shipping_fee_detail["province_id_array"];
				$this->assign("shipping_fee_detail", $shipping_fee_detail);
			}
			$this->assign("is_default", $is_default);
			$existing_address_list = $express->getExpressCompanyProvincesAndCitiesById($co_id, $current_province_id_array, $current_city_id_array, $current_district_id_array);
			$address_list = $address->getProvince($existing_address_list);
			$this->assign("address_list", $address_list);
			return view("express/express_fee_add");
		}
	}
	public function freightTemplateDelete()
	{
		$shipping_fee_id = request()->post("shipping_fee_id", '');
		$express = new \app\admin\service\Express();
		$retval = $express->shippingFeeDelete($shipping_fee_id);
		return AjaxReturn($retval);
	}
}
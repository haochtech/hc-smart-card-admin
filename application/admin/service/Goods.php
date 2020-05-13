<?php


namespace app\admin\service;

use app\common\model\GoodsAttr;
use app\common\model\GoodsAttrDel;
use app\common\model\GoodsAttrModule;
use app\common\model\GoodsAttrValue;
use app\common\model\GoodsDeleted;
use app\common\model\GoodsSku;
use app\common\model\GoodsSkuDeleted;
use app\common\model\GoodsSpec;
use app\common\model\GoodsSpecFormat;
use app\common\model\GoodsSpecValue;
use app\common\model\GoodsSupplier;
use think\Db;
class Goods extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->goods = new \app\common\model\Goods();
	}
	public function getSupplierList($condition = '', $search_text)
	{
		$supplier = new GoodsSupplier();
		return $supplier->getPageLisy($condition, $search_text);
	}
	public function getGoodsList($condition = '')
	{
		$list = $this->getGoodsViewQueryField($condition, "*", '');
		return $list;
	}
	public function ModifyGoodsOnline($condition)
	{
		$data = array("state" => 1, "update_time" => time());
		$result = $this->goods->save($data, "goods_id  in({$condition})");
		if ($result > 0) {
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	public function deleteGoods($goods_id)
	{
		$this->goods->startTrans();
		try {
			$data = array("is_del" => 1, "update_time" => time());
			$res = $this->goods->save($data, "goods_id  in({$goods_id})");
			if ($res > 0) {
				$this->goods->commit();
				return SUCCESS;
			} else {
				return DELETE_FAIL;
			}
		} catch (\Exception $e) {
			$this->goods->rollback();
			return DELETE_FAIL;
		}
	}
	private function addGoodsDeleted($goods_ids)
	{
		$this->goods->startTrans();
		try {
			$goods_id_array = explode(",", $goods_ids);
			foreach ($goods_id_array as $k => $v) {
				$goods_info = $this->goods->get($v);
				$goods_delete_model = new GoodsDeleted();
				$goods_info = json_decode(json_encode($goods_info), true);
				$goods_delete_obj = $goods_delete_model->getInfo(["goods_id" => $v]);
				if (empty($goods_delete_obj)) {
					$goods_info["update_time"] = time();
					$goods_delete_model->save($goods_info);
					$goods_sku_model = new GoodsSku();
					$goods_sku_list = $goods_sku_model->getQuerys(["goods_id" => $v], "*", '');
					foreach ($goods_sku_list as $goods_sku_obj) {
						$goods_sku_deleted_model = new GoodsSkuDeleted();
						$goods_sku_obj = json_decode(json_encode($goods_sku_obj), true);
						$goods_sku_obj["update_date"] = time();
						$goods_sku_deleted_model->save($goods_sku_obj);
					}
					$goods_attribute_model = new GoodsAttr();
					$goods_attribute_list = $goods_attribute_model->getQuerys(["goods_id" => $v], "*", '');
					foreach ($goods_attribute_list as $goods_attribute_obj) {
						$goods_attribute_delete_model = new GoodsAttrDel();
						$goods_attribute_obj = json_decode(json_encode($goods_attribute_obj), true);
						$goods_attribute_delete_model->save($goods_attribute_obj);
					}
				}
			}
			$this->goods->commit();
			return 1;
		} catch (\Exception $e) {
			$this->goods->rollback();
			return $e->getMessage();
		}
	}
	public function ModifyGoodsOffline($condition)
	{
		$data = array("state" => 0, "update_time" => time());
		$result = $this->goods->save($data, "goods_id  in({$condition})");
		if ($result > 0) {
			return SUCCESS;
		} else {
			return UPDATA_FAIL;
		}
	}
	public function getGoodsViewQueryField($condition, $field, $order = '')
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&page", $url[1]);
		$url = "/" . $url[0];
		$goods_view = new \app\common\model\Goods();
		$list = $goods_view->alias("ng")->join("ybmp_images images", "ng.images = images.img_id", "left")->order("ng.sort desc,ng.create_time desc")->field($field)->where($condition)->paginate(20, false, ["query" => ["s" => $url]]);
		return $list;
	}
	public function addSupplier($supplier_name, $name, $tel, $address, $info, $logo, $mch_id)
	{
		$supplier = new GoodsSupplier();
		$data = array("supplier_name" => $supplier_name, "name" => $name, "tel" => $tel, "address" => $address, "info" => $info, "logo" => $logo, "create_time" => time(), "mch_id" => $mch_id);
		$res = $supplier->save($data);
		return $res;
	}
	public function updateSupplier($supplier_id, $supplier_name, $name, $tel, $address, $info, $logo, $bus_id)
	{
		$supplier = new GoodsSupplier();
		$data = array("supplier_name" => $supplier_name, "name" => $name, "tel" => $tel, "address" => $address, "info" => $info, "logo" => $logo, "mch_id" => $bus_id);
		return $supplier->save($data, ["supplier_id" => $supplier_id]);
	}
	public function getSupplierInfo($supplier_id)
	{
		$supplier = new GoodsSupplier();
		return $supplier->get($supplier_id);
	}
	public function deleteSupplier($supplier_id_array)
	{
		$supplier = new GoodsSupplier();
		$res = $supplier->destroy($supplier_id_array);
		return $res;
	}
	protected function checkSupplierIsUse($supplier_id)
	{
		$goods = new \app\common\model\Goods();
		$goods_deleted = new GoodsDeleted();
		$count = $goods->getCount(["supplier_id" => $supplier_id]);
		$count += $goods_deleted->getCount(["supplier_id" => $supplier_id]);
		return $count;
	}
	public function addGoodsSpecService($spec_name, $show_type, $is_visible, $sort, $spec_value_str, $attr_id = 0, $is_screen, $cate_id, $mch_id)
	{
		$goods_spec = new GoodsSpec();
		$goods_spec->startTrans();
		try {
			$data = array("spec_name" => $spec_name, "show_type" => 0, "is_visible" => $is_visible, "sort" => $sort, "create_time" => time(), "cate_id" => $cate_id, "mch_id" => $mch_id);
			$goods_spec->save($data);
			$spec_id = $goods_spec->spec_id;
			if ($attr_id > 0) {
				$attribute = new GoodsAttrModule();
				$attribute_info = $attribute->getInfo(["attr_id" => $attr_id], "*");
				if ($attribute_info["spec_id_array"] == '') {
					$attribute->save(["spec_id_array" => $spec_id], ["attr_id" => $attr_id]);
				} else {
					$attribute->save(["spec_id_array" => $attribute_info["spec_id_array"] . "," . $spec_id], ["attr_id" => $attr_id]);
				}
			}
			$spec_value_array = explode(",", $spec_value_str);
			$spec_value_array = array_filter($spec_value_array);
			$spec_value_array = array_unique($spec_value_array);
			foreach ($spec_value_array as $k => $v) {
				$spec_value = array();
				if ($show_type == 2) {
					$spec_value = explode(":", $v);
					$this->addGoodsSpecValueService($spec_id, $spec_value[0], 1, 255);
				} else {
					$this->addGoodsSpecValueService($spec_id, $v, 1, 255);
				}
			}
			$goods_spec->commit();
			return $spec_id;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			dump($e->getMessage());
			return $e->getMessage();
		}
	}
	public function addGoodsSpecValueService($spec_id, $spec_value_name, $is_visible, $sort = "0")
	{
		$goods_spec_value = new GoodsSpecValue();
		$data = array("spec_id" => $spec_id, "spec_value_name" => $spec_value_name, "is_visible" => $is_visible, "sort" => $sort, "create_time" => time());
		$goods_spec_value->save($data);
		return $goods_spec_value->spec_value_id;
	}
	public function getGoodsSpecList($condition = '', $search_text, $order = '')
	{
		$goods_spec = new GoodsSpec();
		$goods_spec_value = new GoodsSpecValue();
		$goods_spec_list = $goods_spec->getPageLisy($condition, $search_text, $order);
		if (!empty($goods_spec_list)) {
			foreach ($goods_spec_list as $ks => $vs) {
				$goods_spec_value_name = '';
				$spec_value_list = $goods_spec_value->getQuerys(["spec_id" => $vs["spec_id"]], "*", '');
				foreach ($spec_value_list as $kv => $vv) {
					$goods_spec_value_name = $goods_spec_value_name . "," . $vv["spec_value_name"];
				}
				$goods_spec_list[$ks]["spec_value_list"] = $spec_value_list;
				$goods_spec_value_name = $goods_spec_value_name == '' ? '' : substr($goods_spec_value_name, 1);
				$goods_spec_list[$ks]["spec_value_name_list"] = $goods_spec_value_name;
			}
		}
		return $goods_spec_list;
	}
	public function getGoodsSpecAll($condition = '', $field = "*", $order = '')
	{
		$goods_spec = new GoodsSpec();
		$goods_spec_value = new GoodsSpecValue();
		$goods_spec_list = $goods_spec->getQuerys($condition, $field, $order);
		if (!empty($goods_spec_list)) {
			foreach ($goods_spec_list as $ks => $vs) {
				$goods_spec_value_name = '';
				$spec_value_list = $goods_spec_value->getQuerys(["spec_id" => $vs["spec_id"]], "*", '');
				foreach ($spec_value_list as $kv => $vv) {
					$goods_spec_value_name = $goods_spec_value_name . "," . $vv["spec_value_name"];
				}
				$goods_spec_list[$ks]["spec_value_list"] = $spec_value_list;
				$goods_spec_value_name = $goods_spec_value_name == '' ? '' : substr($goods_spec_value_name, 1);
				$goods_spec_list[$ks]["spec_value_name_list"] = $goods_spec_value_name;
			}
		}
		return $goods_spec_list;
	}
	public function getAttrValueAll($condition = '', $field = "*", $order = '')
	{
		$attr_value = new GoodsAttrValue();
		$attr_value_list = $attr_value->getQuerys($condition, $field, $order);
		return $attr_value_list;
	}
	public function getGoodsSpecDetail($spec_id)
	{
		$goods_spec = new GoodsSpec();
		$goods_spec_value = new GoodsSpecValue();
		$info = $goods_spec->getInfo(["spec_id" => $spec_id], "*");
		$goods_spec_value_name = '';
		if (!empty($info)) {
			$goods_spec_value->destroy(["spec_id" => $info["spec_id"], "spec_value_name" => '']);
			$spec_value_list = $goods_spec_value->getQuerys(["spec_id" => $info["spec_id"]], "*", '');
			foreach ($spec_value_list as $kv => $vv) {
				$goods_spec_value_name = $goods_spec_value_name . "," . $vv["spec_value_name"];
			}
		}
		$info["spec_value_name_list"] = substr($goods_spec_value_name, 1);
		$info["spec_value_list"] = $spec_value_list;
		return $info;
	}
	public function updateGoodsSpecService($spec_id, $spec_name, $is_visible, $sort, $spec_value_str, $cate_id, $bus_id)
	{
		$goods_spec = new GoodsSpec();
		$goods_spec->startTrans();
		try {
			$data = array("spec_name" => $spec_name, "is_visible" => $is_visible, "sort" => $sort, "cate_id" => $cate_id, "mch_id" => $bus_id);
			$res = $goods_spec->save($data, ["spec_id" => $spec_id]);
			if (!empty($spec_value_str)) {
				$spec_value_array = explode(",", $spec_value_str);
				$spec_value_array = array_filter($spec_value_array);
				$spec_value_array = array_unique($spec_value_array);
				foreach ($spec_value_array as $k => $v) {
					$this->addGoodsSpecValueService($spec_id, $v, 1, 255);
				}
			}
			$goods_spec->commit();
			return $res;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			return $e->getMessage();
		}
	}
	public function addAttrModule($attr_mod_id, $attr_name, $spec_id, $attr_value_id, $is_use, $sort, $mch_id)
	{
		$attr_module = new GoodsAttrModule();
		$attr_module->startTrans();
		try {
			$data = array("attr_name" => $attr_name, "is_use" => $is_use, "sort" => $sort, "spec_id_array" => $spec_id, "attr_value_id" => $attr_value_id, "create_time" => time(), "mch_id" => $mch_id);
			if ($attr_mod_id == 0) {
				$attr_module->save($data);
				$attr_module_id = $attr_module->attr_id;
				$values = array("attr_id" => $attr_module_id);
			} else {
				$attr_module->save($data, ["attr_id" => $attr_mod_id]);
				$attr_module_id = $attr_mod_id;
			}
			$attr_module->commit();
			return $attr_module_id;
		} catch (\Exception $e) {
			$attr_module->rollback();
			return $e->getMessage();
		}
	}
	public function modifyGoodsSpecValueField($spec_value_id, $field_name, $field_value)
	{
		$goods_spec_value = new GoodsSpecValue();
		return $goods_spec_value->save(["{$field_name}" => $field_value], ["spec_value_id" => $spec_value_id]);
	}
	public function deleteGoodsSpecValue($spec_id, $spec_value_id)
	{
		$result = $this->getGoodsSpecValueCount(["spec_id" => $spec_id]);
		if ($result == 1) {
			return -2;
		} else {
			$goods_spec_value = new GoodsSpecValue();
			return $goods_spec_value->destroy($spec_value_id);
		}
	}
	public function getGoodsSpecValueCount($condition)
	{
		$spec_value = new GoodsSpecValue();
		$count = $spec_value->where($condition)->count();
		return $count;
	}
	public function checkGoodsSpecValueIsUse($spec_id, $spec_value_id)
	{
		$check_str = $spec_id . ":" . $spec_value_id . ";";
		$goods_sku = new GoodsSku();
		$goods_sku_delete = new GoodsSkuDeleted();
		$res = $goods_sku->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
		$res_delete = $goods_sku_delete->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
		if ($res + $res_delete > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function modifyGoodsSpecField($spec_id, $field_name, $field_value)
	{
		$goods_spec = new GoodsSpec();
		$data = array("{$field_name}" => $field_value);
		$res = $goods_spec->save($data, ["spec_id" => $spec_id]);
		return $res;
	}
	public function modifyAttributeFieldService($attr_id, $field_name, $field_value)
	{
		$attribute = new GoodsAttrValue();
		return $attribute->save(["{$field_name}" => $field_value], ["attr_value_id" => $attr_id]);
	}
	public function getAttrModuleInfo($mod_id, $condition = '', $field = "*", $order = '')
	{
		$attr_mod = new GoodsAttrModule();
		$where["attr_id"] = array("eq", $mod_id);
		$attr_mod_list = $attr_mod->getInfo($where);
		$spec_id_array = explode(",", $attr_mod_list["spec_id_array"]);
		$attr_mod_list["spec_id_array"] = $spec_id_array;
		$attr_val = new GoodsAttrValue();
		if (!empty($attr_mod_list["attr_value_id"]) && $attr_mod_list["attr_value_id"] == '') {
			$condition["attr_value_id"] = array("in", $attr_mod_list["attr_value_id"]);
		}
		$attr_val_list = $attr_val->getQuerys($condition, $field, $order);
		$attr_mod_list["attr_values"] = $attr_val_list;
		return $attr_mod_list;
	}
	public function deleteGoodsSpec($spec_id)
	{
		$goods_spec = new GoodsSpec();
		$spec_value = new GoodsSpecValue();
		$goods_spec->startTrans();
		try {
			$spec_id_array = explode(",", $spec_id);
			foreach ($spec_id_array as $k => $v) {
				$res = $this->checkGoodsSpecIsUse($v);
				if ($res) {
					return -1;
					$goods_spec->rollback();
				} else {
					$goods_spec->destroy($v);
					$spec_value->destroy(["spec_id" => $v]);
				}
			}
			$goods_spec->commit();
			return 1;
		} catch (\Exception $e) {
			$goods_spec->rollback();
			return $e->getMessage();
		}
	}
	public function checkGoodsSpecIsUse($spec_id)
	{
		$goods_spec_value = new GoodsSpecValue();
		$goods_sku = new GoodsSku();
		$spec_value_list = $goods_spec_value->getQuerys(["spec_id" => $spec_id], "*", '');
		if (!empty($spec_value_list)) {
			$check_str = '';
			$res = 0;
			foreach ($spec_value_list as $k => $v) {
				$check_str = $spec_id . ":" . $v["spec_value_id"] . ";";
				$res += $goods_sku->where(" CONCAT(attr_value_items, ';') like '%" . $check_str . "%'")->count();
				if ($res > 0) {
					return true;
					break;
				}
			}
			if ($res == 0) {
				return false;
			}
		} else {
			return false;
		}
	}
	public function addAttributeService($attr_name, $is_use, $sort, $value_string, $cate_id, $mch_id)
	{
		$attribute = new GoodsAttrValue();
		$attribute->startTrans();
		try {
			$data = array("attr_value_name" => $attr_name, "is_use" => $is_use, "sort" => $sort, "attr_id" => 0, "value" => $value_string, "cate_id" => $cate_id, "mch_id" => $mch_id);
			$attribute->save($data);
			$attr_value_id = $attribute->attr_value_id;
			$attribute->commit();
			return $attr_value_id;
		} catch (\Exception $e) {
			$attribute->rollback();
			return $e->getMessage();
		}
	}
	public function updateAttributeService($attr_id, $attr_name, $is_use, $attr_value_str, $sort, $cate_id)
	{
		$attribute = new GoodsAttrValue();
		$attribute->startTrans();
		try {
			$data = array("attr_value_name" => $attr_name, "is_use" => $is_use, "value" => $attr_value_str, "sort" => $sort, "cate_id" => $cate_id);
			$res = $attribute->save($data, ["attr_value_id" => $attr_id]);
			$attribute->commit();
			return $res;
		} catch (\Exception $e) {
			$attribute->rollback();
			return $e->getMessage();
		}
	}
	public function addAttributeValueService($attr_id, $attr_value_name, $sort, $value)
	{
		$attribute_value = new GoodsAttrValue();
		$data = array("attr_id" => $attr_id, "attr_value_name" => $attr_value_name, "type" => 3, "sort" => $sort, "value" => $value);
		$attribute_value->save($data);
		return $attribute_value->attr_value_id;
	}
	public function getAttributeModServiceList($condition = '', $field = "*", $order = '')
	{
		$attribute = new GoodsAttrModule();
		$attribute_value = new GoodsAttrValue();
		$list = $attribute->getQuerys($condition, $field, $order);
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$new_array = $attribute_value->getQuerys(["attr_id" => $v["attr_id"]], "attr_value_name", '');
				$value_str = '';
				foreach ($new_array as $kn => $vn) {
					$value_str = $value_str . "," . $vn["attr_value_name"];
				}
				$value_str = substr($value_str, 1);
				$list[$k]["value_str"] = $value_str;
			}
		}
		return $list;
	}
	public function getAttributeServiceList($condition = '', $search_text = '', $order = '')
	{
		$attribute_value = new GoodsAttrValue();
		$list = $attribute_value->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function getAttributeServiceAll($condition = '', $field = "*", $order = '')
	{
		$attribute_value = new GoodsAttrValue();
		$list = $attribute_value->getQuerys($condition, $field, $order);
		return $list;
	}
	public function getAttributeServiceListSelect($condition = '', $search_text = '', $order = array("sort" => "asc"))
	{
		$attribute = new GoodsAttrModule();
		$attribute_value = new GoodsAttrValue();
		$spec = new GoodsSpec();
		$list = $attribute->getPageLisy($condition, $search_text, $order);
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$attr_value_array = explode(",", $v["attr_value_id"]);
				$value_str = '';
				foreach ($attr_value_array as $kn => $vn) {
					$attr_array = $attribute_value->getInfo(["attr_value_id" => $vn], "attr_value_name", '');
					$value_str = $value_str . "," . $attr_array["attr_value_name"];
				}
				$value_str = substr($value_str, 1);
				$list[$k]["value_str"] = $value_str;
			}
			foreach ($list as $k => $v) {
				$spec_value_array = explode(",", $v["spec_id_array"]);
				$spec_str = '';
				foreach ($spec_value_array as $key => $value) {
					$spec_array = $spec->getInfo(["spec_id" => $value], "spec_name", '');
					$spec_str = $spec_str . "," . $spec_array["spec_name"];
				}
				$spec_str = substr($spec_str, 1);
				$list[$k]["spec_value_str"] = $spec_str;
			}
		}
		return $list;
	}
	public function getAttributeServiceDetail($attr_id, $condition = '')
	{
		$attribute = new GoodsAttrValue();
		$info = $attribute->get($attr_id);
		$exp = explode(",", $info["value"]);
		$info["exp"] = $exp;
		return $info;
	}
	public function deleteAttributeValueService($attr_id, $attr_value_id)
	{
		$attribute_value = new GoodsAttrValue();
		$result = $this->getGoodsAttrValueCount(["attr_id" => $attr_id]);
		if ($result == 1) {
			return -2;
		} else {
			return $attribute_value->destroy($attr_value_id);
		}
	}
	public function deleteAttributeService($attr_id)
	{
		$attribute_value = new GoodsAttrValue();
		$res = $attribute_value->destroy(["attr_value_id" => $attr_id]);
		return $res;
	}
	public function getGoodsAttrValueCount($condition)
	{
		$attr_value = new GoodsAttrValue();
		$count = $attr_value->where($condition)->count();
		return $count;
	}
	public function getAttributeValueServiceList($condition = '', $field = "*", $order = '')
	{
		$attribute_value = new GoodsAttrValue();
		return $attribute_value->getQuerys($condition, $field, $order);
	}
	public function getAttributeInfo($condition)
	{
		$attribute = new GoodsAttrModule();
		$info = $attribute->getInfo($condition, "*");
		return $info;
	}
	public function getSpecInfo($condition)
	{
		$attribute = new GoodsSpecValue();
		$info = $attribute->getInfo($condition, "*");
		return $info;
	}
	public function getGoodsSpecQuery($condition)
	{
		$goods_spec = new GoodsSpec();
		$goods_spec_query = $goods_spec->getQuerys($condition, "*", "sort");
		foreach ($goods_spec_query as $k => $v) {
			$goods_spec_value = new GoodsSpecValue();
			$goods_spec_value_query = $goods_spec_value->getQuerys(["spec_id" => $v["spec_id"]], "*", '');
			$goods_spec_query[$k]["values"] = $goods_spec_value_query;
		}
		return $goods_spec_query;
	}
	public function addGoodsSpecValue($spec_id, $spec_value, $sort = 0)
	{
		$spec_value_model = new GoodsSpecValue();
		$data = array("spec_id" => $spec_id, "spec_value_name" => $spec_value, "sort" => $sort, "create_time" => time());
		$find_id = $spec_value_model->get(["spec_value_name" => $spec_value, "spec_id" => $spec_id]);
		if (!empty($find_id)) {
			return $find_id["spec_value_id"];
		} else {
			$res = $spec_value_model->save($data);
			return $spec_value_model->spec_value_id;
		}
	}
	public function getGoodsAttrSpecQuery($condition)
	{
		if ($condition["attr_id"] == 0) {
			return -1;
		}
		$goods_attribute = $this->getAttributeInfo($condition);
		$condition_spec["spec_id"] = array("in", $goods_attribute["spec_id_array"]);
		$condition_spec["is_visible"] = 1;
		$spec_list = $this->getGoodsSpecQuery($condition_spec);
		$attribute_detail = $this->getAttributeServiceInfo($condition["attr_id"], ["is_search" => 1]);
		$attribute_list = $attribute_detail["value_list"];
		foreach ($attribute_list as $k => $v) {
			$value_items = explode(",", $v["value"]);
			$attribute_list[$k]["value_items"] = $value_items;
		}
		$list["spec_list"] = $spec_list;
		$list["attribute_list"] = $attribute_list;
		return $list;
	}
	public function getAttributeServiceInfo($attr_id, $condition = '')
	{
		$attribute = new GoodsAttrModule();
		$info = $attribute->get($attr_id);
		$array = array();
		$condition = array();
		if (!empty($info)) {
			$condition["attr_value_id"] = array("in", $info["attr_value_id"]);
			$array = $this->getAttributeValueServiceList($condition, $field = "*", $order = '');
			$info["value_list"] = $array;
		}
		return $info;
	}
	private function getGoodsCategoryId($category_id)
	{
		$goods_category = new \app\common\model\GoodsCate();
		$info = $goods_category->get($category_id);
		if ($info["level"] == 1) {
			return array($category_id, 0, 0);
		}
		if ($info["level"] == 2) {
			return array($info["pid"], $category_id, 0);
		}
		if ($info["level"] == 3) {
			$info_parent = $goods_category->get($info["pid"]);
			return array($info_parent["pid"], $info["pid"], $category_id);
		}
	}
	public function addOrEditGoods($this_lib, $mch_id, $goods_id, $goods_name, $category_id, $supplier_id, $brand_id, $market_price, $price, $cost_price, $shipping_fee, $shipping_fee_id, $stock, $min_buy, $clicks, $sales, $picture, $keywords, $introduction, $description, $sort, $image_array, $sku_array, $state, $goods_attribute_id, $goods_attribute, $goods_spec_format, $sku_picture_values, $barcode, $user_share)
	{
		$error = 0;
		$this->goods->startTrans();
		try {
			$data_goods = array("goods_name" => $goods_name, "cate_id" => $category_id, "supplier_id" => $supplier_id, "brand_id" => $brand_id, "market_price" => $market_price, "price" => $price, "promotion_price" => $price, "cost_price" => $cost_price, "shipping_fee" => $shipping_fee, "shipping_fee_id" => $shipping_fee_id, "stock" => $stock, "min_buy" => $min_buy, "clicks" => $clicks, "sales" => $sales, "keywords" => $keywords, "introduction" => $introduction, "description" => $description, "sort" => $sort, "images" => $picture, "img_id_array" => $image_array, "state" => $state, "goods_attribute_id" => $goods_attribute_id, "goods_attribute" => $goods_attribute, "goods_spec_format" => $goods_spec_format, "mch_id" => $mch_id, "barcode" => $barcode, "user_share" => $user_share);
			$spce = json_decode($goods_spec_format, true);
			$sku_picture_array = json_decode($sku_picture_values, true);
			$ismain_id = '';
			$sku_imgs = array();
			foreach ($sku_picture_array as $sku_img) {
				$ismain_id = $sku_img["spec_id"];
				$img_k = $sku_img["spec_id"] . ":" . $sku_img["spec_value_id"];
				$sku_imgs[$img_k] = $sku_img["img_ids"];
			}
			foreach ($spce as &$sku_item) {
				if ($sku_item["spec_id"] == $ismain_id) {
					$sku_item["ismain"] = true;
				} else {
					unset($sku_item["ismain"]);
				}
				foreach ($sku_item["value"] as &$sku_value) {
					$sk = $sku_value["spec_id"] . ":" . $sku_value["spec_value_id"];
					if (isset($sku_imgs[$sk])) {
						$sku_value["images"] = $sku_imgs[$sk];
					} else {
						unset($sku_value["images"]);
					}
				}
			}
			$data_goods["goods_spec_format"] = json_encode($spce, true);
			if ($this_lib == 1 && $goods_id != 0) {
				$data_goods["create_time"] = time();
				$data_goods["sale_date"] = time();
				$this->goods->save($data_goods);
				$goods_id = $this->goods->goods_id;
				$this->spec_format($goods_id, $spce);
				if (!empty($sku_array)) {
					$sku_list_array = explode("§", $sku_array);
					foreach ($sku_list_array as $k => $v) {
						$img_ids = '';
						foreach ($sku_imgs as $img_k => $img_v) {
							if (strpos($v, $img_k)) {
								$img_ids = $img_v;
								break;
							}
						}
						$res = $this->addOrUpdateGoodsSkuItem($goods_id, $v, $img_ids, $sku_picture_array);
						if (!$res) {
							$error = 1;
						}
					}
				} else {
					$goods_sku = new GoodsSku();
					$sku_data = array("goods_id" => $goods_id, "sku_name" => '', "market_price" => $market_price, "price" => $price, "promote_price" => $price, "cost_price" => $cost_price, "stock" => $stock, "images" => 0, "create_date" => time());
					$res = $goods_sku->save($sku_data);
					if (!$res) {
						$error = 1;
					}
				}
			}
			if ($this_lib == 1 && $goods_id == 0) {
				$data_goods["create_time"] = time();
				$data_goods["sale_date"] = time();
				$this->goods->save($data_goods);
				$goods_id = $this->goods->goods_id;
				$this->spec_format($goods_id, $spce);
				if (!empty($sku_array)) {
					$sku_list_array = explode("§", $sku_array);
					foreach ($sku_list_array as $k => $v) {
						$img_ids = '';
						foreach ($sku_imgs as $img_k => $img_v) {
							if (strpos($v, $img_k)) {
								$img_ids = $img_v;
								break;
							}
						}
						$res = $this->addOrUpdateGoodsSkuItem($goods_id, $v, $img_ids, $sku_picture_array);
						if (!$res) {
							$error = 1;
						}
					}
				} else {
					$goods_sku = new GoodsSku();
					$sku_data = array("goods_id" => $goods_id, "sku_name" => '', "market_price" => $market_price, "price" => $price, "promote_price" => $price, "cost_price" => $cost_price, "stock" => $stock, "images" => 0, "create_date" => time());
					$res = $goods_sku->save($sku_data);
					if (!$res) {
						$error = 1;
					}
				}
			}
			if ($goods_id > 0 && $this_lib == 0) {
				$data_goods["update_time"] = time();
				$res = $this->goods->save($data_goods, ["goods_id" => $goods_id]);
				$this->spec_format($goods_id, $spce);
				if (!empty($sku_array)) {
					$sku_list_array = explode("§", $sku_array);
					$this->deleteSkuItem($goods_id, $sku_list_array);
					foreach ($sku_list_array as $k => $v) {
						$img_ids = '';
						foreach ($sku_imgs as $img_k => $img_v) {
							if (strpos($v, $img_k)) {
								$img_ids = $img_v;
								break;
							}
						}
						$res = $this->addOrUpdateGoodsSkuItem($goods_id, $v, $img_ids, $sku_picture_array);
						if (!$res) {
							$error = 1;
						}
					}
				} else {
					$sku_data = array("goods_id" => $goods_id, "sku_name" => '', "market_price" => $market_price, "price" => $price, "promote_price" => $price, "cost_price" => $cost_price, "stock" => $stock, "images" => 0, "update_date" => time());
					$goods_sku = new GoodsSku();
					$count = $goods_sku->getCount(["goods_id" => $goods_id]);
					if ($count > 0) {
						$retval = $goods_sku->destroy(["goods_id" => $goods_id, "attr_value_items" => array("NEQ", '')]);
						$res = $goods_sku->save($sku_data, ["goods_id" => $goods_id]);
					} else {
						$res = $goods_sku->save($sku_data);
					}
				}
			}
			if ($error == 0) {
				$this->goods->commit();
				return $goods_id;
			} else {
				$this->goods->rollback();
				return 0;
			}
		} catch (\Exception $e) {
			$this->goods->rollback();
			return $e->getMessage();
		}
	}
	public function spec_format($goods_id, $spec_list)
	{
		foreach ($spec_list as $k => $v) {
			foreach ($v["value"] as $x => $y) {
				$sku_form = new GoodsSpecFormat();
				$data = array("goods_id" => $goods_id, "spec_value_name" => $y["spec_value_name"], "spec_name" => $y["spec_name"], "spec_id" => $y["spec_id"], "spec_value_id" => $y["spec_value_id"]);
				$check = Db::name("ybmp_goods_spec_format")->where($data)->find();
				if ($check) {
					$sku_form->save($data, ["id" => $check["id"]]);
				} else {
					$sku_form->save($data);
				}
			}
		}
	}
	private function deleteSkuItem($goods_id, $sku_list_array)
	{
		$sku_item_list_array = array();
		foreach ($sku_list_array as $k => $sku_item_array) {
			$sku_item = explode("¦", $sku_item_array);
			$sku_item_list_array[] = $sku_item[0];
		}
		$goods_sku = new GoodsSku();
		$list = $goods_sku->where("goods_id=" . $goods_id)->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if (!in_array($v["attr_value_items"], $sku_item_list_array)) {
					$goods_sku->destroy($v["sku_id"]);
				}
			}
		}
	}
	public function addGoodsSkuPicture($goods_id, $sku_img_array)
	{
		$goods_sku_picture = new GoodsSku();
		$data = array("goods_id" => $goods_id, "images" => $sku_img_array);
		$retval = $goods_sku_picture->save($data);
		return $retval;
	}
	private function addOrUpdateGoodsSkuItem($goods_id, $sku_item_array, $img_ids, $img_arr)
	{
		$sku_item = explode("¦", $sku_item_array);
		$goods_sku = new GoodsSku();
		$sku_name = $this->createSkuName($sku_item[0]);
		$condition = array("goods_id" => $goods_id, "attr_value_items" => $sku_item[0]);
		if (!empty($sku_item[0]) && !empty($img_arr)) {
			$aa = explode(";", $sku_item[0]);
			if ($aa) {
				foreach ($aa as $k => $v) {
					$v = explode(":", $v);
					for ($i = 0; $i < count($img_arr); $i++) {
						if ($img_arr[$i]["spec_id"] == $v[0] && $img_arr[$i]["spec_value_id"] == $v[1]) {
							$img_ids = $img_arr[$i]["img_ids"];
						}
					}
				}
			}
		}
		$sku_count = $goods_sku->where($condition)->find();
		if (empty($sku_count)) {
			$data = array("goods_id" => $goods_id, "sku_name" => $sku_name, "attr_value_items" => $sku_item[0], "price" => $sku_item[1], "promote_price" => $sku_item[1], "market_price" => $sku_item[2], "cost_price" => $sku_item[3], "stock" => $sku_item[4], "images" => $img_ids, "create_date" => time());
			$goods_sku->save($data);
			return $goods_sku->sku_id;
		} else {
			$data = array("goods_id" => $goods_id, "sku_name" => $sku_name, "price" => $sku_item[1], "promote_price" => $sku_item[1], "market_price" => $sku_item[2], "cost_price" => $sku_item[3], "stock" => $sku_item[4], "images" => $img_ids, "update_date" => time());
			$res = $goods_sku->save($data, ["sku_id" => $sku_count["sku_id"]]);
			return $res;
		}
	}
	private function createSkuName($pvs)
	{
		$name = '';
		$pvs_array = explode(";", $pvs);
		foreach ($pvs_array as $k => $v) {
			$value = explode(":", $v);
			$prop_id = $value[0];
			$prop_value = $value[1];
			$goods_spec_value_model = new GoodsSpecValue();
			$value_name = $goods_spec_value_model->getInfo(["spec_value_id" => $prop_value], "spec_value_name");
			$name = $name . $value_name["spec_value_name"] . " ";
		}
		return $name;
	}
	public function regainGoodsDeleted($goods_ids)
	{
		$this->goods->startTrans();
		try {
			$data = array("is_del" => 0, "update_time" => time());
			$result = $this->goods->save($data, "goods_id  in({$goods_ids})");
			$this->goods->commit();
			return SUCCESS;
		} catch (\Exception $e) {
			$this->goods->rollback();
			return UPDATA_FAIL;
		}
	}
	public function deleteRecycleGoods($goods_id)
	{
		$goods_delete = new \app\common\model\Goods();
		$goods_delete->startTrans();
		try {
			$res = $goods_delete->where("goods_id in ({$goods_id}) ")->delete();
			if ($res > 0) {
				$goods_id_array = explode(",", $goods_id);
				$goods_sku_model = new GoodsSku();
				$goods_attribute_model = new GoodsAttr();
				foreach ($goods_id_array as $k => $v) {
					$goods_sku_model->where("goods_id = {$v}")->delete();
					$goods_attribute_model->where("goods_id = {$v}")->delete();
				}
			}
			$goods_delete->commit();
			if ($res > 0) {
				return SUCCESS;
			} else {
				return DELETE_FAIL;
			}
		} catch (\Exception $e) {
			$goods_delete->rollback();
			return DELETE_FAIL;
		}
	}
	public function getGoodsDetail($goods_id)
	{
		$goods = new \app\common\model\Goods();
		$goods_detail = $goods->get($goods_id);
		if ($goods_detail == null) {
			return null;
		}
		$goods_sku = new GoodsSku();
		$goods_sku_detail = $goods_sku->where("goods_id=" . $goods_id)->select();
		foreach ($goods_sku_detail as $k => $goods_sku) {
			$goods_sku_detail[$k]["member_price"] = $goods_sku["price"];
		}
		$goods_detail["sku_list"] = $goods_sku_detail;
		$spec_list = json_decode($goods_detail["goods_spec_format"], true);
		if (!empty($spec_list)) {
			foreach ($spec_list as $k => $v) {
				foreach ($v["value"] as $m => $t) {
					if (empty($t["spec_show_type"])) {
						$spec_list[$k]["value"][$m]["spec_show_type"] = 1;
					}
				}
			}
		}
		$goods_detail["spec_list"] = $spec_list;
		$goods_img = new \app\common\model\Images();
		$order = "instr('," . $goods_detail["img_id_array"] . ",',CONCAT(',',img_id,','))";
		$goods_img_list = $goods_img->getQuerys(["img_id" => ["in", $goods_detail["img_id_array"]]], "*", $order);
		$img_temp_array = array();
		if (trim($goods_detail["img_id_array"]) != '') {
			$img_array = explode(",", $goods_detail["img_id_array"]);
			foreach ($img_array as $k => $v) {
				if (!empty($goods_img_list)) {
					foreach ($goods_img_list as $t => $m) {
						if ($m["img_id"] == $v) {
							$img_temp_array[] = $m;
						}
					}
				}
			}
		}
		$goods_picture = $goods_img->get($goods_detail["images"]);
		$goods_detail["img_temp_array"] = $img_temp_array;
		$goods_detail["img_list"] = $goods_img_list;
		$goods_detail["picture_detail"] = $goods_picture;
		$category_name = $this->getGoodsCategoryName($goods_detail["cate_id"]);
		$goods_detail["cate_name"] = $category_name;
		if ($goods_detail["goods_attribute_id"] != 0) {
			$attribute_model = new GoodsAttrModule();
			$attribute_info = $attribute_model->getInfo(["attr_id" => $goods_detail["goods_attribute_id"]], "attr_name");
			$goods_detail["goods_attribute_name"] = $attribute_info["attr_name"];
			$goods_attribute_model = new GoodsAttr();
			$goods_attribute_list = $goods_attribute_model->getQuerys(["goods_id" => $goods_id], "*", '');
			$goods_detail["goods_attribute_list"] = $goods_attribute_list;
		} else {
			$goods_detail["goods_attribute_name"] = '';
			$goods_detail["goods_attribute_list"] = array();
		}
		$goos_sku_picture_query = $goods_sku->getQuerys(["goods_id" => $goods_id], "images,attr_value_items", '');
		$album_picture = new \app\common\model\Images();
		foreach ($goos_sku_picture_query as $k => $v) {
			if ($v["images"] != "0") {
				$tmp_img_array = $album_picture->getQuerys(["img_id" => $v["images"]], "*", '');
				$goos_sku_picture_query[$k]["sku_picture_query"] = $tmp_img_array;
			} else {
				unset($goos_sku_picture_query[$k]);
			}
		}
		sort($goos_sku_picture_query);
		$goods_detail["sku_picture_array"] = $goos_sku_picture_query;
		$orderGoods = new \app\common\model\OrderGoods();
		$num = 0;
		$num = $orderGoods->getSum(["goods_id" => $goods_id], "num");
		$goods_detail["purchase_num"] = $num;
		return $goods_detail;
	}
	private function getGoodsCategoryName($cate_id, $name = '')
	{
		$goods_category = new \app\common\model\GoodsCate();
		$info = $goods_category->getInfo(["cate_id" => $cate_id], "cate_name,pid");
		if (!empty($info["cate_name"])) {
			if ($name == '') {
				$name .= $info["cate_name"];
			} else {
				$name = $info["cate_name"] . " > " . $name;
			}
		}
		if ($info["pid"] != 0) {
			$cate_id = $info["pid"];
			return $this->getGoodsCategoryName($cate_id, $name);
		} else {
			return $name;
		}
	}
	public function ModifyGoodsRecommend($goods_ids, $re)
	{
		$goods = new \app\common\model\Goods();
		$goods->startTrans();
		try {
			$goods_id_array = explode(",", $goods_ids);
			$data = array("is_hot" => $re);
			foreach ($goods_id_array as $k => $v) {
				$goods = new \app\common\model\Goods();
				$goods->save($data, ["goods_id" => $v]);
			}
			$goods->commit();
			return 1;
		} catch (\Exception $e) {
			$goods->rollback();
			return $e->getMessage();
		}
	}
	public function ModifyGoodsRecommendNew($goods_ids, $new)
	{
		$goods = new \app\common\model\Goods();
		$goods->startTrans();
		try {
			$goods_id_array = explode(",", $goods_ids);
			$data = array("is_new" => $new);
			foreach ($goods_id_array as $k => $v) {
				$goods = new \app\common\model\Goods();
				$goods->save($data, ["goods_id" => $v]);
			}
			$goods->commit();
			return 1;
		} catch (\Exception $e) {
			$goods->rollback();
			return $e->getMessage();
		}
	}
	public function ModifyGoodsRecommendTui($goods_ids, $new)
	{
		$goods = new \app\common\model\Goods();
		$goods->startTrans();
		try {
			$goods_id_array = explode(",", $goods_ids);
			$data = array("is_recommend" => $new);
			foreach ($goods_id_array as $k => $v) {
				$goods = new \app\common\model\Goods();
				$goods->save($data, ["goods_id" => $v]);
			}
			$goods->commit();
			return 1;
		} catch (\Exception $e) {
			$goods->rollback();
			return $e->getMessage();
		}
	}
	public function attrModDel($mod_id)
	{
		$mod = new GoodsAttrModule();
		$res = $mod->destroy(["attr_id" => $mod_id]);
		return $res;
	}
	public function attrModIs_use($mod_id)
	{
		$mod = new GoodsAttrModule();
		$res = $mod->destroy(["attr_id" => $mod_id]);
		return $res;
	}
	public function attrModOff($mod_id)
	{
		$mod = new GoodsAttrModule();
		$res = $mod->save(["is_use" => 0], ["attr_id" => $mod_id]);
		return $res;
	}
	public function attrModOn($mod_id)
	{
		$mod = new GoodsAttrModule();
		$res = $mod->save(["is_use" => 1], ["attr_id" => $mod_id]);
		return $res;
	}
	public function editGoodsType($good_id, $key)
	{
		$goods = new \app\common\model\Goods();
		if ($key == "new") {
			$data = array("is_new" => 0);
		}
		if ($key == "hot") {
			$data = array("is_hot" => 0);
		}
		if ($key == "tui") {
			$data = array("is_recommend" => 0);
		}
		$res = $goods->save($data, ["goods_id" => $good_id]);
		return $res;
	}
}
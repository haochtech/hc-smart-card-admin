<?php


namespace app\admin\controller;

use app\admin\service\Area;
use app\admin\service\GoodsCate;
use app\admin\service\GoodsLabel;
use app\admin\service\Images;
use app\admin\service\Supplier;
use think\Db;
class Goods extends Base
{
	public function supplier()
	{
		$supplier = new \app\admin\service\Goods();
		$search_text = request()->post("search_text", '');
		$condition["supplier_name"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $this->bus_id);
		$list = $supplier->getSupplierList($condition, $search_text);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return view("goods/supplier");
	}
	public function supplier_add()
	{
		$supplier = new \app\admin\service\Goods();
		if (request()->isAjax()) {
			$supplier_name = request()->post("supplier_name", '');
			$name = request()->post("name", '');
			$tel = request()->post("tel", '');
			$address = request()->post("address", '');
			$info = request()->post("info", '');
			$logo = request()->post("logo", '');
			$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
			$res = $supplier->addSupplier($supplier_name, $name, $tel, $address, $info, $logo, $mch_id);
			return AjaxReturn($res);
		}
		return view("goods/supplier_add");
	}
	public function supplier_edit()
	{
		$supplier = new \app\admin\service\Goods();
		if (request()->isAjax()) {
			$supplier_id = request()->post("supplier_id", '');
			$supplier_name = request()->post("supplier_name", '');
			$name = request()->post("name", '');
			$tel = request()->post("tel", '');
			$address = request()->post("address", '');
			$info = request()->post("info", '');
			$logo = request()->post("logo", '');
			$res = $supplier->updateSupplier($supplier_id, $supplier_name, $name, $tel, $address, $info, $logo, $this->bus_id);
			return AjaxReturn($res);
		}
		$supplier_id = request()->get("supp_id", 0);
		$info = $supplier->getSupplierInfo($supplier_id);
		$this->assign("supplier_id", $supplier_id);
		$this->assign("info", $info);
		return view("goods/supplier_edit");
	}
	public function supplier_del()
	{
		$supplier = new \app\admin\service\Goods();
		$supplier_id = request()->post("supp_id", 0);
		$res = $supplier->deleteSupplier($supplier_id);
		return AjaxReturn($res);
	}
	public function goods_spec()
	{
		$goods = new \app\admin\service\Goods();
		$search_text = request()->post("search_text", '');
		$condition["spec_name"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $this->bus_id);
		$list = $goods->getGoodsSpecList($condition, $search_text, array("sort" => "asc"));
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return view("goods/goods_spec");
	}
	public function goods_spec_add()
	{
		if (request()->isAjax()) {
			$goods = new \app\admin\service\Goods();
			$spec_name = request()->post("spec_name", '');
			$is_visible = request()->post("is_visible", '');
			$sort = request()->post("sort", '');
			$show_type = request()->post("show_type", '');
			$spec_value_str = request()->post("spec_value_str", '');
			$attr_id = request()->post("attr_id", 0);
			$is_screen = request()->post("is_screen", 0);
			$cate_id = request()->post("cate_id", 0);
			$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
			$res = $goods->addGoodsSpecService($spec_name, $show_type, $is_visible, $sort, $spec_value_str, $attr_id, $is_screen, $cate_id, $mch_id);
			return AjaxReturn($res);
		} else {
			$goods_cate = new GoodsCate();
			$res = $goods_cate->getGoodsCateV1("level=1");
			$this->assign("res", $res);
			return view("goods/goods_spec_add");
		}
	}
	public function addGoodsSpecValue()
	{
		$goods = new \app\admin\service\Goods();
		$spec_id = request()->post("spec_id", 0);
		$spec_value_name = request()->post("spec_value_name", '');
		$is_visible = 1;
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$res = $goods->addGoodsSpecValueService($spec_id, $spec_value_name, $is_visible, '', $mch_id);
		return AjaxReturn($res);
	}
	public function goods_spec_edit()
	{
		$goods = new \app\admin\service\Goods();
		$goods_cate = new GoodsCate();
		if (request()->isAjax()) {
			$spec_id = request()->post("spec_id", '');
			$spec_name = request()->post("spec_name", '');
			$is_visible = request()->post("is_visible", '');
			$sort = request()->post("sort", "255");
			$spec_value_str = request()->post("spec_value_str", '');
			$cate_id = request()->post("cate_id", '');
			$res = $goods->updateGoodsSpecService($spec_id, $spec_name, $is_visible, $sort, $spec_value_str, $cate_id, $this->bus_id);
			return AjaxReturn($res);
		} else {
			$spec_id = request()->get("spec_id", '');
			$detail = $goods->getGoodsSpecDetail($spec_id);
			$where["level"] = 1;
			$where["mch_id"] = $this->bus_id;
			$res = $goods_cate->getGoodsCateV1($where);
			$this->assign("res", $res);
			$this->assign("info", $detail);
			return view("goods/goods_spec_edit");
		}
	}
	public function setGoodsSpecField()
	{
		$goods = new \app\admin\service\Goods();
		$spec_id = request()->post("spec_id", '');
		$field_value = request()->post("value", '');
		$field_name = request()->post("name");
		$retval = $goods->modifyGoodsSpecField($spec_id, $field_name, $field_value);
		return AjaxReturn($retval);
	}
	public function setGoodsattrField()
	{
		$goods = new \app\admin\service\Goods();
		$attr_id = request()->post("attr_id", '');
		$field_value = request()->post("value", '');
		$field_name = request()->post("name");
		$retval = $goods->modifyGoodsattrField($attr_id, $field_name, $field_value);
		return AjaxReturn($retval);
	}
	public function setAttributeField()
	{
		$goods = new \app\admin\service\Goods();
		$attr_id = request()->post("id");
		$field_name = request()->post("name");
		$field_value = request()->post("value");
		$reval = $goods->modifyAttributeFieldService($attr_id, $field_name, $field_value);
		return AjaxReturn($reval);
	}
	public function modifyGoodsSpecValueField()
	{
		$goods = new \app\admin\service\Goods();
		$spec_value_id = request()->post("spec_value_id", '');
		$field_name = request()->post("field_name", '');
		$field_value = request()->post("field_value", '');
		$retval = $goods->modifyGoodsSpecValueField($spec_value_id, $field_name, $field_value);
		return AjaxReturn($retval);
	}
	public function deleteGoodsSpecValue()
	{
		$goods = new \app\admin\service\Goods();
		$spec_id = request()->post("spec_id", 0);
		$spec_value_id = request()->post("spec_value_id", 0);
		$res = $goods->deleteGoodsSpecValue($spec_id, $spec_value_id);
		return AjaxReturn($res);
	}
	public function deleteGoodsSpec()
	{
		$spec_id = request()->post("spec_id", 0);
		$goods = new \app\admin\service\Goods();
		$res = $goods->deleteGoodsSpec($spec_id);
		return AjaxReturn($res);
	}
	public function attributelist()
	{
		$goods = new \app\admin\service\Goods();
		$search_text = input("param.search_text", '');
		$condition["attr_value_name"] = array("like", "%" . $search_text . "%");
		$condition["mch_id"] = array("eq", $this->bus_id);
		$goodsEvaluateList = $goods->getAttributeServiceList($condition, $search_text, array("sort" => "asc"));
		$page = $goodsEvaluateList->render();
		$this->assign("att_list", $goodsEvaluateList);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return view("goods/goods_attr");
	}
	public function attribute_add()
	{
		if (request()->isAjax()) {
			$goods = new \app\admin\service\Goods();
			$attr_name = request()->post("attr_name", '');
			$is_use = request()->post("is_visible", '');
			$sort = request()->post("sort", '');
			$value_string = request()->post("attr_value_str", '');
			$cate_id = request()->post("cate_id", '');
			$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
			$goodsAttribute = $goods->addAttributeService($attr_name, $is_use, $sort, $value_string, $cate_id, $mch_id);
			return AjaxReturn($goodsAttribute);
		}
		$goods_cate = new GoodsCate();
		$res = $goods_cate->getGoodsCateV1("level=1");
		$this->assign("res", $res);
		return view("goods/goods_attr_add");
	}
	public function goods_attr_edit()
	{
		$goods = new \app\admin\service\Goods();
		if (request()->isAjax()) {
			$attr_id = request()->post("attr_id", '');
			$attr_name = request()->post("attr_name", '');
			$is_use = request()->post("is_visible", '');
			$sort = request()->post("sort", '');
			$attr_value_str = request()->post("attr_value_str", '');
			$cate_id = request()->post("cate_id", '');
			$goodsAttribute = $goods->updateAttributeService($attr_id, $attr_name, $is_use, $attr_value_str, $sort, $cate_id);
			return AjaxReturn($goodsAttribute);
		}
		$goods = new \app\admin\service\Goods();
		$goods_cate = new GoodsCate();
		$attr_id = request()->get("attr_id", '');
		$attribute_detail = $goods->getAttributeServiceDetail($attr_id);
		$where["level"] = 1;
		$where["mch_id"] = $this->bus_id;
		$res = $goods_cate->getGoodsCateV1($where);
		$this->assign("res", $res);
		$this->assign("info", $attribute_detail);
		return view("goods/goods_attr_edit");
	}
	public function deleteAttributeValue()
	{
		$goods = new \app\admin\service\Goods();
		$attr_id = request()->post("attr_id", 0);
		$attr_value_id = request()->post("attr_value_id", 0);
		$res = $goods->deleteAttributeValueService($attr_id, $attr_value_id);
		return AjaxReturn($res);
	}
	public function deleteAttr()
	{
		$attr_id = request()->post("attr_id");
		$goods = new \app\admin\service\Goods();
		$res = $goods->deleteAttributeService($attr_id);
		return AjaxReturn($res);
	}
	public function attribute_but()
	{
		$id = input("param.order");
		$order_list = input("param.order_list");
		if (!empty($order_list)) {
			$order_list = explode(",", $order_list);
		}
		$this->assign("order", $id);
		$this->assign("order_list", $order_list);
		return view("goods/goods_attr_but");
	}
	public function goodsCategoryList()
	{
		$goods_category = new GoodsCate();
		$one_list = $goods_category->getFormatGoodsCategoryList($this->bus_id);
		$this->assign("category_list", $one_list);
		return view("goods/goods_cate_list");
	}
	public function goods_add_cate()
	{
		$goodscate = new GoodsCate();
		if (request()->isAjax()) {
			$cate_name = request()->post("cate_name", '');
			$pid = request()->post("pid", '');
			$is_visible = request()->post("is_visible", '');
			$keywords = request()->post("keywords", '');
			$description = request()->post("description", '');
			$sort = request()->post("sort", '');
			$cate_pic = request()->post("cate_pic", '');
			$short_name = request()->post("short_name", '');
			$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
			$result = $goodscate->addOrEditGoodsCategory(0, $cate_name, $short_name, $pid, $is_visible, $keywords, $description, $sort, $cate_pic, $mch_id);
			return AjaxReturn($result);
		}
		$category_list = $goodscate->getGoodsCategoryTree(0, $this->bus_id);
		$this->assign("category_list", $category_list);
		return view("goods/goods_add_cate");
	}
	public function updateGoodsCate()
	{
		$goodscate = new GoodsCate();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax()) {
			$cate_id = request()->post("cate_id", '');
			$cate_name = request()->post("cate_name", '');
			$short_name = request()->post("short_name", '');
			$pid = request()->post("pid", '');
			$is_visible = request()->post("is_visible", '');
			$keywords = request()->post("keywords", '');
			$description = request()->post("description", '');
			$sort = request()->post("sort", '');
			$cate_pic = request()->post("cate_pic", '');
			$result = $goodscate->addOrEditGoodsCategory($cate_id, $cate_name, $short_name, $pid, $is_visible, $keywords, $description, $sort, $cate_pic, $mch_id);
			return AjaxReturn($result);
		}
		$category_id = request()->get("cate_id", '');
		$result = $goodscate->getGoodsCategoryDetail($category_id);
		$this->assign("data", $result);
		if ($result["level"] == 1) {
			$chile_list = $goodscate->getGoodsCategoryTree($category_id, $mch_id);
			if (empty($chile_list)) {
				$category_list = $goodscate->getGoodsCategoryTree(0, $mch_id);
			} else {
				$is_have = false;
				foreach ($chile_list as $k => $v) {
					if ($v["level"] == 3) {
						$is_have = true;
					}
				}
				if ($is_have) {
					$category_list = array();
				} else {
					$category_list = $goodscate->getGoodsCategoryListByParentId(0, $mch_id);
				}
			}
		} elseif ($result["level"] == 2) {
			$chile_list = $goodscate->getGoodsCategoryListByParentId($category_id, $mch_id);
			if (empty($chile_list)) {
				$category_list = $goodscate->getGoodsCategoryTree(0, $mch_id);
			} else {
				$category_list = $goodscate->getGoodsCategoryListByParentId(0, $mch_id);
			}
		} elseif ($result["level"] == 3) {
			$category_list = $goodscate->getGoodsCategoryTree(0, $mch_id);
		}
		if (!empty($category_list)) {
			foreach ($category_list as $k => $v) {
				if ($v["cate_id"] == $category_id && $category_id !== 0) {
					unset($category_list[$k]);
				} else {
					if (isset($v["child_list"])) {
						$temp_array = $v["child_list"];
						foreach ($temp_array as $t => $m) {
							if ($m["cate_id"] == $category_id && $category_id !== 0) {
								unset($temp_array[$t]);
							}
						}
						sort($temp_array);
						$category_list[$k]["child_list"] = $temp_array;
					}
				}
			}
			sort($category_list);
		}
		$this->assign("category_list", $category_list);
		return view("goods/goods_cate_edit");
	}
	public function modifyGoodsCategoryField()
	{
		$goodscate = new GoodsCate();
		$fieldid = request()->post("fieldid", '');
		$fieldname = request()->post("fieldname", '');
		$fieldvalue = request()->post("fieldvalue", '');
		$res = $goodscate->ModifyGoodsCategoryField($fieldid, $fieldname, $fieldvalue);
		return $res;
	}
	public function deleteGoodsCategory()
	{
		$goodscate = new GoodsCate();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$cate_id = request()->post("cate_id", '');
		$res = $goodscate->deleteGoodsCategory($cate_id, $mch_id);
		return AjaxReturn($res);
	}
	public function deleteGoods()
	{
		$goods_ids = request()->post("goods_ids");
		$goodservice = new \app\admin\service\Goods();
		$retval = $goodservice->deleteGoods($goods_ids);
		return AjaxReturn($retval);
	}
	public function goodslist()
	{
		$goodservice = new \app\admin\service\Goods();
		$search_text = input("param.search_text");
		$status = input("param.status");
		$where = [];
		if ($status == 1) {
			$where["ng.state"] = ["eq", "1"];
		}
		if ($status == 2) {
			$where["ng.state"] = ["in", "0,10"];
		}
		if ($status == 3) {
			$where["ng.stock"] = ["eq", "0"];
		}
		if ($status == 4) {
			return self::goodsDelList();
		} else {
			if (!empty($search_text)) {
				$where["ng.goods_name"] = ["like", "%" . $search_text . "%"];
			}
			$star_time = input("param.star_time");
			$end_time = input("param.end_time");
			if (!empty($star_time)) {
				$star = strtotime($star_time);
				$where["ng.create_time"] = ["between", [$star, $star + 86400]];
			}
			if (!empty($star_time) && !empty($end_time)) {
				$star = strtotime($star_time);
				$end = strtotime($end_time);
				$where["ng.create_time"] = ["between", [$star, $end]];
			}
			$where["ng.is_del"] = array("<>", "1");
			$where["ng.mch_id"] = array("eq", $this->bus_id);
			$cate_id = input("param.goods_cate");
			if (!empty($cate_id) || $cate_id != 0) {
				$where["ng.cate_id"] = array("eq", $cate_id);
			}
			$result = $goodservice->getGoodsList($where);
			$page = $result->render();
			$this->assign("page", $page);
			$this->assign("cate_id", $cate_id);
			$this->assign("result", $result);
			$this->assign("search_text", $search_text);
			$this->assign("star_time", $star_time);
			$this->assign("end_time", $end_time);
			$this->assign("status", $status);
			$goods_cate = Db::name("ybmp_goods_cate")->where("is_visible=1")->where("mch_id", $this->bus_id)->select();
			$this->assign("goods_cate", $goods_cate);
			return view("goods/goods_list");
		}
	}
	public function ModifyGoodsSort()
	{
		$goods_id = request()->post("id", '');
		$sort = request()->post("sort", "0");
		$res = Db::name("ybmp_goods")->where(["goods_id" => $goods_id])->update(["sort" => $sort]);
		$res = $res !== false ? 1 : 0;
		return AjaxReturn($res);
	}
	public function ModifyGoodsOnline()
	{
		$condition = request()->post("goods_ids", '');
		$goods_detail = new \app\admin\service\Goods();
		$result = $goods_detail->ModifyGoodsOnline($condition);
		return AjaxReturn($result);
	}
	public function ModifyGoodsOffline()
	{
		$condition = request()->post("goods_ids", '');
		$goods_detail = new \app\admin\service\Goods();
		$result = $goods_detail->ModifyGoodsOffline($condition);
		return AjaxReturn($result);
	}
	public function add_goods()
	{
		$goods_group = new GoodsCate();
		$goodsbrand = new \app\admin\service\GoodsBrand();
		$supplier = new Supplier();
		$goods = new \app\admin\service\Goods();
		$express = new \app\admin\service\Express();
		$area = new Area();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$condition["mch_id"] = array("eq", $mch_id);
		$this_lib = request()->get("this_lib", 0);
		$goodsbrandList = $goodsbrand->getGoodsBrandAll($condition);
		$this->assign("goodsbrand_list", $goodsbrandList);
		$goodsId = request()->get("goodsId", 0);
		$groupList = $goods_group->getGoodsGroupList($condition);
		$this->assign("group_list", $groupList);
		$supplier_list = $supplier->getSupplierList($condition);
		$this->assign("supplier_list", $supplier_list);
		$area_province = $area->getAreaList();
		$this->assign("area_province", $area_province);
		$expressCompanyList = Db::name("ybmp_express_shipping")->alias("ship")->join("ybmp_express_company comp", "comp.co_id = ship.co_id", "left")->where("comp.mch_id", $this->bus_id)->field("ship.*")->select();
		$this->assign("expressCompany", $expressCompanyList);
		$goods_attribute_list = $goods->getAttributeModServiceList($condition);
		$this->assign("goods_attribute_list", $goods_attribute_list);
		if (empty($groupList)) {
			$this->assign("group_str", '');
		} else {
			$this->assign("group_str", json_encode($groupList));
		}
		$this->assign("goods_id", $goodsId);
		$this->assign("shop_type", 2);
		$album = new Images();
		$detault_album_detail = $album->getDefaultAlbumDetail();
		$this->assign("detault_album_id", $detault_album_detail["group_id"]);
		if ($goodsId > 0 && $this_lib == 1) {
			$this->assign("goodsid", $goodsId);
			$this->assign("this_lib", $this_lib);
			$goods_info = $goods->getGoodsDetail($goodsId);
			$goods_info["sku_list"] = json_encode($goods_info["sku_list"]);
			$goods_info["img_list"] = json_encode($goods_info["img_list"]);
			$goods_info["goods_attribute_list"] = json_encode($goods_info["goods_attribute_list"]);
			if (trim($goods_info["goods_spec_format"]) != '') {
				$album = new Images();
				$goods_spec_array = json_decode($goods_info["goods_spec_format"], true);
				foreach ($goods_spec_array as $k => $v) {
					foreach ($v["value"] as $t => $m) {
						if (is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
							$picture_detail = $album->getAlubmPictureDetail(["pic_id" => $m["spec_value_data"]]);
							if (!empty($picture_detail)) {
								$goods_spec_array[$k]["value"][$t]["spec_value_data_src"] = $picture_detail["pic_cover"];
							}
						} else {
							if (!is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
								$goods_spec_array[$k]["value"][$t]["spec_value_data_src"] = $m["spec_value_data"];
							}
						}
					}
				}
				$goods_spec_format = json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE);
				$goods_info["goods_spec_format"] = $goods_spec_format;
			}
			$goods_info["description"] = str_replace(PHP_EOL, '', $goods_info["description"]);
			$cate_v1_id = $goods_group->InfiniteCate($goods_info["cate_id"]);
			$this->assign("cate_v1_id", $cate_v1_id);
			$this->assign("goods_info", $goods_info);
			$this->assign("goods_attr_id", $goods_info["goods_attribute_id"]);
			if (!empty($goods_info["sku_picture_array"])) {
				$sku_picture_array_str = json_encode($goods_info["sku_picture_array"]);
			} else {
				$sku_picture_array_str = '';
			}
			$this->assign("sku_picture_array_str", $sku_picture_array_str);
			return view("goods/goods_edit_lib");
		}
		if ($goodsId > 0 && $this_lib == 0) {
			$this->assign("goodsid", $goodsId);
			$goods_info = $goods->getGoodsDetail($goodsId);
			$goods_info["sku_list"] = json_encode($goods_info["sku_list"]);
			$goods_info["img_list"] = json_encode($goods_info["img_list"]);
			$goods_info["goods_attribute_list"] = json_encode($goods_info["goods_attribute_list"]);
			if (trim($goods_info["goods_spec_format"]) != '') {
				$album = new Images();
				$goods_spec_array = json_decode($goods_info["goods_spec_format"], true);
				foreach ($goods_spec_array as $k => $v) {
					foreach ($v["value"] as $t => $m) {
						if (is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
							$picture_detail = $album->getAlubmPictureDetail(["pic_id" => $m["spec_value_data"]]);
							if (!empty($picture_detail)) {
								$goods_spec_array[$k]["value"][$t]["spec_value_data_src"] = $picture_detail["pic_cover"];
							}
						} else {
							if (!is_numeric($m["spec_value_data"]) && $m["spec_show_type"] == 3) {
								$goods_spec_array[$k]["value"][$t]["spec_value_data_src"] = $m["spec_value_data"];
							}
						}
					}
				}
				$goods_spec_format = json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE);
				$goods_info["goods_spec_format"] = $goods_spec_format;
			}
			$goods_info["description"] = str_replace(PHP_EOL, '', $goods_info["description"]);
			$cate_v1_id = $goods_group->InfiniteCate($goods_info["cate_id"]);
			$goods_info["goods_attribute"] = str_replace("&quot;", "\"", $goods_info["goods_attribute"]);
			if ($goods_info["user_share"] > 0) {
				$this->assign("share_type_n", "元");
				$this->assign("share_type", 1);
			} else {
				$this->assign("share_type_n", "%");
				$this->assign("share_type", 2);
				$goods_info["user_share"] = abs($goods_info["user_share"]) * 100;
			}
			$this->assign("cate_v1_id", $cate_v1_id);
			$this->assign("goods_info", $goods_info);
			$this->assign("goods_attr_id", $goods_info["goods_attribute_id"]);
			if (!empty($goods_info["sku_picture_array"])) {
				$sku_picture_array_str = json_encode($goods_info["sku_picture_array"]);
			} else {
				$sku_picture_array_str = '';
			}
			$this->assign("sku_picture_array_str", $sku_picture_array_str);
			return view("goods/goods_edit");
		} else {
			$this->assign("goods_attr_id", 0);
			$this_mch_good_cate = Db::name("ybmp_goods_cate")->where("mch_id", $mch_id)->order("create_time asc")->find();
			$this->assign("this_mch_good_cate", $this_mch_good_cate);
			return view("goods/goods_add");
		}
	}
	public function getGoodsSpecListByAttrId()
	{
		$goods = new \app\admin\service\Goods();
		$condition["attr_id"] = request()->post("attr_id", 0);
		$condition["mch_id"] = array("eq", $this->bus_id);
		$list = $goods->getGoodsAttrSpecQuery($condition);
		return $list;
	}
	public function getGoodsAttrListByAttrId()
	{
		$goods = new \app\admin\service\Goods();
		$attr_id = request()->post("attr_id", 0);
		$list = $goods->getAttributeServiceDetail($attr_id);
		return $list;
	}
	public function CateGoryPropvaluesGet()
	{
		$propId = request()->post("propId", '');
		$value = request()->post("value", '');
		$goodservice = new \app\admin\service\Goods();
		$res = $goodservice->addGoodsSpecValue($propId, $value);
		return $res;
	}
	public function controlDialogSku()
	{
		$attr_id = request()->get("attr_id", 0);
		$this->assign("attr_id", $attr_id);
		return view("goods/goods_DialogSku");
	}
	public function goods_cate()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax()) {
			$data = input("param.");
			$where = [];
			$where["mch_id"] = ["eq", $mch_id];
			$where["is_visible"] = ["eq", 1];
			if (!empty($data["pid"])) {
				$where["pid"] = ["eq", $data["pid"]];
			}
			$goods_cate = new \app\common\model\GoodsCate();
			$groupList = $goods_cate->where($where)->where("level", $data["level"])->select();
			$a = json_encode($groupList, true);
			return $a;
		}
		return view("goods/goods_cate");
	}
	public function GoodsCreateOrUpdate()
	{
		$res = 0;
		$product = request()->post("product", '');
		if (!empty($product)) {
			$product = json_decode($product, true);
			$product["supplierId"] = empty($product["supplierId"]) ? 0 : $product["supplierId"];
			$product["brandId"] = empty($product["brandId"]) ? 0 : $product["brandId"];
			$product["barcode"] = empty($product["barcode"]) ? '' : $product["barcode"];
			$goodservice = new \app\admin\service\Goods();
			$product["goods_attribute"] = str_replace("&quot;", "\"", $product["goods_attribute"]);
			$user_share = $product["user_share"];
			if ($product["share_type"] == 2) {
				$user_share = 0 - $product["user_share"] / 100;
			}
			$res = $goodservice->addOrEditGoods($product["this_lib"], $this->bus_id, $product["goodsId"], $product["title"], $product["categoryId"], $product["supplierId"], $product["brandId"], $product["market_price"], $product["price"], $product["cost_price"], $product["shipping_fee"], $product["shipping_fee_id"], $product["stock"], $product["min_buy"], $product["base_good"], $product["base_sales"], $product["picture"], $product["key_words"], $product["introduction"], $product["description"], $sort = $product["sort"], $product["imageArray"], $product["skuArray"], $product["is_sale"], $product["goods_attribute_id"], $product["goods_attribute"], $product["goods_spec_format"], $product["sku_picture_vlaues"], $product["barcode"], $user_share);
			if ($product["goodsId"] == 0) {
				$product["goodsId"] = Db::name("ybmp_goods")->max("goods_id");
				if ($product["goodsId"] == '') {
					$product["goodsId"] = 0;
				}
				if (!empty($product["goods_attribute_id"]) && $product["goods_attribute_id"] != "0") {
					$list = Db::name("ybmp_goods_attr_module")->where("attr_id", $product["goods_attribute_id"])->find();
					$data["goods_id"] = $product["goodsId"];
					$attr_value_id = explode(",", $list["attr_value_id"]);
					if (!empty($attr_value_id)) {
						foreach ($attr_value_id as $key => $value) {
							$data["attr_value_id"] = $value;
							$attr_value = Db::name("ybmp_goods_attr_value")->where("attr_value_id", $value)->find();
							$data["attr_value"] = $attr_value["attr_value_name"] == '' ? '' : $attr_value["attr_value_name"];
							$data["attr_value_name"] = $attr_value["value"] == '' ? '' : $attr_value["value"];
							$data["sort"] = 0;
							$data["create_time"] = time();
							Db::name("ybmp_goods_attr")->insert($data);
						}
					}
				}
			} else {
				if (!empty($product["goods_attribute_id"]) && $product["goods_attribute_id"] != "0") {
					$list = Db::name("ybmp_goods_attr_module")->where("attr_id", $product["goods_attribute_id"])->find();
					$data["goods_id"] = $product["goodsId"];
					Db::name("ybmp_goods_attr")->where("goods_id", $data["goods_id"])->delete();
					$attr_value_id = explode(",", $list["attr_value_id"]);
					if (!empty($attr_value_id)) {
						foreach ($attr_value_id as $key => $value) {
							$data["attr_value_id"] = $value;
							$attr_value = Db::name("ybmp_goods_attr_value")->where("attr_value_id", $value)->find();
							$data["attr_value"] = $attr_value["attr_value_name"] == '' ? '' : $attr_value["attr_value_name"];
							$data["attr_value_name"] = $attr_value["value"] == '' ? '' : $attr_value["value"];
							$data["sort"] = 0;
							$data["create_time"] = time();
							Db::name("ybmp_goods_attr")->insert($data);
						}
					}
				}
			}
		}
		return $res;
	}
	public function goodTypeEdit()
	{
		$goodservice = new \app\admin\service\Goods();
		$good_id = input("param.goods_id");
		$key = input("param.key");
		$res = $goodservice->editGoodsType($good_id, $key);
		return AjaxReturn($res);
	}
	public function goodsbrand()
	{
		$goodsbrand = new \app\admin\service\GoodsBrand();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$result = $goodsbrand->getGoodsBrandList($condition, '', "create_time");
		$page = $result->render();
		$this->assign("result", $result);
		$this->assign("page", $page);
		return view("goods/goods_brand");
	}
	public function goodsbrand_add()
	{
		if (request()->isAjax()) {
			$goodsbrand = new \app\admin\service\GoodsBrand();
			$brand_name = request()->post("brand_name", '');
			$brand_initial = request()->post("brand_initial", '');
			$brand_pic = request()->post("brand_pic", '');
			$brand_recommend = request()->post("brand_recommend", '');
			$res = $goodsbrand->addOrUpdateGoodsBrand('', $brand_name, $brand_initial, $brand_pic, $brand_recommend, $this->bus_id);
			return AjaxReturn($res);
		}
		return view("goods/goods_brand_add");
	}
	public function goodsbrand_edit()
	{
		if (request()->isAjax()) {
			$goodsbrand = new \app\admin\service\GoodsBrand();
			$brand_id = input("param.brand_id", '');
			$brand_name = input("param.brand_name", '');
			$brand_initial = input("param.brand_initial", '');
			$brand_pic = input("param.brand_pic", '');
			$brand_recommend = input("param.brand_recommend", '');
			$res = $goodsbrand->addOrUpdateGoodsBrand($brand_id, $brand_name, $brand_initial, $brand_pic, $brand_recommend, $this->bus_id);
			return AjaxReturn($res);
		} else {
			$goodsbrand = new \app\admin\service\GoodsBrand();
			$brand_id = input("param.brand_id", '');
			$brand_info = $goodsbrand->getGoodsBrandInfo($brand_id);
			$this->assign("brand_info", $brand_info);
			return view("goods/goods_brand_edit");
		}
	}
	public function deleteGoodsBrand()
	{
		$brand_id = input("param.brand_id", '');
		$goodsbrand = new \app\admin\service\GoodsBrand();
		$res = $goodsbrand->deleteGoodsBrand($brand_id);
		return AjaxReturn($res);
	}
	public function goodsDelList()
	{
		$goodservice = new \app\admin\service\Goods();
		$search_text = input("param.search_text", '');
		$condition["ng.goods_name"] = array("like", "%" . $search_text . "%");
		$condition["ng.is_del"] = array("eq", 1);
		$condition["ng.mch_id"] = array("eq", $this->bus_id);
		$result = $goodservice->getGoodsList($condition);
		$page = $result->render();
		$this->assign("page", $page);
		$this->assign("result", $result);
		$this->assign("search_text", $search_text);
		$this->assign("status", 4);
		return view("goods/goods_del_list");
	}
	public function regainGoodsDeleted()
	{
		if (request()->isAjax()) {
			$goods_ids = input("param.goods_ids");
			$goods = new \app\admin\service\Goods();
			$res = $goods->regainGoodsDeleted($goods_ids);
			return AjaxReturn($res);
		}
	}
	public function emptyDeleteGoods()
	{
		$goods_ids = input("param.goods_ids");
		$goodsservice = new \app\admin\service\Goods();
		$res = $goodsservice->deleteRecycleGoods($goods_ids);
		return AjaxReturn($res);
	}
	public function goods_label()
	{
		$goodslabel = new GoodsLabel();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$list = $goodslabel->getGoodsGroupList($condition, '', "sort");
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view("goods/goods_label");
	}
	public function goods_label_add()
	{
		if (request()->isAjax()) {
			$goodslabel = new GoodsLabel();
			$label_name = input("param.label_name", '');
			$sort = input("param.sort", '');
			$label_pic = input("param.label_pic", '');
			$result = $goodslabel->addOrEditGoodsGroup(0, $label_name, $sort, $label_pic, $this->bus_id);
			return AjaxReturn($result);
		} else {
			return view("goods/goods_label_add");
		}
	}
	public function goods_label_edit()
	{
		if (request()->isAjax()) {
			$goodslabel = new GoodsLabel();
			$label_id = request()->post("label_id", '');
			$label_name = request()->post("label_name", '');
			$sort = request()->post("sort", '');
			$label_pic = request()->post("label_pic", '');
			$result = $goodslabel->addOrEditGoodsGroup($label_id, $label_name, $sort, $label_pic, $this->bus_id);
			return AjaxReturn($result);
		} else {
			$goodslabel = new GoodsLabel();
			$group_id = request()->get("label_id", '');
			$result = $goodslabel->getGoodsGroupDetail($group_id);
			$this->assign("data", $result);
			return view("goods/goods_label_edit");
		}
	}
	public function ModifyGoodsRecommend()
	{
		$goods_ids = request()->post("goods_id", '');
		$re = request()->post("re", "0");
		$goods_detail = new \app\admin\service\Goods();
		$result = $goods_detail->ModifyGoodsRecommend($goods_ids, $re);
		return AjaxReturn($result);
	}
	public function ModifyGoodsRecommendNew()
	{
		$goods_ids = request()->post("goods_id", '');
		$new = request()->post("new", "0");
		$goods_detail = new \app\admin\service\Goods();
		$result = $goods_detail->ModifyGoodsRecommendNew($goods_ids, $new);
		return AjaxReturn($result);
	}
	public function ModifyGoodsRecommendTui()
	{
		$goods_ids = request()->post("goods_id", '');
		$new = request()->post("tui", "0");
		$goods_detail = new \app\admin\service\Goods();
		$result = $goods_detail->ModifyGoodsRecommendTui($goods_ids, $new);
		return AjaxReturn($result);
	}
	public function goods_attr_mod()
	{
		$search_text = request()->param("search_text", '');
		$goods = new \app\admin\service\Goods();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$list = $goods->getAttributeServiceListSelect(["mch_id" => $mch_id, "attr_name" => array("like", "%" . $search_text . "%")]);
		$page = $list->render();
		$this->assign("search_text", $search_text);
		$this->assign("page", $page);
		$this->assign("list", $list);
		return view("goods/goods_attr_mod");
	}
	public function add_attr_mod()
	{
		$goods = new \app\admin\service\Goods();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax()) {
			$attr_mod_id = input("param.attr_mod_id", "0");
			$attr_name = input("param.attr_name", '');
			$sort = input("param.sort", "0");
			$spec_id = input("param.spec_id", "0");
			$attr_value_id = input("param.attr_value_id", "0");
			$is_use = input("param.is_use", "0");
			$res = $goods->addAttrModule($attr_mod_id, $attr_name, $spec_id, $attr_value_id, $is_use, $sort, $mch_id);
			return AjaxReturn($res);
		}
		$where["mch_id"] = array("eq", $mch_id);
		$where["is_visible"] = array("eq", "1");
		$spec_list = $goods->getGoodsSpecAll($where);
		$this->assign("spec_list", $spec_list);
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["is_use"] = array("eq", "1");
		$attr_value = $goods->getAttrValueAll($condition);
		$this->assign("attr_value", $attr_value);
		return view("goods/goods_attr_mod_add");
	}
	public function attr_mod_edit()
	{
		$goods = new \app\admin\service\Goods();
		$mod_id = input("param.mod_id");
		$attr_mod_info = $goods->getAttrModuleInfo($mod_id);
		$this->assign("attr_mod_info", $attr_mod_info);
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$where["mch_id"] = array("eq", $mch_id);
		$where["is_visible"] = array("eq", "1");
		$spec_list = $goods->getGoodsSpecAll($where);
		$this->assign("spec_list", $spec_list);
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["is_use"] = array("eq", "1");
		$attr_value = Db::name("ybmp_goods_attr_value")->where($condition)->select();
		$this->assign("attr_value", $attr_value);
		return view("goods/goods_attr_mod_edit");
	}
	public function attr_mod_del()
	{
		$goods = new \app\admin\service\Goods();
		$mod_id = input("param.mod_id");
		$res = $goods->attrModDel($mod_id);
		return AjaxReturn($res);
	}
	public function attrModOff()
	{
		$goods = new \app\admin\service\Goods();
		$mod_id = input("param.mod_id");
		$res = $goods->attrModOff($mod_id);
		return AjaxReturn($res);
	}
	public function attrModOn()
	{
		$goods = new \app\admin\service\Goods();
		$mod_id = input("param.mod_id");
		$res = $goods->attrModOn($mod_id);
		return AjaxReturn($res);
	}
	public function select_code()
	{
		$code = input("param.code");
		$url = THIS_URL . "api/api/barcode/Search";
		$post_data = array("code" => $code);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $output;
	}
	public function select_lib_goods()
	{
		$area = input("param.ser_area", "-1");
		$s = explode("=/", $_SERVER["QUERY_STRING"]);
		$s = explode("&", $s[1]);
		$where = [];
		if ($area != -1 && !empty($area)) {
			$where["g.cate_id"] = ["eq", $area];
		}
		$art = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "m.img_id=g.images")->where("g.mch_id", "1")->field("g.goods_id,g.create_time,g.goods_name,g.price,g.introduction,m.img_cover_small")->order("g.create_time desc")->where($where)->paginate(20, false, ["query" => ["s" => "/" . $s[0], "ser_area" => $area]]);
		$page = $art->render();
		$this->assign("goods", $art);
		$this->assign("page", $page);
		$cate = Db::name("ybmp_goods_cate")->where("is_visible", "1")->where("mch_id", "1")->select();
		$this->assign("cate", $cate);
		$this->assign("area", $area);
		return view("goods/goods_test");
	}
	public function get_sort_list()
	{
		$num = input("param.num");
		$sort = input("param.sort");
		$cate = input("param.cate");
		$where = [];
		if ($cate != 0) {
			$where["g.cate_id"] = ["eq", $cate];
		}
		if ($sort == "time") {
			$order = "g.create_time desc";
		}
		if ($sort == "pop") {
			$order = "g.is_hot desc";
		}
		if ($sort == "sales") {
			$order = "g.real_sales desc";
		}
		$input = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images=i.img_id")->where("g.mch_id", $this->bus_id)->where("g.is_del=0")->where($where)->field("g.*,i.img_cover")->order($order)->limit($num)->select();
		return $input;
	}
	public function get_sort_img_list()
	{
		$num = input("param.num");
		$sort = input("param.sort");
		$cate = input("param.cate");
		$where = [];
		if ($cate != 0) {
			$where["class_id"] = ["eq", $cate];
		}
		if ($sort == "time") {
			$order = "create_time desc";
		}
		if ($sort == "pop") {
			$order = "click desc";
		}
		if ($sort == "sales") {
			$order = "is_recommend desc";
		}
		$input = Db::name("ybmp_article")->where("mch_id", $this->bus_id)->where($where)->order($order)->limit($num)->select();
		return $input;
	}
	public function goods_collect()
	{
		if (request()->isAjax() && request()->isPost()) {
			$html = '';
			$url = request()->post("url");
			$url = str_replace("\"", "'", $url);
			$img = "https://hws.m.taobao.com/cache/mtop.wdetail.getItemDescx/4.1/?data=%7B%22item_num_id%22%3A%22";
			preg_match_all("/id=(.*?)&/", $url, $res);
			$id = isset($res[1][0]) ? $res[1][0] : '';
			$head = "https://h5api.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0/?data=%7B%22exParams%22%3A%22%7B%5C%22id%5C%22%3A%5C%22{$id}%5C%22%7D%22%2C%22itemNumId%22%3A%22{$id}%22%7D";
			$detail = json_decode(get_url_content($head, "https"), true);
			for ($i = 0; $i < count($detail["data"]["item"]["images"]); $i++) {
				$html .= "<div nstype=\"goods_image\" class=\"upload-thumb draggable-element\">";
				$html .= "<img nstype=\"goods_image\" src=\"https:" . $detail["data"]["item"]["images"][$i] . "\">";
				$html .= "<input type=\"hidden\" name=\"goods_down_img\" class=\"upload_img_id\" nstype=\"goods_image\" value=\"https:" . $detail["data"]["item"]["images"][$i] . "\">";
				$html .= "<div class=\"black-bg\" onclick=\"remlong(this);\">";
				$html .= "<div class=\"off-box\">&times;</div>";
				$html .= "</div>";
				$html .= "</div>";
			}
			$html .= "<input type=\"hidden\" name=\"is_ai\" id=\"is_ai\" value=\"1\">";
			$list = json_decode(get_url_content($img . $id . "%22%7D", "https"), true);
			$url = substr($url, 1, strlen($url) - 1);
			$cont = get_url_content($url, "https");
			preg_match_all("/name=\"current_price\"[\\s]*value= \"(.*?)\"/", $cont, $res);
			$data["price"] = isset($res[1][0]) ? $res[1][0] : '';
			preg_match_all("/class=\"tb-main-title\"[\\s]*data-title=\"(.*?)\"/", $cont, $res);
			$data["title"] = isset($res[1][0]) ? $res[1][0] : '';
			preg_match_all("/<ul[\\s]*class=\"attributes-list\">(.*?)<\\/ul>/s", $cont, $res);
			$data["content"] = preg_replace("/[\\n\\r]*[\\s]{2,}/", '', $res[1][0]);
			preg_match_all("/location\\.protocol.*:\\s'\\/\\/(.*?)',\\s/", $cont, $res);
			$pic = get_url_content("http://" . $res[1][0]);
			$pic = str_replace("var desc='", '', substr($pic, 0, strlen($pic) - 3));
			$data["content"] .= $pic;
			$data["base_num"] = rand(10, 100);
			if (isset($list["data"]["images"])) {
				for ($i = 0; $i < count($list["data"]["images"]); $i++) {
					$data["content"] .= "<img src='" . $list["data"]["images"][$i] . "' style='max-width: 750.0px;'>";
				}
				$data["html"] = $html;
			} else {
				$data["html"] = $html;
			}
			return json($data);
		}
		return view();
	}
	public function goods_collect_auto()
	{
		$condition["mch_id"] = $this->bus_id;
		$type = request()->post("type");
		$img_arr = request()->post("img_arr");
		if ($type == "uppic") {
			$data["img_size_big"] = "700,700";
			$data["img_spec_big"] = "700,700";
			$data["img_size_mid"] = "360,360";
			$data["img_spec_mid"] = "360,360";
			$data["img_size_small"] = "240,240";
			$data["img_spec_small"] = "240,240";
			$data["img_size_micro"] = "240,240";
			$data["img_spec_micro"] = "240,240";
			$condition["group_name"] = "默认相册";
			$default = db::name("ybmp_images_group")->field("group_id")->where($condition)->find();
			if (empty($default["group_id"])) {
				db::query("insert ims_ybmp_images_group into ('group_name','is_default','sort','create_time','mch_id') VALUES ('默认相册',1,1,'" . time() . "','" . $condition["mch_id"] . "')");
				$gid = db::name("ybmp_images_group")->max("group_id");
			} else {
				$gid = $default["group_id"];
			}
			$img = explode(",", $img_arr);
			$iid = array();
			for ($i = 0; $i < count($img); $i++) {
				$data["img_name"] = time() . "Arliki";
				$data["img_tag"] = "Arliki";
				$data["img_cover"] = $img[$i];
				$data["group_id"] = $gid;
				$i_id = db::name("ybmp_images")->insertGetId($data);
				$iid[$i] = $i_id;
			}
			$res["fid"] = $iid[0];
			$res["imgarr"] = implode(",", $iid);
			return $res["imgarr"];
		}
	}
}
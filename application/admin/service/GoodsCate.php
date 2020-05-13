<?php


namespace app\admin\service;

use app\common\model\Images;
class GoodsCate extends Base
{
	private $goods_cate;
	function __construct()
	{
		parent::__construct();
		$this->goods_cate = new \app\common\model\GoodsCate();
	}
	public function getGoodsGroupList($condition = '')
	{
		$list = $this->goods_cate->getQuerys($condition, "*", '');
		foreach ($list as $k => $v) {
			$img = new Images();
			$pic_info = array();
			$pic_info["pic_cover"] = '';
			if (!empty($v["group_pic"])) {
				$pic_info = $img->get($v["group_pic"]);
			}
			$list[$k]["picture"] = $pic_info;
		}
		return $list;
	}
	public function getFormatGoodsCategoryList($mch_id)
	{
		$one_list = $this->getCategoryTreeUseInShopIndex($mch_id);
		return $one_list;
	}
	public function getCategoryTreeUseInShopIndex($mch_id)
	{
		$goods_category_model = new \app\common\model\GoodsCate();
		$goods_category_one = $goods_category_model->getQuerys(["level" => 1, "mch_id" => $mch_id], "cate_id,cate_name,is_visible,short_name,pid,cate_pic,sort", "sort");
		if (!empty($goods_category_one)) {
			foreach ($goods_category_one as $k_cat_one => $v_cat_one) {
				$goods_category_two_list = $goods_category_model->getQuerys(["level" => 2, "pid" => $v_cat_one["cate_id"]], "cate_id,cate_name,short_name,pid,is_visible,cate_pic,sort", "sort");
				$v_cat_one["count"] = count($goods_category_two_list);
				if (!empty($goods_category_two_list)) {
					foreach ($goods_category_two_list as $k_cat_two => $v_cat_two) {
						$cat_three_list = $goods_category_model->getQuerys(["level" => 3, "pid" => $v_cat_two["cate_id"]], "cate_id,cate_name,short_name,pid,is_visible,cate_pic,sort", "sort");
						$v_cat_two["count"] = count($cat_three_list);
						$v_cat_two["child_list"] = $cat_three_list;
					}
				}
				$v_cat_one["child_list"] = $goods_category_two_list;
			}
		}
		return $goods_category_one;
	}
	public function ModifyGoodsCategoryField($category_id, $field_name, $field_value)
	{
		$res = $this->goods_cate->ModifyTableField("cate_id", $category_id, $field_name, $field_value);
		return $res;
	}
	public function getGoodsCategoryTree($pid, $mch_id)
	{
		$list = array();
		$one_list = $this->getGoodsCategoryListByParentId($pid, $mch_id);
		foreach ($one_list as $k1 => $v1) {
			$two_list = array();
			$two_list = $this->getGoodsCategoryListByParentId($v1["cate_id"], $mch_id);
			$one_list[$k1]["child_list"] = $two_list;
		}
		$list = $one_list;
		return $list;
	}
	public function InfiniteCate($cate_id)
	{
		$cate = new \app\common\model\GoodsCate();
		$cate_list = $cate->where("cate_id", $cate_id)->find();
		if ($cate_list["pid"] == 0) {
			return $cate_list["cate_id"];
		} else {
			$cate_list = $cate->where("cate_id", $cate_list["pid"])->find();
			if ($cate_list["pid"] == 0) {
				return $cate_list["cate_id"];
			} else {
				$cate_list = $cate->where("cate_id", $cate_list["pid"])->find();
				if ($cate_list["pid"] == 0) {
					return $cate_list["cate_id"];
				}
			}
		}
	}
	public function getGoodsCategoryListByParentId($pid, $mch_id)
	{
		$where["pid"] = array("eq", $pid);
		$where["mch_id"] = array("eq", $mch_id);
		$list = $this->getGoodsCategoryList(1, 0, $where, "pid,sort");
		if (!empty($list)) {
			for ($i = 0; $i < count($list["data"]); $i++) {
				$parent_id = $list["data"][$i]["cate_id"];
				$child_list = $this->getGoodsCategoryList(1, 1, "pid=" . $parent_id, "pid,sort");
				if (!empty($child_list) && $child_list["total_count"] > 0) {
					$list["data"][$i]["is_parent"] = 1;
				} else {
					$list["data"][$i]["is_parent"] = 0;
				}
			}
		}
		return $list["data"];
	}
	public function getGoodsCategoryList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = "*")
	{
		$list = $this->goods_cate->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	public function addOrEditGoodsCategory($cate_id, $cate_name, $short_name, $pid, $is_visible, $keywords = '', $description = '', $sort = 0, $cate_pic, $mch_id)
	{
		if ($pid == 0) {
			$level = 1;
		} else {
			$level = $this->getGoodsCategoryDetail($pid)["level"] + 1;
		}
		$data = array("cate_name" => $cate_name, "short_name" => $short_name, "pid" => $pid, "level" => $level, "is_visible" => $is_visible, "keywords" => $keywords, "description" => $description, "sort" => $sort, "cate_pic" => $cate_pic, "create_time" => time(), "mch_id" => $mch_id);
		$dd = array("cate_name" => $cate_name, "pid" => $pid, "level" => $level, "mch_id" => $mch_id);
		if ($cate_id == 0) {
			$find = $this->goods_cate->where($dd)->find();
			if (!empty($find)) {
				return "已有此分类,无法重复创建";
			}
			$result = $this->goods_cate->save($data);
			if ($result) {
				$res = $this->goods_cate->cate_id;
			} else {
				$res = $this->goods_cate->getError();
			}
		} else {
			$res = $this->goods_cate->save($data, ["cate_id" => $cate_id]);
			if ($res !== false) {
				$this->goods_cate->save(["level" => $level + 1], ["pid" => $cate_id]);
				return $res;
			} else {
				$res = $this->goods_cate->getError();
			}
		}
		return $res;
	}
	public function getGoodsCategoryDetail($pid)
	{
		$res = $this->goods_cate->get($pid);
		return $res;
	}
	public function deleteGoodsCategory($cate_id, $mch_id)
	{
		$sub_list = $this->getGoodsCategoryListByParentId($cate_id, $mch_id);
		if (!empty($sub_list)) {
			$res = SYSTEM_DELETE_FAIL;
		} else {
			$res = $this->goods_cate->destroy($cate_id);
		}
		return $res;
	}
	public function getGoodsCateV1($condition = '', $field = "*", $order = '')
	{
		$list = $this->goods_cate->getQuerys($condition, $field, $order);
		return $list;
	}
}
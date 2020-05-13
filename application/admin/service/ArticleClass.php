<?php


namespace app\admin\service;

class ArticleClass extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->article_class = new \app\common\model\ArticleClass();
	}
	public function getArticleClass($condition = '', $field = "*", $order = '')
	{
		$list = $this->article_class->getQuerys($condition, $field, $order);
		return $list;
	}
	public function getClassCategoryDetail($pid)
	{
		$res = $this->article_class->get($pid);
		return $res;
	}
	public function getClassCategoryTree($pid, $mch_id, $dynamic = 2)
	{
		$list = array();
		$one_list = $this->getClassCategoryListByParentId($pid, $mch_id, $dynamic);
		$list = $one_list;
		return $list;
	}
	public function getClassCategoryListByParentId($pid, $mch_id, $dynamic = 2)
	{
		$where["pid"] = array("eq", $pid);
		$where["mch_id"] = array("eq", $mch_id);
		$where["is_del"] = array("eq", 1);
		$where["is_dynamic"] = $dynamic;
		$list = $this->getClassCategoryList(1, 0, $where, "pid,sort");
		if (!empty($list)) {
			for ($i = 0; $i < count($list["data"]); $i++) {
				$parent_id = $list["data"][$i]["class_id"];
				$child_list = $this->getClassCategoryList(1, 1, "pid=" . $parent_id, "pid,sort");
				if (!empty($child_list) && $child_list["total_count"] > 0) {
					$list["data"][$i]["is_parent"] = 1;
				} else {
					$list["data"][$i]["is_parent"] = 0;
				}
			}
		}
		return $list["data"];
	}
	public function getClassCategoryList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = "*")
	{
		$list = $this->article_class->pageQuery($page_index, $page_size, $condition, $order, $field);
		return $list;
	}
	public function getFormatClassCategoryList($mch_id, $dy = 2)
	{
		$one_list = $this->getCategoryTreeUseInShopIndex($mch_id, $dy);
		return $one_list;
	}
	public function getCategoryTreeUseInShopIndex($mch_id, $dy = 2)
	{
		$goods_category_model = new \app\common\model\ArticleClass();
		$goods_category_one = $goods_category_model->getQuerys(["level" => 1, "mch_id" => $mch_id, "is_del" => 1, "is_dynamic" => $dy], "class_id,name,pid,class_img,sort,create_time", "sort");
		if (!empty($goods_category_one)) {
			foreach ($goods_category_one as $k_cat_one => $v_cat_one) {
				$goods_category_two_list = $goods_category_model->getQuerys(["level" => 2, "pid" => $v_cat_one["class_id"], "is_del" => 1, "is_dynamic" => $dy], "class_id,name,pid,class_img,sort,create_time", "sort");
				$v_cat_one["count"] = count($goods_category_two_list);
				if (!empty($goods_category_two_list)) {
					foreach ($goods_category_two_list as $k_cat_two => $v_cat_two) {
						$cat_three_list = $goods_category_model->getQuerys(["level" => 3, "pid" => $v_cat_two["class_id"], "is_del" => 1, "is_dynamic" => $dy], "class_id,name,pid,class_img,sort,create_time", "sort");
						$v_cat_two["count"] = count($cat_three_list);
						$v_cat_two["child_list"] = $cat_three_list;
					}
				}
				$v_cat_one["child_list"] = $goods_category_two_list;
			}
		}
		return $goods_category_one;
	}
	public function addArticleClass($data)
	{
		$res = $this->article_class->save($data);
		return $res;
	}
	public function getArticleClassById($class_id)
	{
		$info = $this->article_class->getInfo(["class_id" => $class_id]);
		return $info;
	}
	public function updateArticleClass($data)
	{
		$res = $this->article_class->save($data, ["class_id" => $data["class_id"]]);
		return $res;
	}
	public function delArticleClass($class_id)
	{
		$article = new \app\common\model\Article();
		$article_class = new \app\common\model\ArticleClass();
		$article_list = $article->getInfo(["class_id" => $class_id]);
		if ($article_list) {
			return "-1";
		} else {
			$res = $article_class->destroy($class_id);
			return $res;
		}
	}
}
<?php


namespace app\api\service;

use think\Db;
use think\Cache;
class ArticleService
{
	private $ac = "ybmp_article_class";
	private $a = "ybmp_article";
	private $g = "ybmp_goods";
	private $i = "ybmp_images";
	public function getArticleClass($data)
	{
		$rs = Db::name($this->ac)->where("mch_id", $data["mch_id"])->where("is_del", 1)->order("sort", "desc")->order("create_time", "desc")->select();
		return $rs;
	}
	public static function InfiniteCate($class_id, $mch_id)
	{
		$rs = Cache::get($mch_id . "class:" . $class_id);
		if ($rs != false) {
			return $rs;
		}
		$rs = array();
		$cate_list = null;
		$cate_list = Db::name("ybmp_article_class")->where("pid", $class_id)->where("is_dynamic", 2)->where("mch_id", $mch_id)->where("is_del", 1)->order("sort", "desc")->order("create_time", "desc")->select();
		foreach ($cate_list as $key => $value) {
			$value["cate"] = self::InfiniteCate($value["class_id"], $mch_id);
			$rs[] = $value;
		}
		Cache::set($mch_id . "class:" . $class_id, $rs, CACHE_TIME);
		return $rs;
	}
	public static function InfiniteArticle($class_id, $mch_id)
	{
		$rs = array();
		$rs[] = $class_id;
		$cate_list = Db::name("ybmp_article_class")->where("pid", $class_id)->where("mch_id", $mch_id)->where("is_dynamic", 2)->where("is_del", 1)->field("class_id")->select();
		foreach ($cate_list as $key => $value) {
			$rs = array_merge($rs, self::InfiniteArticle($value["class_id"], $mch_id));
		}
		return $rs;
	}
	public function getArticle($data, $page)
	{
		unset($data["ident"]);
		$new_data = ["mch_id" => $data["mch_id"]];
		if (isset($data["class_id"])) {
			$new_data["class_id"] = ["in", self::InfiniteArticle($data["class_id"], $data["mch_id"])];
			$class_id = Db::name($this->ac)->where($data)->where("is_del", 1)->find();
			if (empty($class_id)) {
				$rs["article_name"] = "文章列表";
				$rs["class_style"] = 2;
			} else {
				$rs["article_name"] = $class_id["name"];
				$rs["class_style"] = $class_id["class_style"];
			}
		} else {
			$rs["article_name"] = "文章列表";
			$one_ac = Db::name($this->ac)->where(["mch_id" => $data["mch_id"], "is_del" => 1])->order("class_id asc")->find();
			$rs["class_style"] = $one_ac["class_style"] ? $one_ac["class_style"] : 2;
		}
		$info = Db::name($this->a)->where($new_data)->where(["status" => 2, "is_dynamic" => 2, "type" => 1])->page($page, PAGE_NUM)->order("sort", "desc")->order("create_time", "desc")->select();
		if (empty($info)) {
			return $rs;
		}
		foreach ($info as $key => $value) {
			$info[$key]["create_time"] = date("Y-m-d H:i:s", $value["create_time"]);
		}
		$rs["info"] = $info;
		return $rs;
	}
	public function getArticleInfo($data)
	{
		if (isset($data["ident"])) {
			$rs = Db::name($this->a)->where("mch_id", $data["mch_id"])->where("ident", $data["ident"])->find();
		} else {
			$rs = Db::name($this->a)->where("article_id", $data["article_id"])->find();
		}
		if (empty($rs)) {
			return $rs;
		}
		Db::name($this->a)->where("article_id", $data["article_id"])->setInc("click");
		$rs["create_time"] = date("Y-m-d H:i:s", $rs["create_time"]);
		$goods_id = explode(",", $rs->goods_array);
		$goods_info = array();
		foreach ($goods_id as $key => $value) {
			$goods_info[$key] = Db::name($this->g)->field("goods_id,goods_name,price,images,introduction")->where("goods_id", $value)->where("is_del", 0)->find();
			if (empty($goods_info[$key])) {
				unset($goods_info[$key]);
				continue;
			}
			$pic = Db::name($this->i)->where("img_id", $goods_info[$key]->images)->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			$goods_info[$key]["pic"] = $pic;
		}
		$rs["goods"] = $goods_info;
		return $rs;
	}
}
<?php


namespace app\app\service;

use app\common\model\Goods;
use app\common\model\Images;
use app\common\model\GoodsSku;
use app\common\model\GoodsAttr;
use app\common\model\GoodsCate;
use app\common\model\GoodsSpecFormat;
use think\Cache;
use think\Db;
class GoodsService
{
	public function getGoodsList($data, $order, $by, $page = 1)
	{
		if (isset($data["cate_id"])) {
			$data["cate_id"] = ["in", self::InfiniteGoods($data["cate_id"], $data["mch_id"])];
		}
		$goods = new Goods();
		$goods_list = null;
		$goods_list = $goods->field("goods_id,goods_name,price,introduction,images,stock,cate_id")->where($data)->where("state", 1)->where("is_del", 0)->page($page, PAGE_NUM)->order($order, $by)->order("sort", "asc")->select();
		if (empty($goods_list)) {
			return $goods_list;
		}
		foreach ($goods_list as $value) {
			$imgas = new Images();
			$pic = $imgas->where("img_id", $value->images)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$value["goods_picture"] = __IMG($pic->toArray()["img_cover_big"]);
			}
			$goods_sku = new GoodsSku();
			$value["sku"] = $goods_sku->where("goods_id", $value->goods_id)->select();
			foreach ($value["sku"] as $s_value) {
				$s_imgas = new Images();
				$s_pic = $s_imgas->where("img_id", $s_value->images)->field("img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($s_pic) {
					$s_value["sku_pic"] = __IMG($pic->toArray()["img_cover_big"]);
				}
			}
		}
		return $goods_list;
	}
	public function checkGoods($data)
	{
		unset($data["stock"]);
		$goods = new Goods();
		$goods_info = $goods->field("goods_id,goods_name,price,introduction,images,stock")->where($data)->where("state", 1)->where("is_del", 0)->find();
		return $goods_info;
	}
	public function updateStock($goods_id, $data)
	{
		$rs = array();
		$goods_id = intval($goods_id);
		if ($goods_id <= 0) {
			return $rs;
		}
		if (empty($data)) {
			return $rs;
		}
		Db::startTrans();
		try {
			foreach ($data as $key => $value) {
				$sku_model = new GoodsSku();
				$sku_model->allowField(true)->save($value, ["sku_id" => $key]);
			}
			$sku_stock = $sku_model->field("goods_id,sku_name,sku_id,stock")->where("goods_id", $goods_id)->select();
			$stock = 0;
			foreach ($sku_stock as $v) {
				$stock += $v["stock"];
			}
			$data_sku = array("stock" => $stock);
			$model = new Goods();
			$model->allowField(true)->save($data_sku, ["goods_id" => $goods_id]);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return 1;
	}
	public static function InfiniteCate($cate_id, $mch_id)
	{
		$rs = Cache::get($mch_id . "cate:" . $cate_id);
		if ($rs != false) {
			return $rs;
		}
		$rs = array();
		$cate = new GoodsCate();
		$cate_list = null;
		$cate_list = $cate->where("pid", $cate_id)->where("mch_id", $mch_id)->where("is_visible", 1)->order("sort", "desc")->select();
		foreach ($cate_list as $key => $value) {
			$value->cate_pic = __IMG($value->cate_pic);
			$value->cate = self::InfiniteCate($value->cate_id, $mch_id);
			$rs[] = $value;
		}
		Cache::set($mch_id . "cate:" . $cate_id, $rs, CACHE_TIME);
		return $rs;
	}
	public static function InfiniteGoods($cate_id, $mch_id)
	{
		$rs = array();
		$cate = new GoodsCate();
		$rs[] = $cate_id;
		$cate_list = $cate->where("pid", $cate_id)->where("mch_id", $mch_id)->where("is_visible", 1)->field("cate_id")->select();
		foreach ($cate_list as $key => $value) {
			$rs = array_merge($rs, self::InfiniteGoods($value->cate_id, $mch_id));
		}
		return $rs;
	}
}
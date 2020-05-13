<?php


namespace app\api\service;

use think\Db;
require_once BASE_ROOT . "core/application/common.php";
class CartService
{
	private $g = "ybmp_goods";
	private $c = "ybmp_cart";
	private $i = "ybmp_images";
	private $s = "ybmp_goods_sku";
	public function getCart($data, $page = 1)
	{
		$cart_goods_list = null;
		$cart_goods_list = Db::name($this->c)->where($data)->page($page, PAGE_NUM)->order("create_time", "desc")->select();
		foreach ($cart_goods_list as $key => $value) {
			$pic = Db::name($this->i)->where("img_id", $value["goods_images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$cart_goods_list[$key]["pic"] = $pic;
			}
			$goods_info = Db::name($this->g)->where("goods_id", $value["goods_id"])->where("is_del", 0)->find();
			if (empty($goods_info)) {
				continue;
			}
			$cart_goods_list[$key]["sku"] = Db::name($this->s)->where("sku_id", $value["sku_id"])->field("price,sku_name,stock")->find();
		}
		return $cart_goods_list;
	}
	public function addCart($data)
	{
		$info = Db::name($this->c)->where("sku_id", $data["sku_id"])->where("goods_id", $data["goods_id"])->where("buyer_id", $data["buyer_id"])->where("mch_id", $data["mch_id"])->find();
		if (!empty($info)) {
			$rs = Db::name($this->c)->where("cart_id", $info["cart_id"])->setInc("num", $data["num"]);
		} else {
			$rs = Db::name($this->c)->insert($data);
		}
		return $rs;
	}
	public function cartNum($data)
	{
		$rs = Db::name($this->c)->where("cart_id", $data["cart_id"])->update(["num" => $data["num"]]);
		return $rs;
	}
	public function delCart($data)
	{
		$rs = Db::name($this->c)->delete($data["cart_id"]);
		return $rs;
	}
}
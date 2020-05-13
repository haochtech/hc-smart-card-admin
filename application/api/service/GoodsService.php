<?php


namespace app\api\service;

use think\Cache;
use think\Db;
require_once BASE_ROOT . "core/application/common.php";
class GoodsService
{
	private $g = "ybmp_goods";
	private $c = "ybmp_cart";
	private $f = "ybmp_user_favorites";
	private $b = "ybmp_goods_brand";
	private $i = "ybmp_images";
	private $s = "ybmp_goods_sku";
	private $cate = "ybmp_goods_cate";
	private $a = "ybmp_user_address";
	private $hd = "ybmp_business_activity";
	private $dq = "ybmp_area";
	private $attr = "ybmp_goods_attr";
	private $spec = "ybmp_goods_spec_format";
	public function getGoodsList($data, $order, $by, $page = 1)
	{
		if (isset($data["cate_id"])) {
			$data["cate_id"] = ["in", self::InfiniteGoods($data["cate_id"], $data["mch_id"])];
		}
		$goods_list = null;
		$goods_list = Db::name($this->g)->field("goods_id,goods_name,price,introduction,images,cate_id")->where($data)->where("state", 1)->where("is_del", 0)->page($page, PAGE_NUM)->order($order, $by)->order("sort", "asc")->select();
		if (empty($goods_list)) {
			return $goods_list;
		}
		foreach ($goods_list as $k => $value) {
			$pic = Db::name($this->i)->where("img_id", $value["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$goods_list[$k]["pic"] = $pic;
			}
		}
		return $goods_list;
	}
	public function getGoodsDetail($data, $uid)
	{
		Db::name($this->g)->where("goods_id", $data["goods_id"])->setInc("clicks");
		$goods_detail = Db::name($this->g)->where($data)->where("state", 1)->where("is_del", 0)->find();
		if (empty($goods_detail)) {
			return null;
		}
		$shipping_fee_id = $goods_detail["shipping_fee_id"];
		if (!empty($shipping_fee_id)) {
			$post_fee = Db::name("ybmp_express_shipping")->where(["shipping_fee_id" => $shipping_fee_id])->field("bynum_snum,bynum_sprice,bynum_xnum,bynum_xprice")->find();
			$goods_detail["post_fee"] = $post_fee;
		}
		if ($uid != '') {
			$fav_c = Db::name($this->f)->where("fav_id", $data["goods_id"])->where("uid", $uid)->find();
			if (!empty($fav_c)) {
				$goods_detail["favorites"] = true;
			} else {
				$goods_detail["favorites"] = false;
			}
			$goods_detail["cart"] = Db::name($this->c)->where("buyer_id", $uid)->where("mch_id", $data["mch_id"])->sum("num");
		}
		$goods_detail["goods_brand"] = Db::name($this->b)->where("brand_id", $goods_detail["brand_id"])->select();
		$goods_pic = Db::name($this->i)->field("img_id,img_cover,img_cover_big,img_cover_mid,img_cover_small")->where("img_id", "in", $goods_detail["img_id_array"])->select();
		$s = array();
		$e = explode(",", $goods_detail["img_id_array"]);
		for ($j = 0; $j < count($e); $j++) {
			for ($i = 0; $i < count($goods_pic); $i++) {
				if ($e[$j] == $goods_pic[$i]["img_id"]) {
					array_push($s, $goods_pic[$i]);
					break;
				}
			}
		}
		$goods_detail["pic"] = $s;
		$goods_detail["sku"] = Db::name($this->s)->where("goods_id", $data["goods_id"])->order("sku_id")->select();
		foreach ($goods_detail["sku"] as $key => $value) {
			$pic = Db::name($this->i)->where("img_id", $value["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			$goods_detail["sku"][$key]["pic"] = $pic;
		}
		if (!empty($goods_detail["goods_attribute"])) {
			$goods_detail["goods_attr"] = json_decode($goods_detail["goods_attribute"], true);
			$attrs = array();
			$attrs_name = array();
			foreach ($goods_detail["goods_attr"] as $attr_item) {
				if (empty($attrs[$attr_item["attr_value_id"]])) {
					$attrs_name[$attr_item["attr_value_id"]] = $attr_item["attr_value"];
					$attrs[$attr_item["attr_value_id"]] = $attr_item["attr_value_name"];
				} else {
					$attrs[$attr_item["attr_value_id"]] .= "," . $attr_item["attr_value_name"];
				}
			}
			$goods_detail["goods_attr"] = array();
			foreach ($attrs as $ak => $av) {
				$aitem = array();
				$aitem["attr_value_id"] = $ak;
				$aitem["attr_value"] = $attrs_name[$ak];
				$aitem["attr_value_name"] = $av;
				$goods_detail["goods_attr"][] = $aitem;
			}
		} else {
			$goods_detail["goods_attr"] = [];
		}
		if (!empty($goods_detail["goods_spec_format"])) {
			$goods_detail["goods_spec_format"] = json_decode($goods_detail["goods_spec_format"], true);
		} else {
			$goods_detail["goods_spec_format"] = [];
		}
		$activity_info = Db::name($this->hd)->where("mch_id", $data["mch_id"])->order("satisfy_money", "desc")->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->select();
		foreach ($activity_info as $act_k => $act_v) {
			$activity_info[$act_k]["star_time"] = date("Y-m-d", $act_v["star_time"]);
			$activity_info[$act_k]["end_time"] = date("Y-m-d", $act_v["end_time"]);
			$activity_info[$act_k]["satisfy_money"] = intval($act_v["satisfy_money"]);
			$activity_info[$act_k]["discount_money"] = intval($act_v["discount_money"]);
		}
		$goods_detail["activity"] = $activity_info;
		Cache::set("goods:" . $data["goods_id"], $goods_detail, CACHE_TIME);
		return $goods_detail;
	}
	public function cateGoods($mch_id)
	{
		$cate_info = Db::name($this->cate)->where("pid", 0)->where("mch_id", $mch_id)->select();
		foreach ($cate_info as $key => $value) {
			$goods_list = Db::name($this->g)->field("goods_id,goods_name,price,introduction,images,sales,min_buy,stock")->where("cate_id", $value["cate_id"])->where("mch_id", $mch_id)->where("state", 1)->where("is_del", 0)->order("sort", "asc")->select();
			if (!empty($goods_list)) {
				foreach ($goods_list as $k1 => $g_v) {
					$pic = Db::name($this->i)->where("img_id", $g_v["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
					if ($pic) {
						$goods_list[$k1]["pic"] = $pic;
					}
				}
			}
			$cate_info[$key]["goods_list"] = $goods_list;
		}
		return $cate_info;
	}
	public function goodsClicks($data)
	{
		$rs = Db::name($this->g)->where("goods_id", $data)->setInc("clicks");
		return $rs;
	}
	public function Favorites($data)
	{
		$info = Db::name($this->f)->where($data)->find();
		if (!empty($info)) {
			$rs = Db::name($this->f)->where($data)->delete();
			$res["status"] = 0;
			$res["rs"] = $rs;
		} else {
			$data["fav_time"] = time();
			$rs = Db::name($this->f)->insert($data);
			$res["status"] = 1;
			$res["rs"] = $rs;
		}
		return $res;
	}
	public function delFavorites($data)
	{
		$rs = Db::name($this->f)->delete($data["log_id"]);
		return $rs;
	}
	public function getFavorites($data, $page)
	{
		$fav_list = null;
		$fav_list = Db::name($this->f)->where($data)->page($page, PAGE_NUM)->select();
		if (empty($fav_list)) {
			return $fav_list;
		}
		foreach ($fav_list as $key => $value) {
			$goods_info = Db::name($this->g)->where("goods_id", $value["fav_id"])->where("is_del", 0)->find();
			if (empty($goods_info)) {
				Db::name($this->f)->delete($value["log_id"]);
				unset($fav_list[$key]);
				continue;
			}
			$fav_list[$key]["goods"] = $goods_info;
			$pic = Db::name($this->i)->where("img_id", $goods_info["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$fav_list[$key]["pic"] = $pic;
			}
		}
		return $fav_list;
	}
	public function getGoods($data)
	{
		$map = ["goods_id" => $data["goods_id"], "sku_id" => $data["sku_id"]];
		$info = Db::name($this->s)->where($map)->find();
		if (empty($info)) {
			return null;
		}
		$info["goods"] = Db::name($this->g)->field("goods_name,images,shipping_fee_id,shipping_fee")->where("goods_id", $info["goods_id"])->where("is_del", 0)->find();
		if (empty($info["goods"])) {
			return null;
		}
		$shipping_fee_id = $info["goods"]["shipping_fee_id"];
		if (!empty($shipping_fee_id)) {
			$post_fee = Db::name("ybmp_express_shipping")->where(["shipping_fee_id" => $shipping_fee_id])->field("bynum_snum,bynum_sprice,bynum_xnum,bynum_xprice")->find();
			$info["post_fee"] = $post_fee;
		}
		if (empty($info->images)) {
			$pic = Db::name($this->i)->where("img_id", $info["goods"]["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
		} else {
			$pic = Db::name($this->i)->where("img_id", $info["images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
		}
		if ($pic) {
			$info["pic"] = $pic;
		}
		$address = Db::name($this->a)->field("consigner,phone,address,area")->where("uid", $data["uid"])->where("is_default ", 1)->find();
		if (empty($address)) {
			$address = Db::name($this->a)->field("consigner,phone,address,area")->where("uid", $data["uid"])->order("create_time", "desc")->find();
		}
		$res = Db::name($this->dq)->where("id", $address["area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$address["province"] = $pro["name"];
		$address["city"] = $city["name"];
		$address["district"] = $res["name"];
		$info["address"] = $address;
		$activity_info = Db::name($this->hd)->where("mch_id", $data["mch_id"])->where("is_use", 1)->order("satisfy_money", "desc")->where("star_time", "<", time())->where("end_time", ">", time())->select();
		foreach ($activity_info as $act_k => $act_v) {
			$activity_info[$act_k]["star_time"] = $this->__TIME($act_v["star_time"]);
			$activity_info[$act_k]["end_time"] = $this->__TIME($act_v["end_time"]);
		}
		$info["activity"] = $activity_info;
		return $info;
	}
	public function cartGoods($data)
	{
		$data["cart_id"] = rtrim($data["cart_id"], ",");
		$cart_id = explode(",", $data["cart_id"]);
		foreach ($cart_id as $key => $value) {
			$cart_info = Db::name($this->c)->where("cart_id", $value)->where("buyer_id", $data["uid"])->find();
			if (empty($cart_info)) {
				continue;
			}
			$cart_info["goods"] = Db::name($this->g)->field("goods_name,images,shipping_fee_id,shipping_fee")->where("goods_id", $cart_info["goods_id"])->where("is_del", 0)->find();
			if (empty($cart_info["goods"])) {
				continue;
			}
			$shipping_fee_id = $cart_info["goods"]["shipping_fee_id"];
			if (!empty($shipping_fee_id)) {
				$post_fee = Db::name("ybmp_express_shipping")->where(["shipping_fee_id" => $shipping_fee_id])->field("bynum_snum,bynum_sprice,bynum_xnum,bynum_xprice")->find();
				$cart_info["post_fee"] = $post_fee;
			}
			$pic = Db::name($this->i)->where("img_id", $cart_info["goods_images"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
			if ($pic) {
				$cart_info["pic"] = $pic;
			}
			$cart_info["price"] = Db::name($this->s)->where("sku_id", $cart_info["sku_id"])->field("price,market_price,promote_price,cost_price,sku_name")->find();
			$info["info"][$key] = $cart_info;
		}
		if (empty($info)) {
			return null;
		}
		$address = Db::name($this->a)->field("consigner,phone,address,area")->where("uid", $data["uid"])->where("is_default ", 1)->find();
		if (empty($address)) {
			$address = Db::name($this->a)->field("consigner,phone,address,area")->where("uid", $data["uid"])->order("create_time", "desc")->find();
		}
		$res = Db::name($this->dq)->where("id", $address["area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$address["province"] = $pro["name"];
		$address["city"] = $city["name"];
		$address["district"] = $res["name"];
		$info["address"] = $address;
		$activity_info = Db::name($this->hd)->where("mch_id", $data["mch_id"])->order("satisfy_money", "desc")->where("is_use", 1)->where("star_time", "<", time())->where("end_time", ">", time())->select();
		foreach ($activity_info as $act_k => $act_v) {
			$activity_info[$act_k]["star_time"] = $this->__TIME($act_v["star_time"]);
			$activity_info[$act_k]["end_time"] = $this->__TIME($act_v["end_time"]);
		}
		$info["activity"] = $activity_info;
		return $info;
	}
	public static function InfiniteCate($cate_id, $mch_id)
	{
		$rs = Cache::get($mch_id . "cate:" . $cate_id);
		if ($rs != false) {
			return $rs;
		}
		$rs = array();
		$cate_list = null;
		$cate_list = Db::name("ybmp_goods_cate")->where("pid", $cate_id)->where("mch_id", $mch_id)->where("is_visible", 1)->order("sort", "asc")->select();
		foreach ($cate_list as $key => $value) {
			$value["cate_pic"] = self::__IMG($value["cate_pic"]);
			$value["cate"] = self::InfiniteCate($value["cate_id"], $mch_id);
			$rs[] = $value;
		}
		Cache::set($mch_id . "cate:" . $cate_id, $rs, CACHE_TIME);
		return $rs;
	}
	public static function InfiniteGoods($cate_id, $mch_id)
	{
		$rs = array();
		$rs[] = $cate_id;
		$cate_list = Db::name("ybmp_goods_cate")->where("pid", $cate_id)->where("mch_id", $mch_id)->where("is_visible", 1)->field("cate_id")->select();
		if ($cate_list) {
			foreach ($cate_list as $key => $value) {
				$rs = array_merge($rs, self::InfiniteGoods($value["cate_id"], $mch_id));
			}
		}
		return $rs;
	}
	public function __IMG($img_path)
	{
		$path = '';
		if (!empty($img_path)) {
			if (stristr($img_path, "http://") === false && stristr($img_path, "https://") === false) {
				$path = "http://" . $_SERVER["HTTP_HOST"] . "/" . $img_path;
			} else {
				$path = $img_path;
			}
		}
		return $path;
	}
	public function __TIME($time)
	{
		$date = '';
		if (!empty($time) && $time != 0) {
			$date = date("Y-m-d H:i:s", $time);
		}
		return $date;
	}
}
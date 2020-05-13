<?php


namespace app\api\controller;

use think\Request;
use app\api\service\GoodsService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Goods extends BaseController
{
	public function GoodsList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["cate_id" => Request::instance()->param("cate_id"), "is_recommend" => Request::instance()->param("isrecommand", 0), "is_hot" => Request::instance()->param("ishot", 0), "is_new" => Request::instance()->param("isnew", 0), "goods_name" => ["like", "%" . Request::instance()->param("keywords") . "%"], "mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = Request::instance()->param("order");
		$by = Request::instance()->param("by");
		$page = Request::instance()->param("page", 1);
		$data = array_filter($data);
		$goods = new GoodsService();
		$info = $goods->getGoodsList($data, $order, $by, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GoodsDetail()
	{
		$rs = array("code" => 0, "info" => array());
		$goods_id = Request::instance()->param("goods_id");
		$uid = Request::instance()->param("uid", '');
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["goods_id" => $goods_id, "mch_id" => $mch_id];
		$rule = [["goods_id", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->getGoodsDetail($data, $uid);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "该商品已不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CateGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->cateGoods($mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GoodsClicks()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["goods_id" => Request::instance()->param("goods_id")];
		$rule = [["goods_id", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$cart = new GoodsService();
		$info = $cart->goodsClicks($data["goods_id"]);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "点击次数添加失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetCate()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$info = GoodsService::InfiniteCate(0, $mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Favorites()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["fav_id" => Request::instance()->param("fav_id"), "uid" => Request::instance()->param("uid")];
		$rule = [["fav_id", "require|number"], ["uid", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->Favorites($data);
		if (empty($info["rs"])) {
			$rs["code"] = 1;
			$rs["msg"] = "收藏操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelFavorites()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["log_id" => Request::instance()->param("log_id")];
		$rule = [["log_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->delFavorites($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "收藏商品删除失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["goods_id" => Request::instance()->param("goods_id"), "sku_id" => Request::instance()->param("sku_id"), "uid" => Request::instance()->param("uid"), "mch_id" => $mch_id];
		$rule = [["goods_id", "require|number"], ["sku_id", "require|number"], ["uid", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->getGoods($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "商品不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CartGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["cart_id" => Request::instance()->param("cart_id"), "uid" => Request::instance()->param("uid"), "mch_id" => $mch_id];
		$rule = [["cart_id", "require"], ["uid", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$info = $goods->cartGoods($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "商品不存在";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
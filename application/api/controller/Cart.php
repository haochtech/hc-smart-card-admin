<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
use think\Request;
use think\Log;
use app\api\service\CartService;
class Cart extends BaseController
{
	public function Cart()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$page = Request::instance()->param("page", 1);
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["buyer_id", "require", "uid不能为空"], ["mch_id", "require", "不存在商户"]];
		$data = ["buyer_id" => $uid, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$cart = new CartService();
		$info = $cart->getCart($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AddCart()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["buyer_id" => Request::instance()->param("buyer_id"), "goods_id" => Request::instance()->param("goods_id"), "goods_name" => Request::instance()->param("goods_name"), "sku_id" => Request::instance()->param("sku_id"), "num" => Request::instance()->param("num"), "goods_images" => Request::instance()->param("goods_images"), "mch_id" => $mch_id, "create_time" => time()];
		$rule = [["buyer_id", "require|number"], ["goods_id", "require|number"], ["goods_name", "require"], ["sku_id", "require|number"], ["num", "require|number|>:0"], ["goods_images", "require|number"], ["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$cart = new CartService();
		$info = $cart->addCart($data);
		if (empty($info)) {
			Log::write("添加购物车失败 --" . $data["buyer_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "添加购物车失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function CartNum()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["cart_id" => Request::instance()->param("cart_id"), "num" => Request::instance()->param("num")];
		$rule = [["cart_id", "require|number"], ["num", "require|number|>:0"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$cart = new CartService();
		$info = $cart->cartNum($data);
		if (empty($info)) {
			Log::write("商品数量修改失败 --" . $data["cart_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "商品数量修改失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function DelCart()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["cart_id" => Request::instance()->param("cart_id")];
		$rule = [["cart_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$cart = new CartService();
		$info = $cart->delCart($data);
		if (empty($info)) {
			Log::write("购物车商品删除失败 --" . $data["cart_id"]);
			$rs["code"] = 1;
			$rs["msg"] = "购物车商品删除失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
<?php


namespace app\app\controller;

use think\Db;
use think\Request;
use app\app\service\GoodsService;
class Goods extends BaseController
{
	public function CateGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$order = Request::instance()->param("order", 0);
		$by = Request::instance()->param("by", "desc");
		$cid = Request::instance()->param("cid", 0);
		$page = Request::instance()->param("page", 1);
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$mch_id = $app_id;
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order_name = "sort";
		if ($order == 1) {
			$order_name = "real_sales";
		} elseif ($order == 2) {
			$order_name = "stock";
		} elseif ($order == 3) {
			$order_name = "state";
		}
		$where["mch_id"] = $mch_id;
		$where["is_del"] = 0;
		if ($cid != 0) {
			$where["cate_id"] = $cid;
		}
		$cate_info = Db::name("ybmp_goods_cate")->where("pid", 0)->where("mch_id", $mch_id)->select();
		$goods = $value["goods_list"] = Db::name("ybmp_goods")->field("goods_id,state,goods_name,price,introduction,images,real_sales,sales,min_buy,stock")->where($where)->order($order_name, $by)->order("goods_id", "desc")->page($page)->select();
		foreach ($goods as &$g_v) {
			$pic = Db::name("ybmp_images")->where("img_id", $g_v["images"])->field("img_cover")->find();
			$g_v["img"] = $pic["img_cover"];
		}
		$dd["cate"] = $cate_info;
		$dd["goods"] = $goods;
		$rs["info"] = $dd;
		return json_encode($rs);
	}
	public function ModifyState()
	{
		$rs = array("code" => 0, "info" => array());
		$goods_id = Request::instance()->param("goods_id");
		$state = Request::instance()->param("state");
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$rule = [["mch_id", "require", "不存在商户"], ["goods_id", "require"], ["state", "require", "回复不能为空"]];
		$new_state = $state == 0 ? 1 : 0;
		$data = ["goods_id" => $goods_id, "mch_id" => $app_id, "state" => $state];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$info = Db::name("ybmp_goods")->where($data)->update(["state" => $new_state]);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "操作失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GoodsList()
	{
		$rs = array("code" => 0);
		$app_id = Request::instance()->param("app_id");
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
	public function GoodsStock()
	{
		$rs = array("code" => 0);
		$goods_id = Request::instance()->param("goods_id");
		$stock = Request::instance()->param("stock");
		$app_id = Request::instance()->param("app_id");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "stock" => $stock, "goods_id" => $goods_id];
		$rule = [["mch_id", "require", "不存在商户"], ["goods_id", "require"], ["stock", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$goods = new GoodsService();
		$check = $goods->checkGoods($data);
		if (empty($check)) {
			$rs["code"] = 1;
			$rs["msg"] = "商品不存在";
			return json_encode($rs);
		}
		$stock = explode(",", $stock);
		$stock = array_filter($stock);
		$sku_id = array();
		foreach ($stock as $key => $value) {
			$data = array("stock" => intval(explode(":", $value)[1]));
			$sku_id[explode(":", $value)[0]] = $data;
		}
		$info = $goods->updateStock($goods_id, $sku_id);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "商品库存更新失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function GetCate()
	{
		$rs = array("code" => 0);
		$app_id = Request::instance()->param("app_id");
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
	public function AddGoods()
	{
		$rs = array("code" => 0);
		$app_id = Request::instance()->param("app_id");
		$cate_id = Request::instance()->param("cate_id");
		$goods_name = Request::instance()->param("goods_name");
		$introduction = Request::instance()->param("introduction");
		$keywords = Request::instance()->param("keywords");
		$market_price = Request::instance()->param("market_price");
		$price = Request::instance()->param("price");
		$cost_price = Request::instance()->param("cost_price");
		$sales = Request::instance()->param("sales");
		$clicks = Request::instance()->param("clicks");
		$img_arr = Request::instance()->param("img_id_array");
		$description = Request::instance()->param("description");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "cate_id" => $cate_id, "goods_name" => $goods_name, "introduction" => $introduction, "keywords" => $keywords, "market_price" => $market_price, "price" => $price, "cost_price" => $cost_price, "sales" => $sales, "clicks" => $clicks, "img_id_array" => $img_arr, "description" => $description];
		$rule = [["mch_id", "require", "不存在商户"], ["cate_id", "require", "分类不能为空"], ["goods_name", "require", "名称不能为空"], ["price", "require", "销售价格不能为空"], ["img_id_array", "require", "图片不能为空"], ["description", "require", "商品详情不能为空"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$res = Db::name("ybmp_goods")->insert($data);
		if ($res) {
			$rs["code"] = 0;
			$rs["msg"] = "添加成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "添加失败";
		}
		return json_encode($rs);
	}
}
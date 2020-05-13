<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use think\Cache;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Product extends BaseController
{
	public function productClass()
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
		$info = Cache::get($mch_id . "_product_class");
		if ($info != false) {
			$rs["info"] = $info;
			json_encode($rs);
		}
		$info = array();
		$cate_list = Db::name("ybmp_product_class")->where("mch_id", $mch_id)->order("sort asc")->select();
		foreach ($cate_list as $value) {
			$info[] = $value;
		}
		Cache::set($mch_id . "_product_class", $rs, CACHE_TIME);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function product_list()
	{
		$rs = array("code" => 0, "info" => array());
		$class_id = Request::instance()->param("class_id", 0);
		$app_id = Request::instance()->param("i");
		$page = Request::instance()->param("page", 1);
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "class_id" => $class_id];
		$data = array_filter($data);
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$where["mch_id"] = $mch_id;
		$where["status"] = 1;
		if ($class_id > 0) {
			$where["class_id"] = $class_id;
		}
		$info = Db::name("ybmp_product")->where($where)->order("sort asc")->field("id,title,class_id,image")->order("sort asc")->page($page, 20)->select();
		if (empty($info)) {
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function product_info()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$info = Db::name("ybmp_product")->where(["id" => $id, "mch_id" => $mch_id])->find();
		if (empty($info)) {
			$rs["msg"] = "暂无文章";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
<?php


namespace app\api\controller;

use app\api\service\AddonsService;
use think\Request;
class Addons extends BaseController
{
	public function GetAddons()
	{
		$rs = array("code" => 0, "info" => array());
		$addons = Request::instance()->param("addons");
		$method = Request::instance()->param("method");
		$param = Request::instance()->param("param");
		$data = ["addons" => $addons, "method" => $method];
		$rule = [["addons", "require"], ["method", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$class = get_addon_class($addons, "addon_api");
		if (!class_exists($class)) {
			$rs["code"] = 1;
			$rs["msg"] = "插件实例化失败";
			return json_encode($rs);
		}
		$obj = new $class();
		$param = json_decode($param, true);
		$info = $obj->{$method}($param);
		if ($info["code"] > 0) {
			if ($info["msg"] == "插件未安装") {
				$rs["code"] = 2;
				$rs["msg"] = $info["msg"];
			} else {
				$rs["code"] = 1;
				$rs["msg"] = $info["msg"];
			}
			return json_encode($rs);
		}
		$rs["info"] = $info["info"];
		return json_encode($rs);
	}
	public function AddonsList()
	{
		$rs = array("code" => 0, "info" => array());
		$page = request()->param("page", 1);
		$addons = new AddonsService();
		$info = $addons->getAddonsList($page);
		if ($info == "error") {
			$rs["code"] = 1;
			$rs["msg"] = $info;
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
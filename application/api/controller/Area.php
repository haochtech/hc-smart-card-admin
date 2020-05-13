<?php


namespace app\api\controller;

use app\api\service\AddressService;
use think\Request;
use think\Db;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Area extends BaseController
{
	public function GetArea()
	{
		$area = new AddressService();
		$area_list = $area->getArea();
		return json_encode($area_list);
	}
	public function UserAddress()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		if (empty($uid)) {
			$rs["code"] = 1;
			$rs["msg"] = "未获取到用户信息";
			return json_encode($rs);
		}
		$area = new AddressService();
		$rs["info"] = $area->getUserAddress($uid);
		return json_encode($rs);
	}
}
<?php


namespace app\api\controller;

use app\api\service\AdvertService;
use Think\Request;
class Advert extends BaseController
{
	public function Advert()
	{
		$rs = array("code" => 0, "info" => array());
		$advert = new AdvertService();
		$info = $advert->getAdvert();
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AdvertClicks()
	{
		$rs = array("code" => 0, "info" => array());
		$data = ["adv_id" => Request::instance()->param("adv_id")];
		$rule = [["adv_id", "require|number"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$advert = new AdvertService();
		$info = $advert->AdvertClicks($data);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "点击次数添加失败";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
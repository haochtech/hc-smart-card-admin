<?php


namespace app\api\service;

use app\common\model\Advert;
use app\common\model\AdvertPosition;
class AdvertService
{
	public function getAdvert()
	{
		$advert_position = new AdvertPosition();
		$advert_position_list = null;
		$advert_position_list = $advert_position->where("is_use", 1)->select();
		$advert = new Advert();
		foreach ($advert_position_list as $value) {
			$value["advert"] = $advert->where("is_use", 1)->where("ap_id", $value->ap_id)->select();
			foreach ($value["advert"] as $v) {
				$v->adv_image = __IMG($v->adv_image);
			}
		}
		return $advert_position_list;
	}
	public function AdvertClicks($data)
	{
		$advert = new Advert();
		$rs = $advert->where($data)->setInc("click_num");
		return $rs;
	}
}
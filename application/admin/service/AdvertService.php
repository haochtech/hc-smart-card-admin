<?php


namespace app\admin\service;

use app\common\model\Advert;
use app\common\model\AdvertPosition;
class AdvertService extends Base
{
	function __construct()
	{
		parent::__construct();
	}
	public function getAdvertPosition($condition = '', $search_text, $order)
	{
		$advert_position = new AdvertPosition();
		$rs = $advert_position->getPageLisy($condition, $search_text, $order);
		return $rs;
	}
	public function addHeight($adv_id, $info)
	{
		$advert = new Advert();
		$data = array("adv_height" => $info);
		$res = $advert->save($data, ["adv_id" => $adv_id]);
		return $res;
	}
	public function addAdvertPosition($mch_id, $ap_intro, $ap_name, $sort, $height, $width)
	{
		$advert_position = new AdvertPosition();
		$data = array("ap_name" => $ap_name, "ap_intro" => $ap_intro, "create_time" => time(), "sort" => $sort, "mch_id" => $mch_id, "height" => $height, "width" => $width);
		$res = $advert_position->save($data);
		return $res;
	}
	public function setAdvertPositionUse($ap_id, $is_use)
	{
		$advert_position = new AdvertPosition();
		$data = array("is_use" => $is_use);
		$res = $advert_position->save($data, ["ap_id" => $ap_id]);
		return $res;
	}
	public function getAdvertPositionDetail($ap_id)
	{
		$advert_position = new AdvertPosition();
		$info = $advert_position->getInfo(["ap_id" => $ap_id]);
		return $info;
	}
	public function updateAdvertPositionDetail($mch_id, $ap_id, $ap_name, $ap_intro, $sort, $height, $width)
	{
		$advert_position = new AdvertPosition();
		$data = array("ap_name" => $ap_name, "ap_intro" => $ap_intro, "mch_id" => $mch_id, "sort" => $sort, "height" => $height, "width" => $width);
		$res = $advert_position->save($data, ["ap_id" => $ap_id]);
		return $res;
	}
	public function delAdvertPosition($ap_id)
	{
		$advert = new Advert();
		$advert_position = new AdvertPosition();
		$advert_position->startTrans();
		try {
			$advert->destroy(["ap_id" => $ap_id]);
			$res = $advert_position->destroy($ap_id);
			$advert_position->commit();
		} catch (\Exception $e) {
			$advert_position->rollback();
			return $e->getMessage();
		}
		return $res;
	}
	public function getAdvert($condition = '', $search_text, $order)
	{
		$advert = new Advert();
		$rs = $advert->getPageLisy($condition, $search_text, $order);
		return $rs;
	}
	public function addAdvert($cate_id, $mod_id, $ap_id, $adv_title, $adv_url, $adv_image, $sort, $background, $mch_id)
	{
		$advert = new Advert();
		$data = array("ap_id" => $ap_id, "adv_title" => $adv_title, "adv_url" => $adv_url, "adv_image" => $adv_image, "sort" => $sort, "background" => $background, "create_time" => time(), "mch_id" => $mch_id, "mod_id" => $mod_id, "cate_id" => $cate_id);
		if ($mod_id == 1) {
			$data["adv_height"] = 188;
		}
		$res = $advert->save($data);
		return $res;
	}
	public function setAdvertUse($adv_id, $is_use)
	{
		$advert = new Advert();
		$data = array("is_use" => $is_use);
		$res = $advert->save($data, ["adv_id" => $adv_id]);
		return $res;
	}
	public function getAdvertDetail($adv_id)
	{
		$advert = new Advert();
		$info = $advert->getInfo(["adv_id" => $adv_id]);
		return $info;
	}
	public function updateAdvert($adv_id, $adv_title, $adv_url, $adv_image, $sort, $background, $mch_id)
	{
		$advert = new Advert();
		$data = array("adv_title" => $adv_title, "adv_url" => $adv_url, "adv_image" => $adv_image, "sort" => $sort, "background" => $background, "mch_id" => $mch_id);
		$res = $advert->save($data, ["adv_id" => $adv_id, "mch_id" => $mch_id]);
		return $res;
	}
	public function delAdvert($adv_id)
	{
		$advert = new Advert();
		$res = $advert->destroy($adv_id);
		return $res;
	}
	public function AdvertPositionSort($ap_id, $sort)
	{
		$advert = new Advert();
		$data = array("sort" => $sort);
		$res = $advert->save($data, ["adv_id" => $ap_id]);
		return $res;
	}
	public function AdvertSort($ap_id, $sort)
	{
		$advert = new AdvertPosition();
		$data = array("sort" => $sort);
		$res = $advert->save($data, ["ap_id" => $ap_id]);
		return $res;
	}
	public function getAdvertPosImg($condition = '', $field = "*", $order = '')
	{
		$advert = new Advert();
		$list = $advert->getQuerys($condition, $field, $order);
		return $list;
	}
	public function UpdateAdvertImg($adv_title, $adv_id, $key)
	{
		$advert = new Advert();
		if ($key == "title") {
			$data = ["adv_title" => $adv_title];
		} elseif ($key == "url") {
			$data = ["adv_url" => $adv_title];
		} elseif ($key == "width") {
			$data = ["adv_width" => $adv_title];
		}
		$res = $advert->save($data, ["adv_id" => $adv_id]);
		return $res;
	}
	public function UpdateAdvertProportion($info, $ap_id, $key)
	{
		$advert_position = new AdvertPosition();
		if ($key == "width") {
			$data = ["width" => $info];
		} elseif ($key == "height") {
			$data = ["height" => $info];
		}
		$res = $advert_position->save($data, ["ap_id" => $ap_id]);
		return $res;
	}
	public function editNavigation($adv_id, $cate_id, $adv_title, $sort, $background, $adv_url, $adv_image)
	{
		$advert = new Advert();
		$data = array("adv_title" => $adv_title, "adv_url" => $adv_url, "adv_image" => $adv_image, "sort" => $sort, "background" => $background, "cate_id" => $cate_id);
		$res = $advert->save($data, ["adv_id" => $adv_id]);
		return $res;
	}
}
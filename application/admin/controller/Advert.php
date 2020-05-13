<?php


namespace app\admin\controller;

use app\admin\service\ConfigService;
use app\admin\service\GoodsCate;
use app\admin\service\AdvertService;
use think\Db;
class Advert extends Base
{
	public function index()
	{
		$advert = new AdvertService();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$condition["mod_id"] = array("eq", 1);
		$list = $advert->getAdvert($condition, '', "sort desc");
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$SlideHeight = db::name("ybmp_config")->where("mch_id", $this->bus_id)->where("key", "SLIDEHEIGHT")->find();
		$this->assign("SlideHeight", $SlideHeight["value"]);
		return view("advert/advert_position_list");
	}
	public function navigation()
	{
		$advert = new AdvertService();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$condition["mod_id"] = array("eq", 2);
		$list = $advert->getAdvert($condition, '', "sort desc");
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view("advert/advert_position_navigation");
	}
	public function advertising()
	{
		$advert = new AdvertService();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$list = $advert->getAdvertPosition($condition, '', "sort desc");
		foreach ($list as $k => $v) {
			$condition["ap_id"] = array("=", $v["ap_id"]);
			$adv_list = $advert->getAdvertPosImg($condition, '', "sort desc");
			$list[$k]["adv_img"] = $adv_list;
		}
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		return view("advert/advert_position_advertising");
	}
	public function add_height()
	{
		$info = isset($_POST["info"]) ? $_POST["info"] : 0;
		$config = new ConfigService();
		$res = $config->addHeight($info, $this->bus_id);
		return AjaxReturn($res);
	}
	public function AdvertPositionSort()
	{
		$ap_id = isset($_POST["adv_id"]) ? $_POST["adv_id"] : 0;
		$sort = isset($_POST["sort"]) ? $_POST["sort"] : 0;
		$advert = new AdvertService();
		$res = $advert->AdvertPositionSort($ap_id, $sort);
		return AjaxReturn($res);
	}
	public function AdvertSort()
	{
		$ap_id = isset($_POST["ap_id"]) ? $_POST["ap_id"] : 0;
		$sort = isset($_POST["sort"]) ? $_POST["sort"] : 0;
		$advert = new AdvertService();
		$res = $advert->AdvertSort($ap_id, $sort);
		return AjaxReturn($res);
	}
	public function addAdvert()
	{
		$advert = new AdvertService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$mod_id = 1;
			$ap_id = 0;
			$cate_id = isset($_POST["cate_id"]) ? $_POST["cate_id"] : "0";
			$adv_title = isset($_POST["adv_title"]) ? $_POST["adv_title"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$background = isset($_POST["background"]) ? $_POST["background"] : '';
			$adv_url = isset($_POST["adv_url"]) ? $_POST["adv_url"] : '';
			$adv_image = isset($_POST["adv_image"]) ? $_POST["adv_image"] : '';
			$res = $advert->addAdvert($cate_id, $mod_id, $ap_id, $adv_title, $adv_url, $adv_image, $sort, $background, $mch_id);
			return AjaxReturn($res);
		}
		return view("advert/advert_add");
	}
	public function addNavigation()
	{
		$advert = new AdvertService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$mod_id = 2;
			$ap_id = 0;
			$cate_id = isset($_POST["cate_id"]) ? $_POST["cate_id"] : "0";
			$adv_title = isset($_POST["adv_title"]) ? $_POST["adv_title"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$background = isset($_POST["background"]) ? $_POST["background"] : '';
			$adv_url = isset($_POST["adv_url"]) ? $_POST["adv_url"] : '';
			$adv_image = isset($_POST["adv_image"]) ? $_POST["adv_image"] : '';
			$res = $advert->addAdvert($cate_id, $mod_id, $ap_id, $adv_title, $adv_url, $adv_image, $sort, $background, $mch_id);
			return AjaxReturn($res);
		}
		$cate = new GoodsCate();
		$list = $cate->getFormatGoodsCategoryList($mch_id);
		$this->assign("list", $list);
		return view("advert/add_navigation");
	}
	public function addAdvertising()
	{
		$advert = new AdvertService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$ap_intro = isset($_POST["ap_intro"]) ? $_POST["ap_intro"] : '';
			$ap_name = isset($_POST["ap_name"]) ? $_POST["ap_name"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$height = isset($_POST["height"]) ? $_POST["height"] : "0";
			$width = isset($_POST["width"]) ? $_POST["width"] : "0";
			$res = $advert->addAdvertPosition($mch_id, $ap_intro, $ap_name, $sort, $height, $width);
			return AjaxReturn($res);
		}
		return view("advert/add_advertising");
	}
	public function setAdvertUse()
	{
		if (request()->isAjax() && request()->isPost()) {
			$adv_id = request()->post("adv_id", '');
			$is_use = request()->post("is_use", '');
			$advert = new AdvertService();
			$res = $advert->setAdvertUse($adv_id, $is_use);
			return AjaxReturn($res);
		}
	}
	public function updateAdvert()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$advert = new AdvertService();
			$adv_id = isset($_POST["adv_id"]) ? $_POST["adv_id"] : '';
			$adv_title = isset($_POST["adv_title"]) ? $_POST["adv_title"] : '';
			$adv_url = isset($_POST["adv_url"]) ? $_POST["adv_url"] : '';
			$adv_image = isset($_POST["adv_image"]) ? $_POST["adv_image"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$background = isset($_POST["background"]) ? $_POST["background"] : '';
			$res = $advert->updateAdvert($adv_id, $adv_title, $adv_url, $adv_image, $sort, $background, $mch_id);
			return AjaxReturn($res);
		}
		$advert = new AdvertService();
		$adv_id = isset($_GET["adv_id"]) ? $_GET["adv_id"] : '';
		$info = $advert->getAdvertDetail($adv_id);
		$this->assign("info", $info);
		return view("advert/advert_edit");
	}
	public function updateAdvertPos()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$advert = new AdvertService();
			$ap_id = isset($_POST["ap_id"]) ? $_POST["ap_id"] : '';
			$ap_name = isset($_POST["ap_name"]) ? $_POST["ap_name"] : '';
			$ap_intro = isset($_POST["ap_intro"]) ? $_POST["ap_intro"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$height = isset($_POST["height"]) ? $_POST["height"] : "0";
			$width = isset($_POST["width"]) ? $_POST["width"] : "0";
			$res = $advert->updateAdvertPositionDetail($mch_id, $ap_id, $ap_name, $ap_intro, $sort, $height, $width);
			return AjaxReturn($res);
		}
		$advert = new AdvertService();
		$ap_id = isset($_GET["ap_id"]) ? $_GET["ap_id"] : '';
		$info = $advert->getAdvertPositionDetail($ap_id);
		$this->assign("info", $info);
		return view("advert/advert_position_edit");
	}
	public function AddAdvertPosImg()
	{
		$advert = new AdvertService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$mod_id = 3;
			$adv_img = input("param.adv_img", '');
			$ap_id = input("param.ap_id", '');
			$res = $advert->addAdvert('', $mod_id, $ap_id, '', '', $adv_img, '', '', $mch_id);
			return AjaxReturn($res);
		}
		$ap_id = input("param.ap_id", "0");
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["ap_id"] = array("eq", $ap_id);
		$img_list = $advert->getAdvertPosImg($condition, '', "sort desc");
		$this->assign("img_list", $img_list);
		$this->assign("ap_id", $ap_id);
		return view("advert/advert_img");
	}
	public function AdvertImg()
	{
		$advert = new AdvertService();
		if (request()->isAjax() && request()->isPost()) {
			$adv_title = input("param.adv_title", '');
			$adv_id = input("param.adv_id", '');
			$key = input("param.key", '');
			$res = $advert->UpdateAdvertImg($adv_title, $adv_id, $key);
			return AjaxReturn($res);
		}
	}
	public function AdvertPositionProportion()
	{
		$advert = new AdvertService();
		if (request()->isAjax() && request()->isPost()) {
			$info = input("param.info", '');
			$ap_id = input("param.ap_id", '');
			$key = input("param.key", '');
			$res = $advert->UpdateAdvertProportion($info, $ap_id, $key);
			return AjaxReturn($res);
		}
	}
	public function editNavigation()
	{
		$advert = new AdvertService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isAjax() && request()->isPost()) {
			$adv_id = isset($_POST["adv_id"]) ? $_POST["adv_id"] : "0";
			$cate_id = isset($_POST["cate_id"]) ? $_POST["cate_id"] : "0";
			$adv_title = isset($_POST["adv_title"]) ? $_POST["adv_title"] : '';
			$sort = isset($_POST["sort"]) ? $_POST["sort"] : '';
			$background = isset($_POST["background"]) ? $_POST["background"] : '';
			$adv_url = isset($_POST["adv_url"]) ? $_POST["adv_url"] : '';
			$adv_image = isset($_POST["adv_image"]) ? $_POST["adv_image"] : '';
			$res = $advert->editNavigation($adv_id, $cate_id, $adv_title, $sort, $background, $adv_url, $adv_image);
			return AjaxReturn($res);
		}
		$cate = new GoodsCate();
		$list = $cate->getFormatGoodsCategoryList($mch_id);
		$this->assign("list", $list);
		$adv_id = input("param.adv_id", "0");
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["mod_id"] = array("eq", 2);
		$adv_list = $advert->getAdvertDetail($adv_id);
		$this->assign("adv_list", $adv_list);
		$this->assign("adv_id", $adv_id);
		return view("advert/edit_navigation");
	}
	public function delAdvert()
	{
		$adv_id = isset($_POST["adv_id"]) ? $_POST["adv_id"] : '';
		$advert = new AdvertService();
		$res = $advert->delAdvert($adv_id);
		return AjaxReturn($res);
	}
	public function delAdvertPos()
	{
		$ap_id = isset($_POST["ap_id"]) ? $_POST["ap_id"] : '';
		$advert = new AdvertService();
		$res = $advert->delAdvertPosition($ap_id);
		return AjaxReturn($res);
	}
}
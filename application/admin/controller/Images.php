<?php


namespace app\admin\controller;

use think\Cache;
use think\Db;
class Images extends Base
{
	public function __construct()
	{
		parent::__construct();
		Cache::set("is_load", 1, 20);
	}
	public function __destruct()
	{
		Cache::set("is_load", null);
	}
	public function image_list()
	{
		$album = new \app\admin\service\Images();
		$search_text = input("param.search_text");
		$condition["group_name"] = ["like", "%" . $search_text . "%"];
		$condition["mch_id"] = array("eq", $this->bus_id);
		$retval = $album->getAlbumClassList($condition, '', "sort");
		$page = $retval->render();
		$this->assign("retval", $retval);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return view("images_list");
	}
	public function images_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$images_name = request()->post("images_name", '');
			$images_sort = request()->post("images_sort", 0);
			$album = new \app\admin\service\Images();
			$retval = $album->addAlbumClass($images_name, $images_sort, 0, '', 0, $this->bus_id);
			return AjaxReturn($retval);
		} else {
			return view("images_add");
		}
	}
	public function images_edit()
	{
		$group_id = input("param.search_text", 0);
		$condition = array("group_id" => $group_id);
		$album = new \app\admin\service\Images();
		$list = $album->getPictureList($condition, $group_id, "upload_time desc");
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("group_id", $group_id);
		return view("images/images_show");
	}
	public function dialogAlbumList()
	{
		$number = request()->get("number", 1);
		$spec_id = request()->get("spec_id", 0);
		$ss = request()->get("ss", '');
		$spec_value_id = request()->get("spec_value_id", 0);
		$upload_type = request()->get("upload_type", 1);
		$type = request()->get("type", '');
		$this_id = request()->get("this_id", 0);
		$com = request()->get("com", '');
		$this->assign("type", $type);
		$this->assign("number", $number);
		$this->assign("this_id", $this_id);
		$this->assign("com", $com);
		$this->assign("spec_id", $spec_id);
		$this->assign("ss", $ss);
		$this->assign("spec_value_id", $spec_value_id);
		$this->assign("upload_type", $upload_type);
		$album = new \app\admin\service\Images();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$default_album_detail = $album->getDefaultAlbumDetail();
		$this->assign("default_album_id", $default_album_detail["group_id"] ? $default_album_detail["group_id"] : 0);
		return view("images/images_select");
	}
	public function getAlbumClassALL()
	{
		$condition["mch_id"] = array("eq", $this->bus_id);
		$album = new \app\admin\service\Images();
		$retval = $album->getAlbumClassAll($condition);
		return $retval;
	}
	public function albumPictureList()
	{
		$album_id = request()->post("group_id", 0);
		$condition = array();
		$condition["group_id"] = array("eq", $album_id);
		$album = new \app\admin\service\Images();
		$list = $album->getImgList($condition, '', "upload_time desc");
		return $list;
	}
	public function deletePicture()
	{
		$pic_id_array = request()->post("img_id_array", '');
		$album = new \app\admin\service\Images();
		$retval = $album->deletePicture($pic_id_array);
		return AjaxReturn($retval);
	}
	public function imagesDel()
	{
		$img = new \app\admin\service\Images();
		$img_id = input("param.img_id", "0");
		$res = $img->deleteImgGroup($img_id);
		return AjaxReturn($res);
	}
	public function imagesBox()
	{
		$img = new \app\admin\service\Images();
		$img_id = input("param.img_id");
		$res = $img->updateImagesGroupBox($img_id);
		return AjaxReturn($res);
	}
	public function zhan_img_box()
	{
		if (request()->isAjax() && request()->isPost()) {
			$group_id = input("param.group_id");
			$pic_id_array = input("param.pic_id_array");
			$res = Db::name("ybmp_images")->where("img_id", "in", $pic_id_array)->update(["group_id" => $group_id]);
			return AjaxReturn($res);
		}
		$pic_id_array = request()->param("img_id_array", '');
		$this->assign("pic_id_array", $pic_id_array);
		$group = Db::name("ybmp_images_group")->where("mch_id", $this->bus_id)->select();
		$this->assign("group", $group);
		return view();
	}
	public function silent()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$result = Db::name("ybmp_images_group")->where("mch_id", $this->bus_id)->where("group_id", $data["id"])->update(["group_name" => $data["name"]]);
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
}
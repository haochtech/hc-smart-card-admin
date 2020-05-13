<?php


namespace app\app\service;

use app\common\model\ImagesGroup;
use think\Db;
class Images
{
	public $images_group;
	public $images;
	function __construct()
	{
		$this->images = new \app\common\model\Images();
		$this->images_group = new ImagesGroup();
	}
	public function getAlubmPictureDetail($condition)
	{
		$res = $this->images->getInfo($condition, "*");
		return $res;
	}
	public function getGoodsAlbumUsePictureQuery($condition = '')
	{
		$goods = new \app\common\model\Goods();
		$goods_query = $goods->getQuerys($condition, "img_id_array", '');
		$img_array = array();
		foreach ($goods_query as $k => $v) {
			if (trim($v["img_id_array"]) != '') {
				$tmp_array = explode(",", trim($v["img_id_array"]));
				$img_array = array_merge($img_array, $tmp_array);
			}
		}
		$img_array = array_unique($img_array);
		return $img_array;
	}
	public function getAlbumClassAll($data = '')
	{
		$res = $this->images_group->getQuerys($data, "*", "sort");
		if (!empty($res)) {
			foreach ($res as $k => $v) {
				$count = $this->getAlbumPictureCount($v["group_id"]);
				$res[$k]["pic_count"] = $count;
			}
		}
		return $res;
	}
	public function addPicture($album_id, $pic_name, $pic_tag, $pic_cover, $pic_size, $pic_spec, $pic_cover_big, $pic_size_big, $pic_spec_big, $pic_cover_mid, $pic_size_mid, $pic_spec_mid, $pic_cover_small, $pic_size_small, $pic_spec_small, $pic_cover_micro, $pic_size_micro, $pic_spec_micro, $upload_type, $domain, $bucket)
	{
		$data = array("group_id" => $album_id, "is_wide" => "0", "img_name" => $pic_name, "img_tag" => $pic_tag, "img_cover" => $pic_cover, "img_size" => $pic_size, "img_spec" => $pic_spec, "img_cover_big" => $pic_cover_big, "img_size_big" => $pic_size_big, "img_spec_big" => $pic_spec_big, "img_cover_mid" => $pic_cover_mid, "img_size_mid" => $pic_size_mid, "img_spec_mid" => $pic_spec_mid, "img_cover_small" => $pic_cover_small, "img_size_small" => $pic_size_small, "img_spec_small" => $pic_spec_small, "img_cover_micro" => $pic_cover_micro, "img_size_micro" => $pic_size_micro, "img_spec_micro" => $pic_spec_micro, "upload_time" => time(), "upload_type" => $upload_type, "domain" => $domain, "bucket" => $bucket);
		$res = $this->images->save($data);
		if ($res == 1) {
			return $this->images->img_id;
		} else {
			return $res;
		}
	}
	public function ModifyAlbumPicture($pic_id, $pic_cover, $pic_size, $pic_spec, $pic_cover_big, $pic_size_big, $pic_spec_big, $pic_cover_mid, $pic_size_mid, $pic_spec_mid, $pic_cover_small, $pic_size_small, $pic_spec_small, $pic_cover_micro, $pic_size_micro, $pic_spec_micro, $instance_id, $upload_type, $domain, $bucket)
	{
		$data = array("img_cover" => $pic_cover, "img_size" => $pic_size, "img_spec" => $pic_spec, "img_cover_big" => $pic_cover_big, "img_size_big" => $pic_size_big, "img_spec_big" => $pic_spec_big, "img_cover_mid" => $pic_cover_mid, "img_size_mid" => $pic_size_mid, "img_spec_mid" => $pic_spec_mid, "img_cover_small" => $pic_cover_small, "img_size_small" => $pic_size_small, "img_spec_small" => $pic_spec_small, "img_cover_micro" => $pic_cover_micro, "img_size_micro" => $pic_size_micro, "img_spec_micro" => $pic_spec_micro, "upload_time" => time(), "upload_type" => $upload_type, "domain" => $domain, "bucket" => $bucket);
		$res = $this->images->save($data, ["pic_id" => $pic_id]);
		return $res;
	}
	public function getDefaultAlbumDetail()
	{
		$res = $this->images_group->getInfo(["is_default" => 1]);
		return $res;
	}
	public function GetDefAll($condition)
	{
		$res = $this->images_group->getInfo($condition);
		return $res;
	}
	public function addAlbumClass($aclass_name, $aclass_sort, $pid = 0, $aclass_cover = '', $is_default = 0, $mch_id)
	{
		$album_class = new ImagesGroup();
		$data = array("group_name" => $aclass_name, "sort" => $aclass_sort, "group_cover" => $aclass_cover, "is_default" => $is_default, "create_time" => time(), "pid" => $pid, "mch_id" => $mch_id);
		$retval = $album_class->save($data);
		if ($retval == 1) {
			Db::table("ims_ybmp_images_group")->where("group_id", $album_class->group_id)->update(["pages_url" => IMAGES_ID . $album_class->group_id]);
			return $album_class->group_id;
		} else {
			return $retval;
		}
	}
	public function getAlbumClassList($condition = '', $search_text, $order)
	{
		$album_class = new ImagesGroup();
		$list = $album_class->getPageLisy($condition, $search_text, $order);
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$count = $this->getAlbumPictureCount($v["group_id"]);
				$list[$k]["img_count"] = $count;
				$album_picture = new \app\common\model\Images();
				$pic_cover = '';
				if ($v["group_cover"]) {
					$pic_info = $album_picture->getInfo(["group_id" => $v["group_id"], "img_id" => $v["group_cover"]], "img_cover,upload_type,domain");
					if (!empty($pic_info)) {
						$pic_cover = $pic_info["img_cover"];
					}
					$list[$k]["img_info"] = $pic_info;
					$list[$k]["img_album_cover"] = $pic_cover;
				}
			}
		}
		return $list;
	}
	private function getAlbumPictureCount($group_id)
	{
		$album_picture = new \app\common\model\Images();
		$count = $album_picture->where("group_id=" . $group_id)->count();
		return $count;
	}
	public function getAlbumClassDetail($group_id)
	{
		$res = $this->images_group->get($group_id);
		return $res;
	}
	public function getPictureList($condition = '', $search_text = '', $order = '')
	{
		$list = $this->images->getPageLisy($condition, $search_text, $order);
		return $list;
	}
	public function getImgList($condition = '', $field = "*", $order = '')
	{
		$list = $this->images->getQuerys($condition, $field, $order);
		return $list;
	}
	public function deletePicture($pic_id_array)
	{
		$pic_array = explode(",", $pic_id_array);
		$res = 1;
		if (!empty($pic_array)) {
			$user_img_array = $this->getGoodsAlbumUsePictureQuery();
			foreach ($pic_array as $pic_id) {
				$retval = in_array($pic_id, $user_img_array);
				if (!$retval) {
					$condition = array("img_id" => $pic_id);
					$picture_obj = $this->images->get($pic_id);
					if (!empty($picture_obj)) {
						$pic_cover = $picture_obj["img_cover"];
						removeImageFile($pic_cover);
						$pic_cover_big = $picture_obj["img_cover_big"];
						removeImageFile($pic_cover_big);
						$pic_cover_mid = $picture_obj["img_cover_mid"];
						removeImageFile($pic_cover_mid);
						$pic_cover_small = $picture_obj["img_cover_small"];
						removeImageFile($pic_cover_small);
						$pic_cover_micro = $picture_obj["img_cover_micro"];
						removeImageFile($pic_cover_micro);
					}
					$result = $this->images->destroy($condition);
					if (!$result > 0) {
						$res = -1;
					}
				} else {
					$res = -1;
				}
			}
		} else {
			$res = -1;
		}
		if ($res == 1) {
			return SUCCESS;
		} else {
			return DELETE_FAIL;
		}
	}
	public function deleteImgGroup($img_id)
	{
		$res = $this->images_group->destroy(["group_id" => $img_id]);
		$this->images->destroy(["group_id" => $img_id]);
		return $res;
	}
	public function updateImagesGroupBox($img_id)
	{
		$img = $this->images->getInfo(["img_id" => $img_id]);
		$data = array("group_cover" => $img["img_id"], "group_id" => $img["group_id"]);
		$res = $this->images_group->save($data, ["group_id" => $img["group_id"]]);
		return $res;
	}
	public function addImagesGroup($mch_id)
	{
		$data = array("group_name" => "默认相册", "is_default" => 1, "create_time" => time(), "mch_id" => $mch_id);
		$res = $this->images_group->save($data);
		return $res;
	}
}
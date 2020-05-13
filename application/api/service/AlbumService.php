<?php


namespace app\api\service;

use think\Db;
class AlbumService
{
	private $i = "ybmp_images";
	private $ig = "ybmp_images_group";
	public function getAlbum($data, $page)
	{
		$group_id = Db::name($this->i)->where($data)->find();
		if (empty($group_id)) {
			return $group_id;
		}
		$rs["group_name"] = $group_id["group_name"];
		$info = Db::name($this->i)->where("group_id", $group_id["group_id"])->page($page, PAGE_NUM)->select();
		if (empty($info)) {
			return $rs;
		}
		$rs["info"] = $info;
		return $rs;
	}
	public function getAlbumImages($data)
	{
		$rs = Db::name($this->ig)->where($data)->where("is_default", "0")->order("sort", "desc")->order("create_time", "desc")->select();
		if (!empty($rs)) {
			foreach ($rs as $k1 => $value) {
				$pic = Db::name($this->i)->where("img_id", $value["group_cover"])->field("img_cover,img_cover_big,img_cover_mid,img_cover_small")->find();
				if ($pic) {
					$rs[$k1]["pic"] = $pic;
				}
			}
		}
		return $rs;
	}
}
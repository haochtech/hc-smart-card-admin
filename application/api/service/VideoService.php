<?php


namespace app\api\service;

use app\common\model\VideoClass;
use app\common\model\Video;
use app\common\model\BusVideo;
class VideoService
{
	public function getVideoList($data, $page)
	{
		$video = new BusVideo();
		$rs = $video->where($data)->page($page, PAGE_NUM)->order("add_time", "desc")->select();
		if (empty($rs)) {
			return $rs;
		}
		foreach ($rs as $key => $value) {
			$value->img = __IMG($value->img);
			if (date("Y") == date("Y", $value->add_time)) {
				$value->add_time = date("n月j日", $value->add_time);
			} else {
				$value->add_time = date("Y-m-d", $value->add_time);
			}
		}
		return $rs;
	}
	public function getVideoClass($data, $page)
	{
		$video_class = new VideoClass();
		$rs = $video_class->where($data)->page($page, PAGE_NUM)->order("sort", "desc")->order("create_time", "desc")->select();
		foreach ($rs as $key => $value) {
			$video = new Video();
			$value->count = $video->where("class_id", $value->class_id)->count();
			$value->images = __IMG($value->images);
			if (date("Y") == date("Y", $value->create_time)) {
				$value->create_time = date("n月j日", $value->create_time);
				$value->update_time = date("n月j日", $value->update_time);
			} else {
				$value->create_time = date("Y-m-d", $value->create_time);
				$value->update_time = date("Y-m-d", $value->update_time);
			}
		}
		return $rs;
	}
	public function getVideo($data, $page)
	{
		if (isset($data["ident"])) {
			$class_data = ["ident" => $data["ident"], "mch_id" => $data["mch_id"]];
			$video_class = new VideoClass();
			$class_id = $video_class->where($class_data)->value("class_id");
			if (empty($class_id)) {
				return $class_id;
			}
			unset($data["ident"]);
			unset($data["mch_id"]);
			$data["class_id"] = $class_id;
		}
		$video = new Video();
		$rs = $video->where($data)->page($page, PAGE_NUM)->order("sort", "desc")->order("create_time", "desc")->select();
		if (empty($rs)) {
			return $rs;
		}
		foreach ($rs as $key => $value) {
			$value->image = __IMG($value->image);
			$value->url = __IMG($value->url);
			if (date("Y") == date("Y", $value->create_time)) {
				$value->create_time = date("n月j日", $value->create_time);
			} else {
				$value->create_time = date("Y-m-d", $value->create_time);
			}
		}
		return $rs;
	}
	public function getVideoInfo($data)
	{
		$video = new Video();
		$rs = $video->where($data)->order("sort", "desc")->order("create_time", "desc")->find();
		if (empty($rs)) {
			return $rs;
		}
		$rs["image"] = __IMG($rs->image);
		$rs["url"] = __IMG($rs->url);
		if (date("Y") == date("Y", $rs->create_time)) {
			$rs["create_time"] = date("n月j日", $rs->create_time);
		} else {
			$rs["create_time"] = date("Y-m-d", $rs->create_time);
		}
		return $rs;
	}
}
<?php


namespace app\admin\service;

use think\Db;
class VideoService extends Base
{
	public function getVideoClass($data)
	{
		$class_list = Db::name("ybtc_video_class")->where($data)->order("sort", "desc")->paginate(20);
		return $class_list;
	}
	public function addVideoClass($data)
	{
		$class = Db::name("ybtc_video_class")->insert($data);
		return $class;
	}
	public function getVideoClassById($class_id)
	{
		$info = Db::name("ybtc_video_class")->where(["class_id" => $class_id])->find();
		return $info;
	}
	public function updateVideoClass($data)
	{
		$info = Db::name("ybtc_video_class")->where("class_id", $data["class_id"])->update($data);
		return $info;
	}
	public function delVideoClass($data)
	{
		Db::startTrans();
		try {
			$class = Db::name("ybtc_video_class")->where("class_id", $data)->delete();
			Db::name("ybtc_video")->where("class_id", $data)->delete();
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $class;
	}
	public function getVideo($data)
	{
		$class_list = Db::name("ybtc_video")->where($data)->order("sort", "desc")->paginate(20);
		return $class_list;
	}
	public function getClass($mch_id)
	{
		$class_list = Db::name("ybtc_video_class")->where("mch_id", $mch_id)->order("sort", "desc")->select();
		return $class_list;
	}
	public function addVideo($data)
	{
		Db::startTrans();
		try {
			$class = Db::name("ybtc_video")->insert($data);
			Db::name("ybtc_video_class")->where("class_id", $data["class_id"])->update(["update_time" => time()]);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $class;
	}
	public function getVideoById($video_id)
	{
		$info = Db::name("ybtc_video")->where(["video_id" => $video_id])->find();
		return $info;
	}
	public function updateVideo($data)
	{
		$info = Db::name("ybtc_video")->where("video_id", $data["video_id"])->update($data);
		return $info;
	}
	public function VideoDel($data)
	{
		$class = Db::name("ybtc_video")->where("video_id", $data)->delete();
		return $class;
	}
}
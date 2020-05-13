<?php


namespace app\admin\controller;

use app\admin\service\VideoService;
use think\Db;
class Video extends Base
{
	public function index()
	{
		$list = Db::name("ybmp_bus_video")->where("mch_id", $this->bus_id)->paginate(20);
		$this->assign("list", $list);
		return view();
	}
	public function video_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data["title"] = input("param.title");
			$data["url"] = input("param.video_url");
			$data["img"] = input("param.brand_pic");
			$data["mch_id"] = $this->bus_id;
			$data["add_time"] = time();
			$res = Db::name("ybmp_bus_video")->insert($data);
			return AjaxReturn($res);
		}
		return view();
	}
	public function video_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$id = input("param.id");
			$data["title"] = input("param.title");
			$data["url"] = input("param.video_url");
			$data["img"] = input("param.brand_pic");
			$data["mch_id"] = $this->bus_id;
			$res = Db::name("ybmp_bus_video")->where("id", $id)->update($data);
			return AjaxReturn($res);
		}
		$id = input("param.id");
		$info = Db::name("ybmp_bus_video")->where("id", $id)->find();
		$this->assign("info", $info);
		return view();
	}
	public function video_del()
	{
		$id = input("param.id");
		$res = Db::name("ybmp_bus_video")->where("id", $id)->delete();
		return AjaxReturn($res);
	}
	public function VideoClass()
	{
		$data = array();
		$search_text = request()->post("search_text");
		$data["name"] = ["like", "%" . $search_text . "%"];
		$data["mch_id"] = $this->bus_id;
		$video_class = new VideoService();
		$class = $video_class->getVideoClass($data);
		$page = $class->render();
		$this->assign("class", $class);
		$this->assign("page", $page);
		return view("video/video_class_list");
	}
	public function AddVideoClass()
	{
		$article_class = new VideoService();
		if (request()->isPost()) {
			$data["name"] = request()->post("class_name");
			$data["mch_id"] = $this->bus_id;
			$data["sort"] = request()->post("class_sort", 0);
			$data["images"] = input("param.images", '');
			$data["create_time"] = time();
			$data["pid"] = 0;
			$res = $article_class->addVideoClass($data);
			return AjaxReturn($res);
		}
		return view("video/video_class_add");
	}
	public function DelVideoClass()
	{
		$class_id = input("param.class_id", '');
		$video = new VideoService();
		$res = $video->delVideoClass($class_id);
		return AjaxReturn($res);
	}
	public function EditVideoClass()
	{
		$article_class = new VideoService();
		if (request()->isPost()) {
			$data["class_id"] = input("param.class_id", "0");
			$data["name"] = input("param.class_name", '');
			$data["sort"] = input("param.class_sort", '');
			$data["images"] = input("param.images", '');
			$data["pid"] = 0;
			$res = $article_class->updateVideoClass($data);
			return AjaxReturn($res);
		}
		$class_id = input("param.class_id");
		$list = $article_class->getVideoClassById($class_id);
		$this->assign("class", $list);
		return view("video/video_class_edit");
	}
	public function Video()
	{
		$data = array();
		$search_text = request()->post("search_text");
		$class_id = request()->post("class_id");
		$data["title"] = ["like", "%" . $search_text . "%"];
		$data["class_id"] = $class_id;
		$data = array_filter($data);
		$video_class = new VideoService();
		$data["mch_id"] = $this->bus_id;
		$class = $video_class->getVideo($data);
		$page = $class->render();
		$this->assign("class", $class);
		$this->assign("page", $page);
		return view("video/video_list");
	}
	public function AddVideo()
	{
		$video_class = new VideoService();
		if (request()->isPost()) {
			$data["mch_id"] = $this->bus_id;
			$data["title"] = input("param.title", '');
			$data["class_id"] = input("param.class_id", '');
			$data["short_title"] = input("param.short_title", '');
			$data["author"] = input("param.author", '');
			$data["content"] = input("param.content", '');
			$data["image"] = input("param.images", '');
			$data["url"] = input("param.video", '');
			$data["keyword"] = input("param.keyword", '');
			$data["is_recommend"] = input("param.is_recommend", '');
			$data["status"] = 2;
			$data["create_time"] = time();
			$res = $video_class->addVideo($data);
			return AjaxReturn($res);
		}
		$class = $video_class->getClass($this->bus_id);
		$this->assign("class", $class);
		return view("video/video_add");
	}
	public function EditVideo()
	{
		$video = new VideoService();
		if (request()->isPost()) {
			$data["video_id"] = input("param.video_id", '');
			$data["title"] = input("param.title", '');
			$data["class_id"] = input("param.class_id", '');
			$data["short_title"] = input("param.short_title", '');
			$data["author"] = input("param.author", '');
			$data["content"] = input("param.content", '');
			$data["image"] = input("param.images", '');
			$data["url"] = input("param.video", '');
			$data["keyword"] = input("param.keyword", '');
			$data["is_recommend"] = input("param.is_recommend", '');
			$data["status"] = 2;
			$res = $video->updateVideo($data);
			return AjaxReturn($res);
		}
		$video_id = input("param.video_id");
		$class = $video->getClass($this->bus_id);
		$this->assign("class", $class);
		$list = $video->getVideoById($video_id);
		$this->assign("info", $list);
		return view("video/video_edit");
	}
	public function VideoDel()
	{
		$video_id = input("param.video_id", '');
		$video = new VideoService();
		$res = $video->VideoDel($video_id);
		return AjaxReturn($res);
	}
}
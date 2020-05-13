<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use app\api\service\VideoService;
class Video extends BaseController
{
	public function VideoList()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$title = Request::instance()->param("title");
		$page = Request::instance()->param("page", 1);
		$data = ["mch_id" => $mch_id, "ident" => $ident, "class_id" => $class_id, "title" => ["like", "%" . $title . "%"]];
		$video = new VideoService();
		$data = array_filter($data);
		$info = $video->getVideoList($data, $page);
		if (empty($info)) {
			$rs["msg"] = "暂无视频";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function VideoClass()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$page = Request::instance()->param("page", 1);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$video = new VideoService();
		$info = $video->getVideoClass($data, $page);
		if (empty($info)) {
			$rs["msg"] = "暂无分类";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Video()
	{
		$rs = array("code" => 0, "info" => array());
		$ident = Request::instance()->param("ident");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$class_id = Request::instance()->param("class_id");
		$title = Request::instance()->param("title");
		$page = Request::instance()->param("page", 1);
		$data = ["mch_id" => $mch_id, "ident" => $ident, "class_id" => $class_id, "title" => ["like", "%" . $title . "%"]];
		$video = new VideoService();
		$data = array_filter($data);
		$info = $video->getVideo($data, $page);
		if (empty($info)) {
			$rs["msg"] = "暂无视频";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function VideoInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$video_id = Request::instance()->param("video_id");
		$data = ["video_id" => $video_id];
		$rule = [["video_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$video = new VideoService();
		$info = $video->getVideoInfo($data);
		if (empty($info)) {
			$rs["msg"] = "暂无视频";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function txurl()
	{
		$rs = array("code" => 0, "info" => array());
		$url = Request::instance()->param("url");
		$rs["info"] = $url;
		$arrs = explode("/", str_replace(".html", '', $url));
		$vid = end($arrs);
		$tencent_video_info = get_url_content("http://vv.video.qq.com/getinfo?vids={$vid}&platform=101001&charge=0&otype=json");
		$tencent_video_json = substr(explode("QZOutputJson=", $tencent_video_info)[1], 0, -1);
		$tencent_video_array = json_decode($tencent_video_json, true);
		$fvkey = $tencent_video_array["vl"]["vi"][0]["fvkey"];
		$fn = $tencent_video_array["vl"]["vi"][0]["fn"];
		$tx_url = $tencent_video_array["vl"]["vi"][0]["ul"]["ui"][0]["url"];
		if (!empty($tx_url) && !empty($fn) && !empty($fvkey)) {
			$video_url = $tx_url . $fn . "?vkey=" . $fvkey;
			$rs["info"] = $video_url;
			return json_encode($rs);
		}
		return json_encode($rs);
	}
}
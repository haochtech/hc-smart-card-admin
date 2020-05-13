<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use app\api\service\AlbumService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Album extends BaseController
{
	public function Album()
	{
		$rs = array("code" => 0, "info" => array());
		$group_id = Request::instance()->param("group_id");
		$app_id = Request::instance()->param("i");
		$page = Request::instance()->param("page", 1);
		$mch_id = $this->getMchId($app_id);
		$data = ["group_id" => $group_id];
		$rule = [["group_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$article = new AlbumService();
		$data = array_filter($data);
		$info = $article->getAlbum($data, $page);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function AlbumImages()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$article = new AlbumService();
		$info = $article->getAlbumImages($data);
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
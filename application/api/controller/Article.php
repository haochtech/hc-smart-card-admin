<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use app\api\service\ArticleService;
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Article extends BaseController
{
	public function ArticleClass()
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
		$article = new ArticleService();
		$info = $article::InfiniteCate(0, $mch_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Article()
	{
		$rs = array("code" => 0, "info" => array());
		$class_id = Request::instance()->param("class_id");
		$ident = Request::instance()->param("ident");
		$app_id = Request::instance()->param("i");
		$page = Request::instance()->param("page", 1);
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "ident" => $ident, "class_id" => $class_id];
		$data = array_filter($data);
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$article = new ArticleService();
		$info = $article->getArticle($data, $page);
		if (empty($info)) {
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function ArticleInfo()
	{
		$rs = array("code" => 0, "info" => array());
		$ident = Request::instance()->param("ident");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["article_id" => Request::instance()->param("article_id"), "mch_id" => $mch_id, "ident" => $ident];
		$article = new ArticleService();
		$data = array_filter($data);
		$info = $article->getArticleInfo($data);
		if (empty($info)) {
			$rs["msg"] = "暂无文章";
			return json_encode($rs);
		}
		$rs["info"] = $info;
		return json_encode($rs);
	}
}
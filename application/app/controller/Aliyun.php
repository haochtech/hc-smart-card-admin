<?php


namespace app\app\controller;

use think\Db;
use think\Request;
use app\app\service\AliyunService;
class Aliyun extends BaseController
{
	protected $config = array("appid" => "LTAIA6equwSykSCK", "secret" => "2OTqoYSU2XVZe28cfiJpjk1FD3BEqY", "appkey" => "24965934");
	public function Push()
	{
		$rs = array("code" => 0, "info" => array());
		$courier_id = Request::instance()->param("mch_id");
		$title = Request::instance()->param("title", "新订单");
		$content = Request::instance()->param("content", "您有新订单了，请注意查看");
		$data = ["courier_id" => $courier_id];
		$rule = [["courier_id", "require"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$aliyun = new AliyunService();
		$info = $aliyun->push($this->config, $courier_id, $title, $content);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function Android()
	{
		if ($wx = DI()->config->get("app.Aliyun")) {
			$this->config = array_merge($this->config, $wx);
		}
		$domain = new Domain_Aliyun();
		$info = $domain->Android($this->config);
		if (empty($info)) {
			DI()->logger->debug("push failed");
			$rs["code"] = 1;
			$rs["msg"] = T("push failed");
			return $rs;
		}
		$rs["info"] = $info;
		return $rs;
	}
	public function IOS()
	{
		if ($wx = DI()->config->get("app.Aliyun")) {
			$this->config = array_merge($this->config, $wx);
		}
		$domain = new Domain_Aliyun();
		$info = $domain->IOS($this->config);
		if (empty($info)) {
			DI()->logger->debug("push failed");
			$rs["code"] = 1;
			$rs["msg"] = T("push failed");
			return $rs;
		}
		$rs["info"] = $info;
		return $rs;
	}
}
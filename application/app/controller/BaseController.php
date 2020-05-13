<?php


namespace app\app\controller;

use think\Controller;
use think\Validate;
use think\Cache;
use think\Config;
use think\Db;
header("Access-Control-Allow-Origin: *");
class BaseController extends Controller
{
	public function __construct()
	{
	}
	protected function checkParam($rule, $data)
	{
		$validate = new Validate($rule);
		$result = $validate->check($data);
		if (!$result) {
			return $validate->getError();
		}
		return null;
	}
	public function getMchId($app_id)
	{
		$rs = Cache::get("app" . $app_id);
		if ($rs != false) {
			return $rs;
		}
		$info = Db::name("ybmp_business")->field("id")->where("uniacid", $app_id)->find();
		$rs = $info["id"];
		Cache::set("app" . $app_id, $rs, CACHE_TIME);
		return $rs;
	}
	public function objectArray($obj)
	{
		$obj = (array) $obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == "resource") {
				return;
			}
			if (gettype($v) == "object" || gettype($v) == "array") {
				$obj[$k] = (array) $this->objectArray($v);
			}
		}
		return $obj;
	}
}
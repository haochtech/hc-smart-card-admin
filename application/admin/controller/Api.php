<?php


namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Api extends Controller
{
	public function upwxversion()
	{
		$busid = Request::instance()->param("busid");
		$version = Request::instance()->param("version");
		$last_version = Request::instance()->param("last_version");
		$last = Db::name("wxapp_versions")->where("version", $last_version)->where("uniacid", $busid)->order("id desc")->find();
		if (!empty($last)) {
			Db::name("wxapp_versions")->where("id", $last["id"])->update(["version" => $version]);
		}
		exit;
	}
}
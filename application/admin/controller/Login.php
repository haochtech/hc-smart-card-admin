<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Cookie;
load()->model("wxapp");
load()->model("user");
class Login extends Controller
{
	public function __construct()
	{
		parent::__construct();
		global $_W;
		$site_name = Db::name("ybmp_copyright")->where("id", 1)->find();
		$GLOBALS["back_type"] = $site_name["back_type"];
		$this->assign("site_name", $site_name["site_name"]);
		$siteroot = $_W["siteroot"];
		$this->assign("siteroot", $siteroot);
	}
	public function che()
	{
		$rr = str_replace(" ", '', request()->param("username"));
		$res = Db::name("users")->where("username", $rr)->count();
		return $res;
	}
	public function logout()
	{
		global $_W;
		$site_url = $_W["siteroot"];
		Cookie::delete("top_mid");
		Cookie::delete("sub_mid");
		Cookie::delete("three_mid");
		unset($_W);
		unset($_SESSION["we7_w"]);
		unset($_SESSION["we7_user"]);
		unset($_SESSION["we7_account"]);
		isetcookie("__session", '', -10000);
		isetcookie("__switch", '', -10000);
		isetcookie("__uniacid", '', -10000);
		isetcookie("__uid", '', -10000);
		isetcookie("__wxappversionids", '', -10000);
		header("Location:" . $site_url);
		exit;
	}
}
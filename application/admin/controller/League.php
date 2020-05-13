<?php


namespace app\admin\controller;

use app\admin\service\ConfigService;
use think\Cache;
use think\Cookie;
use think\Db;
class League extends Base
{
	public function index()
	{
		$un_data["url"] = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data["url"]);
		$data["url"] = $un_url[0];
		$data["username"] = $_SESSION["we7_user"]["username"];
		$data["uniacid"] = $_SESSION["we7_account"]["uniacid"];
		$list = self::query($data, "mand");
		$list = json_decode($list, true);
		$this->assign("list", $list);
		if ($list["id"] != '') {
			Cookie::set("shap", $list["id"]);
		}
		return view();
	}
	public function shape()
	{
		$snap = $_SESSION["we7_w"]["siteroot"] . "addons";
		$cuff = explode("addons", $snap);
		$cuff = explode("//", $cuff[0]);
		$cuff = explode("/", $cuff[1]);
		unset($cuff[0]);
		$cuff = array_values($cuff);
		$temp = '';
		if (count($cuff) > 0) {
			foreach ($cuff as $key => $value) {
				$temp .= "/" . $value;
			}
		} else {
			$temp = "/";
		}
		$un_data = request()->param();
		$un_data["url"] = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data["url"]);
		$un_data["url"] = $un_url[0];
		$un_data["addons"] = $temp;
		$un_data["username"] = $_SESSION["we7_user"]["username"];
		$un_data["shap"] = Cookie::get("shap");
		$un_data["ask"] = WXAPP_TYPE;
		return self::query($un_data, "shape");
	}
	public function query($un_data, $quer)
	{
		$postdata = http_build_query($un_data);
		$opts = array("http" => array("method" => "POST", "header" => "Content-type: application/x-www-form-urlencoded", "content" => $postdata, "timeout" => 15 * 60));
		$context = stream_context_create($opts);
		$result = file_get_contents(IMG_URL . "admin/alliance/" . $quer, false, $context);
		return $result;
	}
	public function setting()
	{
		$isfounder = $_SESSION["we7_w"]["isfounder"];
		if ($isfounder) {
			$uniacid = $_SESSION["we7_account"]["uniacid"];
			if (request()->isAjax() && request()->isPost()) {
				$ucid = Cookie::get("ucid");
				$data = request()->post();
				$data["uniacid"] = $uniacid;
				if ($data["id"] == '') {
					unset($data["id"]);
					$result = Db::name("ybmp_contact")->insert($data);
				} elseif ($data["id"] == $ucid) {
					$result = Db::name("ybmp_contact")->where("id", 1)->update($data);
				} else {
					$result = 0;
				}
				return $result;
			}
			$list = Db::name("ybmp_contact")->where("id", 1)->find();
			$this->assign("list", $list);
			Cookie::set("ucid", $list["id"]);
			return view();
		}
	}
	public function copyright()
	{
		$isfounder = $_SESSION["we7_w"]["isfounder"];
		if ($isfounder) {
			$is_admin = 1;
		} else {
			$is_admin = 0;
		}
		$this->assign("is_admin", $is_admin);
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["site_name"] = isset($_POST["site_name"]) ? $_POST["site_name"] : "超级名片";
			$data["content"] = isset($_POST["content"]) ? $_POST["content"] : "版权信息";
			$data["back_type"] = isset($_POST["back_type"]) ? $_POST["back_type"] : '';
			$retu = Db::name("ybmp_copyright")->where("uniacid", $this->bus_id)->find();
			if (!empty($retu)) {
				$result = Db::name("ybmp_copyright")->where("uniacid", $this->bus_id)->update($data);
			} else {
				$data["uniacid"] = $this->bus_id;
				$result = Db::name("ybmp_copyright")->insert($data);
			}
			if ($result !== false) {
				return 1;
			} else {
				return 0;
			}
		}
		$list = Db::name("ybmp_copyright")->where("uniacid", $this->bus_id)->find();
		if (empty($list)) {
			$list = Db::name("ybmp_copyright")->where("uniacid", 0)->find();
		}
		$this->assign("list", $list);
		return view();
	}
	public function download()
	{
		$isfounder = $_SESSION["we7_w"]["isfounder"];
		if ($isfounder) {
			$data["url"] = WEB_HOST;
			$data["username"] = $_SESSION["we7_user"]["username"];
			$data["uniacid"] = $_SESSION["we7_account"]["uniacid"];
			$list = self::query($data, "mand");
			$list = json_decode($list, true);
			$this->assign("cops", $list);
			return view();
		}
	}
	public function clear_temp()
	{
		$linkstr = $_SERVER["DOCUMENT_ROOT"];
		$linkstr .= "/addons/yb_mingpian/core/runtime";
		return self::rmdirs($linkstr);
	}
	function rmdirs($dir)
	{
		$dir_arr = scandir($dir);
		foreach ($dir_arr as $key => $val) {
			if ($val != "." && $val != "..") {
				if (is_dir($dir . "/" . $val)) {
					if (@rmdir($dir . "/" . $val) != "true") {
						self::rmdirs($dir . "/" . $val);
					}
				} else {
					unlink($dir . "/" . $val);
				}
			}
		}
	}
}
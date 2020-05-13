<?php


namespace app\admin\controller;

use think\Db;
use think\Cache;
class About extends Base
{
	public function index()
	{
		$info = db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->find();
		if (request()->isAjax() && request()->isPost()) {
			$f = $_FILES["mp3file"];
			if (!empty($f)) {
				if ($f["size"] > 100 && $f["size"] < 1024 * 1024 * 3) {
					$type = explode(".", $f["name"]);
					$d["name"] = date("YmdHis") . $this->bus_id . "." . $type[1];
					$d["size"] = floor($f["size"] / 1024);
					$d["tmp_name"] = $f["tmp_name"];
					$d["zname"] = SITE_PATH . "public/logo/" . $d["name"];
					$ch_path = SITE_PATH . "public/logo/";
					if (!file_exists($ch_path)) {
						$mode = intval("0777", 8);
						mkdir($ch_path, $mode, true);
					}
					move_uploaded_file($d["tmp_name"], $d["zname"]);
					$vioce_profile = explode("/core/", $d["zname"]);
					$url = explode("/core/", $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
					$url = "https://" . $url[0] . "/core/" . $vioce_profile[1];
					$data["logo"] = $url;
				} else {
					if ($f["size"] >= 1024 * 1024 * 3) {
						return AjaxReturnMsg(0, "文件" . $f["name"] . "大小错误");
					}
				}
			}
			$data["e_name"] = isset($_POST["e_name"]) ? $_POST["e_name"] : '';
			$id = isset($_POST["id"]) ? $_POST["id"] : '';
			$data["address"] = isset($_POST["address"]) ? $_POST["address"] : '';
			$data["english_name"] = isset($_POST["english_name"]) ? $_POST["english_name"] : '';
			$data["name"] = isset($_POST["names"]) ? $_POST["names"] : '';
			$data["desc"] = isset($_POST["desc"]) ? $_POST["desc"] : '';
			$data["phone"] = isset($_POST["tel"]) ? $_POST["tel"] : '';
			$data["qq"] = isset($_POST["qq"]) ? $_POST["qq"] : '';
			$data["bg_pic"] = isset($_POST["bg_pic"]) ? $_POST["bg_pic"] : '';
			$data["is_mention"] = isset($_POST["is_mention"]) ? $_POST["is_mention"] : '';
			$s = array();
			if (!empty($info["other"])) {
				$s = json_decode($info["other"], true);
			}
			$s["exp"] = isset($_POST["exp"]) ? $_POST["exp"] : '';
			$data["other"] = json_encode($s);
			if (empty($id)) {
				$data["mch_id"] = $this->bus_id;
				$res = db::name("ybmp_business_about")->insert($data);
			} else {
				$res = db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->update($data);
			}
			return AjaxReturn($res);
		}
		$info["mention"] = $this->mention[$info["is_mention"]];
		if (!empty($info["other"])) {
			$info["exp"] = json_decode($info["other"], true)["exp"];
		}
		$this->assign("info", $info);
		return view("about/about_info");
	}
}
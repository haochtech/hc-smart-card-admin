<?php


namespace app\admin\controller;

session_start();
load()->func("file");
global $_W;
$_SESSION["we7_account"] = $_W["uniaccount"];
$_SESSION["we7_user"] = $_W["user"];
$_SESSION["we7_account"] = $_W["account"];
$_W = $_SESSION["we7_w"];
use think\Controller;
use think\Db;
class Download extends Controller
{
	public function download_mod()
	{
		$id = request()->param("id");
		$res = 0;
		$che = 0;
		if (strpos($id, ",")) {
			$ids = explode(",", $id);
			for ($i = 0; $i < count($ids); $i++) {
				if ($ids[$i] > 0) {
					$che++;
					$res += intval($this->down_mod($ids[$i])["code"]);
				}
			}
		} else {
			$che++;
			$res += intval($this->down_mod($id)["code"]);
		}
		if ($res < $che && $res > 0) {
			$res = 2;
		} else {
			if ($res == $che && $res > 0) {
				$res = 1;
			}
		}
		return $res;
	}
	public function down_mod($id)
	{
		$url = THIS_URL . "admin/Download/index";
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$data = ["id" => $id, "url" => $un_data, "username" => $_SESSION["we7_user"]["username"], "uniacid" => $_SESSION["we7_account"]["uniacid"]];
		$output = post_data($url, $data, false);
		$info = json_decode($output, true)["info"];
		$values = $info["style_value"];
		$values_arr = json_decode($values, true);
		if (json_decode($output, true)["code"] == 0) {
			return AjaxReturn(0, json_decode($output, true)["msg"]);
		}
		$ckeck = Db::name("ybmp_tmpl")->where("serve_temp_id", $info["id"])->find();
		if ($ckeck) {
			return AjaxReturn(0, "已经有此模版了~");
		}
		$arr = array();
		$fail_arr = array();
		$success_arr = array();
		$max = Db::name("ybmp_tmpl")->max("id");
		$info_img = $this->GrabImage($info["img"], "public/upload/", $max + 1);
		if ($info_img === 0) {
			return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
		} elseif ($info_img === 1) {
			$fail_arr[] = $info["img"];
		}
		$arr[] = $info["img"];
		$success_arr[$info["img"]] = $info_img;
		$info["img"] = $info_img;
		$json = json_decode($info["index_values"], true);
		global $_W;
		$_W = $_SESSION["we7_w"];
		$fail_arr = array();
		$arr = array();
		foreach ($json["all_data"] as $k => $v) {
			$str = str_replace(array("http://vip.ly100.wang//", "http://vip.ly100.wang/"), '', $v["topimg"]);
			if ($str != '') {
				if (!in_array($v["topimg"], $arr)) {
					$path_url = $this->GrabImage($v["topimg"], "public/upload/", $max + 1);
					if ($path_url === 0) {
						return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
					} elseif ($path_url === 1) {
						$fail_arr[] = $v["topimg"];
					}
					$arr[] = $v["topimg"];
					$json["all_data"][$k]["topimg"] = $path_url;
					$success_arr[$v["topimg"]] = $path_url;
				} else {
					$json["all_data"][$k]["topimg"] = $success_arr[$v["topimg"]];
				}
			}
			if (!empty($v["list"])) {
				foreach ($v["list"] as $a => $b) {
					$str = str_replace(array("http://vip.ly100.wang//", "http://vip.ly100.wang/"), '', $b["imgurl"]);
					if ($str != '') {
						if (!in_array($b["imgurl"], $arr)) {
							$path_url = $this->GrabImage($b["imgurl"], "public/upload/", $max + 1);
							if ($path_url === 0) {
								return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
							} elseif ($path_url === 1) {
								$fail_arr[] = $b["imgurl"];
							}
							$arr[] = $b["imgurl"];
							$json["all_data"][$k]["list"][$a]["imgurl"] = $path_url;
							$success_arr[$b["imgurl"]] = $path_url;
						} else {
							$json["all_data"][$k]["list"][$a]["imgurl"] = $success_arr[$b["imgurl"]];
						}
					}
				}
			}
		}
		global $_W;
		$_W = $_SESSION["we7_w"];
		$nav = $values_arr["window"]["navigationBarTextStyle"] == "white" ? "#ffffff" : "#000000";
		$str = "{\n    \"tabBar\": {\n     \"name\":\"" . $info["name"] . "\",\n     \"color\": \"" . $values_arr["tabBar"]["color"] . "\",\n     \"selectedColor\":\"" . $values_arr["tabBar"]["selectedColor"] . "\",\n    \"background\":\"" . $values_arr["window"]["navigationBarBackgroundColor"] . "\",\n    \"backgroundTextStyle\":\"" . $nav . "\",\n    \"backgroundColor\": \"" . $values_arr["tabBar"]["backgroundColor"] . "\",\n        \"list\": [\n            {\n                \"key\": \"index\",\n                \"imgurl\": \"/yb_mingpian/pages/index/index\",\n                \"name\": \"首页\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_home.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_home.png\"\n            },\n            {\n                \"key\": \"shop_coupon\",\n                \"imgurl\": \"/yb_mingpian/pages/shop_coupon/index\",\n                \"name\": \"优惠券\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_find.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_find.png\"\n            },\n            {\n                \"key\": \"product\",\n                \"imgurl\": \"/yb_mingpian/pages/product/index\",\n                \"name\": \"商品\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_cate.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_cate.png\"\n            },\n            {\n                \"key\": \"cart\",\n                \"imgurl\": \"/yb_mingpian/pages/member/cart/index\",\n                \"name\": \"购物车\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_cart.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_cart.png\"\n            },\n            {\n                \"key\": \"member_index\",\n                \"imgurl\": \"/yb_mingpian/pages/member/index/index\",\n                \"name\": \"会员中心\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_people.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_people.png\"\n            }\n        ]\n    }\n}";
		$res = Db::name("ybmp_tmpl")->insert(["style_value" => $str, "serve_temp_id" => $info["id"], "name" => $info["name"], "template_id" => 1, "mod_class_id" => 1, "create_time" => 0, "is_default" => 0, "index_values" => json_encode($json), "img" => $info["img"]]);
		$dd["all"] = $arr;
		$dd["fail"] = $fail_arr;
		$dd["success"] = $success_arr;
		return AjaxReturn($res, $dd);
	}
	function GrabImage($url, $dir, $mod_id)
	{
		if (empty($url)) {
			return false;
		}
		$ext = strrchr($url, ".");
		if ($ext != ".png" && $ext != ".gif" && $ext != ".jpg" && $ext != ".bmp") {
			echo "格式不支持！";
			return false;
		}
		$dir = realpath($dir);
		$dir .= "/sys_tmpl/" . $mod_id . "/";
		if (!file_exists($dir)) {
			$mode = intval("0777", 8);
			mkdir($dir, $mode, true);
		}
		$file_fax = $this->create_uuid() . $ext;
		$path = $dir . $file_fax;
		ob_start();
		$r = readfile($url);
		if ($r === false) {
			return 1;
		}
		$img = ob_get_contents();
		ob_end_clean();
		$size = strlen($img);
		$fp2 = fopen($path, "a+");
		fwrite($fp2, $img);
		fclose($fp2);
		global $_W;
		$_W = $_SESSION["we7_w"];
		if (!empty($_W["setting"]["remote"]["type"])) {
			$file_path = "public/upload/sys_tmpl/" . $mod_id . "/" . $file_fax;
			if (file_exists(RO_PATH . "/" . $file_path) && filesize($file_path) > 0) {
				$remotestatus = file_remote_upload($file_path);
				if (is_error($remotestatus)) {
					return 0;
				} else {
					$remoteurl = tomedia($file_path);
					return $remoteurl;
				}
			}
		}
		return $_W["siteroot"] . "addons/yb_mingpian/core/public/upload/sys_tmpl/" . $mod_id . "/" . $file_fax;
	}
	public function update_download_mod()
	{
		$mod_id = input("param.mod_id");
		$id = input("param.id");
		$info = Db::name("ybmp_tmpl")->where("id", $mod_id)->find();
		$json = json_decode($info["index_values"], true);
		foreach ($json["all_data"] as $k => $v) {
			@unlink($json["all_data"][$k]["topimg"]);
			if (!empty($v["list"])) {
				foreach ($v["list"] as $a => $b) {
					@unlink($json["all_data"][$k]["list"][$a]["imgurl"]);
				}
			}
		}
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$url = THIS_URL . "admin/Download/index";
		$data = ["id" => $id, "url" => $un_data, "username" => $_SESSION["we7_user"]["username"], "uniacid" => $_SESSION["we7_account"]["uniacid"]];
		$output = post_data($url, $data, false);
		$info = json_decode($output, true)["info"];
		$values = json_decode($output, true)["info"]["style_value"];
		$values_arr = json_decode($values, true);
		if (json_decode($output, true)["code"] == 0) {
			return AjaxReturn(0, json_decode($output, true)["msg"]);
		}
		$arr = array();
		$fail_arr = array();
		$success_arr = array();
		$info_img = $this->GrabImage($info["img"], "public/upload/", $mod_id);
		if ($info_img === 0) {
			return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
		} elseif ($info_img === 1) {
			$fail_arr[] = $info["img"];
		}
		$arr[] = $info["img"];
		$success_arr[$info["img"]] = $info_img;
		$info["img"] = $info_img;
		$json = json_decode($info["index_values"], true);
		global $_W;
		$_W = $_SESSION["we7_w"];
		foreach ($json["all_data"] as $k => $v) {
			$str = str_replace(array("http://vip.ly100.wang//", "http://vip.ly100.wang/"), '', $v["topimg"]);
			if ($str != '') {
				if (!in_array($v["topimg"], $arr)) {
					$path_url = $this->GrabImage($v["topimg"], "public/upload/", $mod_id);
					if ($path_url === 0) {
						return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
					} elseif ($path_url === 1) {
						$fail_arr[] = $v["topimg"];
					}
					$arr[] = $v["topimg"];
					$json["all_data"][$k]["topimg"] = $path_url;
					$success_arr[$v["topimg"]] = $path_url;
				} else {
					$json["all_data"][$k]["topimg"] = $success_arr[$v["topimg"]];
				}
			}
			if (!empty($v["list"])) {
				foreach ($v["list"] as $a => $b) {
					$str = str_replace(array("http://vip.ly100.wang//", "http://vip.ly100.wang/"), '', $b["imgurl"]);
					if ($str != '') {
						if (!in_array($b["imgurl"], $arr)) {
							$path_url = $this->GrabImage($b["imgurl"], "public/upload/", $mod_id);
							if ($path_url === 0) {
								return AjaxReturn(0, "远程附件上传失败,请检查远程附件配置");
							} elseif ($path_url === 1) {
								$fail_arr[] = $b["imgurl"];
							}
							$arr[] = $b["imgurl"];
							$json["all_data"][$k]["list"][$a]["imgurl"] = $path_url;
							$success_arr[$b["imgurl"]] = $path_url;
						} else {
							$json["all_data"][$k]["list"][$a]["imgurl"] = $success_arr[$b["imgurl"]];
						}
					}
				}
			}
		}
		global $_W;
		$_W = $_SESSION["we7_w"];
		$nav = $values_arr["window"]["navigationBarTextStyle"] == "white" ? "#ffffff" : "#000000";
		$str = "{\n    \"tabBar\": {\n     \"name\":\"" . $info["name"] . "\",\n     \"color\": \"" . $values_arr["tabBar"]["color"] . "\",\n     \"selectedColor\":\"" . $values_arr["tabBar"]["selectedColor"] . "\",\n    \"background\":\"" . $values_arr["window"]["navigationBarBackgroundColor"] . "\",\n    \"backgroundTextStyle\":\"" . $nav . "\",\n    \"backgroundColor\": \"" . $values_arr["tabBar"]["backgroundColor"] . "\",\n        \"list\": [\n            {\n                \"key\": \"index\",\n                \"imgurl\": \"/yb_mingpian/pages/index/index\",\n                \"name\": \"首页\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_home.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_home.png\"\n            },\n            {\n                \"key\": \"shop_coupon\",\n                \"imgurl\": \"/yb_mingpian/pages/shop_coupon/index\",\n                \"name\": \"优惠券\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_find.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_find.png\"\n            },\n            {\n                \"key\": \"product\",\n                \"imgurl\": \"/yb_mingpian/pages/product/index\",\n                \"name\": \"商品\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_cate.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_cate.png\"\n            },\n            {\n                \"key\": \"cart\",\n                \"imgurl\": \"/yb_mingpian/pages/member/cart/index\",\n                \"name\": \"购物车\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_cart.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_cart.png\"\n            },\n            {\n                \"key\": \"member_index\",\n                \"imgurl\": \"/yb_mingpian/pages/member/index/index\",\n                \"name\": \"会员中心\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_people.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_people.png\"\n            }\n        ]\n    }\n}";
		if (Db::name("ybmp_tmpl")->where("id", $mod_id)->where("serve_temp_id", $info["id"])->find()) {
			$res = Db::name("ybmp_tmpl")->where("id", $mod_id)->update(["index_values" => json_encode($json), "style_value" => $str, "img" => $info["img"]]);
		} else {
			$res = Db::name("ybmp_tmpl")->where("id", $mod_id)->update(["serve_temp_id" => $info["id"], "index_values" => json_encode($json), "style_value" => $str, "img" => $info["img"]]);
		}
		$dd["all"] = $arr;
		$dd["fail"] = $fail_arr;
		$dd["success"] = $success_arr;
		return AjaxReturn($res, $dd);
	}
	function create_uuid($prefix = "MOD")
	{
		$str = md5(uniqid(mt_rand(), true));
		$uuid = substr($str, 0, 8) . "-";
		$uuid .= substr($str, 8, 4) . "-";
		$uuid .= substr($str, 12, 4) . "-";
		$uuid .= substr($str, 16, 4) . "-";
		$uuid .= substr($str, 20, 12);
		return $prefix . $uuid;
	}
	public function moveUploadFile($file_path, $key)
	{
		$ok = @move_uploaded_file($file_path, $key);
		$result = ["code" => $ok, "path" => $key, "domain" => '', "bucket" => ''];
		return $result;
	}
}
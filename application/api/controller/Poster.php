<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
require_once BASE_ROOT . "core/application/api_common.php";
use think\Request;
use think\Db;
use think\Session;
use think\Cache;
class Poster extends BaseController
{
	public function cardposter()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("staff_id");
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["id", "require"], ["mch_id", "require", "不存在商户"]];
		$data = ["id" => $id, "mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$data["is_del"] = 0;
		$data["radar"] = 1;
		$info = Db::name("ybmp_bus_card")->where($data)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "名片不存在";
			return json_encode($rs);
		}
		$info["bus"] = Db::name("ybmp_business_about")->where("mch_id", $data["mch_id"])->field("name,logo,address")->find();
		$gData = $info;
		if (Cache::get("poster_qcode")) {
			$gData["qcode"] = Cache::get("poster_qcode_" . $id);
		} else {
			$ACCESS_TOKEN = getWxAccessToken($mch_id);
			if ($ACCESS_TOKEN["errcode"] == 0) {
				$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
				$post_data = array("scene" => $id, "page" => "yb_mingpian/pages/cardinfo/index");
				$data2 = post_data2($url, $post_data, false);
				$data3 = json_decode($data2, true);
				if (empty($data3)) {
					Cache::set("poster_qcode_" . $id, $data2);
					$gData["qcode"] = $data2;
				}
			}
		}
		$data = $this->createSharePng2($gData, BASE_ROOT . "/image/scan.png", $id);
		$rs["info"] = $data;
		return json_encode($rs);
	}
	public function createSharePng2($gData, $codeName, $id, $fileName = '')
	{
		$im = imagecreatetruecolor(600, 940);
		$color = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $color);
		$font_file = BASE_ROOT . "/core/public/static/font/MSYH.TTC";
		$font_file_bold = BASE_ROOT . "/core/public/static/font/MSYHBD.TTC";
		$font_color_2 = ImageColorAllocate($im, 28, 28, 28);
		$font_color_3 = ImageColorAllocate($im, 129, 129, 129);
		$font_color_4 = ImageColorAllocate($im, 220, 220, 220);
		$font_color_red = ImageColorAllocate($im, 217, 45, 32);
		$fang_bg_color = ImageColorAllocate($im, 254, 216, 217);
		$goodImg = $this->createImageFromFile($gData["head_photo"]);
		$g_w = imagesx($goodImg);
		$g_h = imagesy($goodImg);
		imagecopyresized($im, $goodImg, 0, 0, 0, 0, 600, 600, $g_w, $g_h);
		if ($gData["qcode"]) {
			$codeImg2 = @imagecreatefromstring($gData["qcode"]);
			imagecopyresized($im, $codeImg2, 374, 700, 0, 0, 170, 170, 430, 430);
		}
		imagettftext($im, 18, 0, 22, 705, $font_color_2, $font_file, $gData["user_name"]);
		imagettftext($im, 18, 0, 22, 740, $font_color_2, $font_file, $gData["position"]);
		imagettftext($im, 16, 0, 22, 800, $font_color_3, $font_file, "手机");
		imagettftext($im, 16, 0, 85, 800, $font_color_3, $font_file, $gData["tel"]);
		imagettftext($im, 16, 0, 22, 840, $font_color_3, $font_file, "微信");
		imagettftext($im, 16, 0, 85, 840, $font_color_3, $font_file, $gData["wechat_number"]);
		if (isset($gData["bus"]["address"]) && !empty($gData["bus"]["address"])) {
			$address = $this->cn_row_substr($gData["bus"]["address"], 2, 12);
			imagettftext($im, 16, 0, 22, 880, $font_color_3, $font_file, "地址");
			imagettftext($im, 16, 0, 85, 880, $font_color_3, $font_file, $address[1]);
			imagettftext($im, 16, 0, 85, 910, $font_color_3, $font_file, $address[2]);
		}
		imagesetthickness($im, 1);
		imageline($im, 22, 762, 280, 762, $font_color_4);
		if ($fileName) {
			imagepng($im, $fileName);
		} else {
			Header("Content-Type: image/png");
			imagepng($im);
		}
		imagedestroy($im);
		imagedestroy($goodImg);
		imagedestroy($codeImg2);
	}
	public function getpic()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$url = Request::instance()->param("url");
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$url = urldecode($url);
		$data = $this->get_url_content3($url);
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		Header("Content-Type: image/" . $ext);
		exit($data);
	}
	function get_url_content3($url, $method = true)
	{
		global $_W;
		$site_root = $_W["siteroot"];
		$site_root = str_replace(["http://", "https://"], '', $site_root);
		$arr = ["jpg", "png", "jpeg", "gif"];
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		if (in_array($ext, $arr)) {
			$tmp_url = $url;
			$tmp_url = str_replace("http://" . $site_root . "addons/yb_mingpian/core/", SITE_PATH, $tmp_url);
			$tmp_url = str_replace("https://" . $site_root . "addons/yb_mingpian/core/", SITE_PATH, $tmp_url);
			if (file_exists($tmp_url)) {
				return file_get_contents($tmp_url);
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if ($method) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$cont = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($httpCode == "301") {
			$cont = file_get_contents($url);
		}
		return $cont;
	}
	public function getqrcode()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$path = Request::instance()->param("path");
		$scene = Request::instance()->param("scene");
		$pid = Request::instance()->param("pid", 0);
		$card_id = Request::instance()->param("card_id", 0);
		$rule = [["mch_id", "require", "不存在商户"]];
		$data = ["mch_id" => $mch_id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$ACCESS_TOKEN = getWxAccessToken($mch_id);
		if ($ACCESS_TOKEN["access_token"]) {
			$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
			$post_data = array("scene" => $scene . "," . $pid . "," . $card_id, "page" => $path);
			$post_data = json_encode($post_data);
			$data = $this->post_data2($url, $post_data, false);
			Header("Content-Type: image/png");
			exit($data);
		} else {
			exit('');
		}
	}
	public function post_data2($url, $param, $return_array = true, $is_file = false)
	{
		if (!$is_file && is_array($param)) {
			$param = json_encode($param, true);
		}
		if ($is_file) {
			$header[] = "content-type: multipart/form-data; charset=UTF-8";
		} else {
			$header[] = "content-type: application/json; charset=UTF-8";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		$return_array && ($res = json_decode($res, true));
		return $res;
	}
	function createImageFromFile($file)
	{
		if (preg_match("/http(s)?:\\/\\//", $file)) {
			$fileSuffix = $this->getNetworkImgType($file);
		} else {
			$fileSuffix = pathinfo($file, PATHINFO_EXTENSION);
		}
		if (!$fileSuffix) {
			return false;
		}
		$theImage = null;
		$theImage = @imagecreatefromjpeg($file);
		if (!empty($theImage)) {
			return $theImage;
		}
		$theImage = @imagecreatefromjpeg($file);
		if (!empty($theImage)) {
			return $theImage;
		}
		$theImage = @imagecreatefrompng($file);
		if (!empty($theImage)) {
			return $theImage;
		}
		$theImage = @imagecreatefromgif($file);
		if (!empty($theImage)) {
			return $theImage;
		}
		$theImage = @imagecreatefromstring($this->get_url_content($file));
		if (!empty($theImage)) {
			return $theImage;
		}
		switch ($fileSuffix) {
			case "jpeg":
				$theImage = @imagecreatefromjpeg($file);
				exit(json_encode($theImage));
				break;
			case "jpg":
				$theImage = @imagecreatefromjpeg($file);
				break;
			case "png":
				$theImage = @imagecreatefrompng($file);
				break;
			case "gif":
				$theImage = @imagecreatefromgif($file);
				break;
			default:
				$theImage = @imagecreatefromstring($this->get_url_content($file));
				break;
		}
		return $theImage;
	}
	function getNetworkImgType($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_exec($ch);
		$http_code = curl_getinfo($ch);
		curl_close($ch);
		if ($http_code["http_code"] == 200) {
			$theImgType = explode("/", $http_code["content_type"]);
			if ($theImgType[0] == "image") {
				return $theImgType[1];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function cn_row_substr($str, $row = 1, $number = 10, $suffix = true)
	{
		$result = array();
		for ($r = 1; $r <= $row; $r++) {
			$result[$r] = '';
		}
		$str = trim($str);
		if (!$str) {
			return $result;
		}
		$theStrlen = strlen($str);
		$oneRowNum = $number * 3;
		for ($r = 1; $r <= $row; $r++) {
			if ($r == $row and $theStrlen > $r * $oneRowNum and $suffix) {
				$result[$r] = $this->mg_cn_substr($str, $oneRowNum - 6, ($r - 1) * $oneRowNum) . "...";
			} else {
				$result[$r] = $this->mg_cn_substr($str, $oneRowNum, ($r - 1) * $oneRowNum);
			}
			if ($theStrlen < $r * $oneRowNum) {
				break;
			}
		}
		return $result;
	}
	function mg_cn_substr($str, $len, $start = 0)
	{
		$q_str = '';
		$q_strlen = $start + $len > strlen($str) ? strlen($str) : $start + $len;
		if ($start and json_encode(substr($str, $start, 1)) === false) {
			for ($a = 0; $a < 3; $a++) {
				$new_start = $start + $a;
				$m_str = substr($str, $new_start, 3);
				if (json_encode($m_str) !== false) {
					$start = $new_start;
					break;
				}
			}
		}
		for ($i = $start; $i < $q_strlen; $i++) {
			if (ord(substr($str, $i, 1)) > 0xa0) {
				$q_str .= substr($str, $i, 3);
				$i += 2;
			} else {
				$q_str .= substr($str, $i, 1);
			}
		}
		return $q_str;
	}
}
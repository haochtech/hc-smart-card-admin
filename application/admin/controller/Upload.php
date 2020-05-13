<?php


namespace app\admin\controller;

use app\admin\service\Images;
use app\common\model\ImagesGroup;
load()->func("file");
global $_W;
$_W = $_SESSION["we7_w"];
use think\Controller;
use think\config;
use think\Db;
use think\Image;
use think\Session;
include EXTEND_PATH . "Arliki/img_compress.php";
define("UPLOAD_VIDEO", Config::get("view_replace_str.UPLOAD_VIDEO"));
class Upload extends Controller
{
	private $return = array();
	private $file_path = '';
	private $file_name = '';
	private $file_size = 0;
	private $file_type = '';
	private $upload_type = 2;
	public function uploadFile()
	{
		$mch_id = Session::get("bus_id");
		$old_path = request()->post("file_path", '');
		$this->file_path = "public/upload/" . $mch_id . "/" . $old_path;
		if ($this->file_path == '') {
			$this->return["message"] = "文件路径不能为空";
			return $this->ajaxFileReturn();
		}
		if (!file_exists($this->file_path)) {
			$mode = intval("0777", 8);
			mkdir($this->file_path, $mode, true);
		}
		$this->file_name = $_FILES["file_upload"]["name"];
		$this->file_size = $_FILES["file_upload"]["size"];
		$this->file_type = $_FILES["file_upload"]["type"];
		if ($this->file_size == 0) {
			$this->return["message"] = "文件大小为0MB";
			return $this->ajaxFileReturn();
		}
		if (!$this->validationFile()) {
			return $this->ajaxFileReturn();
		}
		$guid = md5(time());
		$file_name_explode = explode(".", $this->file_name);
		$suffix = count($file_name_explode) - 1;
		$ext = "." . $file_name_explode[$suffix];
		$newfile = $guid . $ext;
		$ok = $this->moveUploadFile($_FILES["file_upload"]["tmp_name"], $this->file_path . $newfile);
		$mimetype = exif_imagetype($ok["path"]);
		if ($mimetype != IMAGETYPE_GIF && $mimetype != IMAGETYPE_JPEG && $mimetype != IMAGETYPE_PNG && $mimetype != IMAGETYPE_BMP) {
			$this->return["message"] = "文件格式错误";
			return $this->ajaxFileReturn();
		}
		global $_W;
		if (!empty($_W["setting"]["remote"]["type"])) {
			$pathname = $ok["path"];
			$remotestatus = file_remote_upload($pathname);
			if (is_error($remotestatus)) {
				return "远程附件上传失败，请检查配置并重新上传";
			} else {
				$remoteurl = tomedia($ok["path"]);
				$this->return["code"] = 1;
				$this->return["data"] = $remoteurl;
				$this->return["message"] = "上传成功";
				return json_encode($this->return);
			}
		}
		if ($ok["code"]) {
			if (!strstr(UPLOAD_VIDEO, $this->file_path)) {
				@unlink($_FILES["file_upload"]);
				if ($old_path == "video/video/") {
					$this->return["code"] = 1;
					$this->return["data"] = $_W["siteroot"] . "addons/yb_mingpian/core/" . $ok["path"];
					$this->return["message"] = "上传成功";
				} else {
					$image_size = getimagesize($_W["siteroot"] . "addons/yb_mingpian/core/" . $ok["path"]);
					if ($image_size) {
						$width = $image_size[0];
						$height = $image_size[1];
						$name = $file_name_explode[0];
						$group = new ImagesGroup();
						$group_id = $group->getInfo(["mch_id" => $mch_id, "is_default" => "1"]);
						if ($old_path == "goods/") {
							$type = request()->post("type", '');
							$pic_name = request()->post("img_name", $guid);
							$album_id = $group_id["group_id"];
							$pic_tag = request()->post("img_tag", $name);
							$pic_id = request()->post("img_id", '');
							$upload_flag = request()->post("upload_flag", '');
							$retval = $this->photoCreate($this->file_path, str_replace("http:/", "https:/", $_W["siteroot"] . "addons/yb_mingpian/core/" . $ok["path"]), "." . $file_name_explode[$suffix], $type, $pic_name, $album_id, $width, $height, $pic_tag, $pic_id, $ok["domain"], $ok["bucket"], true);
							if ($retval > 0) {
								$this->return["code"] = $retval;
								$this->return["message"] = "上传成功";
								$this->return["data"] = str_replace("http:/", "https:/", $_W["siteroot"] . "addons/yb_mingpian/core/" . $ok["path"]);
							} else {
								$this->return["message"] = "上传失败";
							}
						} else {
							$this->return["code"] = 1;
							$this->return["data"] = str_replace("http:/", "https:/", $_W["siteroot"] . "addons/yb_mingpian/core/" . $ok["path"]);
							$this->return["message"] = "上传成功";
						}
					} else {
						$this->return["message"] = "请检查您的上传参数配置或上传的文件是否有误";
					}
				}
			}
		} else {
			$this->return["message"] = "请检查您的上传参数配置或上传的文件是否有误";
		}
		return $this->ajaxFileReturn();
	}
	private function ajaxFileReturn()
	{
		if (empty($this->return["code"]) || null == $this->return["code"] || '' == $this->return["code"]) {
			$this->return["code"] = 0;
		}
		if (empty($this->return["message"]) || null == $this->return["message"] || '' == $this->return["message"]) {
			$this->return["message"] = '';
		}
		if (empty($this->return["data"]) || null == $this->return["data"] || '' == $this->return["data"]) {
			$this->return["data"] = '';
		}
		return $this->return;
	}
	private function validationFile()
	{
		$flag = true;
		if ($this->file_size > 10000000) {
			$this->return["message"] = "文件上传失败,请检查您上传的文件类型,文件大小不能超过10MB";
			$flag = false;
		}
		$alltype = ["image/jpg", "image/jpeg", "image/png", "image/gif"];
		if (!in_array($this->file_type, $alltype)) {
			$this->return["message"] = "文件类型错误";
			$flag = false;
		}
		return $flag;
	}
	public function photoCreate($upFilePath, $photoPath, $ext, $type = 0, $pic_name, $album_id, $width, $height, $pic_tag, $pic_id, $domain, $bucket, $local = false)
	{
		global $_W;
		$_W = $_SESSION["we7_w"];
		$photoArray = array("bigPath" => array("path" => '', "width" => 700, "height" => 700, "type" => "1"), "middlePath" => array("path" => '', "width" => 360, "height" => 360, "type" => "2"), "smallPath" => array("path" => '', "width" => 240, "height" => 240, "type" => "3"), "littlePath" => array("path" => '', "width" => 60, "height" => 60, "type" => "4"));
		if ($this->upload_type == 1) {
			$image = \think\Image::open($photoPath);
			$photoArray["bigPath"]["path"] = $upFilePath . md5(time() . $pic_tag) . "1" . $ext;
			$photoArray["middlePath"]["path"] = $upFilePath . md5(time() . $pic_tag) . "2" . $ext;
			$photoArray["smallPath"]["path"] = $upFilePath . md5(time() . $pic_tag) . "3" . $ext;
			$photoArray["littlePath"]["path"] = $upFilePath . md5(time() . $pic_tag) . "4" . $ext;
			foreach ($photoArray as $v) {
				if (stristr($type, $v["type"])) {
					$image->thumb($v["width"], $v["height"], \think\Image::sihEd)->save($v["path"]);
				}
			}
		}
		$album = new Images();
		if ($local) {
			$https_path = str_replace("http:/", "https:/", $_W["siteroot"] . "addons/yb_mingpian/core/" . $photoPath);
		} else {
			$https_path = $_W["siteroot"] . "addons/yb_mingpian/core/" . $photoPath;
		}
		if ($pic_id == '') {
			$retval = $album->addPicture($album_id, $pic_name, $pic_tag, $https_path, $width . "," . $height, $width . "," . $height, $photoArray["bigPath"]["path"], $photoArray["bigPath"]["width"] . "," . $photoArray["bigPath"]["height"], $photoArray["bigPath"]["width"] . "," . $photoArray["bigPath"]["height"], $photoArray["middlePath"]["path"], $photoArray["middlePath"]["width"] . "," . $photoArray["middlePath"]["height"], $photoArray["middlePath"]["width"] . "," . $photoArray["middlePath"]["height"], $photoArray["smallPath"]["path"], $photoArray["smallPath"]["width"] . "," . $photoArray["smallPath"]["height"], $photoArray["smallPath"]["width"] . "," . $photoArray["smallPath"]["height"], $photoArray["littlePath"]["path"], $photoArray["littlePath"]["width"] . "," . $photoArray["littlePath"]["height"], $photoArray["littlePath"]["width"] . "," . $photoArray["littlePath"]["height"], $this->upload_type, $domain, $bucket);
		} else {
			$retval = $album->ModifyAlbumPicture($pic_id, $https_path, $width . "," . $height, $width . "," . $height, $photoArray["bigPath"]["path"], $photoArray["bigPath"]["width"] . "," . $photoArray["bigPath"]["height"], $photoArray["bigPath"]["width"] . "," . $photoArray["bigPath"]["height"], $photoArray["middlePath"]["path"], $photoArray["middlePath"]["width"] . "," . $photoArray["middlePath"]["height"], $photoArray["middlePath"]["width"] . "," . $photoArray["middlePath"]["height"], $photoArray["smallPath"]["path"], $photoArray["smallPath"]["width"] . "," . $photoArray["smallPath"]["height"], $photoArray["smallPath"]["width"] . "," . $photoArray["smallPath"]["height"], $photoArray["littlePath"]["path"], $photoArray["littlePath"]["width"] . "," . $photoArray["littlePath"]["height"], $photoArray["littlePath"]["width"] . "," . $photoArray["littlePath"]["height"], $this->instance_id, $this->upload_type, $domain, $bucket);
			$retval = $pic_id;
		}
		return $retval;
	}
	public function moveUploadFile($file_path, $key)
	{
		if ($this->upload_type == 2) {
			$ok = @move_uploaded_file($file_path, $key);
			img_zip($key, 1);
			$result = ["code" => $ok, "path" => $key, "domain" => '', "bucket" => ''];
		}
		return $result;
	}
	public function photoAlbumUpload()
	{
		$data = array();
		$mch_id = Session::get("bus_id");
		$album_id = request()->post("group_id", '');
		$old_path = $this->file_path = request()->post("file_path", '');
		$this->file_path = "public/upload/" . $mch_id . "/" . $old_path;
		if ($this->file_path == '') {
			$this->return["message"] = "文件路径不能为空";
			return $this->ajaxFileReturn();
		}
		if (!file_exists($this->file_path)) {
			$mode = intval("0777", 8);
			mkdir($this->file_path, $mode, true);
		}
		$this->file_name = $_FILES["file_upload"]["name"];
		$this->file_size = $_FILES["file_upload"]["size"];
		$this->file_type = $_FILES["file_upload"]["type"];
		if ($this->file_size == 0) {
			$this->return["message"] = "文件大小为0MB";
			return $this->ajaxFileReturn();
		}
		if ($this->file_size > 5000000) {
			$this->return["message"] = "文件大小不能超过5MB";
			return $this->ajaxFileReturn();
		}
		if (!$this->validationFile()) {
			return $this->ajaxFileReturn();
		}
		$guid = md5(time());
		$file_name_explode = explode(".", $this->file_name);
		$suffix = count($file_name_explode) - 1;
		$ext = "." . $file_name_explode[$suffix];
		$tmp_array = $file_name_explode;
		unset($tmp_array[$suffix]);
		$file_new_name = implode(".", $tmp_array);
		$newfile = md5($file_new_name . $guid) . $ext;
		$ok = $this->moveUploadFile($_FILES["file_upload"]["tmp_name"], $this->file_path . $newfile);
		$mimetype = exif_imagetype($ok["path"]);
		if ($mimetype != IMAGETYPE_GIF && $mimetype != IMAGETYPE_JPEG && $mimetype != IMAGETYPE_PNG && $mimetype != IMAGETYPE_BMP) {
			$this->return["message"] = "文件格式错误";
			return $this->ajaxFileReturn();
		}
		global $_W;
		if (!empty($_W["setting"]["remote"]["type"])) {
			$pathname = $ok["path"];
			$remotestatus = file_remote_upload($pathname);
			if (is_error($remotestatus)) {
				return "远程附件上传失败，请检查配置并重新上传";
			} else {
				$remoteurl = tomedia($ok["path"]);
				$data["origin_file_name"] = $this->file_name;
				$data["file_path"] = $remoteurl;
				$res = Db::name("ybmp_images")->insertGetId(["upload_time" => time(), "group_id" => $album_id, "img_name" => $newfile, "img_tag" => '', "img_cover" => $remoteurl]);
				$data["state"] = 1;
				$data["code"] = 1;
				$data["file_id"] = $res;
				$data["file_name"] = '';
				return $data;
			}
		}
		if ($ok["code"]) {
			@unlink($_FILES["file_upload"]);
			$image_size = @getimagesize($ok["path"]);
			if ($image_size) {
				$group = new ImagesGroup();
				$group_id = $group->getInfo(["mch_id" => $mch_id, "is_default" => "1"]);
				$width = $image_size[0];
				$height = $image_size[1];
				$name = $file_name_explode[0];
				$type = request()->post("type", '');
				$pic_name = request()->post("img_name", $file_new_name . $guid);
				$album_id = request()->post("group_id", '');
				if (empty($album_id)) {
					$album_id = $group_id["group_id"];
				}
				$pic_tag = request()->post("img_tag", $file_new_name);
				$pic_id = request()->post("img_id", '');
				$upload_flag = request()->post("upload_flag", '');
				$retval = $this->photoCreate($this->file_path, $ok["path"], "." . $file_name_explode[$suffix], $type, $pic_name, $album_id, $width, $height, $pic_tag, $pic_id, $ok["domain"], $ok["bucket"], true);
				if ($retval > 0) {
					$album = new Images();
					$picture_info = $album->getAlubmPictureDetail(["img_id" => $retval]);
					$data["file_id"] = $retval;
					$data["file_name"] = $picture_info["img_name"];
					$data["origin_file_name"] = $this->file_name;
					$data["file_path"] = str_replace("http:/", "https:/", $_W["siteroot"] . "addons/yb_mingpian/core/" . $this->file_path . $newfile);
					$data["state"] = "1";
					$data["code"] = 1;
					return $data;
				} else {
					$this->return["message"] = "图片上传失败";
					return $this->ajaxFileReturn();
				}
			} else {
				$this->return["message"] = "图片上传失败";
				return $this->ajaxFileReturn();
			}
		} else {
			$this->return["message"] = "图片上传失败";
			return $this->ajaxFileReturn();
		}
	}
}
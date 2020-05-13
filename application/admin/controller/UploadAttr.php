<?php


namespace app\admin\controller;

use app\admin\service\Images;
use think\Controller;
use think\Config;
use think\Image;
define("UPLOAD_VIDEO", Config::get("view_replace_str.UPLOAD_VIDEO"));
class UploadAttr extends Controller
{
	private $return = array();
	private $file_path = '';
	private $file_name = '';
	private $file_size = 0;
	private $file_type = '';
	private $upload_type = 1;
	public function uploadFile()
	{
		$this->file_path = '';
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
		$guid = time();
		$file_name_explode = explode(".", $this->file_name);
		$suffix = count($file_name_explode) - 1;
		$ext = "." . $file_name_explode[$suffix];
		$newfile = "favicon.ico";
		$ok = $this->moveUploadFile($_FILES["file_upload"]["tmp_name"], $this->file_path . $newfile);
		if ($ok["code"]) {
			if (!strstr(UPLOAD_VIDEO, $this->file_path)) {
				@unlink($_FILES["file_upload"]);
				$image_size = getimagesize($ok["path"]);
				if ($image_size) {
					if ($this->file_path == "upload/goods/") {
						$retval = 1;
						if ($retval > 0) {
							$this->return["code"] = $retval;
							$this->return["message"] = "上传成功";
							$this->return["data"] = $ok["path"];
						} else {
							$this->return["message"] = "上传失败";
						}
					} else {
						$this->return["code"] = 1;
						$this->return["data"] = $ok["path"];
						$this->return["message"] = "上传成功";
					}
				} else {
					$this->return["message"] = "请检查您的上传参数配置或上传的文件是否有误";
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
		return json_encode($this->return);
	}
	private function validationFile()
	{
		$flag = true;
		if ($this->file_type != "image/gif" && $this->file_type != "image/png" && $this->file_type != "image/jpeg" && $this->file_size > 3000000) {
			$this->return["message"] = "文件上传失败,请检查您上传的文件类型,文件大小不能超过3MB";
			$flag = false;
		}
		return $flag;
	}
	public function moveUploadFile($file_path, $key)
	{
		if ($this->upload_type == 1) {
			$ok = @move_uploaded_file($file_path, $key);
			$result = ["code" => $ok, "path" => $key, "domain" => '', "bucket" => ''];
		}
		return $result;
	}
}
<?php


namespace app\app\controller;

use app\admin\controller\Upload;
use think\Request;
use think\Db;
require_once "Upload.php";
class Picture extends BaseController
{
	public function groups()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where["g.mch_id"] = $app_id;
		$list = Db::name("ybmp_images_group")->alias("g")->join("ybmp_images i", "g.group_cover = i.img_id", "LEFT")->field("g.group_id,g.group_name,g.is_default,i.img_cover")->where($where)->select();
		exit(json_encode($list, true));
	}
	public function addgroup()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$name = trim(Request::instance()->param("name"));
		if (empty($name)) {
			$rs["code"] = 1;
			$rs["msg"] = "相册名称不能为空";
			exit(json_encode($rs, true));
		}
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$id = Request::instance()->param("group_id");
		if (empty($id)) {
			$data["group_name"] = $name;
			$data["pid"] = 0;
			$data["is_default"] = 0;
			$data["sort"] = 1;
			$data["create_time"] = time();
			$data["mch_id"] = $app_id;
			$data["is_system"] = 0;
			$id = Db::name("ybmp_images_group")->insertGetId($data);
		} else {
			$data["group_name"] = $name;
			$upid = Db::name("ybmp_images_group")->where(["group_id" => $id])->update($data);
		}
		$upload = new Upload();
		$res = $upload->uploadFile($app_id);
		$res = json_decode($res, true);
		if ($res["code"] == 1) {
			$pic["group_id"] = $id;
			$pic["img_name"] = $res["name"];
			$pic["img_cover"] = str_replace("http:/", "https:/", $res["data"]);
			$pic["upload_time"] = time();
			$img_id = Db::name("ybmp_images")->insertGetId($pic);
			$dd["group_cover"] = $img_id;
			$upid = Db::name("ybmp_images_group")->where(["group_id" => $id])->update($dd);
		}
		if (empty($id)) {
			$rs["code"] = 1;
			$rs["msg"] = "相册创建失败";
		} else {
			$rs["code"] = 0;
			if (isset($upid)) {
				$rs["msg"] = "相册编辑成功";
			} else {
				$rs["msg"] = "相册创建成功";
			}
		}
		exit(json_encode($rs, true));
	}
	public function delgroup()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$id = Request::instance()->param("group_id");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where["group_id"] = $id;
		$where["mch_id"] = $app_id;
		$where["is_default"] = 0;
		$res = Db::name("ybmp_images_group")->where($where)->delete();
		if (empty($res)) {
			$rs["code"] = 1;
			$rs["msg"] = "相册删除失败";
		} else {
			$rs["code"] = 0;
			$rs["msg"] = "相册删除成功";
		}
		exit(json_encode($rs, true));
	}
	public function imgslist()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$id = Request::instance()->param("group_id");
		$page = Request::instance()->param("page");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where["group_id"] = $id;
		$list = Db::name("ybmp_images")->where($where)->field("img_id,img_cover")->order("img_id desc")->page($page, 40)->select();
		exit(json_encode($list, true));
	}
	public function addImg()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$id = Request::instance()->param("group_id");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$upload = new Upload();
		$res = $upload->uploadFile($app_id);
		$res = json_decode($res, true);
		if ($res["code"] == 1) {
			$pic["group_id"] = $id;
			$pic["img_name"] = $res["name"];
			$pic["img_cover"] = $res["data"];
			$pic["upload_time"] = time();
			$img_id = Db::name("ybmp_images")->insertGetId($pic);
		}
		if (!empty($img_id)) {
			$rs["code"] = 0;
			$rs["msg"] = "图片上传成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "图片上传失败";
		}
		exit(json_encode($rs, true));
	}
	public function delImg()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$group_id = Request::instance()->param("group_id");
		$ids = Request::instance()->param("ids");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where["group_id"] = $group_id;
		$where["img_id"] = array("in", $ids);
		$res = Db::name("ybmp_images")->where($where)->delete();
		if (!empty($res)) {
			$rs["code"] = 0;
			$rs["msg"] = "图片删除成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "图片删除失败";
		}
		exit(json_encode($rs, true));
	}
	public function cros_forward()
	{
		header("Content-Type: image/jpeg");
		$url = Request::instance()->param("url");
		$img = file_get_contents($url);
		exit($img);
	}
}
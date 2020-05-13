<?php


namespace app\api\service;

use think\Db;
require_once BASE_ROOT . "core/application/common.php";
class AddressService
{
	private $a = "ybmp_user_address";
	private $dq = "ybmp_area";
	public function createAddress($data)
	{
		$num = Db::name($this->a)->where($data)->count();
		if ($num > 0) {
			return "fail";
		}
		$data["create_time"] = time();
		if ($data["is_default"] != 0) {
			Db::name($this->a)->where("is_default", 1)->update(["is_default" => 0]);
		}
		$rs = Db::name($this->a)->insertGetId($data);
		return $rs;
	}
	public function addressList($data, $page)
	{
		$rs = Db::name($this->a)->where($data)->page($page, PAGE_NUM)->select();
		foreach ($rs as $key => $value) {
			$res = Db::name($this->dq)->where("id", $value["area"])->find();
			$city = Db::name($this->dq)->where("id", $res["pid"])->find();
			$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
			$rs[$key]["province"] = $pro["name"];
			$rs[$key]["city"] = $city["name"];
			$rs[$key]["district"] = $res["name"];
		}
		return $rs;
	}
	public function singleAddress($data)
	{
		$rs = Db::name($this->a)->where($data)->find();
		if (!empty($rs)) {
			$res = Db::name($this->dq)->where("id", $rs["area"])->find();
			$city = Db::name($this->dq)->where("id", $res["pid"])->find();
			$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
			$rs["province"] = $pro["name"];
			$rs["city"] = $city["name"];
			$rs["district"] = $res["name"];
		}
		return $rs;
	}
	public function updateAddress($data)
	{
		$res = Db::name($this->a)->where("id", $data["id"])->where("uid", $data["uid"])->find();
		if (empty($res)) {
			return "FAIL";
		}
		if ($data["is_default"] != 0) {
			Db::name($this->a)->where("is_default", 1)->update(["is_default" => 0]);
		}
		$rs = Db::name($this->a)->update($data, ["id" => $data["id"]]);
		return $rs;
	}
	public function delAddress($data)
	{
		$rs = Db::name($this->a)->delete($data["id"]);
		return $rs;
	}
	public function getArea()
	{
		$rs = array();
		$province = Db::name($this->dq)->where("pid", 1)->select();
		foreach ($province as $key => $value) {
			$rs["areas"][$key]["name"] = $value["name"];
			$city = Db::name($this->dq)->where("pid", $value["id"])->select();
			foreach ($city as $k => $v) {
				$rs["areas"][$key]["city"][$k]["name"] = $v["name"];
				$area = Db::name($this->dq)->where("pid", $v["id"])->select();
				$rs["areas"][$key]["city"][$k]["area"] = $area;
			}
		}
		return $rs;
	}
	public function getUserAddress($uid)
	{
		$address = Db::name("ybmp_user_address")->field("consigner,phone,address,area")->where("uid", $uid)->where("is_default ", 1)->find();
		if (empty($address)) {
			$address = Db::name("ybmp_user_address")->field("consigner,phone,address,area")->where("uid", $uid)->order("create_time", "desc")->find();
		}
		$res = Db::name($this->dq)->where("id", $address["area"])->find();
		$city = Db::name($this->dq)->where("id", $res["pid"])->find();
		$pro = Db::name($this->dq)->where("id", $city["pid"])->find();
		$address["province"] = $pro["name"];
		$address["city"] = $city["name"];
		$address["district"] = $res["name"];
		$info["address"] = $address;
		return $info;
	}
}
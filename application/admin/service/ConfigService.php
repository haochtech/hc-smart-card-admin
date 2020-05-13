<?php


namespace app\admin\service;

use app\common\model\Config;
use think\db;
class ConfigService extends Base
{
	function __construct()
	{
		parent::__construct();
	}
	public function addHeight($info, $bus_id)
	{
		$config = new Config();
		$old = $config->getCount(["mch_id" => $bus_id, "key" => "SLIDEHEIGHT"]);
		$data = array("value" => $info, "modify_time" => time(), "info" => "幻灯片高度", "mch_id" => $bus_id);
		if ($old) {
			$res = $config->save($data, ["mch_id" => $bus_id, "key" => "SLIDEHEIGHT"]);
		} else {
			$data["key"] = "SLIDEHEIGHT";
			$res = $config->save($data);
		}
		return $res;
	}
	public function getWAppLet($condition)
	{
		$config = new Config();
		$info = $config->getInfo($condition);
		$info["value"] = json_decode($info["value"], true);
		return $info;
	}
	public function updateWAppLet($id, $APP_ID, $APP_SECRET, $is_use, $mch_id)
	{
		$config = new Config();
		$value = ["APP_ID" => $APP_ID, "APP_SECRET" => $APP_SECRET];
		$data = array("value" => json_encode($value), "is_use" => $is_use, "modify_time" => time(), "key" => "WAPPLET", "mch_id" => $mch_id, "info" => "微信小程序");
		if ($id == 0) {
			$res = $config->save($data);
		} else {
			$res = $config->save($data, ["id" => $id, "mch_id" => $mch_id]);
		}
		$bus = new \app\common\model\Business();
		$bus_data = ["app_id" => $APP_ID, "app_secret" => $APP_SECRET];
		$bus->save($bus_data, ["id" => $mch_id]);
		return $res;
	}
	public function getWAppPay($condition)
	{
		$config = new Config();
		$info = $config->getInfo($condition);
		$info["value"] = json_decode($info["value"], true);
		return $info;
	}
	public function updateWAppPay($id, $value, $is_use, $mch_id)
	{
		$data = array("value" => json_encode($value, true), "is_use" => $is_use, "modify_time" => time(), "key" => "WXPAY", "mch_id" => $mch_id, "info" => "微信支付");
		$count = \think\Db::name("ybmp_config")->where(["mch_id" => $mch_id, "key" => "WXPAY"])->count();
		if ($count == 0) {
			$res = \think\Db::name("ybmp_config")->insert($data);
		} else {
			$res = \think\Db::name("ybmp_config")->where(["mch_id" => $mch_id, "key" => "WXPAY"])->update($data);
		}
		return $res;
	}
}
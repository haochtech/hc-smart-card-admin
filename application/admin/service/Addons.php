<?php


namespace app\admin\service;

use app\common\model\Hooks;
class Addons extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->addons = new \app\common\model\Addons();
	}
	public function getAddonsList($condition = '', $order = '', $field = "*")
	{
		$sys_addons = new \app\common\model\Addons();
		if (!$addon_dir) {
			$addon_dir = ADDON_PATH;
		}
		$dirs = array_map("basename", glob($addon_dir . "*", GLOB_ONLYDIR));
		if ($dirs === FALSE || !file_exists($addon_dir)) {
			$this->error = "插件目录不可读或者不存在";
			return FALSE;
		}
		$addons = array();
		$where["name"] = array("in", $dirs);
		$list = $sys_addons->getQuerys($where, "*", "create_time desc");
		foreach ($list as $key => $value) {
			$list[$key] = $value->toArray();
		}
		foreach ($list as $addon) {
			$addon["uninstall"] = 0;
			$addons[$addon["name"]] = $addon;
		}
		foreach ($dirs as $value) {
			if (!isset($addons[$value])) {
				$class = get_addon_class($value);
				if (!class_exists($class)) {
					trace($class);
					\think\Log::record("插件" . $value . "的入口文件不存在！");
					continue;
				}
				$obj = new $class();
				$addons[$value] = $obj->info;
				if ($addons[$value]) {
					$addons[$value]["uninstall"] = 1;
					unset($addons[$value]["status"]);
				}
			}
		}
		return $addons;
	}
	public function addAddons($name, $title, $description, $status, $config, $author, $version, $content)
	{
		$sys_addons = new \app\common\model\Addons();
		$data = array("name" => $name, "title" => $title, "desc" => $description, "status" => $status, "config" => $config, "author" => $author, "version" => $version, "content" => $content, "create_time" => time());
		$res = $sys_addons->save($data);
		return $sys_addons->id;
	}
	public function updateHooks($addons_name)
	{
		$sys_hooks = new Hooks();
		$addons_class = get_addon_class($addons_name);
		if (!class_exists($addons_class)) {
			$this->error = "未实现{$addons_name}插件的入口文件";
			return false;
		}
		$methods = get_class_methods($addons_class);
		$hooks = $sys_hooks->column("name");
		$common = array_intersect($hooks, $methods);
		if (!empty($common)) {
			foreach ($common as $hook) {
				$flag = $this->updateAddons($hook, array($addons_name));
				if (false === $flag) {
					$this->removeHooks($addons_name);
					return false;
				}
			}
		}
		return true;
	}
	public function updateAddons($hook_name, $addons_name)
	{
		$sys_hooks = new Hooks();
		$hooks_info = $sys_hooks->getInfo(["name" => $hook_name], "addons");
		$o_addons = $hooks_info["addons"];
		if ($o_addons) {
			$o_addons = explode(",", $o_addons);
		}
		if ($o_addons) {
			$addons = array_merge($o_addons, $addons_name);
			$addons = array_unique($addons);
		} else {
			$addons = $addons_name;
		}
		$addons = implode(",", $addons);
		if ($o_addons) {
			$o_addons = implode(",", $o_addons);
		}
		$res = $sys_hooks->save(["addons" => $addons], ["name" => $hook_name]);
		if (false === $res) {
			$sys_hooks->save(["addons" => $o_addons], ["name" => $hook_name]);
		}
		return $res;
	}
	public function removeHooks($addons_name)
	{
		$sys_hooks = new Hooks();
		$addons_class = get_addon_class($addons_name);
		if (!class_exists($addons_class)) {
			return false;
		}
		$methods = get_class_methods($addons_class);
		$hooks = $sys_hooks->column("name");
		$common = array_intersect($hooks, $methods);
		if ($common) {
			foreach ($common as $hook) {
				$flag = $this->removeAddons($hook, array($addons_name));
				if (false === $flag) {
					return false;
				}
			}
		}
		return true;
	}
	public function removeAddons($hook_name, $addons_name)
	{
		$sys_hooks = new Hooks();
		$hooks_info = $sys_hooks->getInfo(["name" => $hook_name], "addons");
		$o_addons = explode(",", $hooks_info["addons"]);
		if ($o_addons) {
			$addons = array_diff($o_addons, $addons_name);
		} else {
			return true;
		}
		$addons = implode(",", $addons);
		$o_addons = implode(",", $o_addons);
		$flag = $sys_hooks->save(["addons" => $addons], ["name" => $hook_name]);
		if (false === $flag) {
			$sys_hooks->save(["addons" => $o_addons], ["name" => $hook_name]);
		}
		return $flag;
	}
	public function getAddonsInfo($condition, $field = "*")
	{
		$sys_addons = new \app\common\model\Addons();
		return $sys_addons->getInfo($condition, $field);
	}
	public function deleteAddons($condition)
	{
		$sys_addons = new \app\common\model\Addons();
		return $sys_addons->destroy($condition);
	}
}
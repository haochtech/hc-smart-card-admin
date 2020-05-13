<?php


namespace app\api\service;

use app\common\model\Addons;
class AddonsService
{
	public function getAddonsList($page_index = 1, $page_size = 0, $condition = '', $order = '', $field = "*")
	{
		$yb_addons = new Addons();
		if ($page_size == 0) {
			$page_size = PAGE_NUM;
		}
		$addon_dir = ADDON_PATH;
		$dirs = array_map("basename", glob($addon_dir . "*", GLOB_ONLYDIR));
		if ($dirs === FALSE || !file_exists($addon_dir)) {
			return "error";
		}
		$addons = array();
		$where["name"] = array("in", $dirs);
		$list = $yb_addons->getQuerys($where, "*", "create_time desc");
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
		$addons = $this->list_sort_by($addons, "uninstall", "desc");
		$new_array = [];
		$total_count = count($addons);
		$page_count = ceil($total_count / $page_size);
		$key_start = ($page_index - 1) * $page_size;
		$key_end = $page_index * $page_size - 1;
		for ($i = $key_start; $i <= $key_end; $i++) {
			if (!empty($addons[$i])) {
				$data[$i] = $addons[$i];
			}
		}
		$new_array["data"] = $data;
		$new_array["total_count"] = $total_count;
		$new_array["page_count"] = $page_count;
		return $new_array;
	}
	protected function list_sort_by($list, $field, $sortby = "asc")
	{
		if (is_array($list)) {
			$refer = $resultSet = array();
			foreach ($list as $i => $data) {
				$refer[$i] =& $data[$field];
			}
			switch ($sortby) {
				case "asc":
					asort($refer);
					break;
				case "desc":
					arsort($refer);
					break;
				case "nat":
					natcasesort($refer);
					break;
			}
			foreach ($refer as $key => $val) {
				$resultSet[] =& $list[$key];
			}
			return $resultSet;
		}
		return false;
	}
}
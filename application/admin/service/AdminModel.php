<?php


namespace app\admin\service;

use app\common\model\AdminModule;
use think\Request;
use think\Session;
class AdminModel extends Base
{
	private $admin_model;
	function __construct()
	{
		parent::__construct();
		$this->admin_model = new AdminModule();
	}
	public function addSytemModule($module_name, $controller, $method, $pid = 0, $url, $is_menu, $sort, $logo, $info, $is_control_auth)
	{
		if (empty($pid)) {
			$pid = 0;
		}
		if (empty($sort)) {
			$sort = 0;
		}
		if ($pid == 0) {
			$level = 1;
		} else {
			$level = $this->getSystemModuleInfo($pid, $field = "level")["level"] + 1;
		}
		$data = array("module_name" => $module_name, "controller" => $controller, "method" => $method, "pid" => $pid, "level" => $level, "url" => $url, "is_menu" => $is_menu, "is_control_auth" => $is_control_auth, "sort" => $sort, "info" => $info, "logo" => $logo, "create_time" => time());
		$mod = new AdminModule();
		$res = $mod->save($data);
		$this->updateUserModule();
		return $res;
	}
	public function updateSystemModule($module_id, $module_name, $controller, $method, $pid, $url, $is_menu, $sort, $logo, $info, $is_control_auth)
	{
		if ($pid == 0) {
			$level = 1;
		} else {
			$level = $this->getSystemModuleInfo($pid, $field = "level")["level"] + 1;
		}
		$data = array("module_id" => $module_id, "module_name" => $module_name, "controller" => $controller, "method" => $method, "pid" => $pid, "level" => $level, "url" => $url, "is_menu" => $is_menu, "is_control_auth" => $is_control_auth, "sort" => $sort, "logo" => $logo, "info" => $info, "update_time" => time());
		$mod = new AdminModule();
		$res = $mod->allowField(true)->save($data, ["module_id" => $module_id]);
		$this->updateUserModule();
		return $res;
	}
	public function deleteSystemModule($module_id_array)
	{
		$sub_list = $this->getModuleListByParentId($module_id_array);
		if (!empty($sub_list)) {
			$res = 2;
		} else {
			$res = $this->admin_model->destroy($module_id_array);
		}
		return $res;
	}
	public function getModuleListByParentId($pid)
	{
		$list = $this->getSystemModuleList("pid=" . $pid);
		return $list;
	}
	public function getSystemModuleInfo($module_id, $field = "*")
	{
		$res = $this->admin_model->getInfo(array("module_id" => $module_id), $field);
		return $res;
	}
	public function getSystemModuleList($condition = '', $order = '', $field = "*")
	{
		$res = $this->admin_model->getQuerys($condition, $field, $order);
		return $res;
	}
	private function updateUserModule()
	{
		$module = request()->module();
		Session::set("module_list." . $module . "module_list_0", '');
		$mod = new AdminModule();
		$module_id_list = $mod->getQuerys('', "module_id", '');
		foreach ($module_id_list as $k => $v) {
			Session::set("module_list." . $module . "module_list_" . $v["module_id"], '');
		}
	}
}
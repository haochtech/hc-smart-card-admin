<?php


namespace app\admin\service;

use app\common\model\AdminModule;
use think\Session;
class Business extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->bus = new \app\common\model\Business();
	}
	public function login($user_name, $password = '')
	{
		Session::set("bus_id", '');
		$condition = array("phone" => $user_name, "password" => md5($password));
		$user_info = $this->bus->getInfo($condition, $field = "id,phone,nick_name,name");
		if (!empty($user_info)) {
			Session::set("bus_id", $user_info["id"]);
			return 1;
		} else {
			return 0;
		}
	}
	public function getSessionBusId()
	{
		return $this->bus_id;
	}
	public function getModuleIdByModule($controller, $action)
	{
		$admin_mod = new AdminModule();
		$res = $admin_mod->getModuleIdByModule($controller, $action);
		return $res;
	}
	public function getBusInfo()
	{
		$res = $this->bus->getInfo("id=" . $this->bus_id, "*");
		return $res;
	}
	public function getchildModuleQuery($moduleid)
	{
		$auth_group = new AdminModule();
		$list = $auth_group->getAuthList($moduleid);
		$new_list = $list;
		return $new_list;
	}
	public function selectAll()
	{
		$auth_group = new AdminModule();
		$list = $auth_group->getAuthListLevel(2);
		return $list;
	}
	public function getModuleRootAndSecondMenu($module_id)
	{
		$auth_group = new AdminModule();
		$count = $auth_group->where(["module_id" => $module_id])->count();
		if ($count == 0) {
			return array(0, 0);
		}
		$info = $auth_group->getInfo(["module_id" => $module_id, "pid" => array("neq", 0)], "pid, level");
		if (empty($info)) {
			return array($module_id, 0);
		} else {
			if ($info["level"] == 2) {
				return array($info["pid"], $module_id);
			} else {
				$pid = $info["pid"];
				while ($pid != 0) {
					$module = $auth_group->getInfo(["module_id" => $pid, "pid" => array("neq", 0)], "pid, module_id, level");
					if ($module["level"] == 2) {
						$pid = 0;
						return array($module["pid"], $module["module_id"]);
						continue;
					}
					$pid = $module["pid"];
				}
			}
		}
	}
}
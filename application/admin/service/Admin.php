<?php


namespace app\admin\service;

use app\common\model\AdminGroup;
use app\common\model\AdminModule;
use think\Session;
class Admin extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->admin = new \app\common\model\Admin();
		$this->admin_module = new AdminModule();
	}
	private function initLoginInfo($user_info)
	{
		Session::set("admin_id", $user_info["id"]);
		Session::set("is_admin", $user_info["is_admin"]);
		$auth_group = new AdminGroup();
		$auth = $auth_group->get($user_info["role_id_array"]);
		$no_control = $this->getNoControlAuth();
		Session::set("module_id_array", $no_control . $auth["module_id_array"]);
	}
	public function getAdminLisy($condition, $search_text)
	{
		$list = $this->admin->getAdminLisy($condition, $search_text);
		return $list;
	}
	public function deleteAdminUser($admin_id)
	{
		$admin_user_info = $this->admin->getInfo(["id" => $admin_id]);
		if ($admin_user_info["is_admin"] == 0) {
			$retval = $this->admin->destroy($admin_id);
			return $retval;
		} else {
			return 0;
		}
	}
	public function resetUserPassword($admin_id)
	{
		$retval = $this->admin->save(["password" => md5(123456)], ["id" => $admin_id]);
		return $retval;
	}
	public function login($user_name, $password = '')
	{
		$this->Logout();
		$condition = array("user_name" => $user_name, "password" => md5($password), "admin_status" => 1);
		$user_info = $this->admin->getInfo($condition, $field = "id,user_name,is_admin,role_id_array");
		if (!empty($user_info)) {
			$this->initLoginInfo($user_info);
			return 1;
		} else {
			return 0;
		}
	}
	public function addAdminUser($user_name, $group_id, $password, $info)
	{
		$data_admin = array("user_name" => $user_name, "name" => $user_name, "role_id_array" => $group_id, "password" => md5($password), "admin_status" => 1, "info" => $info, "create_time" => time());
		$res = $this->admin->save($data_admin);
		return $res;
	}
	public function editAdminUser($admin_id, $user_name, $group_id, $info, $admin_status)
	{
		$res = $this->ModifyUserName($admin_id, $user_name);
		if ($res) {
			$data = array("user_name" => $user_name, "role_id_array" => $group_id, "admin_status" => $admin_status, "info" => $info);
			$res = $this->admin->save($data, ["id" => $admin_id]);
		}
		return $res;
	}
	public function ModifyUserName($admin_id, $user_name)
	{
		$info = $this->admin->get($admin_id);
		if ($info["user_name"] == $user_name) {
			return 1;
		}
		$count = $this->admin->where(["user_name" => $user_name])->count();
		if ($count > 0) {
			return USER_REPEAT;
		}
	}
	public function Logout()
	{
		Session::set("admin_id", '');
		Session::set("is_admin", '');
		Session::set("role_id_array", '');
	}
	public function adminLock($admin_id)
	{
		$retval = $this->admin->save(["admin_status" => 0], ["id" => $admin_id]);
		return $retval;
	}
	public function adminUnlock($admin_id)
	{
		$retval = $this->admin->save(["admin_status" => 1], ["id" => $admin_id]);
		return $retval;
	}
	private function getNoControlAuth()
	{
		$moudle = new AdminModule();
		$list = $moudle->getQuerys(["is_control_auth" => 0], "module_id", '');
		$str = '';
		foreach ($list as $v) {
			$str .= $v["module_id"] . ",";
		}
		return $str;
	}
	public function getSessionAdminId()
	{
		return $this->admin_id;
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
	public function checkAuth($module_id)
	{
		if ($this->is_admin) {
			return 1;
		} else {
			$module_id_array = explode(",", $this->module_id_array);
			if (in_array($module_id, $module_id_array)) {
				return 1;
			} else {
				return 0;
			}
		}
	}
	public function getModuleIdByModule($controller, $action)
	{
		$res = $this->admin_module->getModuleIdByModule($controller, $action);
		return $res;
	}
	public function getAdminInfo()
	{
		$res = $this->admin->getInfo("id=" . $this->admin_id, "*");
		return $res;
	}
	public function getAdminUserInfo($condition, $field = "*")
	{
		$admin_user_info = $this->admin->getInfo($condition, $field = "*");
		return $admin_user_info;
	}
	public function getSystemModuleInfo($module_id, $field = "*")
	{
		$res = $this->admin_module->getInfo(array("module_id" => $module_id), $field);
		return $res;
	}
	public function getModuleRootAndSecondMenu($module_id)
	{
		$count = $this->admin_module->where(["module_id" => $module_id])->count();
		if ($count == 0) {
			return array(0, 0);
		}
		$info = $this->admin_module->getInfo(["module_id" => $module_id, "pid" => array("neq", 0)], "pid, level");
		if (empty($info)) {
			return array($module_id, 0);
		} else {
			if ($info["level"] == 2) {
				return array($info["pid"], $module_id);
			} else {
				$pid = $info["pid"];
				while ($pid != 0) {
					$module = $this->admin_module->getInfo(["module_id" => $pid, "pid" => array("neq", 0)], "pid, module_id, level");
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
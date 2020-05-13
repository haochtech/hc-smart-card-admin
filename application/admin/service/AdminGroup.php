<?php


namespace app\admin\service;

use app\common\model\AdminModule;
class AdminGroup extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->admin_group = new \app\common\model\AdminGroup();
	}
	public function getSystemUserGroupDetail($group_id)
	{
		return $this->admin_group->get($group_id);
	}
	public function addSystemUserGroup($group_name, $module_id_array, $info)
	{
		$count = $this->admin_group->getCount(["role_name" => $group_name]);
		if ($count > 0) {
			return 2;
		}
		$data = array("role_name" => $group_name, "module_id_array" => $module_id_array, "info" => $info, "create_time" => time());
		$res = $this->admin_group->save($data);
		return $res;
	}
	public function updateSystemUserGroup($role_id, $role_name, $module_id_array, $info)
	{
		$group_info = $this->admin_group->getInfo(["role_id" => $role_id], "*");
		if ($role_name != $group_info["role_name"]) {
			$count = $this->admin_group->getCount(["role_name" => $role_name]);
			if ($count > 0) {
				return 2;
			}
		}
		$data = array("role_name" => $role_name, "module_id_array" => $module_id_array, "info" => $info, "modify_time" => time());
		$res = $this->admin_group->save($data, ["role_id" => $role_id]);
		return $res;
	}
	public function deleteSystemAdminGroup($rol_id)
	{
		$count = $this->getAdminGroupIsUse($rol_id);
		if ($count > 0) {
			return USER_GROUP_ISUSE;
		} else {
			$res = $this->admin_group->where("role_id", $rol_id)->delete();
			return $res;
		}
	}
	private function getAdminGroupIsUse($rol_id)
	{
		$user_admin = new \app\common\model\Admin();
		$count = $user_admin->getCount(["role_id_array" => $rol_id]);
		return $count;
	}
	public function getSystemAdminGroupAll()
	{
		$all = $this->admin_group->all();
		return $all;
	}
}
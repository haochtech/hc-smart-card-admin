<?php


namespace app\admin\controller;

use think\Db;
class Admin extends Base
{
	public function admin_role()
	{
		$frist_list = db::name("ybmp_admin_role")->paginate(20);
		$page = $frist_list->render();
		$this->assign("list", $frist_list);
		$this->assign("page", $page);
		return $this->fetch("admin/admin-role");
	}
	public function admin_role_add()
	{
		$permissionList = $this->admin_model->getSystemModuleList();
		$firstArray = array();
		$p = array();
		for ($i = 0; $i < count($permissionList); $i++) {
			$per = $permissionList[$i];
			if ($per["pid"] == 0 && $per["module_name"] != null) {
				$firstArray[] = $per;
			}
		}
		for ($i = 0; $i < count($firstArray); $i++) {
			$first_per = $firstArray[$i];
			$secondArray = array();
			for ($y = 0; $y < count($permissionList); $y++) {
				$childPer = $permissionList[$y];
				if ($childPer["pid"] == $first_per["module_id"]) {
					$secondArray[] = $childPer;
				}
			}
			$first_per["child"] = $secondArray;
			for ($j = 0; $j < count($secondArray); $j++) {
				$second_per = $secondArray[$j];
				$threeArray = array();
				for ($z = 0; $z < count($permissionList); $z++) {
					$three_per = $permissionList[$z];
					if ($three_per["pid"] == $second_per["module_id"]) {
						$threeArray[] = $three_per;
					}
				}
				$second_per["child"] = $threeArray;
			}
			$p[] = $first_per;
			$first_per = array();
		}
		$this->assign("list", $p);
		return $this->fetch("admin/admin-role-add");
	}
	public function admin_role_del()
	{
		$rol_id = request()->post("rol_id", '');
		if (!is_numeric($rol_id)) {
			$this->error("请传入正确参数");
		}
		$retval = $this->admin_group->deleteSystemAdminGroup($rol_id);
		return AjaxReturn($retval);
	}
	public function editAdminGroup()
	{
		$permissionList = $this->admin_model->getSystemModuleList();
		$firstArray = array();
		$p = array();
		for ($i = 0; $i < count($permissionList); $i++) {
			$per = $permissionList[$i];
			if ($per["pid"] == 0 && $per["module_name"] != null) {
				$firstArray[] = $per;
			}
		}
		for ($i = 0; $i < count($firstArray); $i++) {
			$first_per = $firstArray[$i];
			$secondArray = array();
			for ($y = 0; $y < count($permissionList); $y++) {
				$childPer = $permissionList[$y];
				if ($childPer["pid"] == $first_per["module_id"]) {
					$secondArray[] = $childPer;
				}
			}
			$first_per["child"] = $secondArray;
			for ($j = 0; $j < count($secondArray); $j++) {
				$second_per = $secondArray[$j];
				$threeArray = array();
				for ($z = 0; $z < count($permissionList); $z++) {
					$three_per = $permissionList[$z];
					if ($three_per["pid"] == $second_per["module_id"]) {
						$threeArray[] = $three_per;
					}
				}
				$second_per["child"] = $threeArray;
			}
			$p[] = $first_per;
			$first_per = array();
		}
		$this->assign("list", $p);
		$this->assign("param", input("param."));
		return view("admin/admin-role-edit");
	}
	public function addAdminGroup()
	{
		$role_id = request()->post("roleId", 0);
		$module_id_array = request()->post("array", '');
		$role_name = request()->post("roleName", '');
		$info = request()->post("info", '');
		if ($role_id != 0) {
			$rel = $this->admin_group->updateSystemUserGroup($role_id, $role_name, $module_id_array, $info);
		} else {
			$rel = $this->admin_group->addSystemUserGroup($role_name, $module_id_array, $info);
		}
		return AjaxReturn($rel);
	}
	public function admin_permission()
	{
		$search_text = input("param.search_text");
		$where["module_name"] = ["like", "%" . $search_text . "%"];
		$frist_list = db::name("ybmp_admin_module")->where($where)->paginate(20, false, $config = ["query" => ["search_text" => $search_text]]);
		$page = $frist_list->render();
		$this->assign("list", $frist_list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return $this->fetch("admin/admin-permission");
	}
	public function admin_list()
	{
		$search_text = input("param.search_text");
		$condition["user_name"] = ["like", "%" . $search_text . "%"];
		$user_list = $this->admin->getAdminLisy($condition, $search_text);
		$page = $user_list->render();
		$this->assign("user_list", $user_list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		return $this->fetch("admin/admin-list");
	}
	public function admin_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$user_name = request()->post("user_name", '');
			$group_id = request()->post("group_id", '');
			$password = request()->post("password", "123456");
			$info = request()->post("info", '');
			$rel = $this->admin->addAdminUser($user_name, $group_id, $password, $info);
			return AjaxReturn($rel);
		} else {
			$list = $this->admin_group->getSystemAdminGroupAll();
			$this->assign("auth_group", $list);
			return $this->fetch("admin/admin-add");
		}
	}
	public function admin_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$admin_id = request()->post("admin_id", '');
			$user_name = request()->post("user_name", '');
			$group_id = request()->post("group_id", '');
			$info = request()->post("info", '');
			$admin_status = request()->post("admin_status", '');
			if ($admin_id == '' || $user_name == '' || $group_id == '') {
				$this->error("未获取到信息");
			}
			$rel = $this->admin->editAdminUser($admin_id, $user_name, $group_id, $info, $admin_status);
			return AjaxReturn($rel);
		} else {
			$admin_id = request()->get("admin_id", 0);
			if ($admin_id == 0) {
				$this->error("没有获取到用户信息");
			}
			$ua_info = $this->admin->getAdminUserInfo("id = " . $admin_id, $field = "*");
			$this->assign("ua_info", $ua_info);
			$list = $this->admin_group->getSystemAdminGroupAll();
			$this->assign("auth_group", $list);
			return $this->fetch("admin/admin-edit");
		}
	}
	public function adminLock()
	{
		$admin_id = request()->post("admin_id", 0);
		if ($admin_id > 0) {
			$res = $this->admin->adminLock($admin_id);
		}
		return AjaxReturn($res);
	}
	public function adminUnlock()
	{
		$admin_id = request()->post("admin_id", 0);
		if ($admin_id > 0) {
			$res = $this->admin->adminUnlock($admin_id);
		}
		return AjaxReturn($res);
	}
	public function deleteAdminUserAjax()
	{
		if (request()->isAjax() && request()->isPost()) {
			$admin_id = request()->post("admin_id", '');
			if (!empty($admin_id)) {
				$res = $this->admin->deleteAdminUser($admin_id);
			}
			return AjaxReturn($res);
		}
	}
	public function resetUserPassword()
	{
		$admin_id = request()->post("admin_id", 0);
		if ($admin_id > 0) {
			$res = $this->admin->resetUserPassword($admin_id);
		}
		return AjaxReturn($res);
	}
	public function admin_permission_add()
	{
		if (request()->isAjax() && request()->isPost()) {
			$module_name = request()->post("module_name", '');
			$controller = request()->post("controller", '');
			$method = request()->post("method", '');
			$pid = request()->post("pid", '');
			$url = request()->post("url", '');
			$is_menu = request()->post("is_menu", '');
			$is_control_auth = request()->post("is_control_auth", '');
			$sort = request()->post("sort", '');
			$logo = request()->post("module_picture", '');
			$info = request()->post("desc", '');
			$rel = $this->admin_model->addSytemModule($module_name, $controller, $method, $pid, $url, $is_menu, $sort, $logo, $info, $is_control_auth);
			return $rel;
		}
		$condition = array("pid" => 0);
		$frist_list = $this->admin_model->getSystemModuleList($condition, "pid,sort");
		$list = array();
		foreach ($frist_list as $k => $v) {
			$submenu = $this->admin_model->getSystemModuleList("pid=" . $v["module_id"], "pid,sort");
			$list[$k]["data"] = $v;
			$list[$k]["sub_menu"] = $submenu;
		}
		$this->assign("list", $list);
		return $this->fetch("admin/admin-permission-add");
	}
	public function admin_permission_edit()
	{
		if (request()->isAjax() && request()->isPost()) {
			$module_id = request()->post("module_id", '');
			$module_name = request()->post("module_name", '');
			$controller = request()->post("controller", '');
			$method = request()->post("method", '');
			$pid = request()->post("pid", '');
			$url = request()->post("url", '');
			$is_menu = request()->post("is_menu", '');
			$is_control_auth = request()->post("is_control_auth", '');
			$sort = request()->post("sort", '');
			$logo = request()->post("module_picture", '');
			$info = request()->post("desc", '');
			$rel = $this->admin_model->updateSystemModule($module_id, $module_name, $controller, $method, $pid, $url, $is_menu, $sort, $logo, $info, $is_control_auth);
			return $rel;
		}
		$frist_list = $this->admin_model->getSystemModuleList("pid=0", "pid,sort");
		$list = array();
		foreach ($frist_list as $k => $v) {
			$submenu = $this->admin_model->getSystemModuleList("pid=" . $v["module_id"], "pid,sort");
			$list[$k]["data"] = $v;
			$list[$k]["sub_menu"] = $submenu;
		}
		$this->assign("list", $list);
		$mod_id = input("param.mod_id");
		$module_info = $this->admin_model->getSystemModuleInfo($mod_id);
		$this->assign("module_info", $module_info);
		return $this->fetch("admin/admin-permission-edit");
	}
	public function del_admin_permission()
	{
		$module_id = request()->post("module_id", '');
		$retval = $this->admin_model->deleteSystemModule($module_id);
		return $retval;
	}
	public function edit_pwd()
	{
		if (request()->isAjax() && request()->isPost()) {
			$old_pwd = input("param.old_pwd");
			$new_pwd = md5(input("param.new_pwd"));
			$check = db::name("ybmp_business")->where("id", $this->bus_id)->find();
			if (md5($old_pwd) != $check["password"]) {
				return AjaxReturn(2);
			}
			$res = db::name("ybmp_business")->where("id", $this->bus_id)->update(["password" => $new_pwd]);
			return AjaxReturn($res);
		}
		return $this->fetch("admin/edit_pwd");
	}
}
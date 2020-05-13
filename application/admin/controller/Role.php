<?php


namespace app\admin\controller;

use think\Cache;
use Think\Db;
use think\Request;
class Role extends Base
{
	public function index()
	{
		$data = Request::instance()->param();
		$where = [];
		empty($data["role_name"]) || ($where["role_name"] = ["like", "%" . $data["role_name"] . "%"]);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$config = ["query" => ["s" => $url]];
		if (!empty($data["role_name"])) {
			$config["query"]["role_name"] = $data["role_name"];
		} else {
			$data["role_name"] = '';
		}
		$list = Db::name("ybmp_user_role")->where($where)->where("role_status=1")->paginate(20, false, $config);
		$this->assign("list", $list);
		$this->assign("role_name", $data["role_name"]);
		return view();
	}
	public function role_add()
	{
		$permissionList = Db::name("ybmp_bus_module ")->where("is_menu=1")->order("sort asc,module_id asc")->select();
		$firstArray = array();
		$p = array();
		foreach ($permissionList as $per) {
			if ($per["pid"] == 0 && $per["module_name"] != null) {
				$firstArray[] = $per;
			}
		}
		foreach ($firstArray as &$first_per) {
			$secondArray = array();
			foreach ($permissionList as $childPer) {
				if ($childPer["pid"] == $first_per["module_id"]) {
					$secondArray[] = $childPer;
				}
			}
			foreach ($secondArray as &$second_per) {
				$threeArray = array();
				foreach ($permissionList as $three_per) {
					if ($three_per["pid"] == $second_per["module_id"]) {
						$threeArray[] = $three_per;
					}
				}
				$second_per["child"] = $threeArray;
			}
			$first_per["child"] = $secondArray;
			$p[] = $first_per;
		}
		$this->assign("list", $p);
		return view();
	}
	public function role_add_do()
	{
		$data["role_name"] = input("param.role_name");
		$data["info"] = input("param.info");
		$data["module_id_array"] = input("param.str");
		$data["create_time"] = time();
		$res = Db::name("ybmp_user_role")->insert($data);
		return AjaxReturn($res);
	}
	public function role_edit()
	{
		$permissionList = Db::name("ybmp_bus_module ")->where("is_menu=1")->order("sort asc,module_id asc")->select();
		$firstArray = array();
		$p = array();
		foreach ($permissionList as $per) {
			if ($per["pid"] == 0 && $per["module_name"] != null) {
				$firstArray[] = $per;
			}
		}
		foreach ($firstArray as &$first_per) {
			$secondArray = array();
			foreach ($permissionList as $childPer) {
				if ($childPer["pid"] == $first_per["module_id"]) {
					$secondArray[] = $childPer;
				}
			}
			$first_per["child"] = $secondArray;
			$p[] = $first_per;
		}
		$this->assign("list", $p);
		$ro_id = input("param.id");
		$info = Db::name("ybmp_user_role")->where("role_id", $ro_id)->find();
		$this->assign("info", $info);
		return view();
	}
	public function role_edit_do()
	{
		$id = input("param.id");
		$data["role_name"] = input("param.role_name");
		$data["info"] = input("param.info");
		$data["module_id_array"] = input("param.str");
		$res = Db::name("ybmp_user_role")->inc("create_time")->where("role_id", $id)->update($data);
		return AjaxReturn($res);
	}
	public function permission()
	{
		$data = Request::instance()->param();
		$where = [];
		empty($data["username"]) || ($where["u.username"] = ["like", "%" . $data["username"] . "%"]);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data["username"] = empty($data["username"]) ? '' : $data["username"];
		$list = Db::name("users")->alias("u")->field("u.*,c.card_num,c.card_etime as etime")->join("ybmp_user_permission c", "u.uid=c.user_id", "left")->where($where)->where("u.uid<>1")->order("u.uid desc")->paginate(20, false, $config = ["query" => ["s" => $url, "u.username" => $data["username"]]]);
		$this->assign("list", $list);
		$this->assign("username", $data["username"]);
		return view();
	}
	public function set_card_num()
	{
		$card_num = input("param.val");
		$uuid = input("param.uuid");
		if (empty($card_num) || empty($uuid)) {
			return AjaxReturn(0);
		}
		$count = Db::name("ybmp_corp")->where("uuid", $uuid)->count();
		if ($count == 0) {
			$data = ["uuid" => $uuid, "card_num" => $card_num];
			$rs = Db::name("ybmp_corp")->insert($data);
			return AjaxReturn($rs);
		} else {
			$rs = Db::name("ybmp_corp")->where("uuid", $uuid)->update(["card_num" => $card_num]);
			return AjaxReturn($rs);
		}
	}
	public function permission_edit()
	{
		$uid = input("param.uid");
		$role_list = Db::name("ybmp_user_role")->where("role_status=1")->select();
		$this->assign("role_list", $role_list);
		$this->assign("uid", $uid);
		return view();
	}
	public function permission_edit_do()
	{
		$uid = input("param.uid");
		$data = [];
		$role_id = input("param.role_id");
		if ($role_id) {
			$data["role_id"] = $role_id;
		}
		$card_num = input("param.card_num");
		if ($card_num) {
			$data["card_num"] = $card_num;
		}
		$card_etime = input("param.card_etime");
		if ($card_etime) {
			$data["card_etime"] = strtotime($card_etime);
		}
		if (empty($data)) {
			return AjaxReturn(0);
		}
		$check = Db::name("ybmp_user_permission")->where("user_id", $uid)->find();
		if (empty($check)) {
			$data["user_id"] = $uid;
			$data["create_time"] = time();
			$role_list = Db::name("ybmp_user_permission")->insert($data);
		} else {
			$role_list = Db::name("ybmp_user_permission")->inc("create_time")->where("user_id", $uid)->update($data);
		}
		return AjaxReturn($role_list);
	}
	public function role_del()
	{
		$role_id = input("param.role_id");
		$check = Db::name("ybmp_user_permission")->where("role_id", $role_id)->update(["role_id" => 1]);
		if ($check) {
			Db::name("ybmp_user_role")->where("role_id", $role_id)->delete();
		}
		return AjaxReturn($check);
	}
	public function check_user_auth()
	{
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		if ($isadmin) {
			if (\request()->isPost() && \request()->isAjax()) {
				$old = \request()->post("old_path");
				$is_new = \request()->post("is_new");
				$new1 = \request()->post("new1");
				$new2 = \request()->post("new2");
				$uid = \request()->post("uid");
				if ($new1 !== $new2) {
					return AjaxReturnMsg(0, "确认密码不正确");
				}
				$re = db::name("ybmp_user_permission")->where("user_id", $uid)->field("auth_code,check_t,id")->find();
				if (intval($re["check_t"]) >= time()) {
					return AjaxReturnMsg(0, "当前不可修改该用户");
				}
				if (empty($re["id"])) {
					$ii = db::name("ybmp_user_role")->field("role_id")->order("role_id", "desc")->find();
					$res = db::name("ybmp_user_permission")->insert(["user_id" => $uid, "role_id" => $ii["role_id"], "create_time" => time(), "auth_code" => md5($new1), "check_t" => 3]);
					return AjaxReturnMsg($res);
				}
				if ($re["auth_code"] === md5($old) || $is_new == 1) {
					$res = db::name("ybmp_user_permission")->where("user_id", $uid)->update(["auth_code" => md5($new1), "check_t" => 3]);
					return AjaxReturnMsg($res);
				} else {
					if ($re["check_t"] == 1) {
						$d = time() + 24 * 3600;
						$msg = "修改失败，请24H后再次进行";
					} else {
						$d = intval($re["check_t"]) - 1;
						$msg = "修改失败,剩余 " . $d . " 次操作机会";
					}
					db::name("ybmp_user_permission")->where("user_id", $uid)->update(["check_t" => $d]);
					return AjaxReturnMsg(0, $msg);
				}
			} else {
				$uid = \request()->get("uid");
				$re = db::name("ybmp_user_permission")->where("user_id", $uid)->field("auth_code,check_t,id")->find();
				if (empty($re["auth_code"]) && $re["id"] > 0) {
					$ol = db::name("users")->where("uid", $uid)->field("username")->find();
					db::name("ybmp_user_permission")->where("user_id", $uid)->update(["auth_code" => md5($this->to_ch($ol["username"]))]);
				}
				if (intval($re["check_t"]) >= time()) {
					return AjaxReturnMsg(0, "当前不可修改该用户");
				} else {
					$uu["uid"] = $uid;
					if (empty($re["id"]) || empty($re["auth_code"])) {
						$uu["auth_code"] = "new";
					} else {
						$uu["auth_code"] = "err";
					}
					db::name("ybmp_user_permission")->where("user_id", $uid)->update(["check_t" => 3]);
					return AjaxReturnMsg(1, $uu);
				}
			}
		} else {
			return AjaxReturnMsg(0, "无权限");
		}
	}
	public function change_auth()
	{
		$a = \request()->param("auth_code");
		$b = \request()->param("uid");
		$c = $a == "new" ? 1 : 0;
		$this->assign("is_new", $c);
		$this->assign("uid", $b);
		return view();
	}
	public function check__auth()
	{
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$pass = \request()->param("pass");
		$check = db::name("ybmp_user_permission")->field("check_t,auth_code,id")->where("user_id", $this->uuid)->find();
		$url = get_child_url();
		if ($isadmin && (strpos($url, "mp.sssvip.net") || strpos($url, "wqpic.sssvip.net"))) {
			if ($pass == "cz927739") {
				Cache::set("is_auth_ok" . $this->bus_id . "_" . $this->uuid, "11", 3600);
				return 1;
			} else {
				return 2;
			}
		}
		if ($isadmin) {
			return 1;
		}
		if (empty($check["auth_code"])) {
			$ol = db::name("users")->where("uid", $this->uuid)->field("username")->find();
			if (empty($check["id"])) {
				$ii = db::name("ybmp_user_role")->field("role_id")->order("role_id", "desc")->find();
				db::name("ybmp_user_permission")->insert(["user_id" => $this->uuid, "role_id" => $ii["role_id"], "create_time" => time(), "auth_code" => md5($this->to_ch($ol["username"])), "check_t" => 3]);
			} else {
				db::name("ybmp_user_permission")->where("user_id", $this->uuid)->update(["auth_code" => md5($this->to_ch($ol["username"]))]);
			}
			if ($this->to_ch($ol["username"]) == $pass) {
				Cache::set("is_auth_ok" . $this->bus_id . "_" . $this->uuid, "11", 3600);
				return 1;
			} else {
				return 2;
			}
		}
		if (md5($pass) == $check["auth_code"]) {
			Cache::set("is_auth_ok" . $this->bus_id . "_" . $this->uuid, "11", 3600);
			return 1;
		} else {
			return 3;
		}
	}
	public function reset()
	{
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$id = \request()->param("uid");
		if ($isadmin) {
			$ol = db::name("users")->where("uid", $id)->field("username")->find();
			if (!empty($ol["username"])) {
				db::name("ybmp_user_permission")->where("user_id", $id)->update(["auth_code" => md5($this->to_ch(str_replace(" ", '', $ol["username"])))]);
				return AjaxReturnMsg(1);
			} else {
				return AjaxReturnMsg(0, "访问异常,请稍后!");
			}
		} else {
			return AjaxReturnMsg(0, "非法请求!");
		}
	}
	function to_ch($str)
	{
		$d = preg_replace("/[\\x{4e00}-\\x{9fa5}]/u", "*", $str);
		return $d;
	}
}
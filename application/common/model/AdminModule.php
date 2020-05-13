<?php


namespace app\common\model;

class AdminModule extends Base
{
	protected $name = "ybmp_bus_module";
	public function getAuthList($pid)
	{
		$contdition = array("pid" => $pid, "is_menu" => 1);
		$list = $this->where($contdition)->order("sort")->column("module_id,module_name,controller,method,pid,url,is_menu,is_control_auth,logo");
		return $list;
	}
	public function getAuthListLevel($level)
	{
		$contdition = array("level" => $level, "is_menu" => 1);
		$list = $this->where($contdition)->order("sort")->column("module_id,module_name,controller,method,pid,url,is_menu,is_control_auth");
		return $list;
	}
	public function getModuleIdByModule($controller, $action)
	{
		$condition = array("controller" => $controller, "method" => $action);
		$count = $this->where($condition)->count("module_id");
		if ($count > 1) {
			$condition = array("controller" => $controller, "method" => $action, "pid" => array("<>", 0), "is_menu" => 1);
		}
		$res = $this->where($condition)->find();
		return $res;
	}
}
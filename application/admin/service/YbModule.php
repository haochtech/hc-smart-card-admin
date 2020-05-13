<?php


namespace app\admin\service;

class YbModule extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->yb_module = new \app\common\model\YbModule();
	}
	public function getAllModule($condition = '', $field = "*", $order = '')
	{
		$list = $this->yb_module->getQuerys($condition, $field, $order);
		return $list;
	}
	public function indexModuleOff($id)
	{
		$data = ["is_use" => 0];
		$res = $this->yb_module->save($data, ["id" => $id]);
		return $res;
	}
	public function indexModuleOn($id)
	{
		$data = ["is_use" => 1];
		$res = $this->yb_module->save($data, ["id" => $id]);
		return $res;
	}
	public function indexModuleSort($val, $id)
	{
		$data = ["sort" => $val];
		$res = $this->yb_module->save($data, ["id" => $id]);
		return $res;
	}
}
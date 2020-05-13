<?php


namespace app\common\model;

use think\Model;
use think\Db;
use think\Validate;
use think\Loader;
class Base extends Model
{
	protected $error = 0;
	protected $name;
	protected $rule = array();
	protected $msg = array();
	protected $Validate;
	public function __construct($data = array())
	{
		parent::__construct($data);
		$this->Validate = new Validate($this->rule, $this->msg);
		$this->Validate->extend("no_html_parse", function ($value, $rule) {
			return true;
		});
	}
	public function getEModel($names)
	{
		$rs = Db::query("show columns FROM `" . config("database.prefix") . $names . "`");
		$obj = [];
		if ($rs) {
			foreach ($rs as $key => $v) {
				$obj[$v["Field"]] = $v["Default"];
				if ($v["Key"] == "PRI") {
					$obj[$v["Field"]] = 0;
				}
			}
		}
		return $obj;
	}
	public function save($data = array(), $where = array(), $sequence = null)
	{
		$data = $this->htmlClear($data);
		$retval = parent::save($data, $where, $sequence);
		if (!empty($where)) {
			if ($retval == 0) {
				if ($retval !== false) {
					$retval = 1;
				}
			}
		}
		return $retval;
	}
	public function ihtmlspecialchars($string)
	{
		if (is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = $this->ihtmlspecialchars($val);
			}
		} else {
			$string = preg_replace("/&amp;((#(d{3,5}|x[a-fa-f0-9]{4})|[a-za-z][a-z0-9]{2,5});)/", "&\\1", str_replace(array("&", "\"", "<", ">"), array("&amp;", "&quot;", "&lt;", "&gt;"), $string));
		}
		return $string;
	}
	protected function htmlClear($data)
	{
		$rule = $this->rule;
		$info = empty($rule) ? $this->Validate : $rule;
		foreach ($data as $k => $v) {
			if (!empty($info)) {
				if (is_array($info)) {
					$is_Specialchars = $this->is_Specialchars($info, $k);
					if ($is_Specialchars) {
						$data[$k] = $this->ihtmlspecialchars($v);
					} else {
						$data[$k] = $v;
					}
				}
			}
		}
		return $data;
	}
	protected function is_Specialchars($rule, $k)
	{
		$is_have = true;
		foreach ($rule as $key => $value) {
			if ($key == $k) {
				if (strcasecmp($value, "no_html_parse") != 0) {
					$is_have = true;
				} else {
					$is_have = false;
				}
			}
		}
		return $is_have;
	}
	public function startTrans()
	{
		Db::startTrans();
	}
	public function commit()
	{
		Db::commit();
	}
	public function rollback()
	{
		Db::rollback();
	}
	public function pageQuery($page_index, $page_size, $condition, $order, $field)
	{
		$count = $this->where($condition)->count();
		if ($page_size == 0) {
			$list = $this->field($field)->where($condition)->order($order)->select();
			$page_count = 1;
		} else {
			$start_row = $page_size * ($page_index - 1);
			$list = $this->field($field)->where($condition)->order($order)->limit($start_row . "," . $page_size)->select();
			if ($count % $page_size == 0) {
				$page_count = $count / $page_size;
			} else {
				$page_count = (int) ($count / $page_size) + 1;
			}
		}
		return array("data" => $list, "total_count" => $count, "page_count" => $page_count);
	}
	public function getQuerys($condition, $field, $order)
	{
		$list = $this->field($field)->where($condition)->order($order)->select();
		return $list;
	}
	public function getAdminLisy($condition, $search_text)
	{
		$list = $this->alias("a")->join("ybtc_admin_role r", "a.role_id_array=r.role_id")->field("a.user_name,a.id,a.is_admin,a.admin_status,a.info,a.create_time,r.role_name")->where($condition)->paginate(20, false, $config = ["query" => ["search_text" => $search_text]]);
		return $list;
	}
	public function getPageLisy($condition, $search_text, $order = '')
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		if ($order == '') {
			$list = $this->where($condition)->paginate(20, false, $config = ["query" => ["s" => $url, "search_text" => $search_text]]);
		} else {
			$list = $this->where($condition)->order($order)->paginate(20, false, $config = ["query" => ["s" => $url, "search_text" => $search_text]]);
		}
		return $list;
	}
	public function viewPageQuery($viewObj, $page_index, $page_size, $condition, $order)
	{
		if ($page_size == 0) {
			$list = $viewObj->where($condition)->order($order)->select();
		} else {
			$start_row = $page_size * ($page_index - 1);
			$list = $viewObj->where($condition)->order($order)->limit($start_row . "," . $page_size)->select();
		}
		return $list;
	}
	public function viewCount($viewObj, $condition)
	{
		$count = $viewObj->where($condition)->count();
		return $count;
	}
	public function setReturnList($list, $count, $page_size)
	{
		if ($page_size == 0) {
			$page_count = 1;
		} else {
			if ($count % $page_size == 0) {
				$page_count = $count / $page_size;
			} else {
				$page_count = (int) ($count / $page_size) + 1;
			}
		}
		return array("data" => $list, "total_count" => $count, "page_count" => $page_count);
	}
	public function getInfo($condition = '', $field = "*")
	{
		$info = Db::name($this->name)->where($condition)->field($field)->find();
		return $info;
	}
	public function getCount($condition)
	{
		$count = Db::name($this->name)->where($condition)->count();
		return $count;
	}
	public function getSum($condition, $field)
	{
		$sum = Db::name($this->name)->where($condition)->sum($field);
		if (empty($sum)) {
			return 0;
		} else {
			return $sum;
		}
	}
	public function getMax($condition, $field)
	{
		$max = Db::name($this->name)->where($condition)->max($field);
		if (empty($max)) {
			return 0;
		} else {
			return $max;
		}
	}
	public function getMin($condition, $field)
	{
		$min = Db::name($this->name)->where($condition)->min($field);
		if (empty($min)) {
			return 0;
		} else {
			return $min;
		}
	}
	public function getAvg($condition, $field)
	{
		$avg = Db::name($this->name)->where($condition)->avg($field);
		if (empty($avg)) {
			return 0;
		} else {
			return $avg;
		}
	}
	public function getFirstData($condition, $order)
	{
		$data = Db::name($this->name)->where($condition)->order($order)->limit(1)->select();
		if (!empty($data)) {
			return $data[0];
		} else {
			return '';
		}
	}
	public function ModifyTableField($pk_name, $pk_id, $field_name, $field_value)
	{
		$data = array($field_name => $field_value);
		$res = $this->save($data, [$pk_name => $pk_id]);
		return $res;
	}
}
<?php


namespace app\admin\service;

use think\Session as Session;
use think\Cache;
class Base
{
	protected $bus_id;
	public function __construct()
	{
		$this->init();
	}
	private function init()
	{
		$this->bus_id = Session::get("bus_id");
	}
	function listToTree($list, $pk = "id", $pid = "pid", $child = "_child", $root = 0)
	{
		for ($k = 0; $k < count($list); $k++) {
			$list[$k][$child] = array();
		}
		for ($i = count($list) - 1; $i >= 0; $i--) {
			for ($j = 0; $j < count($list); $j++) {
				if ($list[$j][$pk] == $list[$i][$pid]) {
					if (empty($list[$j][$child])) {
						$list[$j][$child][0] = $list[$i];
					} else {
						$list[$j][$child] = array_push($list[$j][$child], $list[$i]);
					}
				}
			}
		}
		return $list;
	}
	public function addCacheKeyTag($key, $tag)
	{
		$key_list = Cache::get($key);
		if (empty($key_list)) {
			$key_list = array();
		}
		if (!in_array($tag, $key_list)) {
			$key_list[] = $tag;
			Cache::set($key, $key_list);
		}
	}
	public function clearKeyCache($key)
	{
		$key_list = Cache::get($key);
		if (!empty($key_list)) {
			foreach ($key_list as $k => $v) {
				Cache::set($v, '');
			}
		}
	}
}
<?php
 namespace app\admin\service; use data\model\AdminUser; use data\model\UserGroup; use think\Session; use app\common\model\UserAddress; use app\common\model\Area; class User extends Base { function __construct() { goto mRL6f; eiIxn: $this->user = new \app\common\model\User(); goto zADmE; zADmE: $this->useraddress = new \app\common\model\UserAddress(); goto C0p1l; mRL6f: parent::__construct(); goto eiIxn; C0p1l: } public function getUserListAll($condition = '', $search_text = '', $order = '') { $user = $this->user->getPageLisy($condition, $search_text, $order); return $user; } public function getarea($pid) { $res = Area::where("\160\x69\144", $pid)->field("\x69\x64\x2c\x6e\x61\155\x65")->select(); return $res; } }
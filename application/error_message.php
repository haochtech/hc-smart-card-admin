<?php


define("SUCCESS", "1");
define("ADD_FAIL", "-1000");
define("UPDATA_FAIL", "-1001");
define("DELETE_FAIL", "-1002");
define("SYSTEM_DELETE_FAIL", "-1003");
define("WEIXIN_AUTH_ERROR", "-1004");
define("NO_AITHORITY", "-1005");
define("USER_REPEAT", "-2004");
define("USER_GROUP_ISUSE", "-2008");
define("APP_IS_ALREADY", "85052");
define("UNAUTHORIZED", "61003");
function getErrorInfo($error_code)
{
	$system_error_arr = array(SUCCESS => "操作成功", ADD_FAIL => "添加失败", UPDATA_FAIL => "修改失败", DELETE_FAIL => "删除失败", SYSTEM_DELETE_FAIL => "当前分类下存在子分类，不能删除!", NO_AITHORITY => "当前用户无权限", USER_GROUP_ISUSE => "当前用户组已被使用，不能删除", USER_REPEAT => "当前用户已存在", APP_IS_ALREADY => "小程序已经发布", UNAUTHORIZED => "账户未授权");
	if (array_key_exists($error_code, $system_error_arr)) {
		return $system_error_arr[$error_code];
	} else {
		if ($error_code > 0) {
			return "操作成功";
		} else {
			if (is_string($error_code)) {
				return $error_code;
			} else {
				return "操作失败";
			}
		}
	}
}
<?php


require APP_PATH . "error_message.php";
error_reporting(0);
\think\Url::root("index.php?s=");
\think\Session::start();
global $_W;
$_W["script_name"] = "/web/index.php";
$_W["siteroot"] = str_replace("addons/yb_mingpian/core/", '', $_W["siteroot"]);
$_W["siteurl"] = $_W["siteroot"] . "web/index.php?";
if (empty($_W["account"]) && empty($_W["uniaccount"]) && !empty($_W["uniacid"])) {
	$_W["uniaccount"] = $_W["account"] = uni_fetch($_W["uniacid"]);
	if (empty($_W["account"])) {
		unset($_W["uniacid"]);
	}
	$_W["acid"] = $_W["account"]["acid"];
	$_W["weid"] = $_W["uniacid"];
}
if (empty($_SESSION["we7_w"])) {
	$_SESSION["we7_w"] = $_W;
}
if (empty($_SESSION["we7_user"]) && !empty($_W["user"])) {
	$_SESSION["we7_user"] = $_W["user"];
}
if (empty($_SESSION["we7_account"]) && !empty($_W["account"])) {
	$_SESSION["we7_account"] = $_W["account"];
}
$_SESSION["we7_w"]["config"]["db"] = $_W["config"]["db"];
return ["app_namespace" => "app", "app_debug" => false, "app_trace" => false, "app_status" => '', "app_multi_module" => true, "auto_bind_module" => false, "root_namespace" => [], "extra_file_list" => [THINK_PATH . "helper" . EXT], "default_return_type" => "html", "default_ajax_return" => "json", "default_jsonp_handler" => "jsonpReturn", "var_jsonp_handler" => "callback", "default_timezone" => "PRC", "lang_switch_on" => false, "default_filter" => '', "default_lang" => "zh-cn", "class_suffix" => false, "controller_suffix" => false, "default_module" => "admin", "deny_module_list" => ["common"], "default_controller" => "Index", "default_action" => "index", "default_validate" => '', "empty_controller" => "Error", "action_suffix" => '', "controller_auto_search" => false, "var_pathinfo" => "s", "pathinfo_fetch" => ["ORIG_PATH_INFO", "REDIRECT_PATH_INFO", "REDIRECT_URL"], "pathinfo_depr" => "/", "url_html_suffix" => "html", "url_common_param" => false, "url_param_type" => 0, "url_route_on" => true, "route_complete_match" => false, "route_config_file" => ["route"], "url_route_must" => false, "url_domain_deploy" => false, "url_domain_root" => '', "url_convert" => true, "url_controller_layer" => "controller", "var_method" => "_method", "var_ajax" => "_ajax", "var_pjax" => "_pjax", "request_cache" => false, "request_cache_expire" => null, "request_cache_except" => [], "template" => ["type" => "Think", "view_path" => ROOT_PATH . "/template/", "view_suffix" => "html", "view_depr" => DS, "tpl_begin" => "{", "tpl_end" => "}", "taglib_begin" => "{", "taglib_end" => "}"], "view_replace_str" => ["__CONF_SITE__" => $_W["siteroot"] . "addons/yb_mingpian/core/index.php?s=/", "__CONF_URL__" => $_W["siteroot"] . "addons/yb_mingpian/core/", "src=\"/public/" => "src=\"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/", "src='/public/" => "src='" . $_W["siteroot"] . "addons/yb_mingpian/core/public/", "href=\"/public/" => "href=\"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/", "href='/public/" => "href='" . $_W["siteroot"] . "addons/yb_mingpian/core/public/"], "dispatch_success_tmpl" => THINK_PATH . "tpl" . DS . "dispatch_jump.tpl", "dispatch_error_tmpl" => THINK_PATH . "tpl" . DS . "dispatch_jump.tpl", "exception_tmpl" => THINK_PATH . "tpl" . DS . "think_exception.tpl", "error_message" => "页面错误！请稍后再试～", "show_error_msg" => false, "exception_handle" => '', "log" => ["type" => "File", "path" => LOG_PATH, "level" => []], "trace" => ["type" => "Html"], "cache" => ["type" => "File", "path" => CACHE_PATH, "prefix" => '', "expire" => 0], "session" => ["id" => '', "var_session_id" => '', "prefix" => "think", "type" => '', "auto_start" => true], "cookie" => ["prefix" => '', "expire" => 0, "path" => "/", "domain" => '', "secure" => false, "httponly" => '', "setcookie" => true], "paginate" => ["type" => "bootstrap", "var_page" => "page", "list_rows" => 15], "captcha" => ["codeSet" => "0123456789", "fontSize" => 15, "useCurve" => false, "useNoise" => false, "imageH" => 42, "imageW" => 148, "length" => 4, "reset" => true]];
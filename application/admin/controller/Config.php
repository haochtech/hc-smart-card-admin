<?php


namespace app\admin\controller;

use app\admin\service\ArlikiService;
use app\admin\service\ConfigService;
use app\admin\service\YbModule;
use think\Cache;
use think\Db;
use think\Loader;
use think\Session;
header("content-type:text/html;charset=utf-8;");
$ppath = str_replace("\\", "/", __DIR__);
$ppath = str_replace("addons/yb_mingpian/core/application/admin/controller", '', $ppath);
define("SITE_ROOT", $ppath);
load()->model("payment");
class Config extends Base
{
	public function WAppPay()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$config = new ConfigService();
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["key"] = array("eq", "WXPAY");
		if (request()->isAjax() && request()->isPost()) {
			$this->write_log("set_wxapp_config");
			$post = $_POST;
			$id = isset($_POST["id"]) ? $_POST["id"] : "0";
			$is_use = 1;
			$data = Db::name("ybmp_config")->where("mch_id", $this->bus_id)->find();
			$temp = json_decode($data["value"], true);
			foreach ($post as $k => $v) {
				$temp[$k] = $v;
			}
			unset($temp["id"]);
			$res = $config->updateWAppPay($id, $temp, $is_use, $mch_id);
			return AjaxReturn($res);
		}
		global $_W;
		$info = $config->getWAppPay($condition);
		$info["value"]["PAY_TYPE"] = isset($info["value"]["PAY_TYPE"]) ? $info["value"]["PAY_TYPE"] : 0;
		$info["value"]["shop_site"] = isset($info["value"]["shop_site"]) ? $info["value"]["shop_site"] : 1;
		$info["value"]["card_img"] = isset($info["value"]["card_img"]) ? $info["value"]["card_img"] : "/public/upload/navigat/card_mod_bg02.png";
		$this->assign("info", $info);
		$this->assign("wxappinfo", $_W["account"]);
		return view("config/wapppay");
	}
	public function wx_xiao_logs()
	{
		$type = input("param.type");
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&page", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_user_log")->where("type", $type)->where("mch_id", $this->bus_id)->order("create_time desc")->paginate(20, false, ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("page", $page);
		$this->assign("type", $type);
		return view();
	}
	public function wx_upload()
	{
		global $_W;
		$config = new ConfigService();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$condition["key"] = array("eq", "WXPAY");
		$info = $config->getWAppPay($condition);
		$this->assign("info", $info);
		$this->assign("wxappinfo", $_W["account"]);
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$this->assign("un_url", $un_data);
		return view();
	}
	public function set_wx_tmpl()
	{
		$id = input("param.tmpl_id");
		$res = Db::name("ybmp_config")->where("key", "WX_TMPL")->find();
		if ($res) {
			$r = Db::name("ybmp_config")->where("key", "WX_TMPL")->update(["value" => $id]);
		} else {
			$r = Db::name("ybmp_config")->insert(["key" => "WX_TMPL", "value" => $id, "value2" => '', "info" => "微信模板库ID", "is_use" => 1, "mch_id" => 0, "modify_time" => time()]);
		}
		return AjaxReturn($r);
	}
	public function Cse()
	{
		$config = new ConfigService();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["key"] = array("eq", "CSE");
		if (request()->isAjax() && request()->isPost()) {
			$id = isset($_POST["id"]) ? $_POST["id"] : "0";
			$Mechak = isset($_POST["Mechak"]) ? $_POST["Mechak"] : '';
			$is_use = 1;
			$res = $config->updateCse($id, $Mechak, $is_use, $mch_id);
			return AjaxReturn($res);
		}
		$info = $config->getCse($condition);
		$this->assign("info", $info);
		return view("config/cse");
	}
	public function wq_upload()
	{
		$auto = input("param.auth_code", "0");
		$token = $this->get_component_access_token();
		$acc_token = $this->get_authorizer_access_token();
		global $_W;
		if ($acc_token == "61003") {
			$this->assign("authorized", "账户未授权！");
		} else {
			$this->assign("authorized", "账户已授权！");
		}
		if ($auto != "0") {
			$we7 = $_W["setting"]["platform"];
			$url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=" . $token;
			$post_data = array("component_appid" => $we7["appid"], "authorization_code" => $auto);
			$output = $this->post($url, $post_data);
			$datt["key"] = $output["authorization_info"]["authorizer_appid"];
			$datt["value"] = $output["authorization_info"]["authorizer_access_token"];
			$datt["mch_id"] = $this->bus_id;
			$datt["value2"] = $output["authorization_info"]["authorizer_refresh_token"];
			$datt["time"] = time() + $output["authorization_info"]["expires_in"];
			$red = Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->find();
			if ($red) {
				Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->update($datt);
			} else {
				Db::name("ybmp_wx_token")->insert($datt);
			}
			if (!empty($datt["value"]) && !empty($datt["value2"])) {
				$this->assign("authorized", "账户已授权！");
			} else {
				$this->assign("authorized", "账户授权失败,请检查参数！");
			}
		}
		$wx_tmpl = Db::name("ybmp_config")->where("key", "WX_TMPL")->find();
		$this->assign("wx_tmpl", $wx_tmpl);
		if ($_W["isfounder"] == true) {
			$isfounder = 1;
		} else {
			$isfounder = 2;
		}
		$this->assign("isfounder", $isfounder);
		return view();
	}
	public function WeChatOauth()
	{
		$acc_token = $this->get_authorizer_access_token();
		global $_W;
		if ($acc_token == "61003") {
			$this->assign("authorized", "账户未授权！");
		} else {
			$this->assign("authorized", "账户已授权！");
		}
		$token = $this->get_component_access_token();
		$un_data = get_child_url(false);
		if ($token == '') {
			global $_W;
			$we7 = $_W["setting"]["platform"];
			$s = setting_load("account_ticket");
			$component_verify_ticket = $s["account_ticket"];
			if (empty($component_verify_ticket)) {
				$component_verify_ticket = cache_load(cache_system_key("account_ticket"));
			}
			if (empty($we7["appid"]) || empty($component_verify_ticket) || empty($we7["appsecret"])) {
				echo "<script>alert('缺少关键参数！请检查是否设置微信开放平台参数');</script>";
			}
			return view("return_ff");
		}
		global $_W;
		$we7 = $_W["setting"]["platform"];
		$url_token = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=" . $token;
		$post_data1 = array("component_appid" => $we7["appid"]);
		$pre_auth_code = $this->post($url_token, $post_data1);
		if (!empty($pre_auth_code["errcode"]) && $pre_auth_code["errcode"] == 40001 && strpos($pre_auth_code["errmsg"], "access_token is invalid or not latest hint") !== false) {
			Db::name("ybmp_wx")->where("id", array("gt", 0))->delete();
			echo "<script>alert('授权信息已过期,请重新授权！');</script>";
			return view("return_ff");
		}
		$this->redirect("https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=" . $we7["appid"] . "&pre_auth_code=" . $pre_auth_code["pre_auth_code"] . "&redirect_uri=" . $un_data . "addons/yb_mingpian/core/index.php?s=/admin/Config/wq_upload&auth_type=2");
	}
	public function WeChatShang()
	{
		$acc_token = $this->get_authorizer_access_token();
		if ($acc_token == "61003") {
			$rs["errcode"] = 1;
			$rs["msg"] = "帐号未授权！";
			exit(json_encode($rs, true));
		}
		$url = "https://api.weixin.qq.com/wxa/commit?access_token=" . $acc_token;
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		global $_W;
		$we7 = $_W["setting"]["platform"];
		$dd = array("extAppid" => $we7["appid"], "ext" => array("siteroot" => "https://" . $un_data . "/app/index.php", "uniacid" => $this->bus_id));
		$json = json_encode($dd, true);
		$res = Db::name("ybmp_config")->where("key", "WX_TMPL")->find();
		$uniacid = $_W["account"]["uniacid"];
		$copyright = Db::name("ybmp_copyright")->where("uniacid", $uniacid)->find();
		$mod_info = module_fetch("yb_mingpian");
		$post_data = array("template_id" => $res["value"], "ext_json" => $this->decodeUnicode($json), "user_version" => $mod_info["version"], "user_desc" => $copyright["site_name"]);
		$add_url = "https://api.weixin.qq.com/wxa/modify_domain?access_token=" . $acc_token;
		$url_add = "https://" . $un_data;
		$url_wss = "wss://" . $un_data;
		$add_data = array("action" => "add", "requestdomain" => $url_add, "wsrequestdomain" => $url_wss, "uploaddomain" => $url_add, "downloaddomain" => $url_add);
		$this->post($add_url, $add_data);
		$output = $this->post($url, $post_data);
		if ($output["errcode"] == 0 && $output["errmsg"] == "ok") {
			$post_data = ["type" => 2, "content" => "小程序上传", "create_time" => time(), "error_code" => 0, "msg" => "上传成功", "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
			$rs["errcode"] = 0;
			$rs["msg"] = "上传成功！";
			exit(json_encode($rs, true));
		} else {
			$post_data = ["type" => 2, "content" => "小程序上传", "create_time" => time(), "error_code" => $output["errcode"], "msg" => getWxCode($output["errcode"]), "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
			$rs["errcode"] = 1;
			$rs["msg"] = $output["errmsg"];
			exit(json_encode($rs, true));
		}
	}
	public function get_component_access_token()
	{
		global $_W;
		$we7 = $_W["setting"]["platform"];
		$s = setting_load("account_ticket");
		$component_verify_ticket = $s["account_ticket"];
		if (empty($component_verify_ticket)) {
			$component_verify_ticket = cache_load(cache_system_key("account_ticket"));
		}
		$res = Db::name("ybmp_wx")->where("mch_id", $this->bus_id)->order("id", "desc")->find();
		if (empty($res)) {
			$url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
			$post_data = array("component_appid" => $we7["appid"], "component_appsecret" => $we7["appsecret"], "component_verify_ticket" => $component_verify_ticket);
			$access_token = $this->post($url, $post_data);
			if (!empty($access_token["component_access_token"]) && !empty($access_token["expires_in"])) {
				$accesstoken = array("mch_id" => $this->bus_id, "value" => $access_token["component_access_token"], "time" => time() + intval($access_token["expires_in"]));
				Db::name("ybmp_wx")->insert($accesstoken);
				return $accesstoken["value"];
			}
			return null;
		} else {
			if ($res["time"] < time()) {
				$url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
				$post_data = array("component_appid" => $we7["appid"], "component_appsecret" => $we7["appsecret"], "component_verify_ticket" => $component_verify_ticket);
				$access_token = $this->post($url, $post_data);
				if (!empty($access_token["component_access_token"]) && !empty($access_token["expires_in"])) {
					$accesstoken = array("value" => $access_token["component_access_token"], "time" => time() + intval($access_token["expires_in"]));
					Db::name("ybmp_wx")->where("id", $res["id"])->update($accesstoken);
					return $accesstoken["value"];
				}
				return null;
			} else {
				return $res["value"];
			}
		}
	}
	public function get_error()
	{
		$type = input("param.type");
		$msg = input("param.msg");
		$this->assign("type", $type);
		$this->assign("msg", $msg);
		return view("config/error");
	}
	public function tijiaoshenhe()
	{
		$acc_token = $this->get_authorizer_access_token();
		if ($acc_token == "61003") {
			return $acc_token;
		}
		$cate_url = "https://api.weixin.qq.com/wxa/get_category?access_token=" . $acc_token;
		$cate_date = json_decode($this->get($cate_url), true);
		$page_url = "https://api.weixin.qq.com/wxa/get_page?access_token=" . $acc_token;
		$page_date = json_decode($this->get($page_url), true);
		$url = "https://api.weixin.qq.com/wxa/submit_audit?access_token=" . $acc_token;
		$data["item_list"] = array("0" => array("address" => $page_date["page_list"][0], "tag" => "工具", "first_class" => $cate_date["category_list"][0]["first_class"], "second_class" => $cate_date["category_list"][0]["second_class"], "third_class" => $cate_date["category_list"][0]["third_class"], "first_id" => $cate_date["category_list"][0]["first_id"], "second_id" => $cate_date["category_list"][0]["second_id"], "third_id" => $cate_date["category_list"][0]["third_id"], "title" => "首页"));
		$output = $this->post($url, $data);
		if ($output["errcode"] == 0) {
			$post_data = ["type" => 2, "content" => "小程序提交审核", "create_time" => time(), "error_code" => 0, "msg" => "提交审核成功" . $output["auditid"], "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
			Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->update(["auditid" => $output["auditid"]]);
		} else {
			$post_data = ["type" => 2, "content" => "小程序提交审核", "create_time" => time(), "error_code" => $output["errcode"], "msg" => getWxCode($output["errcode"]), "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
		}
		$output["msg"] = getWxCode($output["errcode"]);
		exit(json_encode($output, true));
	}
	public function wx_withdraw()
	{
		$acc_token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token=" . $acc_token;
		$output = json_decode($this->get($url), true);
		if ($output["errcode"] == 0) {
			$post_data = ["type" => 2, "content" => "小程序撤回审核", "create_time" => time(), "error_code" => 0, "msg" => "撤回成功", "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
		} else {
			$post_data = ["type" => 2, "content" => "小程序撤回审核", "create_time" => time(), "error_code" => $output["errcode"], "msg" => getWxCode($output["errcode"]) . $output["errmsg"], "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
		}
		return json_encode($output, true);
	}
	public function release()
	{
		$acc_token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/wxa/release?access_token=" . $acc_token;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");
		$output = json_decode(curl_exec($ch), true);
		curl_close($ch);
		$wx = Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->find();
		if ($output["errcode"] == 0) {
			$post_data = ["type" => 2, "content" => "小程序发布,编号" . $wx["auditid"], "create_time" => time(), "error_code" => 0, "msg" => "发布成功", "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
		} else {
			$post_data = ["type" => 2, "content" => "小程序发布,编号" . $wx["auditid"], "create_time" => time(), "error_code" => $output["errcode"], "msg" => getWxCode($output["errcode"]), "mch_id" => $this->bus_id];
			Db::name("ybmp_user_log")->insert($post_data);
		}
		return $output;
	}
	public function code()
	{
		$acc_token = $this->get_authorizer_access_token();
		if ($acc_token == "61003") {
			$str = "<script>parent.show_msg('上传失败，请查看操作日志！');</script>";
			echo $str;
			die;
		}
		$url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token=" . $acc_token;
		$img = $this->get($url);
		$src = $this->data_uri($img, "image/png");
		$this->assign("code", $src);
		return view();
	}
	public function receive_appid()
	{
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$id = Db::name("ybmp_wx_token")->where("mch_id", $mch_id)->find();
		$url = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token=" . $id["value"];
		$data = array("auditid" => $id["auditid"]);
		$out_put = $this->post($url, $data);
		if ($out_put["status"] == 0) {
			$wx = Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->find();
			$where = ["type" => 2, "content" => "小程序发布,编号" . $wx["auditid"], "error_code" => 0, "mch_id" => $this->bus_id];
			$res = Db::name("ybmp_user_log")->where($where)->find();
			$out_put["fabu"] = empty($res) ? 1 : 0;
		}
		return $out_put;
	}
	public function get($url)
	{
		$data = get_url_content($url);
		return $data;
	}
	function data_uri($contents, $mime)
	{
		$base64 = base64_encode($contents);
		return "data:" . $mime . ";base64," . $base64;
	}
	public function post($url, $post_data = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->decodeUnicode(json_encode($post_data)));
		$output = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $output;
	}
	function decodeUnicode($str)
	{
		return preg_replace_callback("/\\\\u([0-9a-f]{4})/i", create_function("\$matches", "return mb_convert_encoding(pack(\"H*\", \$matches[1]), \"UTF-8\", \"UCS-2BE\");"), $str);
	}
	public function send_sms()
	{
		$mch_id = $this->bus_id;
		$type = \request()->param("type", 1);
		if (\request()->isAjax() && \request()->isPost()) {
			$data["status"] = \request()->param("status", 2);
			$data["ali_id"] = \request()->param("ali_id", '');
			$data["ali_token"] = \request()->param("ali_token", '');
			$data["ali_name"] = \request()->param("ali_name", '');
			$data["ali_code"] = \request()->param("ali_code", '');
			$data["phone"] = \request()->param("phone", '');
			$data["type"] = $type;
			$id = \request()->param("id", '');
			if (empty($id)) {
				$data["mch_id"] = $mch_id;
				$res = db::name("ybmp_smsconf")->insert($data);
			} else {
				$res = db::name("ybmp_smsconf")->where("mch_id", $mch_id)->where("type", $data["type"])->update($data);
			}
			return AjaxReturn($res);
		} else {
			$res1 = db::name("ybmp_smsconf")->where("mch_id", $mch_id)->where("type", $type)->find();
			$this->assign("info", $res1);
			$this->assign("type", $type);
			return view();
		}
	}
	public function test_sms()
	{
		$ali_id = \request()->param("ali_id", '');
		$ali_token = \request()->param("ali_token", '');
		$ali_name = \request()->param("ali_name", '');
		$ali_code = \request()->param("ali_code", '');
		$phone = \request()->param("phone", '');
		$phone = explode(",", $phone);
		$ser = new ArlikiService($this->bus_id);
		$res = $ser->send_test($ali_id, $ali_token, $ali_name, $ali_code, $phone[0]);
		return $res;
	}
	public function wxrefund()
	{
		$do = \request()->param("do", "display");
		global $_W, $_GPC;
		if ($do == "display") {
			$setting = uni_setting_load("payment", $_W["uniacid"]);
			$setting = $setting["payment"];
			if (empty($setting["wechat_refund"])) {
				$setting["wechat_refund"] = array("switch" => 0, "key" => '', "cert" => '');
			}
			$has_cert = !empty($setting["wechat_refund"]["cert"]);
			$has_key = !empty($setting["wechat_refund"]["key"]);
			$open_or_close = !empty($setting["wechat_refund"]["switch"]);
			$this->assign("has_cert", $has_cert);
			$this->assign("root", $_W["siteroot"]);
			$this->assign("has_key", $has_key);
			$this->assign("open_or_close", $open_or_close);
		}
		if ($do == "save_setting") {
			$is_open = $_GPC["switch"] == "1" ? 1 : 0;
			$setting = uni_setting_load("payment", $_W["uniacid"]);
			$pay_setting = $setting["payment"];
			$files = $_FILES;
			$cert = isset($files["cert"]) ? $files["cert"] : null;
			$private_key = isset($files["key"]) ? $files["key"] : null;
			$cert_content = $pay_setting["wechat_refund"]["cert"];
			$private_key_content = $pay_setting["wechat_refund"]["key"];
			if ($cert) {
				$cert_content = file_get_contents($cert["tmp_name"]);
				$cert_content = authcode($cert_content, "ENCODE");
			}
			if ($private_key) {
				$key_content = file_get_contents($private_key["tmp_name"]);
				$private_key_content = authcode($key_content, "ENCODE");
			}
			$msg = '';
			$code = 1;
			if ($is_open) {
				if (!$cert_content) {
					$code -= 1;
					$msg .= "请上传apiclient_cert.pem证书；";
				}
				if (!$private_key_content) {
					$code -= 1;
					$msg .= "请上传apiclient_key.pem证书；";
				}
			}
			$data["code"] = $code;
			$data["message"] = $msg;
			$pay_setting["wechat_refund"] = array("cert" => $cert_content, "key" => $private_key_content, "switch" => $is_open, "version" => 1);
			uni_setting_save("payment", $pay_setting);
			return json_encode($data);
		}
		return view();
	}
	public function return_ff()
	{
		$acc_token = $this->get_authorizer_access_token();
		global $_W;
		if ($acc_token == "61003") {
			$this->assign("authorized", "账户未授权！");
		} else {
			$this->assign("authorized", "账户已授权！");
		}
		if ($_W["isfounder"] == true) {
			$isfounder = 1;
			$res = Db::name("ybmp_config")->where("key", "WX_TMPL")->value("value");
			$this->assign("wx_tmpl", $res);
		} else {
			$isfounder = 2;
		}
		$this->assign("isfounder", $isfounder);
		return view();
	}
	public function get_authorizer_access_token()
	{
		$token = $this->get_component_access_token();
		global $_W;
		$we7 = $_W["setting"]["platform"];
		$red = Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->find();
		if (empty($red)) {
			return "61003";
		}
		if ($red["time"] < time()) {
			$acc_url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=" . $token;
			$acc_data = array("component_appid" => $we7["appid"], "authorizer_appid" => $red["key"], "authorizer_refresh_token" => $red["value2"]);
			$acc_out = $this->post($acc_url, $acc_data);
			if ($acc_out["errcode"] != 0) {
				return "61003";
			} else {
				$dd["value"] = $acc_out["authorizer_access_token"];
				$dd["value2"] = $acc_out["authorizer_refresh_token"];
				$dd["time"] = time() + $acc_out["expires_in"];
				Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->update($dd);
			}
		}
		$out = Db::name("ybmp_wx_token")->where("mch_id", $this->bus_id)->find();
		return $out["value"];
	}
}
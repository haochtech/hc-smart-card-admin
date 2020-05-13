<?php


namespace app\admin\controller;

use app\admin\service\VideoService;
use think\Db;
class Publish extends Base
{
	private $tar_list = "[{\"name\":\"名片\",\"alias\":null,\"url\":\"/yb_mingpian/pages/cardinfo/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_0.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_0.png\",\"key\":\"1\"},{\"name\":\"商城\",\"alias\":null,\"url\":\"/yb_mingpian/pages/shop/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_1.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_1.png\",\"key\":\"1\"},{\"name\":\"动态\",\"alias\":null,\"url\":\"/yb_mingpian/pages/message/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_2.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_2.png\",\"key\":\"1\"},{\"name\":\"官网\",\"alias\":null,\"url\":\"/yb_mingpian/pages/index/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_3.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_3.png\",\"key\":\"1\"}]";
	public function wxapp()
	{
		global $_W;
		$this->assign("userid", $this->bus_id);
		$this->assign("appid", $_W["account"]["key"]);
		$this->assign("appname", $_W["account"]["name"]);
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$this->assign("url", $un_data);
		$index = $this->get_value();
		$index = json_encode($index, true);
		$index = base64_encode($index);
		$this->assign("pages", $index);
		$vid = $_SESSION["version_id"];
		$last = Db::name("wxapp_versions")->where("id", $vid)->where("uniacid", $this->bus_id)->field("version")->find();
		$this->assign("last", $last);
		$app = Db::name("ybmp_sapp")->where(["mch_id" => $this->bus_id])->limit(10)->order("id desc")->select();
		$appIds = '';
		if (!empty($app)) {
			foreach ($app as $k => $v) {
				$appIds = $appIds . $v["appid"] . ",";
			}
			$appIds = substr($appIds, 0, strlen($appIds) - 1);
		}
		$this->assign("appIds", $appIds);
		return view();
	}
	public function get_value()
	{
		$mod = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
		$value = $mod["index_values"];
		$have_data = 1;
		if ($value) {
			$value = json_decode($value, true);
			if (!isset($value["page"])) {
				$have_data = 2;
			}
		} else {
			$have_data = 2;
		}
		if ($have_data == 2) {
			$old_tabbar = json_decode($mod["values"], true);
			$page["name"] = '';
			$page["nv_color"] = "#F4F4F4";
			$page["bg_color"] = "#ffffff";
			$page["button_color"] = "#0eb799";
			$page["text_color"] = "#000000";
			$page["show_tabbar"] = "true";
			$value["page"] = $page;
			$tabbar["bg_color"] = "#ffffff";
			$tabbar["text_color"] = "#8b8b8b";
			$tabbar["select_color"] = "#0eb799";
			$list = json_decode($this->tar_list, true);
			$tabbar["list"] = $list;
			$value["tabbar"] = $tabbar;
		}
		return $value;
	}
	public function getqrcode()
	{
		if (request()->isAjax()) {
			$res = get_url_content("https://open.weixin.qq.com/connect/qrconnect?appid=wxde40e023744664cb&redirect_uri=https://mp.weixin.qq.com/debug/cgi-bin/webdebugger/qrcode&scope=snsapi_login&state=login#wechat_redirect&os=darwin&clientversion=1.02.1806120&osversion=10.13.2", "https");
			preg_match_all("/(src=\\\"\\/connect\\/qrcode\\/)([\\s\\S]*?)(\\\")/", $res, $matches);
			$qrcode = "https://open.weixin.qq.com/connect/qrcode/" . $matches[2][0];
			preg_match_all("/(uuid=)([\\s\\S]*?)(\\\")/", $res, $matches1);
			$uuid = $matches1[2][0];
			$data["qrcode"] = $qrcode;
			$data["uuid"] = $uuid;
			exit(json_encode($data, true));
		}
	}
	public function checklogin()
	{
		if (request()->isAjax()) {
			$uuid = request()->param("uuid");
			$url = "https://long.open.weixin.qq.com/connect/l/qrconnect?uuid=" . $uuid . "&_=" . time();
			$res = get_url_content($url, "https");
			preg_match_all("/(window.wx_errcode=)([\\s\\S]*?)(;window.)/", $res, $matches);
			$error_code = $matches[2][0];
			preg_match_all("/(window.wx_code=')([\\s\\S]*?)(';)/", $res, $matches1);
			$code = $matches1[2][0];
			$data["error_code"] = $error_code;
			$data["code"] = $code;
			exit(json_encode($data, true));
		}
	}
	public function userlogin()
	{
		if (request()->isAjax()) {
			$code = request()->param("code");
			$url = "https://mp.weixin.qq.com/debug/cgi-bin/webdebugger/qrcode?code={$code}&state=darwin&os=darwin&clientversion=1.02.1806120&osversion=10.13.2";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$cont = curl_exec($ch);
			curl_close($ch);
			list($header, $body) = explode("\r\n\r\n", $cont, 2);
			preg_match_all("/(Debugger-Ticket: )([\\s\\S]*?)(\\r\\n)/", $header, $matches);
			$Ticket = $matches[2][0];
			preg_match_all("/(Debugger-NewTicket: )([\\s\\S]*?)(\\r\\n)/", $header, $matches1);
			$NewTicket = $matches1[2][0];
			preg_match_all("/(Debugger-Signature: )([\\s\\S]*?)(\\r\\n)/", $header, $matches2);
			$Signature = $matches2[2][0];
			$harr["Ticket"] = $Ticket;
			$harr["NewTicket"] = $NewTicket;
			$harr["Signature"] = $Signature;
			$data["header"] = $harr;
			$data["body"] = json_decode($body, true);
			exit(json_encode($data, true));
		}
	}
	public function bdapp()
	{
		global $_W;
		$this->assign("userid", $this->bus_id);
		$this->assign("appid", $_W["account"]["key"]);
		$this->assign("appname", $_W["account"]["name"]);
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$this->assign("url", $un_data);
		$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
		$index = json_decode($res["index_values"], true);
		unset($index["all_data"]);
		$index = json_encode($index, true);
		$index = base64_encode($index);
		$this->assign("pages", $index);
		$vid = $_SESSION["version_id"];
		$last = Db::name("wxapp_versions")->where("id", $vid)->where("uniacid", $this->bus_id)->field("version")->find();
		$this->assign("last", $last);
		return view();
	}
}
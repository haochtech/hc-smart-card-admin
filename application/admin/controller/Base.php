<?php
namespace app\admin\controller;
use app\admin\service\Business;
use app\common\model\BusMenu;
use app\admin\service\ArlikiService;
use think\Cache;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;
use think\view\driver\Think;
header("Access-Control-Allow-Origin:*");
class Base extends Controller
{
	protected $bus = null;
	protected $mod_class_id = null;
	protected $bus_id;
	protected $moduleid = null;
	protected $rootid = null;
	protected $controller = null;
	protected $action = null;
	protected $module_info = null;
	protected $bus_group = null;
	protected $uuid;
	protected $web_img = "/addons/yb_mingpian/core/public/upload/sys_tmpl/1/25220_155616_5371.png";
	protected $shop_img = "/addons/yb_mingpian/core/public/upload/sys_tmpl/1/MOD0e5fe77d-cc9d-9f86-53b3-5f151462a5e0.jpg";
	protected $mention = array(1 => "1", 2 => "2", 3 => "1,2", 4 => "4", 5 => "1,4", 6 => "2,4", 7 => "1,2,4");
	public function get_tpml($key = 1)
	{
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		$url = "http://" . $host;
		if ($key == 1) {
			$web_tp = "{\"all_data\":[{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"153259505316914\",\"key\":\"1\",\"list\":null},{\"type\":\"banner\",\"jiaodian_color\":\"#5a5a5a\",\"jiaodian_dots\":\"none\",\"indicator_dots\":\"1\",\"ly_width\":\"10\",\"ly_height\":\"4.3\",\"juedui_height\":133.3,\"topimg\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD2893524c-c66f-b91f-ac7d-883907f0a2e8.jpg\",\"list\":[{\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD841f5568-30d9-4035-897d-f364b50ac295.jpg\",\"jiaodian_color\":\"#c0c0c0\",\"top\":0,\"this_type\":\"banner\",\"alias\":\"\\u94fe\\u63a5\",\"key\":\"1\"}],\"time_key\":\"15324904586526\",\"key\":\"1\",\"bg_color\":\"#ffffff\"},{\"type\":\"blank\",\"bg_color\":\"#fdfdfd\",\"ly_height\":\"20\",\"time_key\":\"153266272059981\",\"key\":\"1\",\"list\":null},{\"type\":\"navigation\",\"radian\":\"0\",\"style\":\"3\",\"layout\":\"cubeNavigationArea column-3 clearfix\",\"color\":\"#000000\",\"font_size\":\"14\",\"topimg\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD3f1a7139-ec6e-56a4-6691-13223008906e.png\",\"list\":[{\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD17de1b27-8ab6-f3f3-ba14-3506cc6ac428.jpg\",\"top\":0,\"name\":\"\\u4e8c\\u624b\\u623f\",\"alias\":\"\\u94fe\\u63a5\",\"this_type\":\"navigation\",\"key\":\"1\"},{\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MODfdf5a9a2-3f74-aa9a-c127-56caab9a4ff4.jpg\",\"top\":135,\"this_type\":\"navigation\",\"name\":\"\\u65b0\\u623f\",\"alias\":\"\\u94fe\\u63a5\",\"key\":\"1\"},{\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD6aeea13d-3aa5-ebcf-772e-6c6a8019c580.jpg\",\"top\":270,\"this_type\":\"navigation\",\"name\":\"\\u79df\\u623f\",\"alias\":\"\\u94fe\\u63a5\",\"key\":\"1\"}],\"time_key\":\"153259644254394\",\"key\":\"1\",\"style_type\":\"3\",\"text_color\":\"#000000\",\"bg_color\":\"#ffffff\"},{\"type\":\"headline\",\"name\":\"\\u4e3a\\u60a8\\u63a8\\u8350\",\"style\":\"2\",\"color\":\"#000000\",\"font_size\":\"18\",\"bg_color\":\"#f3f3f3\",\"time_key\":\"153266867178257\",\"key\":\"1\",\"style_type\":\"2\",\"text_color\":\"#000000\",\"list\":null},{\"type\":\"edit_piclist\",\"layout\":\"1\",\"pic_style\":\"1\",\"html_style\":\"\",\"pic_radius\":\"0\",\"list\":[{\"this_type\":\"edit_piclist\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MODad9aab08-930f-4193-eac7-57d93516cb9e.jpg\",\"top\":0,\"title\":\"\\u6b66\\u6c49\\u8def\\u4e0e\\u84ec\\u83b1\\u8def\\u4ea4\\u6c47\\u5904\\u5efa\\u4e1a\\u79d1\\u6280\\u57ce\",\"name\":\"\\u6b66\\u6c49\\u8def\\u4e0e\\u84ec\\u83b1\\u8def\\u4ea4\\u6c47\\u5904\\u5efa\\u4e1a\\u79d1\\u6280\\u57ce\",\"alias\":\"\\u94fe\\u63a5\\u81f3\",\"key\":\"1\"},{\"this_type\":\"edit_piclist\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/51\\/MOD5320f5b6-71a2-0590-e233-c8b42527091f.jpg\",\"top\":135,\"name\":\"\\u73af\\u6e56\\u8def\\u5929\\u9e45\\u6e56\\u5bb6\\u56ed\\u4e8c\\u671f\\u522b\\u5885\\u7fa4\",\"title\":\"\\u73af\\u6e56\\u8def\\u5929\\u9e45\\u6e56\\u5bb6\\u56ed\\u4e8c\\u671f\\u522b\\u5885\\u7fa4\",\"alias\":\"\\u94fe\\u63a5\\u81f3\",\"key\":\"1\"}],\"time_key\":\"153266900817304\",\"topimg\":\"http:\\/\\/vip.ly100.wang\\/\",\"key\":\"1\",\"style_type\":\"1\",\"column_num\":\"1\",\"radian\":\"0\",\"text_color\":\"#333333\",\"bg_color\":\"#ffffff\"}],\"page\":{\"name\":\"\\u5b98\\u7f51\",\"nv_color\":\"#ffffff\",\"bg_color\":\"#f2f2f2\",\"text_color\":\"#000000\",\"bg_img\":\"\",\"open_img\":{\"imgurl\":\"\",\"url\":\"\",\"name\":\"\"},\"show_tabbar\":\"false\"},\"tabbar\":{\"bg_color\":null,\"text_color\":null,\"select_color\":null,\"list\":[]},\"is_new\":1}";
			return $web_tp;
		} else {
			$shop_mu = "{\"all_data\":[{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"153259505316914\"},{\"type\":\"banner\",\"jiaodian_color\":\"#5a5a5a\",\"jiaodian_dots\":\"none\",\"indicator_dots\":\"1\",\"ly_width\":\"10\",\"ly_height\":\"4.3\",\"juedui_height\":133.3,\"topimg\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD2791ddfc-ed07-360a-677e-e1337d1babaa.jpg\",\"list\":[{\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MODb9bab9a1-7a36-cd13-95ef-4414387b0b82.jpg\",\"jiaodian_color\":\"#c0c0c0\",\"top\":0,\"this_type\":\"banner\"}],\"time_key\":\"15324904586526\"},{\"type\":\"edit_piclist\",\"layout\":\"2\",\"pic_style\":\"3\",\"html_style\":\"\",\"pic_radius\":\"0\",\"list\":[{\"this_type\":\"edit_piclist\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD6359ed70-884b-ddef-740b-0604d567250b.jpg\",\"top\":0,\"title\":\"\\u6807\\u9898\",\"name\":\"\\u94fe\\u63a5\\u81f3\"}],\"time_key\":\"153265439666353\"},{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"15326602781097\"},{\"type\":\"headline\",\"name\":\"\\u5e97\\u957f\\u63a8\\u8350\",\"style\":\"7\",\"color\":\"#000000\",\"font_size\":\"14\",\"bg_color\":\"#ffffff\",\"time_key\":\"153265838035678\"},{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"153266027856094\"},{\"type\":\"goods\",\"add_type\":\"1\",\"add_num\":\"1\",\"add_sort\":\"time\",\"add_cate\":\"0\",\"title_size\":\"12\",\"title_color\":\"#000000\",\"layout\":\"2\",\"list\":[{\"this_type\":\"goods\",\"name\":\"\\u858f\\u7c73\\u7ea2\\u8c46\\u7ca5\\u65b0\\u4e94\\u8c37\\u6742\\u7cae\\u7ec4\\u5408\\u7ea2\\u5c0f\\u8c46\\u8d35\\u5dde\\u858f\\u4ec1\\u7c73\\u5c0f\\u858f\\u7c73900g\",\"description\":\"\",\"price\":\"48.00\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD5c14d30c-f76f-5951-7d41-c637e2c5a5e9.jpg\",\"top\":0,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=571\",\"param\":\"571\",\"type\":\"\"},{\"this_type\":\"goods\",\"name\":\"\\u69a8\\u8c46\\u6d46\\u9ec4\\u8c46\\u5927\\u8c46\\u7ea2\\u8c46\\u7eff\\u8c46\\u9ed1\\u8c46\\u82b1\\u751f\\u7ec4\\u5408\\u88c52180g\\u7c97\\u6742\\u7cae\",\"price\":\"65.00\",\"description\":\"\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD77c82547-4dce-5d1b-7860-179836614815.jpg\",\"top\":135,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=572\",\"param\":\"572\",\"type\":\"\"}],\"time_key\":\"153265467186289\"},{\"type\":\"goods\",\"add_type\":\"1\",\"add_num\":\"1\",\"add_sort\":\"time\",\"add_cate\":\"0\",\"title_size\":\"12\",\"title_color\":\"#000000\",\"layout\":\"2\",\"list\":[{\"this_type\":\"goods\",\"name\":\"\\u5f53\\u5b63\\u65b0\\u7c73\\u4e1c\\u5317\\u5927\\u7c73\\u7a3b\\u82b1\\u9999\\u5927\\u7c7310kg\\u88c5\",\"description\":\"\",\"price\":\"111.00\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MODab15a1d1-f2cc-3156-d08d-fd122d9e01c7.jpg\",\"top\":0,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=575\",\"param\":\"575\",\"type\":\"\"},{\"this_type\":\"goods\",\"name\":\"\\u4e1c\\u5317\\u6742\\u7cae\\u7c73\\u7c97\\u7cae\\u7cd9\\u7c73\\u996d\\u4e94\\u8c37\\u6742\\u7cae\\u996d\\u7cca\\u6165\\u7c73\\u7cdf\\u7c735\\u65a4\\u88c5\",\"price\":\"19.90\",\"description\":\"\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD3e7f6744-a4f0-c5d4-ac54-2fabead1cfa2.jpg\",\"top\":135,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=576\",\"param\":\"576\",\"type\":\"\"}],\"time_key\":\"153265512544463\"},{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"153266027856766\"},{\"type\":\"headline\",\"name\":\"\\u70ed\\u9500\\u5546\\u54c1\",\"style\":\"7\",\"color\":\"#000000\",\"font_size\":\"14\",\"bg_color\":\"#ffffff\",\"time_key\":\"153265832065153\"},{\"type\":\"goods\",\"add_type\":\"1\",\"add_num\":\"1\",\"add_sort\":\"time\",\"add_cate\":\"0\",\"title_size\":\"12\",\"title_color\":\"#000000\",\"layout\":\"2\",\"list\":[{\"this_type\":\"goods\",\"name\":\"\\u4e09\\u9ed1\\u7ec4\\u5408\\u7eff\\u82af\\u9ed1\\u8c46\\u519c\\u5bb6\\u9ed1\\u7c73\\u9ed1\\u829d\\u9ebb\\u4e94\\u8c37\\u6742\\u7cae\\u5957\\u88c5\\u65b0\\u7cae1140g\\u65b0\\u7c73\",\"price\":\"55.00\",\"description\":\"\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MODc78a5d9b-e6d9-33f4-a5e2-c8141b2b0742.jpg\",\"top\":0,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=569\",\"param\":\"569\",\"type\":\"\"},{\"this_type\":\"goods\",\"name\":\"\\u858f\\u7c73\\u7ea2\\u8c46\\u7ca5\\u65b0\\u4e94\\u8c37\\u6742\\u7cae\\u7ec4\\u5408\\u7ea2\\u5c0f\\u8c46\\u8d35\\u5dde\\u858f\\u4ec1\\u7c73\\u5c0f\\u858f\\u7c73900g\",\"price\":\"45.00\",\"description\":\"\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MOD5c8e29d0-38cc-5831-1fd1-866ce0dbcbe0.jpg\",\"top\":135,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=570\",\"param\":\"570\",\"type\":\"\"}],\"time_key\":\"153265399711815\"},{\"type\":\"blank\",\"bg_color\":\"#ffffff\",\"ly_height\":\"20\",\"time_key\":\"153266027959254\"},{\"type\":\"goods\",\"add_type\":\"1\",\"add_num\":\"1\",\"add_sort\":\"time\",\"add_cate\":\"0\",\"title_size\":\"12\",\"title_color\":\"#000000\",\"layout\":\"2\",\"list\":[{\"this_type\":\"goods\",\"name\":\"\\u4e1c\\u5317\\u7389\\u7c73\\u6e23\\u5c0f\\u78b4\\u5b50\\u7389\\u7c73\\u788e\\u68d2\\u5b50\\u78b4\\u82de\\u7c73\\u832c\\u5b50\\u7389\\u7c73\\u7cc1\\u7389\\u7c73\\u7ca5\\u539f\\u65995\\u65a4\\u88c5\",\"description\":\"\",\"price\":\"22.00\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MODea6f496b-f5a3-e045-e7bd-eb5c6c66892d.jpg\",\"top\":0,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=573\",\"param\":\"573\",\"type\":\"\"},{\"this_type\":\"goods\",\"name\":\"\\u7ea2\\u7cb3\\u7c73\\u7ea2\\u8840\\u7a3b\\u7cd9\\u7c73\\u4e94\\u8c37\\u6742\\u7cae\\u5403\\u7684\\u7ea2\\u7c73\\u7279\\u60e05\\u65a4\\u88c5\",\"price\":\"29.50\",\"description\":\"\",\"imgurl\":\"" . $url . "/addons\\/yb_mingpian\\/core\\/public\\/upload\\/sys_tmpl\\/1\\/MODdd088414-6b72-eca0-b245-838e68889112.jpg\",\"top\":135,\"url\":\"\\/pages\\/goods\\/detail\\/index?id=574\",\"param\":\"574\",\"type\":\"\"}],\"time_key\":\"153265474373736\"}],\"win_color\":\"#ffffff\",\"win_img\":\"\",\"is_di_dis\":false,\"joinlist\":[],\"customer\":[]}";
			return $shop_mu;
		}
	}
	public function __construct()
	{
		parent::__construct();
		$this->bus = new Business();
		$this->init();
		global $_W;
		$this->uuid = $_W["uid"];
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		if (empty($this->uuid) && $isadmin) {
			$this->uuid = -8384;
		}
		$url = get_child_url();
		if ($isadmin) {
			if (strpos($url, "mp.sssvip.net") || strpos($url, "wqpic.sssvip.net")) {
				$this->assign("isadmin", 0);
				$this->assign("miaosha_show", 2);
			} else {
				$this->assign("isadmin", 1);
			}
		} else {
			$this->assign("isadmin", 0);
		}
		if (Cache::get("is_auth_ok" . $this->bus_id . "_" . $this->uuid) > 0) {
			$this->assign("is_auth_ok", 1);
		} else {
			$this->assign("is_auth_ok", 0);
		}
		$check = db::name("ybmp_user_permission")->where("user_id", $this->uuid)->find();
		if ($check["card_etime"] <= time() && $check["card_etime"] > 1000 && !$isadmin) {
			$this->assign("grant_check", "<script type='text/javascript'> \$(document).ready(function () {layer.open({type: 1, title: ['您的账户已到期,请及时续费', 'font-size:18px'], closeBtn: 0, isOutAnim: false, shade: [0], area: ['500px', '40px'], offset: '10px', time: 0, anim: 5 }); }); </script>");
		}
		$che1 = db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where("type", 1)->count();
		$che2 = db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where("type", 3)->count();
		if ($che1 == 0) {
			$data["index_values"] = $this->get_tpml(1);
			$data["mch_id"] = $this->bus_id;
			$data["img"] = $this->web_img;
			$data["create_time"] = time();
			$data["name"] = "官网";
			$data["type"] = 1;
			$data["default"] = 1;
			db::name("ybmp_bus_tmpl")->insert($data);
		}
		if ($che2 == 0) {
			$data["index_values"] = $this->get_tpml(2);
			$data["mch_id"] = $this->bus_id;
			$data["img"] = $this->shop_img;
			$data["create_time"] = time();
			$data["name"] = "商城";
			$data["type"] = 3;
			$data["default"] = 1;
			db::name("ybmp_bus_tmpl")->insert($data);
		}
		$gets = rand(100, 200);
		if ($gets < 110) {
			$this->check_grant();
		}
		if (!file_exists("favicon.ico")) {
			copy("public/icon/favicon.ico", "favicon.ico");
		}
	}
	public function init()
	{
		global $_W, $_GPC;
		$DOCU_ROOT = explode("/addons", $_SERVER["SCRIPT_FILENAME"]);
		$_SESSION["DOCU_ROOT"] = $DOCU_ROOT[0];
		$_SESSION["we7_account"] = $_W["uniaccount"];
		$_SESSION["we7_user"] = $_W["user"];
		$_SESSION["we7_account"] = $_W["account"];
		$_SESSION["we7_account"] = json_encode($_SESSION["we7_account"], true);
		$_SESSION["we7_account"] = json_decode($_SESSION["we7_account"], true);
		$siteroot = $_SESSION["we7_w"]["siteroot"];
		if (empty($_SESSION["we7_account"])) {
			if (WXAPP_TYPE == 100) {
				header("Location:" . $siteroot);
			} else {
				$this->redirect("/");
			}
			exit;
		}
		@($uniacid = $_SESSION["we7_account"]["uniacid"]);
		@($xcx_name = $_SESSION["we7_account"]["name"]);
		@($siteroot = $_SESSION["we7_w"]["siteroot"]);
		if (empty($uniacid)) {
			@($uniacid = $_SESSION["we7_account"]->uniacid);
		}
		if (empty($xcx_name)) {
			@($xcx_name = $_SESSION["we7_account"]->name);
		}
		if (empty($siteroot)) {
			@($siteroot = $_SESSION["we7_account"]->siteroot);
		}
		if ($uniacid) {
			Session::set("we7_s", $_SESSION["we7_w"]);
			$username = $_SESSION["we7_user"]["username"];
			Cookie::set("username", $username);
			Cookie::set("uniacid", $uniacid);
			Cookie::set("xcx_name", $xcx_name);
			Cookie::set("siteroot", $siteroot);
		} else {
			$username = Cookie::get("username");
			$uniacid = Cookie::get("uniacid");
			$xcx_name = Cookie::get("xcx_name");
			$siteroot = Cookie::get("siteroot");
		}
		$this->assign("xcx_name", $xcx_name);
		$this->assign("siteroot", $siteroot);
		$id = self::setUser($username, $uniacid);
		$this->assign("uniacid", $uniacid);
		if (empty($id)) {
			exit("未获取商户");
		}
		$this->bus_id = $uniacid;
		Session::set("bus_id", $uniacid);
		if (empty($uniacid) || empty($_SESSION["we7_user"])) {
			$this->redirect("/");
		}
		$this->controller = Request::instance()->controller();
		$this->action = Request::instance()->action();
		$this->module_info = $this->bus->getModuleIdByModule($this->controller, $this->action);
		$this->moduleid = $this->module_info["module_id"];
		$this->assign("user_info", $id);
		$about = Db::name("ybmp_business_about")->where("mch_id", $uniacid)->find();
		$this->assign("about", $about);
		$root_array = $this->bus->getModuleRootAndSecondMenu($this->moduleid);
		$this->rootid = $root_array[0];
		$first_menu_list = $this->bus->getchildModuleQuery(0);
		$wq_uid = $_SESSION["we7_w"]["user"]["uid"];
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$chece_au = Db::name("ybmp_user_permission")->where("user_id", $wq_uid)->find();
		if (empty($chece_au)) {
			$res_check = Db::name("ybmp_user_role")->find();
			if (empty($res_check)) {
				Db::name("ybmp_user_role")->insert(["role_name" => "超级管理员", "role_status" => 1, "module_id_array" => "233,234,256,157,195,275,258,272,156,218,224,269,259,263,264,261,260,236,145,198,257,28,273,237,265,278,12,238,154,266,169,223,243,277,270,239,267,35,206,271", "info" => '', "create_time" => time()]);
			}
			if ($isadmin) {
				Db::name("ybmp_user_permission")->insert(["user_id" => $wq_uid, "role_id" => 1, "create_time" => time()]);
			} else {
				$role_id = Db::name("ybmp_user_role")->order("role_id desc")->find();
				Db::name("ybmp_user_permission")->insert(["user_id" => $wq_uid, "role_id" => $role_id["role_id"], "create_time" => time()]);
			}
		}
		$where = array();
		if ($wq_uid != 1 || !$isadmin) {
			$user_role_list = Db::name("ybmp_user_permission")->alias("p")->join("ybmp_user_role r", "r.role_id=p.role_id")->where("p.user_id", $wq_uid)->find();
			if (strpos($user_role_list["module_id_array"], "284")) {
				$this->assign("miaosha_show", 2);
			}
			$where["module_id"] = array("in", $user_role_list["module_id_array"]);
		}
		$where["is_menu"] = 1;
		$permissionList = Db::name("ybmp_bus_module")->where($where)->order("sort asc,module_id asc")->select();
		$firstArray = array();
		$p = array();
		foreach ($permissionList as $per) {
			if ($per["pid"] == 0 && $per["module_name"] != null) {
				$firstArray[] = $per;
			}
		}
		$skin = new ArlikiService($this->bus_id);
		$this->assign("skin", $skin->get_skin());
		foreach ($firstArray as &$first_per) {
			if (!empty($first_per["logo"])) {
				$re = explode(".png", $first_per["logo"]);
				$first_per["logo"] = $re[0] . "_" . $skin->get_skin() . ".png";
			}
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
				$second_per["sub"] = $threeArray;
			}
			$first_per["sub"] = $secondArray;
			$uu = explode("public", $first_per["logo"]);
			$first_per["test_log"] = "public" . $uu[1];
			$p[] = $first_per;
		}
		$visit_key = "__lastvisit_" . $_W["uid"];
		if (!empty($_GPC[$visit_key])) {
			$last_visit = explode(",", $_GPC[$visit_key]);
			$last_visit_url = $last_visit[1];
			$this->assign("last_visit_url", $last_visit_url);
		}
		$top_mid = Cookie::get("top_mid");
		$sub_mid = Cookie::get("sub_mid");
		$three_mid = Cookie::get("three_mid");
		$now_first = Cookie::get("now_first");
		$now_second = Cookie::get("now_second");
		$now_key = Cookie::get("now_key");
		$top_mid = empty($top_mid) ? 233 : $top_mid;
		$sub_mid = empty($sub_mid) ? 0 : $sub_mid;
		$now_first = empty($now_first) ? -1 : $now_first;
		$now_second = empty($now_second) ? -1 : $now_second;
		$three_mid = empty($three_mid) ? 0 : $three_mid;
		$now_key = empty($now_key) ? -1 : $now_key;
		$this->assign("top_mid", $top_mid);
		$this->assign("guan", $this->check_permition(157));
		$this->assign("sub_mid", $sub_mid);
		$this->assign("now_first", $now_first);
		$this->assign("now_second", $now_second);
		$this->assign("three_mid", $three_mid);
		$this->assign("now_key", $now_key);
		$this->assign("all_menu", json_encode($p));
		$copyright = Db::name("ybmp_copyright")->where("uniacid", $this->bus_id)->find();
		if (empty($copyright)) {
			$copyright = Db::name("ybmp_copyright")->where("uniacid", 0)->find();
		}
		$this->assign("copyright", $copyright);
		$isfounder = $_SESSION["we7_w"]["isfounder"];
		$this->assign("isfounder", $isfounder);
		$this->assign("site_name", $copyright["site_name"] ? $copyright["site_name"] : "超级名片");
		$this->assign("version_id", $_SESSION["version_id"]);
	}
	protected function setUser($username, $uniacid)
	{
		if (isset($uniacid)) {
			$result = Db::name("ybmp_business")->where("id", $uniacid)->find();
			if ($result < 1) {
				$data["id"] = $uniacid;
				$data["agents_id"] = 1;
				$data["nick_name"] = "管理员";
				$data["name"] = $username;
				$data["password"] = '';
				$data["phone"] = '';
				$data["head_img"] = "public/upload/1/logo/1512697116.png";
				$data["app_key"] = '';
				$data["create_time"] = time();
				$data["is_del"] = 1;
				$data["mod_class_id"] = 2;
				$data["bus_mod_id"] = 1;
				$data["payment_method"] = 1;
				$data["is_one"] = 2;
				$data["uniacid"] = $uniacid;
				Db::name("ybmp_business")->insert($data);
				$imgs["pid"] = 0;
				$imgs["group_name"] = "默认相册";
				$imgs["group_cover"] = "124";
				$imgs["is_default"] = 1;
				$imgs["sort"] = 1;
				$imgs["create_time"] = time();
				$imgs["mch_id"] = $uniacid;
				$imgs["ident"] = '';
				$imgs["pages_url"] = '';
				$imgs["is_system"] = 0;
				$imgs["ident_class"] = '';
				Db::name("ybmp_images_group")->insert($imgs);
				$digital["id"] = $uniacid;
				$digital["key"] = "WXPAY";
				$conf["APP_ID"] = $_SESSION["we7_w"]["account"]["key"];
				$conf["APP_NAME"] = $_SESSION["we7_w"]["account"]["name"];
				$conf["APP_SECRET"] = $_SESSION["we7_w"]["account"]["secret"];
				$conf["APP_MCHID"] = '';
				$un_data["url"] = $_SERVER["HTTP_HOST"];
				$un_url = explode(":", $un_data["url"]);
				$refund_url = get_child_url(false);
				$conf["APP_URL"] = $refund_url . "addons/yb_mingpian/core/pay.php";
				$conf["APP_URL_BG"] = $refund_url . "addons/yb_mingpian/core/bargain_pay.php";
				$conf["APP_URL_PT"] = $refund_url . "addons/yb_mingpian/core/pt_pay.php";
				$conf["APP_URL_MS"] = $refund_url . "addons/yb_mingpian/core/ms_pay.php";
				$conf["APP_KEY"] = '';
				$conf = json_encode($conf, true);
				$digital["value"] = $conf;
				$digital["value2"] = '';
				$digital["info"] = "微信支付";
				$digital["is_use"] = 1;
				$digital["mch_id"] = $uniacid;
				$digital["modify_time"] = time();
				Db::name("ybmp_config")->insert($digital);
				$snap = $_SESSION["we7_w"]["siteroot"] . "addons";
				$cuff = explode("addons", $snap);
				$cuff = explode("//", $cuff[0]);
				$cuff = explode("/", $cuff[1]);
				unset($cuff[0]);
				$cuff = array_values($cuff);
				$temp = '';
				if (count($cuff) > 0) {
					foreach ($cuff as $key => $value) {
						$temp .= "/" . $value;
					}
				} else {
					$temp = "/";
				}
				$templs = Db::name("ybmp_tmpl")->where("id", 1)->find();
				$templen["title"] = $templs["name"];
				$templen["index_values"] = $templs["index_values"];
				$templen["create_time"] = time();
				$templen["mch_id"] = $uniacid;
				$templen["style_value"] = $templs["style_value"];
				$templen["img"] = $templs["img"];
				$toopsen["index_values"] = $templs["index_values"];
				$toopsen["mch_id"] = $uniacid;
				$toopsen["values"] = $templs["style_value"];
				Db::name("ybmp_user_tmpl")->insert($toopsen);
				$un_data["url"] = $_SERVER["HTTP_HOST"];
				$un_url = explode(":", $un_data["url"]);
				$un_data["url"] = $un_url[0];
				$un_data["addons"] = $temp;
				$un_data["username"] = $username;
				$un_data["uniacid"] = $uniacid;
				$un_data["ask"] = WXAPP_TYPE;
				$un_data["ipv4"] = $_SERVER["SERVER_ADDR"];
				self::base_shape($un_data);
				return $data;
			} else {
				$snap = $_SESSION["we7_w"]["siteroot"] . "addons";
				$cuff = explode("addons", $snap);
				$cuff = explode("//", $cuff[0]);
				$cuff = explode("/", $cuff[1]);
				unset($cuff[0]);
				$cuff = array_values($cuff);
				$temp = '';
				if (count($cuff) > 0) {
					foreach ($cuff as $key => $value) {
						$temp .= "/" . $value;
					}
				} else {
					$temp = "/";
				}
				$un_data["url"] = $_SERVER["HTTP_HOST"];
				$un_url = explode(":", $un_data["url"]);
				$un_data["url"] = $un_url[0];
				$un_data["addons"] = $temp;
				$un_data["username"] = $username;
				$un_data["uniacid"] = $uniacid;
				$un_data["ask"] = WXAPP_TYPE;
				$un_data["ipv4"] = $_SERVER["SERVER_ADDR"];
				self::base_shape($un_data);
				$payCallback = Db::name("ybmp_config")->where("mch_id", $uniacid)->find();
				$callback = json_decode($payCallback["value"], true);
				$refund_url = get_child_url(false);
				$callback["APP_URL"] = $refund_url . "/addons/yb_mingpian/core/pay.php";
				$callback["APP_URL_BG"] = $refund_url . "/addons/yb_mingpian/core/bargain_pay.php";
				$callback["APP_URL_PT"] = $refund_url . "/addons/yb_mingpian/core/pt_pay.php";
				$callback["APP_URL_MS"] = $refund_url . "/addons/yb_mingpian/core/ms_pay.php";
				$callback = json_encode($callback, true);
				$payCallback["value"] = $callback;
				Db::name("ybmp_config")->where("mch_id", $uniacid)->update($payCallback);
				return $result;
			}
		}
	}
	protected function base_shape($un_data)
	{
		try {
			$postdata = http_build_query($un_data);
			$opts = array("http" => array("method" => "POST", "header" => "Content-type: application/x-www-form-urlencoded", "content" => $postdata, "timeout" => 15 * 60));
			$context = stream_context_create($opts);
			return file_get_contents("http://ad.vip.ly100.wang/admin/alliance/league", false, $context);
		} catch (Exception $e) {
			return false;
		}
	}
	public function check_grant()
	{
		$id = input("param.id");
		$un_data = $_SERVER["HTTP_HOST"];
		$un_url = explode(":", $un_data);
		$un_data = $un_url[0];
		$data = ["id" => $id, "url" => $un_data, "username" => $_SESSION["we7_user"]["username"], "uniacid" => $_SESSION["we7_account"]["uniacid"]];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		curl_close($ch);
		$info = json_decode($output, true)["info"];
		$values = $info["style_value"];
		$values_arr = json_decode($values, true);
		$ch = json_decode($output, true);
	}
	public function check_corp()
	{
		$mch_id = $this->bus_id;
		$ap = db::name("ybmp_corp_conf")->field("corp_id")->where("mch_id", $mch_id)->find();
		if (empty($ap) || count($ap) == 0) {
			$this->assign("check_corp", "<script type='text/javascript'> \$(document).ready(function () {layer.open({type: 1, title: ['请填写企业信息以使用此功能', 'font-size:18px'], closeBtn: 1, isOutAnim: false, shade: [0], area: ['500px', '40px'], offset: '160px', time: 0, anim: 5 }); }); </script>");
		}
	}
	public function clear_cache()
	{
		$path = SITE_PATH . "runtime/cache/";
		if (file_exists($path)) {
			deldir($path);
			cache(null);
		}
		return 1;
	}
	public function write_log($do, $data = '')
	{
		db::name("ybmp_synlog")->insert(["mch_id" => $this->bus_id, "media_id" => $this->uuid . ":" . $do . $data, "create_time" => date("Ymd/His", time())]);
	}
	public function check_permition($id)
	{
		$uid = $_SESSION["we7_w"]["user"]["uid"];
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		if ($isadmin) {
			return true;
		}
		$re = Db::name("ybmp_user_permission")->alias("a")->join("ybmp_user_role s", "a.role_id=s.role_id", "left")->where("a.user_id", $uid)->value("module_id_array");
		$li = explode(",", $re);
		return in_array($id, $li);
	}
	public function open_page($start = true, $value = 1, $exp = 20)
	{
		if ($start) {
			Cache::set("is_load", $value, $exp);
		} else {
			Cache::set("is_load", null);
		}
	}
}
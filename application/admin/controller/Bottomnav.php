<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
class Bottomnav extends Base
{
	public function __construct()
	{
		parent::__construct();
	}
	private $tar_list = "[{\"name\":\"名片\",\"alias\":null,\"url\":\"/yb_mingpian/pages/cardinfo/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_0.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_0.png\",\"key\":\"1\"},{\"name\":\"商城\",\"alias\":null,\"url\":\"/yb_mingpian/pages/shop/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_1.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_1.png\",\"key\":\"1\"},{\"name\":\"动态\",\"alias\":null,\"url\":\"/yb_mingpian/pages/message/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_2.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_2.png\",\"key\":\"1\"},{\"name\":\"官网\",\"alias\":null,\"url\":\"/yb_mingpian/pages/index/index\",\"tabbar_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/white_3.png\",\"tabbar_select_icon\":\"https://mp.sssvip.net/addons/yb_mingpian/core/public/upload/navigat/green_3.png\",\"key\":\"1\"}]";
	public function menu_index()
	{
		return view("bottomnav/index");
	}
	public function gettmpl()
	{
		if (request()->isAjax() && request()->method() == "POST") {
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
				if (!empty($old_tabbar) && !isset($old_tabbar["tabBar"])) {
					foreach ($old_tabbar as $k => $item) {
						$list[$k]["name"] = $item["name"];
					}
				}
				$tabbar["list"] = $list;
				$value["tabbar"] = $tabbar;
			}
			return $value;
		}
	}
	public function save_tmpl()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$data = input("param.data");
			$nc = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->count();
			if ($nc < 1) {
				$res = Db::name("ybmp_user_tmpl")->insert(["index_values" => $data, "mch_id" => $this->bus_id]);
			} else {
				$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["index_values" => $data]);
			}
			return AjaxReturn($res);
		}
	}
	public function menu_select()
	{
		$type = input("param.type");
		$index = input("param.index");
		$data = array(["text" => "商城首页", "type" => "shop", "param" => 2, "path" => "/yb_mingpian/pages/shop/index"], ["text" => "官网首页", "type" => "web", "param" => 2, "path" => "/yb_mingpian/pages/index/index"], ["text" => "企业动态", "type" => "message", "param" => 2, "path" => "/yb_mingpian/pages/message/index"], ["text" => "商品列表", "role_id" => "12", "type" => "product", "param" => 3], ["text" => "购物车", "role_id" => "12", "type" => "cart", "param" => 2, "path" => "/yb_mingpian/pages/member/cart/index"], ["text" => "会员中心", "type" => "cenmember", "param" => 2, "path" => "/yb_mingpian/pages/member/index/index"], ["text" => "文章分类", "type" => "class_article", "param" => 2, "path" => "/yb_mingpian/pages/find/index"], ["text" => "相册分类", "type" => "class_image", "param" => 2, "path" => "/yb_mingpian/pages/class_image/index"], ["text" => "优惠券", "role_id" => "169", "type" => "coupon", "param" => 2, "path" => "/yb_mingpian/pages/shop_coupon/index"], ["text" => "产品列表", "type" => "product_list", "param" => 2, "path" => "/yb_mingpian/pages/product/list/index"], ["text" => "砍价", "role_id" => "223", "type" => "bargain", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/index/index"], ["text" => "砍价列表", "role_id" => "223", "type" => "bargain", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/good_list/index"], ["text" => "拼团", "role_id" => "243", "type" => "pintuan", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/index/index"], ["text" => "拼团列表", "role_id" => "243", "type" => "pintuan_list", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/index/list"], ["text" => "联系我们", "type" => "contact", "param" => 2, "path" => "/yb_mingpian/pages/contact/index"], ["text" => "预约列表", "role_id" => "210", "type" => "book_list", "param" => 2, "path" => "/yb_mingpian/pages/book_list/index"], ["text" => "分销中心", "role_id" => "253", "type" => "my_fenxiao", "param" => 2, "path" => "/yb_mingpian/pages/fenxiao/pages/index/index"], ["text" => "我的关注", "type" => "my_follow", "param" => 2, "path" => "/yb_mingpian/pages/member/favorite/index"], ["text" => "我的优惠券", "role_id" => "169", "type" => "my_coupon", "param" => 2, "path" => "/yb_mingpian/pages/member/coupon/index"], ["text" => "我的预约", "role_id" => "210", "type" => "my_book", "param" => 2, "path" => "/yb_mingpian/pages/appointment/index"], ["text" => "秒杀列表", "role_id" => "284", "type" => "miaosha", "param" => 2, "path" => "/yb_mingpian/pages/miaosha/seckillList/index"], ["text" => "秒杀订单", "role_id" => "284", "type" => "miaosha", "param" => 2, "path" => "yb_mingpian/pages/miaosha/order/index"], ["text" => "我的砍价", "role_id" => "223", "type" => "my_kjm", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/my_record/index"], ["text" => "砍价订单", "role_id" => "223", "type" => "my_kjo", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/order/index"], ["text" => "我的拼团", "role_id" => "243", "type" => "my_ptm", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/group/index"], ["text" => "拼团订单", "role_id" => "243", "type" => "my_pto", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/orders/index"], ["text" => "关于我们", "type" => "about", "param" => 2, "path" => "/yb_mingpian/pages/member/about/index"], ["text" => "打电话", "type" => "phone", "param" => 2], ["text" => "地图", "type" => "map", "param" => 2], ["text" => "付费内容首页", "role_id" => "282", "type" => "paycontent", "param" => 2, "path" => "/yb_mingpian/pages/paycontent/index"], ["text" => "我的订阅", "role_id" => "282", "type" => "paycontent_my", "param" => 2, "path" => "/yb_mingpian/pages/paycontent/my/index"]);
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$founder_groupid = $_SESSION["we7_w"]["user"]["founder_groupid"];
		if (!$isadmin || $founder_groupid == 2) {
			$wq_uid = $_SESSION["we7_w"]["user"]["uid"];
			$role_id = Db::name("ybmp_user_permission")->alias("p")->join("ybmp_user_role r", "p.role_id = r.role_id")->field("r.module_id_array")->where(["p.user_id" => $wq_uid])->find();
			if (!empty($role_id)) {
				$role_ids = explode(",", $role_id["module_id_array"]);
				$arr = array();
				foreach ($data as $item) {
					if (isset($item["role_id"]) && !in_array($item["role_id"], $role_ids)) {
						continue;
					}
					$arr[] = $item;
				}
				$data = $arr;
			}
		}
		$this->assign("menu", $data);
		$this->assign("type", $type);
		$this->assign("index", $index);
		return view("menu/menu_select");
	}
}
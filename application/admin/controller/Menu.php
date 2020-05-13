<?php


namespace app\admin\controller;

use think\Cache;
use think\Db;
use think\Exception;
use think\Request;
class Menu extends Base
{
	private $wx_page = array(array("title" => "官网", "path" => "yb_mingpian/pages/index/index"), array("title" => "名片列表", "path" => "yb_mingpian/pages/card/index"), array("title" => "名片详情", "path" => "yb_mingpian/pages/card/info?id="), array("title" => "商城", "path" => "yb_mingpian/pages/shop/index"), array("title" => "万能页面", "path" => "yb_mingpian/pages/power/index?id=1"), array("title" => "万能表单", "path" => "yb_mingpian/pages/form/index?id=1"), array("title" => "会员中心", "path" => "yb_mingpian/pages/member/index/index"), array("title" => "收货地址", "path" => "yb_mingpian/pages/member/address/index"), array("title" => "购物车", "path" => "yb_mingpian/pages/member/cart/index"), array("title" => "文章列表", "path" => "yb_mingpian/pages/find/index"), array("title" => "商品列表", "path" => "yb_mingpian/pages/product/index?id=（为空默认展示全部）"), array("title" => "商品详情", "path" => "yb_mingpian/pages/goods/detail/index?id=1"), array("title" => "领券列表", "path" => "yb_mingpian/pages/shop_coupon/index"), array("title" => "关于我们", "path" => "yb_mingpian/pages/member/about/index"), array("title" => "h5页面", "path" => "yb_mingpian/pages/web/index?name=标题&url=网页链接"), array("title" => "图片列表", "path" => "yb_mingpian/pages/image/index"), array("title" => "我的预约", "path" => "yb_mingpian/pages/appointment/index"), array("title" => "文章分类列表", "path" => "yb_mingpian/pages/class_article/index"), array("title" => "相册分类列表", "path" => "yb_mingpian/pages/class_image/index"), array("title" => "文章详情", "path" => "yb_mingpian/pages/find_info/index?id=1"), array("title" => "我的优惠券", "path" => "yb_mingpian/pages/member/coupon/index"), array("title" => "我的订单", "path" => "yb_mingpian/pages/order/index"), array("title" => "分销中心", "path" => "yb_mingpian/pages/fenxiao/pages/index/index"), array("title" => "砍价首页", "path" => "yb_mingpian/pages/kanjia/index/index"), array("title" => "砍价列表", "path" => "yb_mingpian/pages/kanjia/good_list/index"), array("title" => "砍价商品详情", "path" => "yb_mingpian/pages/kanjia/goods_info/index?id=1"), array("title" => "我的砍价", "path" => "yb_mingpian/pages/kanjia/my_record/index"), array("title" => "拼团首页", "path" => "yb_mingpian/pages/pintuan/pages/index/index"), array("title" => "拼团列表", "path" => "yb_mingpian/pages/pintuan/pages/index/list"), array("title" => "拼团商品详情", "path" => "yb_mingpian/pages/pintuan/pages/goods/detail?gid=1"), array("title" => "我的拼团", "path" => "yb_mingpian/pages/pintuan/pages/group/index"), array("title" => "拼团订单", "path" => "yb_mingpian/pages/pintuan/pages/orders/index"));
	public function wxapp_page()
	{
		$this->assign("data", $this->wx_page);
		return view("menu/wxapp");
	}
	public function selece_this()
	{
		Cache::set("is_load", 2, 20);
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$type = input("param.type");
		$this_id = input("param.this_id");
		$new = input("param.new", "0");
		$url = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->where("menu", $type)->find();
		$this->assign("new", $new);
		$this->assign("url", $url);
		$this->assign("this_id", $this_id);
		$this->assign("type", $type);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		switch ($type) {
			case "goods":
				$art = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "m.img_id=g.images")->where("g.mch_id", $mch_id)->field("g.goods_id,g.create_time,g.goods_name,g.price,g.introduction,m.img_cover")->order("g.create_time desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("goods", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/goods_test");
				break;
			case "article_info":
				$art = Db::name("ybmp_article")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/article_test");
				break;
			case "article":
				$art = Db::name("ybmp_article_class")->where("is_dynamic=2")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/article_test");
				break;
			case "images":
				$art = Db::name("ybmp_images_group")->where("mch_id", $mch_id)->select();
				foreach ($art as $k => $v) {
					$img = Db::name("ybmp_images")->where("img_id", $v["group_cover"])->find();
					if ($img == '') {
						$art[$k]["img"] = "none";
					} else {
						$art[$k]["img"] = $img["img_cover"];
					}
				}
				$this->assign("art", $art);
				return view("menu/images_menu");
				break;
			case "class_article":
				$art = Db::name("ybmp_article_class")->where("mch_id", $mch_id)->where("is_dynamic", 2)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/class_article");
				break;
			case "class_image":
				$art = Db::name("ybmp_images_group")->where("mch_id", $mch_id)->select();
				foreach ($art as $k => $v) {
					$img = Db::name("ybmp_images")->where("img_id", $v["group_cover"])->find();
					if ($img == '') {
						$art[$k]["img"] = "none";
					} else {
						$art[$k]["img"] = $img["img_cover"];
					}
				}
				$this->assign("art", $art);
				return view("menu/class_images");
				break;
			case "caregory":
				$art = Db::name("ybmp_goods_cate")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/caregory_test");
				break;
			case "power":
				$art = Db::name("ybmp_bus_tmpl")->where("page_type", 3)->where("is_del=1")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/power_test");
				break;
			case "form":
				$art = Db::name("ybmp_bus_form")->where("is_del=1")->order("create_time desc")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/form_test");
				break;
			case "applets":
				$art = Db::name("ybmp_sapp")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/applets_test");
				break;
			case "web_page":
				$art = Db::name("ybmp_applink")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/web_page_test");
				break;
			case "bargain_info":
				$art = Db::name("ybmp_bargain")->alias("b")->join("ybmp_images i", "i.img_id=b.bargain_picture")->where("b.mch_id", $mch_id)->where("b.bargain_type=1")->field("b.*,i.img_cover")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/bargain_test");
				break;
			case "pintuan_info":
				$art = Db::name("ybmp_pt_goods")->alias("b")->join("ybmp_images i", "i.img_id=b.img")->where("b.mch_id", $mch_id)->where("b.isShow=1")->field("b.*,i.img_cover")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("art", $art);
				$this->assign("page", $art->render());
				Cache::set("is_load", null);
				return view("menu/pintuan_info");
				break;
		}
	}
	public function add_class_article()
	{
		$str = input("param.class_str", '');
		$this_id = input("param.this_id", '') + 1;
		$arr = explode(",", $str);
		Db::name("ybmp_article_class")->where("mch_id", $this->bus_id)->update(["ident_class" => '']);
		foreach ($arr as $a) {
			Db::name("ybmp_article_class")->where("class_id", $a)->where("mch_id", $this->bus_id)->update(["ident_class" => "class_article_" . $this_id]);
		}
		return AjaxReturn(1);
	}
	public function find_default()
	{
		$id = \request()->param("id");
		if (true) {
			$data["tpl"] = 0;
			global $_W;
			$_W = $_SESSION["we7_w"];
			$str = "{\n    \"tabBar\": {\n      \"name\":\"商城\",\n     \"color\": \"#8b8b8b\",\n     \"selectedColor\": \"#e02e24\",\n    \"background\":\"#FF1493\",\n    \"backgroundTextStyle\":\"#ffffff\",\n    \"backgroundColor\": \"#ffffff\",\n    \"IsDiDis\": \"false\",\n        \"list\": [\n            {\n                \"key\": \"index\",\n                \"imgurl\": \"/yb_mingpian/pages/index/index\",\n                \"name\": \"首页\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_home.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_home.png\"\n            },\n            {\n                \"key\": \"find\",\n                \"imgurl\": \"/yb_mingpian/pages/find/index\",\n                \"name\": \"发现\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_find.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_find.png\"\n            },\n            {\n                \"key\": \"product\",\n                \"imgurl\": \"/yb_mingpian/pages/product/index\",\n                \"name\": \"商品\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_cate.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_cate.png\"\n            },\n            {\n                \"key\": \"member_index\",\n                \"imgurl\": \"/yb_mingpian/pages/member/index/index\",\n                \"name\": \"会员中心\",\n                \"page_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/gray_people.png\",\n                \"page_select_icon\": \"" . $_W["siteroot"] . "addons/yb_mingpian/core/public/icon/red_people.png\"\n            }\n        ]\n    }\n}";
			$data = $str;
		}
		return $data;
	}
	public function find_my_default()
	{
		$mch_tpl = Db::name("ybmp_user_tmpl_box")->where("mch_id", $this->bus_id)->find();
		$data = json_decode($mch_tpl["style_value"], true);
		$data["tpl"] = 1;
		return $data;
	}
	public function select_icon()
	{
		$linenum = input("param.linenum");
		$mod_id = input("param.mod_id");
		$tmpl_list = Db::name("ybmp_tmpl_icon")->where("tmpl_id", $mod_id)->select();
		$this->assign("mod_id", $mod_id);
		$this->assign("tmpl_list", $tmpl_list);
		$this->assign("url", IMG_URL);
		$this->assign("linenum", $linenum);
		return view("menu/select_icon");
	}
	public function selece_this_not()
	{
		$type = input("param.type");
		$mod_id = input("param.mod_id");
		$this_id = input("param.this_id");
		$menu = Db::name("ybmp_tmpl_menu")->where("type", $type)->find();
		$url = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->where("menu", $type)->select();
		$url["name"] = $menu["text"];
		$url["this_id"] = $this_id;
		return $url;
	}
	public function select_mod_all()
	{
		$type = input("param.type");
		$mod_id = input("param.mod_id");
		$this_id = input("param.this_id");
		$mod_lit = Db::name("ybmp_tmpl_pages")->alias("p")->join("ybmp_tmpl_menu m", "m.type=p.menu")->where("p.menu", $type)->where("p.mod_id", $mod_id)->field("p.*,m.text")->paginate(5);
		$page = $mod_lit->render();
		$this->assign("mod_lit", $mod_lit);
		$this->assign("page", $page);
		$this->assign("this_id", $this_id);
		$this->assign("type", $type);
		return view("menu/select_mod_all");
	}
	public function di_select_mod_all_goods()
	{
		$this_id = input("param.this_id");
		$this->assign("this_id", $this_id);
		return view("menu/di_select_mod_all");
	}
	public function di_select_mod_all_article()
	{
		$this_id = input("param.this_id");
		$this->assign("this_id", $this_id);
		return view("menu/di_select_mod_all_article");
	}
	public function get_view_select()
	{
		$this_id = input("param.this_id");
		$type = input("param.type");
		$this->assign("this_id", $this_id);
		$this->assign("type", $type);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		if ($type == "goods_detail") {
			$goods = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "g.images=m.img_id")->where("g.is_del=0")->where("g.mch_id", $this->bus_id)->order("g.create_time desc")->field("g.*,m.img_cover")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("goods", $goods);
			return view("menu/di_goods_detail");
		}
		if ($type == "find_info") {
			$find_info = Db::name("ybmp_article")->where("status=2")->where("mch_id", $this->bus_id)->order("create_time desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("find_info", $find_info);
			return view("menu/di_find_info");
		}
		if ($type == "image") {
			$images_group = Db::name("ybmp_images_group")->alias("g")->join("ybmp_images m", "g.group_cover=m.img_id", "left")->where("g.mch_id", $this->bus_id)->field("g.*,m.img_cover")->order("g.create_time desc")->where("g.is_default=0")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("images_group", $images_group);
			return view("menu/di_images_groupinfo");
		}
		if ($type == "find") {
			$article_class = Db::name("ybmp_article_class")->where("mch_id", $this->bus_id)->where("is_dynamic", 2)->order("create_time desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("article_class", $article_class);
			return view("menu/di_article_class");
		}
		if ($type == "power") {
			$article_class = Db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where("is_del=1")->order("create_time desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("article_class", $article_class);
			return view("menu/di_power_class");
		}
		if ($type == "form") {
			$article_class = Db::name("ybmp_bus_form")->where("mch_id", $this->bus_id)->where("type", 1)->where("is_del=1")->order("create_time desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("article_class", $article_class);
			return view("menu/di_from");
		}
		if ($type == "applets") {
			$sapp = Db::name("ybmp_sapp")->where("mch_id", $this->bus_id)->order("id desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("sapp", $sapp);
			return view("menu/di_sapp");
		}
		if ($type == "web_page") {
			$sapp = Db::name("ybmp_applink")->where("mch_id", $this->bus_id)->order("id desc")->paginate(15, false, ["query" => ["s" => $url, "type" => $type, "this_id" => $this_id]]);
			$this->assign("sapp", $sapp);
			return view("menu/di_web_page");
		}
	}
	public function select_menu()
	{
		$this_id = input("param.select_id");
		$mod_id = input("param.mod_id");
		$menu = Db::name("ybmp_tmpl_menu")->select();
		$page = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->select();
		$data = array();
		foreach ($page as $key => $value) {
			foreach ($menu as $k => $v) {
				if ($value["menu"] == $v["type"]) {
					$data[$k] = $v;
				}
			}
		}
		$this->assign("this_id", $this_id);
		$this->assign("mod_id", $mod_id);
		$this->assign("menu", $data);
		return view("menu/select_menu_test");
	}
	public function select_join()
	{
		$this_id = input("param.select_id");
		$condition["mch_id"] = $this->bus_id;
		$condition["is_del"] = 2;
		$condition["show"] = 1;
		$data = commonPage("ybmp_offweb_join", $condition, '', 10, "id desc");
		$this->assign("this_id", $this_id);
		$this->assign("page", $data->render());
		$this->assign("list", $data);
		return view("menu/select_join");
	}
	public function index_select_product()
	{
		$this_id = input("param.select_id");
		$condition["p.mch_id"] = $this->bus_id;
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_pro")->alias("p")->join("ybmp_pro_class c", "p.mch_id=c.mch_id and p.cid=c.id")->field("p.name,p.content,p.pic,p.price,p.create_time,c.name class_name,p.type,p.id")->where($condition)->order("p.create_time")->select();
		$arr = array();
		for ($i = 0; $i < count($list); $i++) {
			$arr[$i] = $list[$i];
			$arr[$i]["p_type"] = $list[$i]["type"] == 1 ? "公司" : "个人";
		}
		$this->assign("this_id", $this_id);
		$this->assign("list", $arr);
		return view("menu/select_product");
	}
	public function import_mod()
	{
		$nimei = request()->param("nimei", '');
		$wn_id = request()->param("wn_id", '');
		$type = request()->param("type", '');
		$id = input("param.mod_id", '');
		$my_id = input("param.my_id", '');
		if (request()->isAjax() && request()->isPost() && $id > 0) {
			Db::name("ybmp_business")->inc("create_time")->where("id", $this->bus_id)->update(["bus_mod_id" => $id]);
			if ($my_id == 3) {
				$index_mod = Db::name("ybmp_bus_tmpl")->where("id", $id)->find();
			} else {
				$index_mod = Db::name("ybmp_tmpl")->where("id", $id)->find();
			}
			$json = json_decode($index_mod["style_value"], true);
			foreach ($json as $k => $v) {
				@($json["tabBar"]["IsDiDis"] = true);
			}
			$res = Db::name("ybmp_bus_tmpl")->where("id", $wn_id)->update(["index_values" => $index_mod["index_values"], "img" => $index_mod["img"]]);
			return AjaxReturnMsg(1);
		}
		$data = input("param.");
		$mod_class = input("param.mod_class", '');
		$status = empty($data["status"]) ? 0 : $data["status"];
		if ($nimei) {
			$status = 1;
		}
		$this->assign("status", $status);
		$data["page"] = empty($data["page"]) ? '' : $data["page"];
		if (empty($data["mod_name"])) {
			if ($type == 1) {
				$data["mod_name"] = "官网";
			}
		}
		$page = request()->param("page", 1);
		$mod_name = $data["mod_name"];
		if ($_SESSION["we7_w"]["isfounder"] == true && $status != 1) {
			$ids = '';
			if ($status == 2) {
				$hasdw = Db::name("ybmp_tmpl")->field("serve_temp_id")->select();
				foreach ($hasdw as $dw) {
					if ($ids == '') {
						$ids .= $dw["serve_temp_id"];
					} else {
						$ids .= "," . $dw["serve_temp_id"];
					}
				}
			}
			$url = THIS_URL . "admin/Download/get_mod_all_shop?mod_class_id=" . $mod_class . "&page=" . $page . "&mod_name=" . $mod_name . "&ids=" . $ids;
			$output = get_url_content($url);
			$info = json_decode($output, true)["info"];
			foreach ($info as $k => $v) {
				$check = Db::name("ybmp_tmpl")->where("serve_temp_id", $v["id"])->find();
				if (!empty($check)) {
					$info[$k]["is_dow"] = 1;
					$info[$k]["is_this_id"] = $check["id"];
					$info[$k]["img"] = $check["img"];
				} else {
					$info[$k]["is_dow"] = 0;
				}
			}
			$this->assign("bus_mod_id", $info);
			$this->assign("isfounder", 1);
			$this->assign("count", json_decode($output, true)["count"]);
			$this->assign("page", json_decode($output, true)["page"]);
		} else {
			$sets = '';
			if ($mod_name != '') {
				$sets = "%{$mod_name}%";
			}
			$url = request()->query();
			$url = explode("=/", $url);
			$url = explode("&", $url[1]);
			$url = "/" . $url[0];
			$where["serve_temp_id"] = array("gt", 0);
			if (!empty($sets) && empty($type)) {
				$where["name"] = array("like", $sets);
			}
			$query = request()->param();
			$query["s"] = $url;
			$check = Db::name("ybmp_tmpl")->where($where)->paginate(10, false, ["query" => $query])->each(function ($item, $key) {
				$item["is_dow"] = 1;
				$item["is_this_id"] = $item["id"];
				$item["id"] = $item["serve_temp_id"];
				return $item;
			});
			$page = $check->render();
			$page = str_replace("_mod&amp;page=", "_mod&amp;status=" . $status . "&amp;page=", $page);
			$this->assign("isfounder", 1);
			$this->assign("bus_mod_id", $check);
			$this->assign("page", $page);
		}
		$this->assign("mod_class", $mod_class);
		$this->assign("mod_name", $mod_name);
		$this->assign("wn_id", $wn_id);
		if ($nimei) {
			$this->assign("wn_id", $wn_id);
			$this->assign("type", $type);
			return view("offweb/import_mod");
		}
		return view("menu/import_mod");
	}
	public function my_mod()
	{
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$uuid = $this->uuid;
		$down = request()->param("down", -1);
		$wn_id = request()->param("wn_id", '');
		$type = request()->param("type", '');
		if ($isadmin && empty($uuid)) {
			$uuid = -8384;
		}
		if (empty($uuid)) {
			$this->assign("dd", "暂无自定义模板,请编辑后保存");
			$this->assign("isfounder", -1);
			$this->assign("status", 3);
			$this->assign("page", '');
			return view("menu/import_mod");
		}
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$query["s"] = $url;
		$res = db::name("ybmp_bus_tmpl")->where("uuid", $uuid)->order("id", "desc")->paginate(8, false, ["query" => $query]);
		$list = array();
		for ($i = 0; $i < count($res); $i++) {
			$list[$i] = $res[$i];
			$list[$i]["is_dow"] = $down;
			$list[$i]["is_this_id"] = $res[$i]["id"];
		}
		$this->assign("dd", 2);
		$this->assign("bus_mod_id", $list);
		$this->assign("isfounder", -1);
		$this->assign("page", $res->render());
		$this->assign("status", 3);
		$this->assign("wn_id", $wn_id);
		$this->assign("type", $type);
		if ($down == 1) {
			return view("offweb/import_mod");
		} else {
			return view("menu/import_mod");
		}
	}
	public function del_my_page()
	{
		$id = \request()->param("id");
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$uuid = $this->uuid;
		if ($isadmin && empty($uuid)) {
			$uuid = -8384;
		}
		$res = 0;
		if ($id > 0) {
			$res = Db::name("ybmp_bus_tmpl")->where("id", $id)->where("uuid", $uuid)->update(["uuid" => null]);
		}
		return $res;
	}
	public function change_default()
	{
		$id = request()->param("id");
		$default = request()->param("default", 0);
		$type = request()->param("type");
		$default = $default == 1 ? 0 : 1;
		if ($type != 1) {
			$where = "type<>1";
		} else {
			$where = "type=1";
		}
		if ($default == 1) {
			Db::startTrans();
			try {
				db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where($where)->update(["default" => 0]);
				$rs = db::name("ybmp_bus_tmpl")->where("id", $id)->update(["default" => $default]);
				Db::commit();
				return AjaxReturn($rs);
			} catch (\Exception $e) {
				Db::rollback();
				return AjaxReturn(0);
			}
		} else {
			$ch = Db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where($where)->where("default", 1)->count();
			if ($ch <= 1) {
				return AjaxReturnMsg(0, "需至少保留一个默认值");
			} else {
				$rs = db::name("ybmp_bus_tmpl")->where("id", $id)->update(["default" => $default]);
				return AjaxReturn($rs);
			}
		}
	}
	public function index_module()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$czgw = \request()->param("lalala", '');
			$czg = \request()->param("papapa", '');
			$index_list = json_decode(input("param.index_list"), true);
			unset($index_list["banner"]);
			unset($index_list["catenav"]);
			unset($index_list["advert"]);
			unset($index_list["add_h"]);
			unset($index_list["add_top"]);
			unset($index_list["now_index"]);
			unset($index_list["this_type"]);
			unset($index_list["columntitle"]);
			unset($index_list["imgtextlist"]);
			unset($index_list["tripartite_list"]);
			unset($index_list["quartet_list"]);
			unset($index_list["fight_group_list"]);
			unset($index_list["goodlist"]);
			unset($index_list["edit_button"]);
			unset($index_list["edit_piclist"]);
			unset($index_list["floaticon"]);
			foreach ($index_list["all_data"] as $k => $v) {
				if ($v["type"] == "announcement" || $v["type"] == "message_board" || $v["type"] == "store_door" || $v["type"] == "comment" || $v["type"] == "edit_video" || $v["type"] == "edit_music" || $v["type"] == "search" || $v["type"] == "blank" || $v["type"] == "headline" || $v["type"] == "line" || $v["type"] == "position" || $v["type"] == "rich_text") {
					if ($v["type"] == "edit_video") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
						if ($index_list["all_data"][$k]["video_type"] == 1) {
							if ($index_list["all_data"][$k]["video_type"] == 1) {
								$rsd = $this->get_tx_video($index_list["all_data"][$k]["video_url"]);
								if ($rsd["code"] == 0) {
									$index_list["all_data"][$k]["vip_url"] = $rsd["real_url"];
								} else {
									$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
								}
							} else {
								$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
							}
						}
					}
					if ($v["type"] == "edit_music") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
					}
					if ($v["type"] == "store_door") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
					}
					continue;
				} else {
					if (!empty($v["list"])) {
						foreach ($v["list"] as $key => $value) {
							if (!empty($value["imgurl"])) {
								if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
								} else {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
								}
							} else {
								if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
								} else {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
								}
							}
						}
					}
				}
			}
			$mch_index_mod = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
			if ($mch_index_mod) {
				$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->where("id", $czgw)->inc("create_time")->update(["index_values" => json_encode($index_list)]);
			} else {
				$res = Db::name("ybmp_user_tmpl")->insert(["mch_id" => $this->bus_id, "index_values" => json_encode($index_list), "create_time" => time()]);
			}
			$pages_di = json_decode(input("param.menu_list"), true);
			$data = array();
			for ($i = 0; $i < count($pages_di); $i++) {
				$item = array();
				$item["key"] = $pages_di[$i]["key"];
				$item["lat"] = $pages_di[$i]["lat"];
				$item["lng"] = $pages_di[$i]["lng"];
				$item["appid"] = $pages_di[$i]["appid"];
				$item["path"] = $pages_di[$i]["path"];
				$item["phone"] = $pages_di[$i]["phone"];
				$item["imgurl"] = $pages_di[$i]["imgurl"];
				$item["ident"] = $pages_di[$i]["ident"];
				$item["name"] = $pages_di[$i]["name"];
				$item["title"] = $pages_di[$i]["name"];
				$item["name_this"] = $pages_di[$i]["name_this"];
				$item["page_icon"] = $pages_di[$i]["page_icon"];
				$item["page_select_icon"] = $pages_di[$i]["page_select_icon"];
				$data[] = $item;
			}
			$name = input("param.wx_name", "小程序名称");
			$background = input("param.DhColor_color") == '' ? "#df2f20" : input("param.DhColor_color");
			$backgroundTextStyle = input("param.BtColor_color") == '' ? "#ffffff" : input("param.BtColor_color");
			$selectedColor = input("param.iconColorSet_color") == '' ? "#e02e24" : input("param.iconColorSet_color");
			$color = input("param.iconTitColor_color") == '' ? "#8b8b8b" : input("param.iconTitColor_color");
			$backgroundColor = input("param.pureBorderColor_color") == '' ? "#ffffff" : input("param.pureBorderColor_color");
			$win_color = input("param.win_color") == '' ? "#ffffff" : input("param.win_color");
			$win_img = input("param.win_img") == '' ? '' : input("param.win_img");
			$is_di_dis = input("param.is_di_dis") == "false" ? false : true;
			$ext = array("tabBar" => array("name" => $name, "background" => $background, "backgroundTextStyle" => $backgroundTextStyle, "selectedColor" => $selectedColor, "color" => $color, "backgroundColor" => $backgroundColor, "winColor" => $win_color, "winImg" => $win_img, "IsDiDis" => $is_di_dis, "list" => $data));
			$val = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
			if ($val) {
				$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->inc("create_time")->update(["tmpl_id" => 1, "template_id" => 1, "values" => json_encode($ext)]);
			} else {
				$res = Db::name("ybmp_user_tmpl")->insert(["create_time" => 0, "tmpl_id" => 1, "template_id" => 1, "mch_id" => $this->bus_id, "values" => json_encode($ext)]);
			}
			return AjaxReturn($res);
		}
		$goods_cate = Db::name("ybmp_goods_cate")->where("is_visible=1")->where("mch_id", $this->bus_id)->select();
		$this->assign("goods_cate", $goods_cate);
		$article_class = Db::name("ybmp_article_class")->where("is_del=1")->where("is_dynamic=2")->where("mch_id", $this->bus_id)->select();
		$this->assign("article_class", $article_class);
		$this->assign("wn", request()->param("wn", -1));
		$this->assign("wn_id", request()->param("wn_id", 0));
		$this->assign("type", request()->param("type", ''));
		$this->assign("my_mod", request()->param("my_mod", -1));
		return view("menu/index_module");
	}
	public function index_select_menu()
	{
		$this_id = input("param.select_id");
		$this_type = input("param.this_type");
		$menu = Db::name("ybmp_tmpl_menu")->whereNotIn("id", "2")->select();
		$bus = Db::name("ybmp_business")->where("id", $this->bus_id)->find();
		$page = Db::name("ybmp_tmpl_pages")->order("id")->where("mod_id", 1)->select();
		$data = array();
		foreach ($page as $key => $value) {
			foreach ($menu as $k => $v) {
				if ($value["menu"] == $v["type"]) {
					$data[$k] = $v;
				}
			}
		}
		$this->assign("mod_class_id", $bus["mod_class_id"]);
		$this->assign("mod_id", $bus["bus_mod_id"]);
		$this->assign("this_id", $this_id);
		$this->assign("menu", $data);
		$this->assign("this_type", $this_type);
		return view("menu/index_select_menu");
	}
	public function index_select_goods()
	{
		Cache::set("is_load", 2, 20);
		$this_id = input("param.select_id");
		$url = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->where("menu", "goods")->find();
		$this->assign("url", $url);
		$this->assign("this_id", $this_id);
		$type = input("param.type");
		$this->assign("type", $type);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$art = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "m.img_id=g.images")->where("g.mch_id", $this->bus_id)->where("g.is_del", "0")->field("g.goods_id,g.create_time,g.goods_name,g.price,g.introduction,m.img_cover")->order("g.create_time desc")->paginate(15, false, ["query" => ["s" => $url, "select_id" => $this_id, "type" => $type]]);
		$this->assign("goods", $art);
		$this->assign("page", $art->render());
		$new = input("param.new", "0");
		$this->assign("new", $new);
		Cache::set("is_load", null);
		return view("menu/goods_test");
	}
	public function index_select_article()
	{
		$this_id = input("param.select_id");
		$url1 = request()->query();
		$url1 = explode("=/", $url1);
		$url1 = explode("&", $url1[1]);
		$url1 = "/" . $url1[0];
		$art = Db::name("ybmp_article")->where("mch_id", $this->bus_id)->order("create_time desc")->paginate(15, false, ["query" => ["s" => $url1, "select_id" => $this_id]]);
		$page = $art->render();
		$url = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->where("menu", "article_info")->find();
		$new = input("param.new", "0");
		$this->assign("new", $new);
		$this->assign("url", $url);
		$this->assign("page", $page);
		$this->assign("this_id", $this_id);
		$this->assign("article", $art);
		return view("menu/index_article");
	}
	public function find_mch_index_mod()
	{
		$iid = request()->param("dddd", '');
		if ($iid) {
			$da["id"] = $iid;
		}
		if (empty($da["id"])) {
			return array();
		}
		$da["mch_id"] = $this->bus_id;
		$mod = Db::name("ybmp_user_tmpl")->where($da)->find();
		return json_decode($mod["index_values"], true);
	}
	public function find_mch_my_mod()
	{
		$mod = Db::name("ybmp_user_tmpl_box")->where("mch_id", $this->bus_id)->find();
		return json_decode($mod["index_values"], true);
	}
	public function dialogalbumlist()
	{
		$number = request()->get("number", 1);
		$type = request()->get("type");
		$this_id = request()->get("this_id");
		$com = request()->get("com");
		$this->assign("type", $type);
		$this->assign("this_id", $this_id);
		$this->assign("com", $com);
		$this->assign("number", $number);
		$album = new \app\admin\service\Images();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$default_album_detail = $album->GetDefAll($condition);
		$this->assign("default_album_id", $default_album_detail["group_id"]);
		return view("menu/images_select");
	}
	public function index_select_position()
	{
		return view("menu/index_select_position");
	}
	public function index_di_select_position()
	{
		return view("menu/di_position");
	}
	public function getMenuName()
	{
		$type = input("param.type");
		$res = Db::name("ybmp_tmpl_menu")->where("type", $type)->find();
		return $res["text"];
	}
	public function import_mod_my()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_user_tmpl_box")->order("create_time desc")->where("mch_id", $this->bus_id)->paginate(12, false, ["query" => ["s" => $url, request()->param()]]);
		$page = $list->render();
		$this->assign("page", $page);
		$this->assign("list", $list);
		return view();
	}
	public function add_my_mod()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			global $_W;
			$_W = $_SESSION["we7_w"];
			$index_list = input("param.index_list");
			$index_list = json_decode($index_list, true);
			$title = input("param.title");
			$img = input("param.img_src");
			$imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . ".png";
			if (strstr($img, ",")) {
				$img = explode(",", $img);
				$img = $img[1];
			}
			$path = "public/upload/user_box";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$imageSrc = $path . "/" . $imageName;
			$r = file_put_contents(ROOT_PATH . "/" . $imageSrc, base64_decode($img));
			$img_src = '';
			if ($r) {
				$img_src = $_W["siteroot"] . "addons/yb_mingpian/core/public/upload/user_box/" . $imageName;
			}
			unset($index_list["banner"]);
			unset($index_list["catenav"]);
			unset($index_list["advert"]);
			unset($index_list["add_h"]);
			unset($index_list["add_top"]);
			unset($index_list["now_index"]);
			unset($index_list["this_type"]);
			unset($index_list["columntitle"]);
			unset($index_list["imgtextlist"]);
			unset($index_list["goodlist"]);
			unset($index_list["edit_button"]);
			unset($index_list["edit_piclist"]);
			unset($index_list["floaticon"]);
			foreach ($index_list["all_data"] as $k => $v) {
				if ($v["type"] == "comment" || $v["type"] == "edit_video" || $v["type"] == "edit_music" || $v["type"] == "search" || $v["type"] == "blank" || $v["type"] == "headline" || $v["type"] == "line" || $v["type"] == "position" || $v["type"] == "rich_text") {
					if ($v["type"] == "edit_video") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
						if ($index_list["all_data"][$k]["video_type"] == 1) {
							$rsd = $this->get_tx_video($index_list["all_data"][$k]["video_url"]);
							if ($rsd["code"] == 0) {
								$index_list["all_data"][$k]["vip_url"] = $rsd["real_url"];
							} else {
								$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
							}
						} else {
							$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
						}
					}
					if ($v["type"] == "edit_music") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
					}
					continue;
				} else {
					if (!strstr($index_list["all_data"][$k]["topimg"], "http://" . $_SERVER["HTTP_HOST"])) {
						$index_list["all_data"][$k]["topimg"] = $v["topimg"];
					} else {
						$index_list["all_data"][$k]["topimg"] = $v["topimg"];
					}
					foreach ($v["list"] as $key => $value) {
						if (!empty($value["imgurl"])) {
							if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
							} else {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
							}
						} else {
							if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
							} else {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
							}
						}
					}
				}
			}
			$pages_di = json_decode(input("param.menu_list"), true);
			$data = array();
			for ($i = 0; $i < count($pages_di); $i++) {
				$item = array();
				$item["key"] = $pages_di[$i]["key"];
				$item["lat"] = $pages_di[$i]["lat"];
				$item["lng"] = $pages_di[$i]["lng"];
				$item["appid"] = $pages_di[$i]["appid"];
				$item["path"] = $pages_di[$i]["path"];
				$item["phone"] = $pages_di[$i]["phone"];
				$item["imgurl"] = $pages_di[$i]["imgurl"];
				$item["ident"] = $pages_di[$i]["ident"];
				$item["name"] = $pages_di[$i]["name"];
				$item["title"] = $pages_di[$i]["name"];
				$item["name_this"] = $pages_di[$i]["name_this"];
				$item["page_icon"] = $pages_di[$i]["page_icon"];
				$item["page_select_icon"] = $pages_di[$i]["page_select_icon"];
				$data[] = $item;
			}
			$name = input("param.wx_name", "小程序名称");
			$background = input("param.DhColor_color") == '' ? "#df2f20" : input("param.DhColor_color");
			$backgroundTextStyle = input("param.BtColor_color") == '' ? "#ffffff" : input("param.BtColor_color");
			$selectedColor = input("param.iconColorSet_color") == '' ? "#e02e24" : input("param.iconColorSet_color");
			$color = input("param.iconTitColor_color") == '' ? "#8b8b8b" : input("param.iconTitColor_color");
			$backgroundColor = input("param.pureBorderColor_color") == '' ? "#ffffff" : input("param.pureBorderColor_color");
			$win_color = input("param.win_color") == '' ? "#ffffff" : input("param.win_color");
			$win_img = input("param.win_img") == '' ? '' : input("param.win_img");
			$is_di_dis = input("param.is_di_dis") == "false" ? false : true;
			$ext = array("tabBar" => array("name" => $name, "background" => $background, "backgroundTextStyle" => $backgroundTextStyle, "selectedColor" => $selectedColor, "color" => $color, "backgroundColor" => $backgroundColor, "winColor" => $win_color, "winImg" => $win_img, "IsDiDis" => $is_di_dis, "list" => $data));
			$res = Db::name("ybmp_user_tmpl_box")->insert(["img" => $img_src, "style_value" => json_encode($ext), "title" => $title, "mch_id" => $this->bus_id, "index_values" => json_encode($index_list), "create_time" => time()]);
			return AjaxReturn($res);
		}
	}
	public function update_my_mod()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			global $_W;
			$_W = $_SESSION["we7_w"];
			$index_list = json_decode(input("param.index_list"), true);
			$my_mod = input("param.my_mod");
			$img = input("param.img_src");
			$imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . ".png";
			if (strstr($img, ",")) {
				$img = explode(",", $img);
				$img = $img[1];
			}
			$path = "public/upload/user_box";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$imageSrc = $path . "/" . $imageName;
			$r = file_put_contents(ROOT_PATH . "/" . $imageSrc, base64_decode($img));
			$img_src = '';
			if ($r) {
				$img_src = $_W["siteroot"] . "addons/yb_mingpian/core/public/upload/user_box/" . $imageName;
			}
			unset($index_list["banner"]);
			unset($index_list["catenav"]);
			unset($index_list["advert"]);
			unset($index_list["add_h"]);
			unset($index_list["add_top"]);
			unset($index_list["now_index"]);
			unset($index_list["this_type"]);
			unset($index_list["columntitle"]);
			unset($index_list["imgtextlist"]);
			unset($index_list["goodlist"]);
			unset($index_list["edit_button"]);
			unset($index_list["edit_piclist"]);
			unset($index_list["floaticon"]);
			foreach ($index_list["all_data"] as $k => $v) {
				if ($v["type"] == "comment" || $v["type"] == "edit_video" || $v["type"] == "edit_music" || $v["type"] == "search" || $v["type"] == "blank" || $v["type"] == "headline" || $v["type"] == "line" || $v["type"] == "position" || $v["type"] == "rich_text") {
					if ($v["type"] == "edit_video") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
						if ($index_list["all_data"][$k]["video_type"] == 1) {
							$index_list["all_data"][$k]["video_url"] = $this->get_tx_video($index_list["all_data"][$k]["video_url"]);
						}
					}
					if ($v["type"] == "edit_music") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
					}
					continue;
				} else {
					if (!strstr($index_list["all_data"][$k]["topimg"], "http://" . $_SERVER["HTTP_HOST"])) {
						$index_list["all_data"][$k]["topimg"] = $v["topimg"];
					} else {
						$index_list["all_data"][$k]["topimg"] = $v["topimg"];
					}
					foreach ($v["list"] as $key => $value) {
						if (!empty($value["imgurl"])) {
							if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
							} else {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
							}
						} else {
							if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
							} else {
								$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
							}
						}
					}
				}
			}
			$pages_di = json_decode(input("param.menu_list"), true);
			$data = array();
			for ($i = 0; $i < count($pages_di); $i++) {
				$item = array();
				$item["key"] = $pages_di[$i]["key"];
				$item["lat"] = $pages_di[$i]["lat"];
				$item["lng"] = $pages_di[$i]["lng"];
				$item["appid"] = $pages_di[$i]["appid"];
				$item["path"] = $pages_di[$i]["path"];
				$item["phone"] = $pages_di[$i]["phone"];
				$item["imgurl"] = $pages_di[$i]["imgurl"];
				$item["ident"] = $pages_di[$i]["ident"];
				$item["name"] = $pages_di[$i]["name"];
				$item["title"] = $pages_di[$i]["name"];
				$item["name_this"] = $pages_di[$i]["name_this"];
				$item["page_icon"] = $pages_di[$i]["page_icon"];
				$item["page_select_icon"] = $pages_di[$i]["page_select_icon"];
				$data[] = $item;
			}
			$name = input("param.wx_name", "小程序名称");
			$background = input("param.DhColor_color") == '' ? "#df2f20" : input("param.DhColor_color");
			$backgroundTextStyle = input("param.BtColor_color") == '' ? "#ffffff" : input("param.BtColor_color");
			$selectedColor = input("param.iconColorSet_color") == '' ? "#e02e24" : input("param.iconColorSet_color");
			$color = input("param.iconTitColor_color") == '' ? "#8b8b8b" : input("param.iconTitColor_color");
			$backgroundColor = input("param.pureBorderColor_color") == '' ? "#ffffff" : input("param.pureBorderColor_color");
			$win_color = input("param.win_color") == '' ? "#ffffff" : input("param.win_color");
			$win_img = input("param.win_img") == '' ? '' : input("param.win_img");
			$is_di_dis = input("param.is_di_dis") == "false" ? false : true;
			$ext = array("tabBar" => array("name" => $name, "background" => $background, "backgroundTextStyle" => $backgroundTextStyle, "selectedColor" => $selectedColor, "color" => $color, "backgroundColor" => $backgroundColor, "winColor" => $win_color, "winImg" => $win_img, "IsDiDis" => $is_di_dis, "list" => $data));
			$res = Db::name("ybmp_user_tmpl_box")->where("id", $my_mod)->update(["img" => $img_src, "style_value" => json_encode($ext), "index_values" => json_encode($index_list), "create_time" => time()]);
			return AjaxReturn($res);
		}
	}
	public function del_my_mode()
	{
		$id = input("param.id");
		global $_W;
		$_W = $_SESSION["we7_w"];
		$info = Db::name("ybmp_user_tmpl_box")->where("id", $id)->find();
		$str = str_replace(array($_W["siteroot"] . "addons/yb_mingpian/core/"), '', $info["img"]);
		@unlink($str);
		$res = Db::name("ybmp_user_tmpl_box")->where("id", $id)->delete();
		return AjaxReturn($res);
	}
	public function my_import_mod()
	{
		$id = input("param.id");
		$info = Db::name("ybmp_user_tmpl_box")->where("id", $id)->find();
		$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->inc("create_time")->update(["values" => $info["style_value"], "index_values" => $info["index_values"]]);
		return AjaxReturn($res);
	}
	public function select_fight_group()
	{
		$this_id = input("param.select_id");
		$art = Db::name("ybmp_bargain")->alias("b")->join("ybmp_images i", "b.bargain_picture=i.img_id")->where("b.mch_id", $this->bus_id)->order("b.create_time desc")->field("b.*,i.img_cover")->paginate(20);
		$page = $art->render();
		$new = input("param.new", "0");
		$this->assign("new", $new);
		$this->assign("url", "pages/index/kanjia");
		$this->assign("page", $page);
		$this->assign("this_id", $this_id);
		$this->assign("fight", $art);
		return view("menu/index_fight_group");
	}
	public function universal()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_bus_tmpl")->where("type!=1")->where("mch_id", $this->bus_id)->order("default", "desc")->paginate(15, false, ["query" => ["s" => $url]]);
		$this->assign("list", $list);
		return view("menu/universal");
	}
	public function guan()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_bus_tmpl")->where("type=1")->where("mch_id", $this->bus_id)->order("default", "desc")->order("id desc")->paginate(15, false, ["query" => ["s" => $url]]);
		$this->assign("list", $list);
		return view();
	}
	public function add_page()
	{
		$up = request()->param("up", '');
		$id = request()->param("id", '');
		$type = request()->param("type", '');
		$name = request()->param("vals", '');
		$page_type = request()->param("page_type", '');
		$appid = request()->param("appid", '');
		$index_values = request()->param("index_values", '');
		$class = '';
		if ($type == 1) {
			$class = 1;
		} else {
			if ($type == 2 || $type == 3) {
				$class = -1;
				if ($page_type == 3) {
					$class = 3;
				}
				if ($page_type == 4) {
					$class = 2;
					$page_type = 3;
				}
				if ($page_type == 5) {
					$class = 3;
					$page_type = 3;
				}
			}
		}
		if ($type == -1) {
			if ($page_type == 3) {
				$class = 3;
			}
		}
		if (request()->isAjax() && \request()->isPost()) {
			if (!empty($class)) {
				if (!empty($id)) {
					db::name("ybmp_bus_tmpl")->where("id", $id)->update(["name" => $name, "type" => $class, "page_type" => $page_type, "appid" => $appid, "remark" => $index_values]);
					return AjaxReturnMsg(1);
				} else {
					db::name("ybmp_bus_tmpl")->insert(["name" => $name, "type" => $class, "create_time" => time(), "mch_id" => $this->bus_id, "page_type" => $page_type, "appid" => $appid, "remark" => $index_values]);
					return AjaxReturnMsg(1);
				}
			}
			return AjaxReturnMsg(0);
		}
		if ($up == 1) {
			if ($id) {
				$info = db::name("ybmp_bus_tmpl")->where("id", $id)->find();
				$this->assign("info", $info);
				$this->assign("type", $info["type"]);
				return view();
			} else {
				$this->assign("type", 2);
				return view();
			}
		} else {
			if ($id) {
				$info = db::name("ybmp_bus_tmpl")->where("id", $id)->find();
				$this->assign("info", $info);
				$this->assign("type", $info["type"]);
				return view();
			} else {
				$this->assign("type", 1);
				return view();
			}
		}
	}
	public function add_universal()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			global $_W;
			$my = input("param.my");
			if ($my) {
				$isadmin = $_SESSION["we7_w"]["isfounder"];
				$uuid = $this->uuid;
				if ($isadmin && empty($uuid)) {
					$uuid = -8384;
				}
			}
			$_W = $_SESSION["we7_w"];
			$index_list = json_decode(input("param.index_list"), true);
			$title = input("param.title");
			$img = input("param.img_src");
			$index_list["win_color"] = input("param.win_color");
			$index_list["dh_color"] = input("param.dh_color");
			$imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . ".png";
			if (strstr($img, ",")) {
				$img = explode(",", $img);
				$img = $img[1];
			}
			$path = "public/upload/user_box";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$imageSrc = $path . "/" . $imageName;
			$r = file_put_contents(ROOT_PATH . "/" . $imageSrc, base64_decode($img));
			$img_src = '';
			if ($r) {
				$img_src = $_W["siteroot"] . "addons/yb_mingpian/core/public/upload/user_box/" . $imageName;
			}
			unset($index_list["banner"]);
			unset($index_list["catenav"]);
			unset($index_list["advert"]);
			unset($index_list["add_h"]);
			unset($index_list["add_top"]);
			unset($index_list["now_index"]);
			unset($index_list["this_type"]);
			unset($index_list["columntitle"]);
			unset($index_list["imgtextlist"]);
			unset($index_list["tripartite_list"]);
			unset($index_list["quartet_list"]);
			unset($index_list["fight_group_list"]);
			unset($index_list["goodlist"]);
			unset($index_list["edit_button"]);
			unset($index_list["edit_piclist"]);
			unset($index_list["floaticon"]);
			unset($index_list["menu_list"]);
			unset($index_list["add_h_di"]);
			unset($index_list["add_top_di"]);
			unset($index_list["display"]);
			unset($index_list["nab_name"]);
			unset($index_list["nab_color"]);
			unset($index_list["font_color"]);
			unset($index_list["db_color"]);
			unset($index_list["dbj_color"]);
			unset($index_list["bag_url"]);
			$join = array();
			$m = 0;
			for ($i = 0; $i < count($index_list["all_data"]); $i++) {
				if ($index_list["all_data"][$i]["type"] == "join_us") {
					if (!empty($index_list["all_data"][$i]["list"])) {
						for ($k = 0; $k < count($index_list["all_data"][$i]["list"]); $k++) {
							if (intval($index_list["all_data"][$i]["list"][$k]["key_id"]) > 0) {
								$join["list"][$m] = $index_list["all_data"][$i]["list"][$k];
								$m++;
							}
						}
						$join["type"] = "join_us";
						$join["time_key"] = $index_list["all_data"][$i]["time_key"];
						$index_list["all_data"][$i] = $join;
					}
				}
			}
			foreach ($index_list["all_data"] as $k => $v) {
				if ($v["type"] == "comment" || $v["type"] == "edit_video" || $v["type"] == "edit_music" || $v["type"] == "search" || $v["type"] == "blank" || $v["type"] == "headline" || $v["type"] == "line" || $v["type"] == "position" || $v["type"] == "rich_text") {
					if ($v["type"] == "edit_video") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
						if ($index_list["all_data"][$k]["video_type"] == 1) {
							$rsd = $this->get_tx_video($index_list["all_data"][$k]["video_url"]);
							if ($rsd["code"] == 0) {
								$index_list["all_data"][$k]["vip_url"] = $rsd["real_url"];
							} else {
								$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
							}
						} else {
							$index_list["all_data"][$k]["vip_url"] = $index_list["all_data"][$k]["video_url"];
						}
					}
					if ($v["type"] == "edit_music") {
						$index_list["all_data"][$k]["imgurl"] = $index_list["all_data"][$k]["imgurl"];
					}
					continue;
				} else {
					if (!empty($v["list"])) {
						foreach ($v["list"] as $key => $value) {
							if (!empty($value["imgurl"])) {
								if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
								} else {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $value["imgurl"];
								}
							} else {
								if (!strstr($index_list["all_data"][$k]["list"][$key]["imgurl"], "http://" . $_SERVER["HTTP_HOST"])) {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
								} else {
									$index_list["all_data"][$k]["list"][$key]["imgurl"] = $v["topimg"];
								}
							}
						}
					}
				}
			}
			$id = request()->param("id");
			if ($id == 0) {
				$res = Db::name("ybmp_bus_tmpl")->insertGetId(["img" => $img_src, "name" => $title, "mch_id" => $this->bus_id, "index_values" => json_encode($index_list), "create_time" => time(), "uuid" => $uuid]);
			} else {
				$res = Db::name("ybmp_bus_tmpl")->where("id", $id)->update(["img" => $img_src, "name" => $title, "index_values" => json_encode($index_list), "uuid" => $uuid]);
			}
			return AjaxReturn($res, ["id" => $id]);
		}
		return view("menu/universal_add");
	}
	public function edit_universal()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$id = request()->param("id");
			$mod = Db::name("ybmp_bus_tmpl")->where("id", $id)->find();
			$json = json_decode($mod["index_values"], true);
			$json["id"] = $id;
			$json["name"] = $mod["name"];
			return $json;
		}
		$id = request()->param("id");
		$this->assign("id", $id);
		return view("menu/universal_edit");
	}
	public function del_universal()
	{
		$id = input("param.id");
		global $_W;
		$_W = $_SESSION["we7_w"];
		$info = Db::name("ybmp_bus_tmpl")->where("id", $id)->find();
		$str = str_replace(array($_W["siteroot"] . "addons/yb_mingpian/core/"), '', $info["img"]);
		@unlink($str);
		$del = Db::name("ybmp_bus_tmpl")->where("id", $id)->delete();
		return AjaxReturn($del);
	}
	public function universal_form()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_bus_form")->order("create_time desc")->where("mch_id", $this->bus_id)->paginate(20, false, ["query" => ["s" => $url, request()->param()]]);
		$this->assign("list", $list);
		return view();
	}
	public function universal_form_add()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$img = request()->param("img");
			$id = request()->param("id", "0");
			$form_type = request()->param("form_type");
			global $_W;
			$_W = $_SESSION["we7_w"];
			$imageName = "form_" . date("His", time()) . "_" . rand(1111, 9999) . ".png";
			if (strstr($img, ",")) {
				$img = explode(",", $img);
				$img = $img[1];
			}
			$path = "public/upload/user_box";
			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}
			$imageSrc = $path . "/" . $imageName;
			$r = file_put_contents(ROOT_PATH . "/" . $imageSrc, base64_decode($img));
			$img_src = '';
			if ($r) {
				$img_src = $_W["siteroot"] . "addons/yb_mingpian/core/public/upload/user_box/" . $imageName;
			}
			$data = json_decode(input("param.index_list"), true);
			$title = input("param.title");
			unset($data["checkbox_list"]);
			unset($data["radio_list"]);
			unset($data["now_index"]);
			unset($data["this_index"]);
			if (empty($id) || $id == 0) {
				$res = Db::name("ybmp_bus_form")->insert(["type" => $form_type, "img" => $img_src, "title" => $title, "value" => json_encode($data["all_data"]), "mch_id" => $this->bus_id, "create_time" => time()]);
			} else {
				$res = Db::name("ybmp_bus_form")->where("id", $id)->inc("create_time")->update(["img" => $img_src, "value" => json_encode($data["all_data"])]);
			}
			return AjaxReturn($res);
		}
		$id = input("param.id", "0");
		$this->assign("id", $id);
		return view();
	}
	public function universal_form_edit()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$id = input("param.id");
			$info = Db::name("ybmp_bus_form")->where("id", $id)->find();
			return $info;
		}
	}
	public function cratn_menu()
	{
		return view();
	}
	public function del_universal_form()
	{
		$id = input("param.id");
		$key = input("param.key");
		if ($key == "off") {
			$res = Db::name("ybmp_bus_form")->where("id", $id)->update(["is_del" => 2]);
		} else {
			$res = Db::name("ybmp_bus_form")->where("id", $id)->update(["is_del" => 1]);
		}
		return AjaxReturn($res);
	}
	public function get_form_info()
	{
		$id = input("param.id");
		$user_info = Db::name("ybmp_user_form")->where("id", $id)->find();
		$user_info["param"] = str_replace("\\n", "<br>", $user_info["param"]);
		$user_info["param"] = json_decode($user_info["param"], true);
		foreach ($user_info["param"] as $k => $v) {
			$string_arr = explode("-", $v["name"]);
			$user_info["param"][$k]["key"] = $string_arr[1];
		}
		$this->assign("user_info", $user_info);
		return view("user/get_form_info");
	}
	public function get_form_list()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$data = Request::instance()->param();
		$where = [];
		empty($data["form_id"]) || $data["form_id"] == 0 || ($where["bus_form_id"] = ["eq", $data["form_id"]]);
		$data["form_id"] = empty($data["form_id"]) ? '' : $data["form_id"];
		$list = Db::name("ybmp_user_form")->order("create_time desc")->where($where)->where("mch_id", $this->bus_id)->paginate(10, false, ["query" => ["s" => $url, "form_id" => $data["form_id"]]]);
		$this->assign("list", $list);
		$from_all = Db::name("ybmp_bus_form")->where("mch_id", $this->bus_id)->where("is_del=1")->select();
		$this->assign("from_all", $from_all);
		$this->assign("form_id", $data["form_id"]);
		return view("user/get_form_list");
	}
	public function select_form_all()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_bus_form")->where("mch_id", $this->bus_id)->order("create_time desc")->where("is_del=1")->paginate(10, false, ["query" => ["s" => $url, request()->param()]]);
		$this->assign("list", $list);
		return view();
	}
	public function universal_set_title_do()
	{
		$id = input("param.id");
		$title = input("param.title");
		$res = Db::name("ybmp_bus_form")->where("id", $id)->inc("create_time")->update(["title" => $title]);
		return AjaxReturn($res);
	}
	public function set_universal_limet()
	{
		$id = input("param.id");
		$val = input("param.val");
		$res = Db::name("ybmp_bus_form")->where("id", $id)->inc("create_time")->update(["limit_num" => $val]);
		return AjaxReturn($res);
	}
	public function set_form_title()
	{
		$id = input("param.id");
		$val = input("param.val");
		$res = Db::name("ybmp_bus_form")->where("id", $id)->inc("create_time")->update(["title" => $val]);
		return AjaxReturn($res);
	}
	public function get_tx_video($url)
	{
		$res = array("code" => 1);
		if (strpos($url, "v.qq.com") !== false) {
			preg_match("/\\/([0-9a-zA-Z]+).html/", $url, $match);
			if (empty($match)) {
				$res["msg"] = "地址格式不正确";
				return $res;
			}
			$vid = $match[1];
			try {
				set_time_limit(0);
				$getinfo = "http://vv.video.qq.com/getinfo?vids={$vid}&platform=11&charge=0&otype=xml";
				$info = $this->request2($getinfo);
				$info_arr = $this->xmlToArray($info);
				if ($info_arr["msg"] == "vid is wrong") {
					$res["msg"] = "视频出错";
					return $res;
				}
				$fi = $info_arr["fl"]["fi"];
				if (isset($fi[1])) {
					$format_id = $fi[1]["id"];
					$fmt = $fi[1]["name"];
					$format = "p" . substr($format_id, -3, 3);
					$key = $info_arr["vl"]["vi"]["fvkey"];
					$vid = $info_arr["vl"]["vi"]["vid"];
					$url = $info_arr["vl"]["vi"]["ul"]["ui"][0]["url"];
					if (strlen($format_id) >= 5) {
						$mp4 = $vid . "." . $format . ".1.mp4";
					} else {
						$mp4 = $vid . ".mp4";
					}
					$video_url = $url . $mp4 . "?vkey=" . $key . "&fmt=" . $fmt;
				} else {
					$getinfo = "http://vv.video.qq.com/getinfo?vids={$vid}&platform=101001&charge=0&otype=xml";
					$info = $this->request2($getinfo);
					$info_arr = $this->xmlToArray($info);
					if (isset($info_arr["msg"]) && $info_arr["msg"] == "vid is wrong") {
						$res["msg"] = "视频出错";
						return $res;
					}
					$filename = $info_arr["vl"]["vi"]["fn"];
					$key = $info_arr["vl"]["vi"]["fvkey"];
					$url = $info_arr["vl"]["vi"]["ul"]["ui"][0]["url"];
					$video_url = $url . $filename . "?vkey=" . $key;
				}
				$res["code"] = 0;
				$res["company"] = "腾讯";
				$res["real_url"] = $video_url;
				return $res;
			} catch (\Exception $e) {
				$res["msg"] = "视频解析失败，请重试";
				return $res;
			}
		}
	}
	public function request2($url, $param = array())
	{
		if (empty($url)) {
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if (substr($url, 0, 8) == "https://") {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		if (!empty($param)) {
			$o = '';
			foreach ($param as $k => $v) {
				$o .= "{$k}=" . urlencode($v) . "&";
			}
			$post_data = substr($o, 0, -1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	public function xmlToArray($xml)
	{
		libxml_disable_entity_loader(true);
		$values = json_decode(json_encode(simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA)), true);
		return $values;
	}
	public function center_module()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$data = input("param.data");
			$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["center_values" => $data]);
			return AjaxReturn($res);
		}
		$goods_cate = Db::name("ybmp_goods_cate")->where("is_visible=1")->where("mch_id", $this->bus_id)->select();
		$this->assign("goods_cate", $goods_cate);
		$article_class = Db::name("ybmp_article_class")->where("is_del=1")->where("is_dynamic=2")->where("mch_id", $this->bus_id)->select();
		$this->assign("article_class", $article_class);
		$this->assign("yuming", " http://" . $_SERVER["HTTP_HOST"] . "/addons/yb_mingpian/core/index.php?s=/admin");
		$this->assign("wn", request()->param("wn", -1));
		$this->assign("wn_id", request()->param("wn_id", 0));
		$this->assign("my_mod", request()->param("my_mod", -1));
		return view("menu/center_module");
	}
	public function find_mch_center_mod()
	{
		$mod = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
		return json_decode($mod["center_values"], true);
	}
	public function user_center()
	{
		$wq_uid = $_SESSION["we7_w"]["user"]["uid"];
		$info = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->value("center_values");
		if (empty($info)) {
			$info = "{\"all_data\":[{\"img\":\"/addons/yb_mingpian/core/public/images/member/cart.png\",\"type\":\"order\",\"status\":1,\"title\":\"我的订单\",\"time_key\":\"153474118797080\"},{\"img\":\"/addons/yb_mingpian/core/public/images/member/like.png\",\"type\":\"follow\",\"status\":1,\"title\":\"我的关注\",\"time_key\":\"153474118882593\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/coupon.png\",\"type\":\"coupon\",\"status\":1,\"title\":\"我的优惠券\",\"time_key\":\"153474118930570\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/appointment.png\",\"type\":\"book\",\"status\":1,\"title\":\"我的预约\",\"time_key\":\"153474118919817\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/kj_icon.png\",\"type\":\"kjm\",\"status\":1,\"title\":\"我的砍价\",\"time_key\":\"153474119087708\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/kj_order_icon.png\",\"type\":\"kjo\",\"status\":1,\"title\":\"砍价订单\",\"time_key\":\"153474119043189\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/group_icon.png\",\"type\":\"ptm\",\"status\":1,\"title\":\"我的拼团\",\"time_key\":\"153474119153315\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/groupj_order_icon.png\",\"type\":\"pto\",\"status\":1,\"title\":\"拼团订单\",\"time_key\":\"153474119179761\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/miaosha.png\",\"type\":\"miaosha\",\"status\":1,\"title\":\"秒杀订单\",\"time_key\":\"153474119422866\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/service.png\",\"type\":\"kefu\",\"status\":1,\"title\":\"在线客服\",\"time_key\":\"15347411926469\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/location.png\",\"type\":\"address\",\"status\":1,\"title\":\"收货地址\",\"time_key\":\"153474119365949\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/about.png\",\"type\":\"about\",\"status\":1,\"title\":\"关于我们\",\"time_key\":\"153474119422866\"}],\"win_color\":\"#ffffff\",\"win_img\":\"\"}";
		}
		$role_id = Db::name("ybmp_user_permission")->alias("p")->join("ybmp_user_role r", "p.role_id = r.role_id")->field("r.module_id_array")->where(["p.user_id" => $wq_uid])->find();
		$data = json_decode($info, true);
		if (!strpos($info, "miaosha")) {
			$m = array(0 => array("img" => "http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/miaosha.png", "type" => "miaosha", "title" => "秒杀订单", "status" => 1, "time_key" => "153474119422866"));
			array_splice($data["all_data"], -3, 0, $m);
		}
		if (!strpos($info, "dingyue")) {
			$m = array(0 => array("img" => "http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/dingyue.png", "type" => "dingyue", "title" => "我的订阅", "status" => 1, "time_key" => "153474119422866"));
			array_splice($data["all_data"], 4, 0, $m);
		}
		if (!empty($role_id)) {
			$role_ids = explode(",", $role_id["module_id_array"]);
			foreach ($data["all_data"] as &$item) {
				$item["hidden"] = false;
				if ($item["type"] == "kjm" || $item["type"] == "kjo") {
					if (!in_array(223, $role_ids)) {
						$item["status"] = 2;
						$item["hidden"] = true;
					}
				}
				if ($item["type"] == "ptm" || $item["type"] == "pto") {
					if (!in_array(243, $role_ids)) {
						$item["status"] = 2;
						$item["hidden"] = true;
					}
				}
				if ($item["type"] == "miaosha") {
					if (!in_array(284, $role_ids)) {
						$item["status"] = 2;
						$item["hidden"] = true;
					}
				}
			}
		}
		$new_data = json_encode($data);
		$id = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->value("id");
		if ($id > 0) {
			Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["center_values" => $new_data]);
		} else {
			Db::name("ybmp_user_tmpl")->insert(["center_values" => $new_data, "mch_id" => $this->bus_id]);
		}
		$this->assign("list", $data["all_data"]);
		return view();
	}
	public function del_user_center()
	{
		$type = input("param.type");
		$key = input("param.key");
		$info = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->value("center_values");
		if (empty($info)) {
			$info = "{\"all_data\":[{\"img\":\"/addons/yb_mingpian/core/public/images/member/cart.png\",\"type\":\"order\",\"status\":1,\"title\":\"我的订单\",\"time_key\":\"153474118797080\"},{\"img\":\"/addons/yb_mingpian/core/public/images/member/like.png\",\"type\":\"follow\",\"status\":1,\"title\":\"我的关注\",\"time_key\":\"153474118882593\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/coupon.png\",\"type\":\"coupon\",\"status\":1,\"title\":\"我的优惠券\",\"time_key\":\"153474118930570\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/appointment.png\",\"type\":\"book\",\"status\":1,\"title\":\"我的预约\",\"time_key\":\"153474118919817\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/kj_icon.png\",\"type\":\"kjm\",\"status\":1,\"title\":\"我的砍价\",\"time_key\":\"153474119087708\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/kj_order_icon.png\",\"type\":\"kjo\",\"status\":1,\"title\":\"砍价订单\",\"time_key\":\"153474119043189\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/group_icon.png\",\"type\":\"ptm\",\"status\":1,\"title\":\"我的拼团\",\"time_key\":\"153474119153315\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/groupj_order_icon.png\",\"type\":\"pto\",\"status\":1,\"title\":\"拼团订单\",\"time_key\":\"153474119179761\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/service.png\",\"type\":\"kefu\",\"status\":1,\"title\":\"在线客服\",\"time_key\":\"15347411926469\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/location.png\",\"type\":\"address\",\"status\":1,\"title\":\"收货地址\",\"time_key\":\"153474119365949\"},{\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/about.png\",\"type\":\"about\",\"status\":1,\"title\":\"关于我们\",\"time_key\":\"153474119422866\"}],\"win_color\":\"#ffffff\",\"win_img\":\"\"}";
		}
		$data = json_decode($info, true);
		foreach ($data["all_data"] as &$item) {
			if ($item["type"] == $type) {
				if ($key == "off") {
					$item["status"] = 2;
				} else {
					$item["status"] = 1;
				}
			}
		}
		$new_data = json_encode($data);
		$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["center_values" => $new_data]);
		return AjaxReturn($res);
	}
	public function set_center_title()
	{
		$type = input("param.type");
		$val = input("param.val");
		$info = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->value("center_values");
		if (empty($info)) {
			$info = "{\"all_data\":[{\"img\":\"/public/images/member/cart.png\",\"type\":\"order\",\"status\":1,\"title\":\"我的订单\",\"time_key\":\"153474118797080\"},{\"img\":\"/public/images/member/like.png\",\"type\":\"follow\",\"status\":1,\"title\":\"我的关注\",\"time_key\":\"153474118882593\"},{\"img\":\"/public/images/member/coupon.png\",\"type\":\"coupon\",\"status\":1,\"title\":\"我的优惠券\",\"time_key\":\"153474118930570\"},{\"img\":\"/public/images/member/appointment.png\",\"type\":\"book\",\"status\":1,\"title\":\"我的预约\",\"time_key\":\"153474118919817\"},{\"img\":\"/public/images/member/kj_icon.png\",\"type\":\"kjm\",\"status\":1,\"title\":\"我的砍价\",\"time_key\":\"153474119087708\"},{\"img\":\"/public/images/member/kj_order_icon.png\",\"type\":\"kjo\",\"status\":1,\"title\":\"砍价订单\",\"time_key\":\"153474119043189\"},{\"img\":\"/public/images/member/group_icon.png\",\"type\":\"ptm\",\"status\":1,\"title\":\"我的拼团\",\"time_key\":\"153474119153315\"},{\"img\":\"/public/images/member/groupj_order_icon.png\",\"type\":\"pto\",\"status\":1,\"title\":\"拼团订单\",\"time_key\":\"153474119179761\"},{\"img\":\"/public/images/member/service.png\",\"type\":\"kefu\",\"status\":1,\"title\":\"在线客服\",\"time_key\":\"15347411926469\"},{\"img\":\"/public/images/member/location.png\",\"type\":\"address\",\"status\":1,\"title\":\"收货地址\",\"time_key\":\"153474119365949\"},{\"img\":\"/public/images/member/about.png\",\"type\":\"about\",\"status\":1,\"title\":\"关于我们\",\"time_key\":\"153474119422866\"}],\"win_color\":\"#ffffff\",\"win_img\":\"\"}";
		}
		$data = json_decode($info, true);
		foreach ($data["all_data"] as &$item) {
			if ($item["type"] == $type) {
				$item["title"] = $val;
			}
		}
		$new_data = json_encode($data);
		$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["center_values" => $new_data]);
		return AjaxReturn($res);
	}
	public function menu_select()
	{
		$type = input("param.type");
		$index = input("param.index");
		$data = array(["text" => "首页", "type" => "index", "param" => 2, "path" => "/yb_mingpian/pages/index/index"], ["text" => "商品列表", "role_id" => "12", "type" => "product", "param" => 3], ["text" => "商品分类", "role_id" => "12", "type" => "caregory", "param" => 1, "path" => "/yb_mingpian/pages/product/index2"], ["text" => "商品详情", "role_id" => "12", "type" => "goods", "param" => 1, "path" => "/yb_mingpian/pages/goods/detail/index"], ["text" => "购物车", "role_id" => "12", "type" => "cart", "param" => 2, "path" => "/yb_mingpian/pages/member/cart/index"], ["text" => "会员中心", "type" => "cenmember", "param" => 2, "path" => "/yb_mingpian/pages/member/index/index"], ["text" => "DIY页面", "type" => "power", "param" => 1, "path" => "/yb_mingpian/pages/power/index"], ["text" => "文章分类", "type" => "class_article", "param" => 2, "path" => "/yb_mingpian/pages/find/index"], ["text" => "文章列表", "type" => "article", "param" => 1, "path" => "/yb_mingpian/pages/find/index"], ["text" => "文章详情", "type" => "article_info", "param" => 1, "path" => "/yb_mingpian/pages/find_info/index"], ["text" => "相册分类", "type" => "class_image", "param" => 2, "path" => "/yb_mingpian/pages/class_image/index"], ["text" => "相册列表", "type" => "images", "param" => 1, "path" => "/yb_mingpian/pages/image/index"], ["text" => "优惠券", "role_id" => "169", "type" => "coupon", "param" => 2, "path" => "/yb_mingpian/pages/shop_coupon/index"], ["text" => "产品列表", "type" => "product_list", "param" => 2, "path" => "/yb_mingpian/pages/product/list/index"], ["text" => "产品详情", "type" => "product_info", "param" => 1, "path" => "/yb_mingpian/pages/product/info/index"], ["text" => "砍价", "role_id" => "223", "type" => "bargain", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/index/index"], ["text" => "砍价列表", "role_id" => "223", "type" => "bargain", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/good_list/index"], ["text" => "砍价详情", "role_id" => "223", "type" => "bargain_info", "param" => 1, "path" => "/yb_mingpian/pages/kanjia/goods_info/index"], ["text" => "拼团", "role_id" => "243", "type" => "pintuan", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/index/index"], ["text" => "拼团列表", "role_id" => "243", "type" => "pintuan_list", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/index/list"], ["text" => "拼团详情", "role_id" => "243", "type" => "pintuan_info", "param" => 1, "path" => "/yb_mingpian/pages/pintuan/pages/goods/detail"], ["text" => "小程序", "type" => "applets", "param" => 1], ["text" => "网页", "type" => "web_page", "param" => 1, "path" => "/yb_mingpian/pages/web/index"], ["text" => "万能表单", "role_id" => "273", "type" => "edit_form", "param" => 1, "path" => "/yb_mingpian/pages/form/index"], ["text" => "联系我们", "type" => "contact", "param" => 2, "path" => "/yb_mingpian/pages/contact/index"], ["text" => "预约列表", "role_id" => "289", "type" => "book_list", "param" => 2, "path" => "/yb_mingpian/pages/book_list/index"], ["text" => "我的订单", "role_id" => "12", "type" => "my_order", "param" => 1, "path" => "/yb_mingpian/pages/order/index"], ["text" => "分销中心", "role_id" => "270", "type" => "my_fenxiao", "param" => 2, "path" => "/yb_mingpian/pages/fenxiao/pages/index/index"], ["text" => "我的关注", "type" => "my_follow", "param" => 2, "path" => "/yb_mingpian/pages/member/favorite/index"], ["text" => "我的优惠券", "role_id" => "169", "type" => "my_coupon", "param" => 2, "path" => "/yb_mingpian/pages/member/coupon/index"], ["text" => "我的预约", "role_id" => "289", "type" => "my_book", "param" => 2, "path" => "/yb_mingpian/pages/appointment/index"], ["text" => "秒杀列表", "role_id" => "284", "type" => "miaosha", "param" => 2, "path" => "/yb_mingpian/pages/miaosha/seckillList/index"], ["text" => "秒杀详情", "role_id" => "284", "type" => "miaosha", "param" => 1, "path" => "/yb_mingpian/pages/miaosha/seckillGoods/index"], ["text" => "秒杀订单", "role_id" => "284", "type" => "miaosha", "param" => 2, "path" => "yb_mingpian/pages/miaosha/order/index"], ["text" => "我的砍价", "role_id" => "223", "type" => "my_kjm", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/my_record/index"], ["text" => "砍价订单", "role_id" => "223", "type" => "my_kjo", "param" => 2, "path" => "/yb_mingpian/pages/kanjia/order/index"], ["text" => "我的拼团", "role_id" => "243", "type" => "my_ptm", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/group/index"], ["text" => "拼团订单", "role_id" => "243", "type" => "my_pto", "param" => 2, "path" => "/yb_mingpian/pages/pintuan/pages/orders/index"], ["text" => "收货地址", "type" => "member_address", "param" => 2, "path" => "/yb_mingpian/pages/member/address/index"], ["text" => "关于我们", "type" => "about", "param" => 2, "path" => "/yb_mingpian/pages/member/about/index"], ["text" => "打电话", "type" => "phone", "param" => 2], ["text" => "地图", "type" => "map", "param" => 2], ["text" => "付费内容首页", "role_id" => "282", "type" => "paycontent", "param" => 2, "path" => "/yb_mingpian/pages/paycontent/index"], ["text" => "付费内容详情", "role_id" => "282", "type" => "paycontent_info", "param" => 1, "path" => "/yb_mingpian/pages/paycontent/info/index"], ["text" => "我的订阅", "role_id" => "282", "type" => "paycontent_my", "param" => 2, "path" => "/yb_mingpian/pages/paycontent/my/index"]);
		if ($type == "product_list") {
			$dat = array($data[13]);
			array_push($dat, $data[14]);
			$data = $dat;
		}
		if ($type == "miaosha") {
			$dat = array($data[32]);
			$data = $dat;
		}
		if ($type == "tabbar" && $index == 0) {
			$data = array($data[0]);
		} else {
			$isadmin = $_SESSION["we7_w"]["isfounder"];
			$founder_groupid = $_SESSION["we7_w"]["user"]["founder_groupid"];
			if (!$isadmin || $founder_groupid == 0) {
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
		}
		$this->assign("menu", $data);
		$this->assign("type", $type);
		$this->assign("index", $index);
		return view("menu/menu_select");
	}
	public function menu_select_2()
	{
		Cache::set("is_load", 6, 20);
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$type = input("param.type");
		$this_id = input("param.this_id");
		$path = input("param.path");
		$new = input("param.new", "0");
		$url = Db::name("ybmp_tmpl_pages")->where("mod_id", 1)->where("menu", $type)->find();
		$this->assign("new", $new);
		$this->assign("url", $url);
		$this->assign("this_id", $this_id);
		$this->assign("type", $type);
		$this->assign("path", $path);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$type_key = "1";
		switch ($type) {
			case "goods":
				$art = Db::name("ybmp_goods")->alias("g")->join("ybmp_images m", "m.img_id=g.images")->where("g.mch_id", $mch_id)->where("g.is_del", 0)->field("g.goods_id,g.create_time,g.goods_name,g.price,g.introduction,m.img_cover")->order("g.create_time desc")->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["id"] = $v["goods_id"];
					$v["name"] = $v["goods_name"];
					$v["img"] = $v["img_cover"];
				}
				$this->assign("list", $art);
				break;
			case "article_info":
				$art = Db::name("ybmp_article")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["id"] = $v["article_id"];
					$v["name"] = $v["title"];
					$v["img"] = $v["image"];
				}
				$this->assign("list", $art);
				break;
			case "product_info":
				$art = Db::name("ybmp_product")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["name"] = $v["title"];
					$v["img"] = $v["image"];
				}
				$this->assign("list", $art);
				break;
			case "article":
				$art = Db::name("ybmp_article_class")->where("mch_id", $mch_id)->where("is_del", 1)->where("is_dynamic", 2)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["id"] = $v["class_id"];
					$v["img"] = $v["class_img"];
				}
				$this->assign("list", $art);
				break;
			case "images":
				$art = Db::name("ybmp_images_group")->where("mch_id", $mch_id)->select();
				foreach ($art as $k => &$v) {
					$img = Db::name("ybmp_images")->where("img_id", $v["group_cover"])->find();
					if ($img == '') {
						$v["img"] = "none";
					} else {
						$v["img"] = $img["img_cover"];
					}
					$v["id"] = $v["group_id"];
					$v["name"] = $v["group_name"];
				}
				$this->assign("list", $art);
				break;
			case "class_article":
				$art = Db::name("ybmp_article_class")->where("mch_id", $mch_id)->where("is_dynamic", 2)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["id"] = $v["class_id"];
					$v["img"] = $v["class_img"];
				}
				$this->assign("list", $art);
				break;
			case "caregory":
				$art = Db::name("ybmp_goods_cate")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["id"] = $v["cate_id"];
					$v["name"] = $v["cate_name"];
					$v["img"] = $v["cate_pic"];
				}
				$this->assign("list", $art);
				break;
			case "power":
				$art = Db::name("ybmp_bus_tmpl")->where("is_del", 1)->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				$this->assign("list", $art);
				break;
			case "edit_form":
				$art = Db::name("ybmp_bus_form")->where("is_del=1")->order("create_time desc")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["name"] = $v["title"];
				}
				$this->assign("list", $art);
				break;
			case "applets":
				$art = Db::name("ybmp_sapp")->where("mch_id", $mch_id)->field("id,sapp_name as name,appid,url as path")->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				$this->assign("list", $art);
				$type_key = "2";
				break;
			case "web_page":
				$art = Db::name("ybmp_applink")->where("mch_id", $mch_id)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				$this->assign("list", $art);
				$type_key = "3";
				break;
			case "bargain_info":
				$time = time();
				$art = Db::name("ybmp_bargain")->alias("b")->join("ybmp_images i", "i.img_id=b.bargain_picture")->where("b.mch_id", $mch_id)->where("b.bargain_type=1")->where("b.del=0")->whereTime("b.star_time", "<", $time)->whereTime("b.end_time", ">", $time)->field("b.*,i.img_cover")->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["name"] = $v["bargain_name"];
					$v["img"] = $v["img_cover"];
					$v["price"] = $v["original_price"];
				}
				$this->assign("list", $art);
				break;
			case "pintuan_info":
				$art = Db::name("ybmp_pt_goods")->alias("b")->join("ybmp_images i", "i.img_id=b.img")->where("b.mch_id", $mch_id)->where("b.isShow=1")->field("b.*,i.img_cover")->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["img"] = $v["img_cover"];
				}
				$this->assign("list", $art);
				break;
			case "paycontent_info":
				$art = Db::name("ybmp_paycontent")->where("mch_id", $mch_id)->where("status", 1)->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$this->assign("page", $art->render());
				$art = json_decode(json_encode($art, true), true);
				$art = $art["data"];
				foreach ($art as &$v) {
					$v["name"] = $v["title"];
					$v["img"] = $v["image"];
				}
				$this->assign("list", $art);
				break;
			case "my_order":
				$art = array(["name" => "全部", "id" => ''], ["name" => "待付款", "id" => "0"], ["name" => "待发货", "id" => "1"], ["name" => "待收货", "id" => "2"], ["name" => "已完成", "id" => "3"], ["name" => "退换货", "id" => "4"]);
				$this->assign("list", $art);
				break;
			case "miaosha":
				$art = Db::name("ybmp_activity")->field("id,name,nprice,pic,stime,etime,all_sell")->where(["mch_id" => $this->bus_id, "type" => 1, "is_del" => 2, "status" => 1])->paginate(15, false, ["query" => ["s" => $url, "path" => $path, "type" => $type, "mod_id" => 1, "this_id" => $this_id]]);
				$re = array();
				for ($i = 0; $i < count($art); $i++) {
					$re[$i] = $art[$i];
					$re[$i]["img"] = $art[$i]["pic"];
					$re[$i]["price"] = $art[$i]["nprice"];
				}
				$this->assign("list", $re);
				break;
		}
		Cache::set("is_load", null);
		$this->assign("type_key", $type_key);
		return view("menu/menu_select_2");
	}
}
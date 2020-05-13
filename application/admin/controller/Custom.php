<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
class Custom extends Base
{
	public function __construct()
	{
		parent::__construct();
		$goods_cate = Db::name("ybmp_goods_cate")->where("is_visible=1")->where("mch_id", $this->bus_id)->select();
		$this->assign("goods_cate", $goods_cate);
		$article_class = Db::name("ybmp_article_class")->where("is_del=1")->where("mch_id", $this->bus_id)->where("is_dynamic=2")->select();
		$this->assign("article_class", $article_class);
		$this->assign("yuming", get_child_url(false) . "addons/yb_mingpian/core/index.php?s=/admin");
		$product_cate = Db::name("ybmp_product_class")->where("mch_id", $this->bus_id)->order("sort asc")->select();
		$this->assign("product_class", $product_cate);
	}
	public function index()
	{
		$this->assign("page_flag", 1);
		$this->assign("id", 0);
		return view("custom/index");
	}
	public function user_center()
	{
		$this->assign("page_flag", 0);
		$this->assign("id", 0);
		$my_cell = array(["text" => "会员信息", "type" => "member", "icon" => 38], ["text" => "我的订单", "role_id" => "12", "type" => "order", "icon" => 28], ["text" => "状态订单", "role_id" => "12", "type" => "order_status", "icon" => 28], ["text" => "分销中心", "role_id" => "253", "type" => "fenxiao", "icon" => 29], ["text" => "我的关注", "type" => "follow", "icon" => 30], ["text" => "我的优惠券", "role_id" => "169", "type" => "coupon", "icon" => 31], ["text" => "我的预约", "role_id" => "210", "type" => "book", "icon" => 32], ["text" => "我的砍价", "role_id" => "223", "type" => "kjm", "icon" => 33], ["text" => "砍价订单", "role_id" => "223", "type" => "kjo", "icon" => 34], ["text" => "我的拼团", "role_id" => "243", "type" => "ptm", "icon" => 38], ["text" => "拼团订单", "role_id" => "243", "type" => "pto", "icon" => 34], ["text" => "在线客服", "type" => "kefu", "icon" => 35], ["text" => "收货地址", "type" => "address", "icon" => 36], ["text" => "关于我们", "type" => "about", "icon" => 37], ["text" => "我的订阅", "role_id" => "264", "type" => "paycontent", "icon" => "dingyue"]);
		$isadmin = $_SESSION["we7_w"]["isfounder"];
		$founder_groupid = $_SESSION["we7_w"]["user"]["founder_groupid"];
		if (!$isadmin || $founder_groupid == 2) {
			$wq_uid = $_SESSION["we7_w"]["user"]["uid"];
			$role_id = Db::name("ybmp_user_permission")->alias("p")->join("ybmp_user_role r", "p.role_id = r.role_id")->field("r.module_id_array")->where(["p.user_id" => $wq_uid])->find();
			if (!empty($role_id)) {
				$role_ids = explode(",", $role_id["module_id_array"]);
				$arr = array();
				foreach ($my_cell as $item) {
					if (isset($item["role_id"]) && !in_array($item["role_id"], $role_ids)) {
						continue;
					}
					$arr[] = $item;
				}
				$my_cell = $arr;
			}
		}
		$this->assign("my_cell", $my_cell);
		return view("custom/index");
	}
	public function power()
	{
		$id = request()->param("id", 0);
		$type = request()->param("type", 0);
		$this->assign("page_flag", 2);
		$this->assign("id", $id);
		$this->assign("wn_id", $id);
		$this->assign("type", $type);
		return view("custom/index");
	}
	public function mytmpl()
	{
		$id = request()->param("id", 0);
		$this->assign("page_flag", 3);
		$this->assign("id", $id);
		return view("custom/index");
	}
	public function use_mytmpl()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$id = input("param.id");
			$info = Db::name("ybmp_user_tmpl_box")->where("id", $id)->find();
			$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["values" => $info["style_value"], "index_values" => $info["index_values"]]);
			return AjaxReturn($res);
		}
	}
	public function gettmpl()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$page_flag = request()->param("page_flag", 1);
			$id = request()->param("id", 0);
			$value = '';
			if ($page_flag == 0) {
				$mod = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
				$value = $mod["center_values"];
			} elseif ($page_flag == 1) {
				$mod = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->find();
				$value = $mod["index_values"];
			} elseif ($page_flag == 2) {
				if (!empty($id)) {
					$mod = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $this->bus_id, "id" => $id])->find();
					$value = $mod["index_values"];
				}
			} elseif ($page_flag == 3) {
				if (!empty($id)) {
					$mod = Db::name("ybmp_user_tmpl_box")->where(["mch_id" => $this->bus_id, "id" => $id])->find();
					$value = $mod["index_values"];
				}
			}
			$value = str_replace("pagesurl", "url", $value);
			$value = json_decode($value, true);
			if (empty($value["page"])) {
				if ($page_flag == 0) {
					global $_W;
					$url = $_W["siteroot"];
					$str = "{\"all_data\":[{\"type\":\"member\",\"time_key\":\"153814260469010\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/about.png\",\"name\":\"会员信息\",\"bg_color\":\"#f2f2f2\",\"text_color\":\"#333333\"},{\"type\":\"order\",\"time_key\":\"153814233179128\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/about.png\",\"name\":\"我的订单\"},{\"type\":\"order_status\",\"time_key\":\"153822855089529\"},{\"type\":\"follow\",\"time_key\":\"153814233325731\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/like.png\",\"name\":\"我的关注\"},{\"type\":\"coupon\",\"time_key\":\"153814233879156\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/coupon.png\",\"name\":\"我的优惠券\"},{\"type\":\"kefu\",\"time_key\":\"153814234039795\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/service.png\",\"name\":\"在线客服\"},{\"type\":\"address\",\"time_key\":\"153814234041348\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/location.png\",\"name\":\"收货地址\"},{\"type\":\"about\",\"time_key\":\"153814234185092\",\"img\":\"http://mp.sssvip.net/addons/yb_mingpian/core/public/images/member/about.png\",\"name\":\"关于我们\"}],\"page\":{\"name\":\"页面标题\",\"nv_color\":\"#ffffff\",\"bg_color\":\"#f2f2f2\",\"text_color\":\"#000000\",\"bg_img\":\"\",\"open_img\":\"\",\"open_img_url\":\"\",\"show_tabbar\":\"false\"},\"tabbar\":{\"bg_color\":\"#ffffff\",\"text_color\":\"#333333\",\"select_color\":\"#fe4b71\",\"list\":[{\"name\":\"首页\",\"alias\":\"首页\",\"url\":\"/yb_mingpian/pages/index/index\",\"tabbar_icon\":\"/addons/yb_mingpian/core/public/icon/gray_home.png\",\"tabbar_select_icon\":\"/addons/yb_mingpian/core/public/icon/red_home.png\",\"key\":\"1\"},{\"name\":\"发现\",\"alias\":\"发现\",\"url\":\"/yb_mingpian/pages/find/index\",\"tabbar_icon\":\"/addons/yb_mingpian/core/public/icon/gray_find.png\",\"tabbar_select_icon\":\"/addons/yb_mingpian/core/public/icon/red_find.png\",\"key\":\"1\"},{\"name\":\"商品\",\"alias\":\"商品\",\"url\":\"/yb_mingpian/pages/product/index\",\"tabbar_icon\":\"/addons/yb_mingpian/core/public/icon/gray_cate.png\",\"tabbar_select_icon\":\"/addons/yb_mingpian/core/public/icon/red_cate.png\",\"key\":\"1\"},{\"name\":\"购物车\",\"alias\":\"购物车\",\"url\":\"/yb_mingpian/pages/member/cart/index\",\"tabbar_icon\":\"/addons/yb_mingpian/core/public/icon/gray_cart.png\",\"tabbar_select_icon\":\"/addons/yb_mingpian/core/public/icon/red_cart.png\",\"key\":\"1\"},{\"name\":\"会员中心\",\"alias\":\"会员中心\",\"url\":\"/yb_mingpian/pages/member/index/index\",\"tabbar_icon\":\"/addons/yb_mingpian/core/public/icon/gray_people.png\",\"tabbar_select_icon\":\"/addons/yb_mingpian/core/public/icon/red_people.png\",\"key\":\"1\"}]}}";
					$str = json_decode($str, true);
					foreach ($str["all_data"] as &$ccitem) {
						$ccitem["img"] = str_replace("http://mp.sssvip.net/", $url, $ccitem["img"]);
					}
					return $str;
				}
				$old_tabbar = array();
				if ($page_flag == 1) {
					$old_tabbar = json_decode($mod["values"], true);
				} elseif ($page_flag == 3) {
					$old_tabbar = json_decode($mod["style_value"], true);
					$old_tabbar["tabBar"]["name"] = $mod["title"];
				} elseif ($page_flag == 2) {
					$tabBar = $value;
					unset($tabBar["all_data"]);
					$tabBar["name"] = $mod["name"];
					$old_tabbar["tabBar"] = $tabBar;
				}
				if (!empty($old_tabbar)) {
					$old_tabbar = $old_tabbar["tabBar"];
					$page["name"] = isset($old_tabbar["name"]) ? $old_tabbar["name"] : "页面标题";
					$page["nv_color"] = isset($old_tabbar["background"]) ? $old_tabbar["background"] : "#ffffff";
					$page["bg_color"] = isset($old_tabbar["winColor"]) ? $old_tabbar["winColor"] : "#f2f2f2";
					$page["text_color"] = isset($old_tabbar["backgroundTextStyle"]) ? $old_tabbar["backgroundTextStyle"] : "#000000";
					$page["bg_img"] = isset($old_tabbar["win_img"]) ? $old_tabbar["win_img"] : '';
					$page["open_img"]["imgurl"] = isset($old_tabbar["openImg"]) ? $old_tabbar["openImg"] : '';
					$page["open_img"]["url"] = isset($old_tabbar["openImgUrl"]) ? $old_tabbar["openImgUrl"] : '';
					$page["open_img"]["name"] = isset($old_tabbar["openImgUrlName"]) ? $old_tabbar["openImgUrlName"] : '';
					$page["show_tabbar"] = $old_tabbar["IsDiDis"] ? "true" : "false";
					$value["page"] = $page;
					$tabbar["bg_color"] = $old_tabbar["backgroundColor"];
					$tabbar["text_color"] = $old_tabbar["color"];
					$tabbar["select_color"] = $old_tabbar["selectedColor"];
					$list = array();
					foreach ($old_tabbar["list"] as $item) {
						$tb = array();
						$tb["name"] = $item["name"];
						$tb["alias"] = $item["name_this"];
						$tb["url"] = $item["imgurl"];
						$tb["tabbar_icon"] = $item["page_icon"];
						$tb["tabbar_select_icon"] = $item["page_select_icon"];
						$tb["key"] = isset($item["ident"]) ? $item["ident"] : "1";
						$list[] = $tb;
					}
					$tabbar["list"] = $list;
					$value["tabbar"] = $tabbar;
				}
				foreach ($value["all_data"] as $dk => &$ditem) {
					$ditem["key"] = isset($ditem["ident"]) ? $ditem["ident"] : "1";
					if ($ditem["type"] == "navigation" || $ditem["type"] == "headline") {
						$ditem["style_type"] = $ditem["style"];
						$ditem["text_color"] = $ditem["color"];
					}
					if ($ditem["type"] == "article") {
						$ditem["type"] = "article_list";
						$ditem["style_type"] = $ditem["layout"];
						$ditem["text_color"] = $ditem["title_color"];
						$ditem["font_size"] = $ditem["title_size"];
					}
					if ($ditem["type"] == "edit_piclist") {
						$ditem["style_type"] = $ditem["pic_style"];
						$ditem["column_num"] = $ditem["layout"];
						$ditem["radian"] = $ditem["pic_radius"];
						$ditem["text_color"] = "#333333";
					}
					if ($ditem["type"] == "edit_video") {
						$ditem["url"] = $ditem["video_url"];
						$ditem["ly_height"] = $ditem["video_height"];
					}
					if ($ditem["type"] == "edit_music") {
						$ditem["url"] = $ditem["music_url"];
						$ditem["name"] = $ditem["title"];
					}
					if ($ditem["type"] == "line") {
						$ditem["type"] = "blankline";
						$ditem["style_type"] = $ditem["style"];
						$ditem["line_color"] = $ditem["color"];
					}
					if ($ditem["type"] == "floaticon" || $ditem["type"] == "customer") {
						$ditem["form_bottom"] = $ditem["b_form_bottom"];
						$ditem["form_margin"] = 100 - $ditem["b_form_margin"];
						$ditem["form_transparent"] = $ditem["b_form_transparent"];
					}
					if ($ditem["type"] == "edit_button") {
						$ditem["name"] = $ditem["button_name"];
						$ditem["radius"] = $ditem["button_radius"];
						$ditem["show_border"] = $ditem["button_border"];
						$ditem["bg_color"] = $ditem["button_bg_color"];
						$ditem["text_color"] = $ditem["button_title_color"];
						$ditem["border_color"] = $ditem["button_border_color"];
						$ditem["show_icon"] = $ditem["img_style"];
					}
					if ($ditem["type"] == "edit_form") {
						$ditem["name"] = "选择表单";
					}
					if ($ditem["type"] == "announcement") {
						$ditem["text_color"] = $ditem["color"];
						$ditem["content"] = $ditem["title"];
					}
					if ($ditem["type"] == "ad") {
						$ditem["ly_height"] = $ditem["height"];
						$ditem["imgurl"] = $ditem["img"];
					}
					if ($ditem["type"] == "goods") {
						$ditem["bg_color"] = "#ffffff";
						$ditem["text_color"] = $ditem["title_color"];
						$ditem["font_size"] = $ditem["title_size"];
						$ditem["style_type"] = $ditem["layout"];
						$ditem["style_type"] = $ditem["layout"];
					}
					if ($ditem["type"] == "search") {
						$ditem["hot_word"] = $ditem["default"];
						$ditem["style_type"] = $ditem["search_style"];
						$bg_color = $ditem["bg_color"];
						$ditem["bg_color"] = $ditem["li_bg_color"];
						$ditem["bd_color"] = $bg_color;
					}
					if ($ditem["type"] == "store_door") {
						$ditem["text_color"] = "#333333";
						$ditem["name"] = $ditem["door_name"];
						$ditem["time"] = $ditem["door_job"];
						$ditem["phone"] = $ditem["door_phone"];
					}
					if ($ditem["type"] == "position") {
						$ditem["name"] = $ditem["position_name"];
						$ditem["lon"] = $ditem["longitude"];
						$ditem["lat"] = $ditem["latitude"];
						$ditem["show_type"] = $ditem["is_display"];
						$ditem["text_color"] = "#333333";
					}
					if (!isset($ditem["bg_color"])) {
						$ditem["bg_color"] = "#ffffff";
					}
					foreach ($ditem["list"] as &$subitem) {
						if (!isset($subitem["alias"])) {
							$subitem["alias"] = isset($subitem["name"]) ? $subitem["name"] : "链接";
						}
						$subitem["key"] = isset($subitem["ident"]) ? $subitem["ident"] : "1";
						if ($ditem["type"] == "edit_piclist") {
							$subitem["name"] = $subitem["title"];
						}
						if ($ditem["type"] == "tripartite" || $ditem["type"] == "quartet") {
							$subitem["imgurl"] = $subitem["img"];
						}
					}
				}
			} else {
				if ($page_flag == 2) {
					$value["page"]["name"] = $mod["name"];
				}
			}
			return $value;
		}
	}
	public function save_tmpl()
	{
		if (request()->isAjax() && request()->method() == "POST") {
			$page_flag = request()->param("page_flag", 1);
			$id = request()->param("id", 0);
			$data = input("param.data");
			$data = str_replace("pagesurl", "url", $data);
			$title = request()->param("title", '');
			$flag = request()->param("flag", 0);
			if ($page_flag == 0) {
				$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["center_values" => $data]);
			} elseif ($page_flag == 1) {
				if ($flag == 1) {
					$dd["title"] = $title;
					$dd["index_values"] = $data;
					$dd["img"] = $this->saveimg();
					$dd["create_time"] = time();
					$dd["mch_id"] = $this->bus_id;
					$dd["style_value"] = '';
					$dd["uuid"] = $this->uuid;
					$res = Db::name("ybmp_user_tmpl_box")->insert($dd);
				} else {
					$res = Db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->update(["index_values" => $data]);
				}
			} elseif ($page_flag == 2) {
				$dd["name"] = $title;
				$index_list = json_decode($data, true);
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
				$index_list["is_new"] = 1;
				$dd["index_values"] = json_encode($index_list);
				$dd["img"] = $this->saveimg();
				if ($flag == 1) {
					$dd["uuid"] = $this->uuid;
				}
				if (!empty($id)) {
					$res = Db::name("ybmp_bus_tmpl")->where(["mch_id" => $this->bus_id, "id" => $id])->update($dd);
				} else {
					$dd["create_time"] = time();
					$dd["is_del"] = 1;
					$dd["mch_id"] = $this->bus_id;
					$res = Db::name("ybmp_bus_tmpl")->insertGetId($dd);
					$rr["code"] = $res !== false ? 1 : 0;
					$rr["new_id"] = $res;
					return $rr;
				}
			} elseif ($page_flag == 3) {
				$dd["index_values"] = $data;
				$dd["img"] = $this->saveimg();
				$dd["uuid"] = $this->uuid;
				$res = Db::name("ybmp_user_tmpl_box")->where(["mch_id" => $this->bus_id, "id" => $id])->update($dd);
			}
			$res = $res !== false ? 1 : 0;
			return AjaxReturn($res);
		}
	}
	public function xx_list()
	{
		if (request()->isAjax() && request()->isPost()) {
			$type = request()->param("type");
			$num = input("param.num");
			$sort = input("param.sort");
			$cate = input("param.cate");
			$list = array();
			$where = [];
			if ($type == "goods") {
				if ($cate != 0) {
					$where["g.cate_id"] = ["eq", $cate];
				}
				if ($sort == "time") {
					$order = "g.create_time desc";
				}
				if ($sort == "pop") {
					$order = "g.is_hot desc";
				}
				if ($sort == "sales") {
					$order = "g.real_sales desc";
				}
				$list = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images=i.img_id")->where("g.mch_id", $this->bus_id)->where("g.is_del=0")->where($where)->field("g.price,g.goods_name as name,g.introduction as description,g.goods_id as id,i.img_cover as imgurl")->order($order)->limit($num)->select();
			} elseif ($type == "article") {
				if ($cate != 0) {
					$where["class_id"] = ["eq", $cate];
				}
				if ($sort == "time") {
					$order = "create_time desc";
				}
				if ($sort == "pop") {
					$order = "click desc";
				}
				if ($sort == "sales") {
					$order = "is_recommend desc";
				}
				$list = Db::name("ybmp_article")->where("mch_id", $this->bus_id)->where($where)->order($order)->field("link,title as name,short_title as description,image as imgurl,article_id as id")->limit($num)->select();
			} elseif ($type == "product") {
				if ($cate != 0) {
					$where["class_id"] = ["eq", $cate];
				}
				$list = Db::name("ybmp_product")->where("mch_id", $this->bus_id)->where($where)->order("sort asc")->field("title as name,image as imgurl,id")->limit($num)->select();
			}
			return $list;
		}
	}
	private function saveimg()
	{
		global $_W;
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
		return $img_src;
	}
}
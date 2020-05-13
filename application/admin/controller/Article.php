<?php


namespace app\admin\controller;

use app\admin\service\ArticleClass;
use think\Db;
class Article extends Base
{
	public function article_list()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&page", $url[1]);
		$url = "/" . $url[0];
		$article = new \app\admin\service\Article();
		$condition["a.mch_id"] = array("eq", $this->bus_id);
		$condition["a.status"] = array("eq", "2");
		$condition["a.is_dynamic"] = array("eq", "2");
		$class_name = input("param.class_name");
		if ($class_name != 0) {
			$condition["c.class_id"] = array("eq", $class_name);
		}
		$a_list = db::name("ybmp_article_class")->where("is_del", 1)->where("mch_id", $this->bus_id)->where("is_dynamic", 2)->select();
		$list = db::name("ybmp_article")->alias("a")->join("ybmp_article_class c", "c.class_id=a.class_id")->field("a.*,c.name as class_name")->order("a.create_time desc")->where($condition)->paginate(20, false, ["query" => ["s" => $url]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("class_name", $class_name);
		$this->assign("a_list", $a_list);
		$this->assign("page", $page);
		return view("article/article_list");
	}
	public function add_article()
	{
		$mch_id = $this->bus_id;
		if (request()->isPost()) {
			$article = new \app\admin\service\Article();
			$data["mch_id"] = $mch_id;
			$data["title"] = input("param.title", '');
			$data["class_id"] = input("param.class_id", '');
			$data["short_title"] = input("param.short_title", '');
			$data["author"] = input("param.author", '');
			$data["content"] = input("param.content", '');
			$data["image"] = input("param.article_pic", '');
			$data["keyword"] = input("param.keyword", '');
			$data["is_recommend"] = input("param.is_recommend", '');
			$data["status"] = 2;
			$data["create_time"] = time();
			$data["goods_array"] = input("param.goods_array", '');
			$data["link"] = input("param.link", '');
			$dy = input("param.dy", '');
			if ($dy) {
				$data["is_dynamic"] = 1;
			}
			$article->addArticle($data);
			$aid = db::name("ybmp_article")->max("article_id");
			$res["code"] = 1;
			if ($dy) {
				$a["mch_id"] = $mch_id;
				$a["article_id"] = $aid;
				$a["create_time"] = $data["create_time"];
				db::name("ybmp_information")->insert($a);
				$res["dy"] = true;
			}
			return $res;
		}
		$article_class = new ArticleClass();
		$condition["mch_id"] = array("eq", $mch_id);
		$condition["is_del"] = array("eq", 1);
		$dynamic = input("param.dynamic", "3");
		$d = $dynamic == 3 ? 2 : 1;
		$condition["is_dynamic"] = $d;
		$class = $article_class->getArticleClass($condition);
		if ($dynamic) {
			$this->assign("nn", "动态");
			$this->assign("dd", $dynamic);
		} else {
			$this->assign("dd", -1);
			$this->assign("nn", "文章");
		}
		$this->assign("class", $class);
		$bus_tmpl = db::name("ybmp_business")->where("id", $this->bus_id)->find();
		$this->assign("use", $bus_tmpl["mod_class_id"]);
		return view("article/add_article");
	}
	public function edit_article()
	{
		$article = new \app\admin\service\Article();
		$article_id = input("param.article_id", '');
		if (request()->isPost()) {
			$data["article_id"] = input("param.article_id", '');
			$data["title"] = input("param.title", '');
			$data["class_id"] = input("param.class_id", '');
			$data["short_title"] = input("param.short_title", '');
			$data["author"] = input("param.author", '');
			$data["content"] = input("param.content", '');
			$data["image"] = input("param.article_pic", '');
			$data["keyword"] = input("param.keyword", '');
			$data["is_recommend"] = input("param.is_recommend", '');
			$data["status"] = 2;
			$data["create_time"] = time();
			$data["link"] = input("param.link", '');
			$arrys = input("param.goods_array", '');
			if ($arrys != '' || !empty($arrys)) {
				$data["goods_array"] = $arrys;
			}
			$res = $article->editArticle($data, $article_id);
			return AjaxReturn($res);
		}
		$condition["mch_id"] = array("eq", $this->bus_id);
		$condition["is_del"] = array("eq", 1);
		$condition["is_dynamic"] = array("eq", 2);
		$class = db::name("ybmp_article_class")->where($condition)->select();
		$this->assign("class", $class);
		$article = db::name("ybmp_article")->where("article_id", $article_id)->find();
		$this->assign("info", $article);
		$bus_tmpl = db::name("ybmp_business")->where("id", $this->bus_id)->find();
		$this->assign("use", $bus_tmpl["mod_class_id"]);
		return view("article/edit_article");
	}
	public function add_article_goods()
	{
		$mch_id = $this->bus_id;
		if (request()->isAjax() && request()->isPost()) {
			$cate_id = input("param.cate_id", "0");
			$goods_list = db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "i.img_id=g.images")->field("g.goods_id,g.goods_name,i.img_cover")->where("g.cate_id", $cate_id)->where("g.state", "1")->where("g.is_del<>1")->where("g.mch_id", $mch_id)->select();
			return $goods_list;
		}
		$cate = db::name("ybmp_goods_cate")->where("is_visible", "1")->where("mch_id", $mch_id)->select();
		foreach ($cate as $k => $v) {
			$goods_count = db::name("ybmp_goods")->where("state", "1")->where("is_del<>1")->where("cate_id", $v["cate_id"])->count();
			$cate[$k]["goods_count"] = $goods_count;
		}
		$this->assign("cate", $cate);
		return view("article/add_article_goods");
	}
	public function del_article()
	{
		$article_id = input("param.article_id", '');
		$id = input("param.id", '');
		$dynamic = input("param.dynamic", '');
		if ($dynamic) {
			$res = db::name("ybmp_information")->where("id", $id)->update(["is_del" => "2"]);
			$this->write_log("del_information:" . $id);
			return AjaxReturn($res);
		}
		$article = new \app\admin\service\Article();
		$data["status"] = "-1";
		$res = $article->delArticle($data, $article_id);
		return AjaxReturn($res);
	}
	public function article_class()
	{
		$dy = request()->param("dy", "3");
		$this->assign("dy", $dy);
		$d = $dy == 2 ? 1 : 2;
		$article_class = new ArticleClass();
		$one_list = $article_class->getFormatClassCategoryList($this->bus_id, $d);
		$this->assign("class_list", $one_list);
		return view("article/article_class");
	}
	public function add_article_class()
	{
		$dy = request()->param("dy", "3");
		$this->assign("dy", $dy);
		$d = $dy == 3 ? 2 : 1;
		$article_class = new ArticleClass();
		if (request()->isPost()) {
			$data["mch_id"] = $this->bus_id;
			$data["pid"] = input("param.pid", 0);
			if ($data["pid"] == 0) {
				$data["level"] = 1;
			} else {
				$data["level"] = $article_class->getClassCategoryDetail($data["pid"])["level"] + 1;
			}
			$data["name"] = input("param.class_name", '');
			$data["class_style"] = input("param.this_radio", '');
			$data["sort"] = input("param.class_sort", '');
			$data["class_img"] = input("param.brand_pic", '');
			$data["create_time"] = time();
			$data["is_dynamic"] = $d;
			$res = db::name("ybmp_article_class")->insertGetId($data);
			db::name("ybmp_article_class")->where("class_id", $res)->update(["pages_url" => CLASS_ID . $res]);
			return AjaxReturn($res);
		}
		$category_list = $article_class->getClassCategoryTree(0, $this->bus_id, $d);
		$this->assign("class_list", $category_list);
		return view("article/add_article_class");
	}
	public function edit_article_class()
	{
		$dy = request()->param("dy", "3");
		$d = $dy == 3 ? 2 : 1;
		$this->assign("dy", $dy);
		$article_class = new ArticleClass();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		if (request()->isPost()) {
			$data["class_id"] = input("param.class_id", "0");
			$data["name"] = input("param.class_name", '');
			$data["pid"] = input("param.pid", 0);
			if ($data["pid"] == 0) {
				$data["level"] = 1;
			} else {
				$data["level"] = $article_class->getClassCategoryDetail($data["pid"])["level"] + 1;
			}
			$data["sort"] = input("param.class_sort", '');
			$data["class_style"] = input("param.this_radio", '');
			$img = input("param.brand_pic", '');
			if (!strstr($img, THIS_URL)) {
				$data["class_img"] = $img;
			} else {
				$data["class_img"] = $img;
			}
			$data["create_time"] = time();
			$res = $article_class->updateArticleClass($data);
			return AjaxReturn($res);
		}
		$category_id = request()->get("class_id", '');
		$result = $article_class->getClassCategoryDetail($category_id);
		$this->assign("class", $result);
		if ($result["level"] == 1) {
			$chile_list = $article_class->getClassCategoryTree($category_id, $mch_id, $d);
			if (empty($chile_list)) {
				$category_list = $article_class->getClassCategoryTree(0, $mch_id, $d);
			} else {
				$is_have = false;
				foreach ($chile_list as $k => $v) {
					if ($v["level"] == 3) {
						$is_have = true;
					}
				}
				if ($is_have) {
					$category_list = array();
				} else {
					$category_list = $article_class->getClassCategoryListByParentId(0, $mch_id, $d);
				}
			}
		} elseif ($result["level"] == 2) {
			$chile_list = $article_class->getClassCategoryListByParentId($category_id, $mch_id, $d);
			if (empty($chile_list)) {
				$category_list = $article_class->getClassCategoryTree(0, $mch_id, $d);
			} else {
				$category_list = $article_class->getClassCategoryListByParentId(0, $mch_id, $d);
			}
		} elseif ($result["level"] == 3) {
			$category_list = $article_class->getClassCategoryTree(0, $mch_id, $d);
		}
		if (!empty($category_list)) {
			foreach ($category_list as $k => $v) {
				if ($v["class_id"] == $category_id && $category_id !== 0) {
					unset($category_list[$k]);
				} else {
					if (isset($v["child_list"])) {
						$temp_array = $v["child_list"];
						foreach ($temp_array as $t => $m) {
							if ($m["class_id"] == $category_id && $category_id !== 0) {
								unset($temp_array[$t]);
							}
						}
						sort($temp_array);
						$category_list[$k]["child_list"] = $temp_array;
					}
				}
			}
			sort($category_list);
		}
		$this->assign("class_list", $category_list);
		return view("article/edit_article_class");
	}
	public function del_article_class()
	{
		$class_id = input("param.class_id", "0");
		$res = db::name("ybmp_article_class")->where("class_id", $class_id)->update(["is_del" => 2]);
		return AjaxReturn($res);
	}
	public function message_send()
	{
		return view("article/message_send");
	}
	public function message_send_wx()
	{
		return view("article/message_send_wx");
	}
	public function send_message()
	{
		$id = input("param.tmpl_id");
		$this->assign("tmpl_id", $id);
		$this->assign("data_time", date("Y年m月d日"));
		return view("article/send_message_to");
	}
	public function add_user_msg()
	{
		$id = input("param.tmpl_id");
		$content = json_decode(input("param.menu_rlist"), true);
		$time = time();
		$str_array = array();
		for ($i = 0; $i < count($content); $i++) {
			$str_array["keyword" . ($i + 1)] = ["value" => $content[$i]["values"]];
		}
		$bus_user = db::name("ybmp_user_formid")->where("create_time", ">", $time - 597800)->where("mch_id", $this->bus_id)->order("create_time asc")->group("open_id")->select();
		$ok = 0;
		$no = 0;
		$return = array();
		foreach ($bus_user as $k => $v) {
			$data = array("touser" => $v["open_id"], "page" => "index", "template_id" => $id, "form_id" => $v["form_id"], "data" => $str_array);
			$res = $this->post_yes_send($data);
			if ($res["errcode"] == 0) {
				$ok++;
			} else {
				$no++;
			}
			db::name("ybmp_user_formid")->where("id", $v["id"])->delete();
			$return["ok"] = $ok;
			$return["no"] = $no;
		}
		return $return;
	}
	public function post_yes_send($json)
	{
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $token;
		$acc_out = $this->post($url, $json);
		return $acc_out;
	}
	public function post_send_message()
	{
		$id = input("param.tmpl_id");
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=" . $token;
		$data = array("offset" => 0, "count" => 20);
		$acc_out = $this->post($url, $data);
		$this_tmpl = array();
		foreach ($acc_out["list"] as $k => $v) {
			if ($v["template_id"] == $id) {
				$this_tmpl = $v;
			}
		}
		$string_arr = explode("\n", $this_tmpl["content"]);
		array_pop($string_arr);
		header("Content-type: text/html; charset=utf-8");
		$str_arr = array();
		foreach ($string_arr as $k => $v) {
			preg_match_all("/[\\x{4e00}-\\x{9fff}]+/u", $v, $matches);
			$str = join('', $matches[0]);
			array_push($str_arr, $str);
		}
		$this_tmpl["content"] = $str_arr;
		return $this_tmpl;
	}
	public function post_message_send()
	{
		$offset = input("param.offset") - 1;
		$count = 20;
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=" . $token;
		$data = array("offset" => $offset * $count, "count" => $count);
		$acc_out = $this->post($url, $data);
		return $acc_out;
	}
	public function post_del_send()
	{
		$template_id = input("param.id");
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/del?access_token=" . $token;
		$data = array("template_id" => $template_id);
		$acc_out = $this->post($url, $data);
		return $acc_out;
	}
	public function select_message_send()
	{
		if (request()->isAjax() && request()->isPost()) {
			$tmpl_id = input("param.tmpl_id");
			$token = $this->get_authorizer_access_token();
			$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/library/get?access_token=" . $token;
			$data = array("id" => $tmpl_id);
			$acc_out = $this->post($url, $data);
			return $acc_out;
		}
		$tmpl_id = input("param.tmpl_id");
		$this->assign("tmpl_id", $tmpl_id);
		$this->assign("data_time", date("Y年m月d日"));
		return view("article/select_message_send");
	}
	public function add_my_menu()
	{
		$list = json_decode(input("param.select_menu_list"), true);
		$string = '';
		foreach ($list as $k => $v) {
			$string .= $v["keyword_id"] . ",";
		}
		$string = substr($string, 0, -1);
		$string_arr = explode(",", $string);
		$tmpl_id = input("param.tmpl_id");
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/add?access_token=" . $token;
		$data = array("id" => $tmpl_id, "keyword_id_list" => $string_arr);
		$acc_out = $this->post($url, $data);
		return $acc_out;
	}
	public function wx_tmpl_list()
	{
		$offset = input("param.offset") - 1;
		$count = 20;
		$token = $this->get_authorizer_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/library/list?access_token=" . $token;
		$data = array("offset" => $offset * $count, "count" => $count);
		$acc_out = $this->post($url, $data);
		return $acc_out;
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
	public function get($url)
	{
		$data = get_url_content($url);
		return $data;
	}
	public function get_component_access_token()
	{
		$res = db::name("ybmp_config")->where("key", "WINXINTOKEN")->find();
		if ($res["modify_time"] + 6000 <= time()) {
			$mi = db::name("ybmp_config")->where("key", "WINXINTOKEN")->find();
			$url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
			$post_data = array("component_appid" => "wx7be48fd3337a5e4c", "component_appsecret" => "b22cc2bceea3d4e3248b3b3cd83ae9be", "component_verify_ticket" => $mi["value"]);
			$access_token = $this->post($url, $post_data);
			db::name("ybmp_config")->where("key", "WINXINTOKEN")->update(["value2" => $access_token["component_access_token"], "modify_time" => time()]);
			$component_access_token = $access_token["component_access_token"];
		} else {
			$component_access_token = $res["value2"];
		}
		return $component_access_token;
	}
	public function get_authorizer_access_token()
	{
		$token = $this->get_component_access_token();
		$mch_id = isset($this->bus_id) ? $this->bus_id : "0";
		$red = db::name("ybmp_business_config")->where("mch_id", $mch_id)->find();
		if ($red["date_time"] + 10 <= time()) {
			$acc_url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=" . $token;
			$acc_data = array("component_appid" => "wx7be48fd3337a5e4c", "authorizer_appid" => $red["appId"], "authorizer_refresh_token" => $red["authorizer_refresh_token"]);
			$acc_out = $this->post($acc_url, $acc_data);
			if ($acc_out["errcode"] != 0) {
				return "61003";
			} else {
				$dd["token"] = $acc_out["authorizer_access_token"];
				$dd["date_time"] = time();
				db::name("ybmp_business_config")->where("mch_id", $mch_id)->update($dd);
			}
		}
		$out = db::name("ybmp_business_config")->where("mch_id", $mch_id)->find();
		return $out["token"];
	}
	function decodeUnicode($str)
	{
		return preg_replace_callback("/\\\\u([0-9a-f]{4})/i", create_function("\$matches", "return mb_convert_encoding(pack(\"H*\", \$matches[1]), \"UTF-8\", \"UCS-2BE\");"), $str);
	}
	public function article_type()
	{
		$id = input("param.id");
		$aid = input("param.aid");
		$type = input("param.type");
		if ($aid != 0) {
			db::name("ybmp_article")->where("article_id", $aid)->update(["type" => $type]);
		}
		$res = db::name("ybmp_information")->where("id", $id)->update(["state" => $type]);
		return AjaxReturnMsg(1);
	}
	public function collect()
	{
		if (request()->isAjax() && request()->isPost()) {
			$content_url = request()->post("url");
			var_dump($content_url);
			$html = get_url_content($content_url);
			var_dump($html);
			preg_match_all("/id=\"js_content\">(.*)<script/iUs", $html, $content, PREG_PATTERN_ORDER);
			if (!empty($content[1][0]) && '' != $content[1][0]) {
				$content = "<div id='js_content'>" . $content[1][0];
				$content = str_replace("data-src", "src", $content);
				$content = str_replace("preview.html", "player.html", $content);
			} else {
				$content = '';
			}
			preg_match_all("/var msg_title = \\\"(.*?)\\\";/si", $html, $title, PREG_PATTERN_ORDER);
			$title = htmlspecialchars_decode($title[1][0]);
			preg_match_all("/var msg_desc = \\\"(.*?)\\\";/si", $html, $short_title, PREG_PATTERN_ORDER);
			$short_title = htmlspecialchars_decode($short_title[1][0]);
			preg_match_all("/var nickname = \\\"(.*?)\\\";/si", $html, $m);
			$nickname = htmlspecialchars_decode($m[1][0]);
			preg_match_all("/var msg_cdn_url = \\\"(.*?)\\\";/si", $html, $m);
			$msg_img = htmlspecialchars_decode($m[1][0]);
			$data["title"] = trim($title);
			$data["short_title"] = trim($short_title);
			$data["nickname"] = trim($nickname);
			$data["msg_img"] = trim($msg_img);
			$data["content"] = trim($content);
			return json($data);
		}
		return view();
	}
}
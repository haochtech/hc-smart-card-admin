<?php


namespace app\admin\controller;

load()->func("file");
use app\admin\service\OffwebService;
use think\Db;
require_once APP_PATH . "api_common.php";
class Card extends Base
{
	public function index()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$search = request()->param("search_text", '');
		$where = [];
		empty($search) ?: ($where["user_name"] = ["like", "%" . $search . "%"]);
		$where["is_del"] = 0;
		$where["radar"] = 1;
		$where["mch_id"] = $this->bus_id;
		$list = Db::name("ybmp_bus_card")->where($where)->order("default", "desc")->order("sort", "asc")->order("is_head", "asc")->order("default", "desc")->order("sort", "desc")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => $search]]);
		$page = $list->render();
		$this->assign("list", $list);
		$this->assign("search_text", $search);
		$this->assign("page", $page);
		return view();
	}
	public function card_add()
	{
		$de = db::name("ybmp_depart")->field("id,name")->where("mch_id", $this->bus_id)->where("is_del", 2)->select();
		$this->assign("de", $de);
		return view();
	}
	public function card_edit()
	{
		$goods = db::name("ybmp_goods")->field("goods_name,goods_id")->where("mch_id", $this->bus_id)->select();
		$id = input("id", '');
		$off = input("off", 2);
		$_SESSION["staff_id"] = $id;
		$card = Db::name("ybmp_bus_card")->where("id", $id)->find();
		$depart = Db::name("ybmp_depart")->where("mch_id", $this->bus_id)->order("sort desc,id")->select();
		$c_goods = explode(",", $card["proposal_goods_id"]);
		$effect_tag = $card["effect_tag"];
		$effect_tag_arr = json_decode($effect_tag, true);
		$effect_tag_arr_key = array_keys($effect_tag_arr);
		$effect_tag = implode(",", $effect_tag_arr_key);
		$size = phpinfo_array();
		$this->assign("off", $off);
		$this->assign("max_filesize", intval($size["Core"]["upload_max_filesize"]));
		$this->assign("card", $card);
		$this->assign("effect_tag", $effect_tag);
		$this->assign("goods", $goods);
		$this->assign("c_goods", $c_goods);
		$this->assign("depart", $depart);
		return view();
	}
	public function dialogalbumlist()
	{
		$number = request()->get("number", 5);
		$type = request()->get("type", '');
		$this->assign("type", $type);
		$this->assign("number", $number);
		$album = new \app\admin\service\Images();
		$condition["mch_id"] = array("eq", $this->bus_id);
		$default_album_detail = $album->GetDefAll($condition);
		$this->assign("default_album_id", $default_album_detail["group_id"]);
		return view("card/select_img");
	}
	public function do_card_add()
	{
		if ($this->request->isAjax()) {
			$data = $this->request->param();
			$data["mch_id"] = $this->bus_id;
			$data["is_del"] = 0;
			$data["state"] = 1;
			$data["click"] = 0;
			$data["likes"] = 0;
			$data["wxtel"] = $data["tel"];
			$data["create_time"] = time();
			$data["update_time"] = time();
			$data["position"] = str_replace(" ", '', $data["position"]);
			$data["user_name"] = str_replace(" ", '', $data["user_name"]);
			if (!preg_match_all("/^1[356789]\\d{9}\$/", $data["tel"], $check)) {
				return AjaxReturnMsg(0, "手机" . $data["tel"] . "格式错误");
			}
			unset($data["editorValue"]);
			$ser = new OffwebService();
			$num = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->count();
			$data["UserId"] = strtoascii($data["user_name"] . $num);
			$res = $ser->syn_add_user($data["UserId"], $data["user_name"], $data["tel"], $data["did"], $data["position"], $data["email"]);
			if ($res == "true") {
				$data["profile"] = "我的个人简介";
				Db::name("ybmp_bus_card")->insert($data);
				return AjaxReturnMsg(1, "添加成功！");
			} else {
				return AjaxReturnMsg(0, $res);
			}
		} else {
			return AjaxReturnMsg(0, "异常访问！");
		}
	}
	public function do_card_edit()
	{
		if (request()->isAjax()) {
			$f = $_FILES["mp3file"];
			$data = json_decode($this->request->param("parmes"), true);
			$data["position"] = str_replace(" ", '', $data["position"]);
			$data["user_name"] = str_replace(" ", '', $data["user_name"]);
			if (!preg_match_all("/^1[356789]\\d{9}\$/", $data["tel"], $check)) {
				return AjaxReturnMsg(0, "手机" . $data["tel"] . "格式错误");
			}
			if (!preg_match_all("/^.*@.*\\..*\$/", $data["email"], $check)) {
				return AjaxReturnMsg(0, "邮箱" . $data["email"] . "格式错误");
			}
			if (!empty($f)) {
				if ($f["size"] > 1024 && $f["size"] < 1024 * 1024 * 5) {
					$id = $_SESSION["staff_id"];
					$type = explode(".", $f["name"]);
					if ($type[1] != "mp3") {
						return AjaxReturnMsg(0, "a" . $type[1] . "格式错误！");
					}
					$d["name"] = date("YmdHis") . $this->bus_id . "_" . $id . "." . $type[1];
					$d["size"] = floor($f["size"] / 1024);
					$d["tmp_name"] = $f["tmp_name"];
					$d["zname"] = SITE_PATH . "public/video/" . $d["name"];
					$ch_path = SITE_PATH . "public/video/";
					if (!file_exists($ch_path)) {
						$mode = intval("0777", 8);
						mkdir($ch_path, $mode, true);
					}
					move_uploaded_file($d["tmp_name"], $d["zname"]);
					$vioce_profile = explode("/core/", $d["zname"]);
					$url = explode("/core/", $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
					$url = "https://" . $url[0] . "/core/" . $vioce_profile[1];
					db::name("ybmp_bus_card")->where("id", $id)->update(["vioce_profile" => $url]);
				} else {
					if ($f["size"] >= 1024 * 1024 * 5) {
						return AjaxReturnMsg(0, "文件" . $f["name"] . "大小错误");
					}
				}
			}
			$data["update_time"] = time();
			unset($data["editorValue"]);
			unset($data["effect_tag"]);
			$ser = new OffwebService();
			$userid = Db::name("ybmp_bus_card")->where("id", $data["id"])->find();
			if ($userid["user_name"] == $data["user_name"] && $userid["did"] == $data["did"] && $userid["position"] == $data["position"] && $userid["email"] == $data["email"] && $userid["sort"] == $data["sort"] && $userid["gender"] == $data["gender"]) {
				Db::name("ybmp_bus_card")->update($data);
				return AjaxReturnMsg(1, "修改成功！");
			} else {
				$res = $ser->syn_add_user($userid["UserId"], $data["user_name"], '', $data["did"], $data["position"], $data["email"], $data["sort"], $data["gender"]);
				if ($res == "true") {
					Db::name("ybmp_bus_card")->update($data);
					return AjaxReturnMsg(1, "修改成功！");
				} else {
					return AjaxReturnMsg(0, $res);
				}
			}
		} else {
			return AjaxReturnMsg(0, "异常访问！");
		}
	}
	public function change_card()
	{
		$id = request()->post("id");
		$type = request()->post("type", '');
		$default = request()->post("default", '');
		if (!empty($type)) {
			$res = db::name("ybmp_bus_card")->where("id", $id)->update(["state" => $type]);
		}
		if (!empty($default)) {
			if ($default == 1) {
				$d["is_head"] = 2;
			}
			$d["default"] = $default;
			$res = db::name("ybmp_bus_card")->where("id", $id)->update($d);
		}
		return AjaxReturn($res);
	}
	public function show_goods()
	{
		$id = request()->param("ids", '');
		$sha = request()->param("sha", '');
		$pro = request()->param("pro", '');
		$type = request()->param("type", "goods");
		if ($type == "goods") {
			$goods = db::name("ybmp_goods")->alias("g")->join("ybmp_images a", "a.img_id=g.images", "left")->field("g.goods_name,g.goods_id,a.img_cover,g.create_time")->where("g.mch_id", $this->bus_id)->where("g.state", 1)->where("g.is_del", 0)->select();
			if ($id) {
				$card = Db::name("ybmp_bus_card")->field("proposal_goods_id")->where("id", $id)->find();
				$cgoods = explode(",", $card["proposal_goods_id"]);
			}
			if ($sha) {
				$cgoods = explode(",", $sha);
			}
		} else {
			$goods = db::name("ybmp_product")->alias("a")->join("ybmp_product_class c", "a.class_id=c.id", "left")->field("title,image,name,a.id")->where("a.mch_id", $this->bus_id)->where("a.status", 1)->where("c.status", 1)->select();
			if ($id) {
				$card = Db::name("ybmp_bus_card")->field("proposal_goods_id")->where("id", $id)->find();
				$cgoods = explode(",", $card["proposal_goods_id"]);
			}
			if ($pro) {
				$cgoods = explode(",", $pro);
			}
		}
		$this->assign("goods", $goods);
		$this->assign("type", $type);
		$this->assign("c_goods", $cgoods);
		return view("offweb/show_goods");
	}
	public function select_mod()
	{
		$id = request()->param("id", '');
		$cid = request()->param("cid", '');
		if (!empty($cid)) {
			$cid = $cid == "-1" ? 0 : $cid;
			db::name("ybmp_bus_card")->where("id", $id)->update(["cid" => $cid]);
			return AjaxReturnMsg(1, $cid);
		}
		$d = db::name("ybmp_bus_card")->field("cid")->where("id", $id)->find();
		$a = SITE_PATH . "public/upload/card_tmpl/*.jpg";
		$a = glob($a);
		$b = array();
		for ($i = 0; $i < count($a); $i++) {
			$c = explode("/public/", $a[$i]);
			$b[$i] = "public/" . $c[1];
		}
		$e = Db::name("ybmp_config")->where("mch_id", $this->bus_id)->value("value");
		$e = json_decode($e, true);
		$this->assign("now_img", $e["card_img"]);
		$this->assign("list", $b);
		$this->assign("now", $d["cid"]);
		$this->assign("id", $id);
		return view();
	}
	public function send_card()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = db::name("ybmp_sendcard")->where("mch_id", $this->bus_id)->where("status", 1)->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$info = array();
		for ($i = 0; $i < count($list); $i++) {
			$info[$i] = $list[$i];
			if (empty($list[$i]["user"])) {
				$info[$i]["num"] = 0;
			} else {
				$n = explode(",", $list[$i]["user"]);
				$info[$i]["num"] = count($n);
			}
			$info[$i]["user_num"] = db::name("ybmp_sendlog")->group("user_id")->where(["aid" => $list[$i]["id"], "user_type" => 1])->count();
		}
		$this->assign("show", "list");
		$this->assign("page", $list->render());
		$this->assign("list", $info);
		return view();
	}
	public function send_card_edit()
	{
		$id = request()->param("id", '');
		$user = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("radar", 1)->select();
		$this->assign("user", $user);
		$this->assign("show", "list");
		if (!empty($id)) {
			$info = db::name("ybmp_sendcard")->where("id", $id)->find();
			$info["user_list"] = explode(",", $info["user"]);
			$this->assign("i", $info);
			$this->assign("id", $id);
		}
		return view();
	}
	public function send_card_do()
	{
		if (request()->isAjax()) {
			$data["name"] = request()->param("name");
			$id = request()->param("id", '');
			$data["user"] = request()->param("user");
			$data["content"] = request()->param("content");
			$data["update_time"] = time();
			if (!empty($id)) {
				$res = db::name("ybmp_sendcard")->where("id", $id)->update($data);
			} else {
				$data["mch_id"] = $this->bus_id;
				$res = db::name("ybmp_sendcard")->insert($data);
			}
			return AjaxReturnMsg($res);
		}
		return AjaxReturnMsg(0);
	}
	public function send_del()
	{
		if (request()->isAjax()) {
			$id = request()->param("id", '');
			if (!empty($id)) {
				db::name("ybmp_sendcard")->where("id", $id)->update(["status" => 2]);
				return AjaxReturnMsg(1);
			}
		}
		return AjaxReturnMsg(0);
	}
	public function show_staff()
	{
		$id = request()->param("id", '');
		$a = db::name("ybmp_sendcard")->where("id", $id)->find();
		$w["b.id"] = ["in", $a["user"]];
		$list = db::name("ybmp_bus_card")->alias("b")->field("user_name,b.id staff_id,d.name,head_photo")->where($w)->join("ybmp_depart d", "d.id=b.did and d.mch_id=b.mch_id", "left")->select();
		for ($i = 0; $i < count($list); $i++) {
			$list[$i]["user_num"] = db::name("ybmp_sendlog")->group("user_id")->where("staff_id", $list[$i]["staff_id"])->where(["aid" => $a["id"], "user_type" => 1])->count();
		}
		$data["id"] = $id;
		$data["list"] = $list;
		return json_encode($data);
	}
	public function show_user()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$id = request()->param("id", '');
		$list = array();
		$list["name"] = request()->param("name", '');
		$list["user_name"] = request()->param("user_name", '');
		$staff_id = request()->param("staff_id", '');
		$res = db::name("ybmp_sendlog")->group("user_id")->where("aid", $id)->where(["user_type" => 1])->where("staff_id", $staff_id)->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$list["page"] = $res->render();
		for ($i = 0; $i < count($res); $i++) {
			$list["list"][$i] = $res[$i];
			$r = db::name("ybmp_user")->where("uid", $res[$i]["user_id"])->field("user_headimg,nick_name")->find();
			$list["list"][$i]["user_headimg"] = $r["user_headimg"];
			$list["list"][$i]["nick_name"] = $r["nick_name"];
			$list["list"][$i]["update_time"] = date("Y-m-d H:i:s", $res[$i]["update_time"]);
		}
		return json_encode($list);
	}
	public function card_ban()
	{
		$id = \request()->param("id");
		$card_ban = \request()->param("is_relay", 1);
		if ($card_ban == 2) {
			$card_ban = 1;
		} else {
			$card_ban = 2;
		}
		$res = db::name("ybmp_bus_card")->where("id", $id)->update(["is_relay" => $card_ban]);
		return AjaxReturn($res);
	}
	public function all_user()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$li = db::name("ybmp_sendlog")->alias("a")->join("ybmp_user b", "a.user_id=b.uid", "left")->join("ybmp_sendcard c", "a.aid=c.id", "left")->join("ybmp_bus_card d", "a.staff_id=d.id", "left")->field("b.user_headimg,b.nick_name,a.update_time,c.name,d.user_name")->order("a.update_time", "desc")->where("a.mch_id", $this->bus_id)->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$this->assign("list", $li);
		$this->assign("page", $li->render());
		return view();
	}
	public function do_head()
	{
		$id = request()->param("id");
		if (!empty($id)) {
			Db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->update(["is_head" => 2]);
			$res = Db::name("ybmp_bus_card")->where("id", $id)->update(["is_head" => 1, "default" => 2]);
			return AjaxReturn($res);
		} else {
			return AjaxReturn(0);
		}
	}
}
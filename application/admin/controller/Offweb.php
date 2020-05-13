<?php


namespace app\admin\controller;

use think\console\output\Ask;
use think\Db;
use think\Request;
use think\Cache;
use app\admin\service\OffwebService;
load()->func("file");
require_once APP_PATH . "api_common.php";
require EXTEND_PATH . "PHPExcel/PHPExcel.php";
class Offweb extends Base
{
	private $user_corp = array("cid" => '', "tsecret" => '');
	private $send_list = array("order_create" => array("name" => "普通订单下单成功", "status" => 1), "order_pay" => array("name" => "普通订单支付成功", "status" => 1), "order_refund" => array("name" => "普通订单申请退款", "status" => 1), "ms_order_create" => array("name" => "秒杀订单下单成功", "status" => 1), "ms_order_pay" => array("name" => "秒杀订单支付成功", "status" => 1), "ms_order_refund" => array("name" => "秒杀订单申请退款", "status" => 1), "kj_order_create" => array("name" => "砍价订单下单成功", "status" => 1), "kj_order_pay" => array("name" => "砍价订单支付成功", "status" => 1), "kj_order_refund" => array("name" => "砍价订单申请退款", "status" => 1), "pt_order_create" => array("name" => "拼团订单下单成功", "status" => 1), "pt_order_pay" => array("name" => "拼团订单支付成功", "status" => 1), "pt_order_refund" => array("name" => "拼团订单申请退款", "status" => 1));
	public function __construct()
	{
		parent::__construct();
		$d = db::name("ybmp_corp_conf")->where("mch_id", $this->bus_id)->find();
		$this->user_corp["cid"] = $d["corp_id"];
		$this->user_corp["tsecret"] = $d["tsecret"];
		if (empty($this->user_corp["tsecret"])) {
			return view("offweb/corp_conf");
		}
	}
	public function join_us()
	{
		$search_text = request()->post("search_text");
		$con["mch_id"] = $this->bus_id;
		$con["name"] = array("like", "%" . $search_text . "%");
		$list = commonPage("ybmp_offweb_join", $con, $search_text, 10, "id desc");
		$page = $list->render();
		$this->assign("jobo_list", $list);
		$this->assign("search_text", $search_text);
		$this->assign("page", $page);
		return view();
	}
	public function join_change()
	{
		$condition["id"] = request()->post("id");
		$condition["mch_id"] = $this->bus_id;
		$status = request()->post("show");
		if (!empty($status)) {
			$data["show"] = $status == "2" ? 1 : 2;
			$res = db::name("ybmp_offweb_join")->where($condition)->update($data);
		} else {
			$data["is_del"] = 1;
			$res = db::name("ybmp_offweb_join")->where($condition)->update($data);
		}
		return $res;
	}
	public function join_edit()
	{
		$id = input("param.id");
		if (empty($id)) {
			if (request()->isPost() && request()->isAjax()) {
				$data["name"] = request()->post("name");
				$data["pay"] = request()->post("pay");
				$data["exp"] = request()->post("exp");
				$data["hr"] = request()->post("hr");
				$data["major"] = request()->post("major");
				$data["content"] = request()->post("content");
				$data["show"] = request()->post("show");
				$data["mch_id"] = $this->bus_id;
				$res = db::name("ybmp_offweb_join")->insert($data);
				return AjaxReturn($res);
			}
			return view();
		} else {
			if (request()->isPost() && request()->isAjax()) {
				$data["name"] = request()->post("name");
				$data["pay"] = request()->post("pay");
				$data["exp"] = request()->post("exp");
				$data["major"] = request()->post("major");
				$data["hr"] = request()->post("hr");
				$data["content"] = request()->post("content");
				$res = db::name("ybmp_offweb_join")->where("id", $id)->update($data);
				return AjaxReturn($res);
			}
			$data = db::name("ybmp_offweb_join")->where("id", $id)->find();
			$this->assign("data", $data);
			return view();
		}
	}
	public function dynamic()
	{
		$mch_id = $this->bus_id;
		$search_text = request()->post("search_text");
		$class_name = input("param.class_name", 0);
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$con["i.mch_id"] = $mch_id;
		$con["i.is_del"] = 1;
		if ($class_name != 0) {
			$con["a.class_id"] = array("eq", $class_name);
		}
		$list = db::name("ybmp_information")->alias("i")->join("ybmp_article a", "a.article_id=i.article_id", "left")->join("ybmp_bus_card c", "c.id=i.staff_id", "left")->join("ybmp_article_class ac", "ac.class_id=a.class_id", "left")->field("i.id,i.article_id,title,image,author,i.click,i.create_time,i.is_del,a.status,forward,staff_id,type,c.user_name,c.position,ac.name,ac.class_id,i.state")->where($con)->order("i.id desc")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => $search_text]]);
		$listaa = array();
		for ($i = 0; $i < count($list); $i++) {
			$listaa[$i] = $list[$i];
			$c = db::name("ybmp_information_comments")->where("information_id", $list[$i]["id"])->where("state", 1)->count();
			$listaa[$i]["information_num"] = $c;
			$listaa[$i]["title"] = empty($list[$i]["title"]) ? "员工发布" : $list[$i]["title"];
			if ($list[$i]["create_time"] == 0) {
				$d = db::name("ybmp_article")->field("create_time")->where("article_id", $list[$i]["article_id"])->find();
				$listaa[$i]["create_time"] = $d["create_time"];
			}
		}
		$a_list = db::name("ybmp_article_class")->where("is_del=1")->where("is_dynamic=1")->where("mch_id", $this->bus_id)->select();
		$this->assign("a_list", $a_list);
		$this->assign("class_name", $class_name);
		$this->assign("page", $list->render());
		$this->assign("list", $listaa);
		return view();
	}
	public function dynamic_edit()
	{
		$article = new \app\admin\service\Article();
		$article_id = input("param.article_id", '');
		$mch_id = $this->bus_id;
		$id = input("param.id", '');
		if (request()->isPost() && $article_id > 0) {
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
			$data["is_dynamic"] = 1;
			$data["link"] = input("param.link", '');
			$arrys = input("param.goods_array", '');
			if ($arrys != '' || !empty($arrys)) {
				$data["goods_array"] = $arrys;
			}
			$res = $article->editArticle($data, $article_id);
			return AjaxReturn($res);
		} else {
			if (request()->isPost() && $article_id == 0) {
				$data = array();
				$data["content"] = input("param.contents", '');
				$data["state"] = input("param.type", '');
				$id = input("param.id", '');
				$data["pic_arr"] = input("param.dd", '');
				$data["staff_id"] = input("param.staff_id", '');
				$res = db::name("ybmp_information")->where("id", $id)->update($data);
				return AjaxReturn($res);
			}
		}
		$condition["mch_id"] = array("eq", $this->bus_id);
		$condition["is_del"] = array("eq", 1);
		if ($article_id == 0) {
			$con["i.mch_id"] = $mch_id;
			$con["i.id"] = $id;
			$article = db::name("ybmp_information")->alias("i")->join("ybmp_bus_card c", "c.id=i.staff_id", "left")->field("i.id,i.content,i.pic_arr,c.user_name,i.state,c.position,i.article_id,i.staff_id")->where($con)->find();
			$p["mch_id"] = $mch_id;
			$p["is_del"] = 0;
			$peo = db::name("ybmp_bus_card")->field("id,user_name,position")->where($p)->select();
			$p = explode(",", $article["pic_arr"]);
			$ic = array();
			for ($i = 0; $i < count($p); $i++) {
				$ic[$i]["id"] = $i + 1;
				$ic[$i]["url"] = $p[$i];
			}
			$article["pic_arr_temp"] = json_encode($ic);
			$this->assign("user", 1);
			$this->assign("peo", $peo);
		} else {
			$class = db::name("ybmp_article_class")->where("is_dynamic", 1)->where($condition)->select();
			$this->assign("class", $class);
			$article = db::name("ybmp_article")->where("article_id", $article_id)->find();
			$bus_tmpl = db::name("ybmp_business")->where("id", $this->bus_id)->find();
			$this->assign("use", $bus_tmpl["mod_class_id"]);
			$this->assign("user", 2);
		}
		$this->assign("info", $article);
		return view();
	}
	public function show_comment()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$iid = input("param.iid", '');
		$id = input("param.id", '');
		if ($id) {
			$res = db::name("ybmp_information_comments")->where("id", $id)->update(["state" => 2]);
			return AjaxReturn($res);
		}
		$data = db::name("ybmp_information_comments")->alias("c")->join("ybmp_user a", "a.uid=c.user_id", "left")->field("c.details,c.id,c.time,user_headimg,nick_name")->order("c.sort desc")->where(["c.information_id" => $iid, "c.state" => "1"])->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$this->assign("page", $data->render());
		$this->assign("data", $data);
		return view();
	}
	public function get_tickt()
	{
		$a["name"] = json_encode(request()->param(), true);
		db::name("ybmp_depart")->insert($a);
		die("success");
	}
	public function corp_conf()
	{
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		if (isset($_SERVER["PHP_SELF"])) {
			$child_path = $_SERVER["PHP_SELF"];
		} else {
			$child_path = $_SERVER["REQUEST_URI"];
		}
		$child_path = explode("/addons", $child_path);
		if (request()->isAjax() && request()->isPost()) {
			$data["corp_id"] = str_replace(" ", '', request()->post("corp_id"));
			$data["aid"] = str_replace(" ", '', request()->post("aid"));
			$data["wxqrcode"] = str_replace(" ", '', request()->post("wxqrcode"));
			$data["show_sea"] = str_replace(" ", '', request()->post("show_sea"));
			$data["is_power_code"] = str_replace(" ", '', request()->post("is_power_code"));
			$data["asecret"] = str_replace(" ", '', request()->post("asecret"));
			$data["tsecret"] = str_replace(" ", '', request()->post("tsecret"));
			$data["txl_get_pho"] = request()->post("txl_get_pho");
			$data["index_get_pho"] = request()->post("index_get_pho");
			$data["shop_get_pho"] = request()->post("shop_get_pho");
			$data["web_get_pho"] = request()->post("web_get_pho");
			$data["dynamic_get_pho"] = request()->post("dynamic_get_pho");
			$old_s = str_replace(" ", '', request()->post("old_aid"));
			$old_t = str_replace(" ", '', request()->post("old_tsecret"));
			$id = str_replace(" ", '', request()->post("id"));
			$co = db::name("ybmp_corp_conf")->whereNotIn("mch_id", $this->bus_id)->where("tsecret", $data["tsecret"])->count();
			$co += db::name("ybmp_corp_conf")->whereNotIn("mch_id", $this->bus_id)->where("corp_id", $data["corp_id"])->count();
			if ($co >= 1) {
				return AjaxReturnMsg(0, "存在相同企业号");
			}
			$f = $_FILES["wxwx"];
			if ($f && $f["size"] > 8) {
				$type = explode(".", $f["name"]);
				if ($type[1] != "txt") {
					return AjaxReturnMsg(0, "格式无效:" . $type[1]);
				}
				$d["name"] = $f["name"];
				$d["tmp_name"] = $f["tmp_name"];
				$d["zname"] = $_SERVER["DOCUMENT_ROOT"] . "/" . $d["name"];
				move_uploaded_file($d["tmp_name"], $d["zname"]);
				$data["file_path"] = "http://" . $host . "/" . $d["name"];
			}
			if (!empty($id)) {
				$che = db::name("ybmp_corp_conf")->where("id", $id)->field("corp_id,tsecret")->find();
				if (empty($che["corp_id"]) || empty($che["tsecret"])) {
					db::name("ybmp_corp_conf")->where("id", $id)->update($data);
				}
				if ($old_t != $data["tsecret"] || $old_s != $data["corp_id"]) {
					$ch = $this->check_new_token($data["corp_id"], $data["tsecret"], $old_s);
					if ($ch == 1) {
						db::name("ybmp_corp_conf")->where("id", $id)->update($data);
						$ser = new OffwebService();
						if ($ser->all_clear($this->bus_id)) {
							$this->write_log("change_corp_conf");
							$re = $ser->first_get();
							if ($re == "true") {
								return AjaxReturnMsg(1);
							} else {
								db::name("ybmp_corp_conf")->where("id", $id)->update(["tsecret" => $old_t, "corp_id" => $old_s]);
								return AjaxReturnMsg(0, $re);
							}
						}
					} else {
						return AjaxReturnMsg(0, "新参数无法正常获取secret");
					}
				}
				db::name("ybmp_corp_conf")->where("id", $id)->update($data);
				return AjaxReturnMsg(1);
			} else {
				$data["mch_id"] = $this->bus_id;
				$iii = db::name("ybmp_corp_conf")->insertGetId($data);
				if ($iii > 0) {
					$this->write_log("first_set_corp_conf:", $data["corp_id"] . $iii);
					$ser = new OffwebService();
					$re = $ser->first_get();
					if ($re == "true") {
						return AjaxReturnMsg(1);
					} else {
						db::name("ybmp_corp_conf")->where("id", $iii)->delete();
						return AjaxReturnMsg(0, $re . "<br/>请检查企业ID和通讯录Secret是否正确");
					}
				}
				return AjaxReturnMsg(0, "保存失败,请稍后重试");
			}
		} else {
			$mch_id = $this->bus_id;
			$info = db::name("ybmp_corp_conf")->where("mch_id", $mch_id)->find();
			$this->assign("info", $info);
			$this->assign("aipath", "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web.php?mch_id=" . $this->bus_id . "&do=staffer_index");
			$this->assign("bosspath", "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/web.php?mch_id=" . $this->bus_id . "&do=boss_index");
			return view();
		}
	}
	public function check_new_token($id, $to, $old)
	{
		$corpid = $old;
		Cache::set("t_token_" . $corpid, null);
		Cache::set("t_time_" . $corpid, null);
		$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$id}&corpsecret={$to}";
		$token = json_decode(get_url_content($url), true);
		if ($token["errcode"] == 0 && $token["errmsg"] == "ok") {
			return 1;
		} else {
			return 2;
		}
	}
	public function new_reback()
	{
		$mch_id = $this->bus_id;
		$ser = new OffwebService();
		db::name("ybmp_synlog")->insert(["mch_id" => $this->bus_id, "media_id" => $this->uuid . ":date_reback", "create_time" => date("Ymd/His", time())]);
		if ($ser->all_clear($mch_id)) {
			$re = $ser->first_get();
			if ($re == "true") {
				return AjaxReturnMsg(1);
			} else {
				return AjaxReturnMsg(0, $re);
			}
		}
		return AjaxReturnMsg(0, "数据清除异常,请稍后重试");
	}
	public function grant_()
	{
		$ser = new OffwebService();
		echo "<pre>";
		$ser->first_get();
		die;
		$pre = $ser->return_pre_auth();
		$mch_id = $this->bus_id;
		$state = floor(pow($mch_id, floor($mch_id % 10 / 2) == 0 ? 1 : floor($mch_id % 10 / 2)) / 3);
		$url = "https://open.work.weixin.qq.com/3rdapp/install?suite_id=" . $this->app2["SuiteID"] . "&pre_auth_code={$pre}&redirect_uri=http://" . $_SERVER["SERVER_NAME"] . "/addons/yb_mingpian/core/index.php?s=/admin/offweb/grant_reback&state={$state}";
		$this->assign("url", $url);
		return view();
	}
	public function grant_reback()
	{
		$auth_code = request()->param("auth_code");
		$state = request()->param("state");
		if (!empty($auth_code) && !empty($state)) {
			$ser = new OffwebService();
			$dd = $ser->return_prement_code($auth_code, $state);
		}
		return view("offweb/grant");
	}
	public function structure()
	{
		$this->check_corp();
		$batch_syn = request()->post("batch_syn", '');
		if ($batch_syn) {
			$this->write_log("syn_all_update");
			$ser = new OffwebService();
			$a = $ser->syn_all_update($this->bus_id);
			$msg = '';
			if (!empty($a["depart"]["errmsg"]) && ($a["depart"]["errmsg"] = !"ok") || !empty($a["depart"]) && ($a["depart"] = !"ok")) {
				$msg .= "部门同步失败;";
			}
			if (!empty($a["user"]["errmsg"]) && ($a["user"]["errmsg"] = !"ok") || !empty($a["user"]) && ($a["user"] = !"ok")) {
				$msg .= "员工同步失败;";
			}
			$message = '';
			if (intval($a["update"]["depart"]) + intval($a["update"]["staff"]) > 0) {
				$message .= "<br>更新：";
			}
			if ($a["update"]["depart"] > 0) {
				$message .= $a["update"]["depart"] . "个部门;";
			}
			if ($a["update"]["staff"] > 0) {
				$message .= $a["update"]["staff"] . "位成员;";
			}
			if (intval($a["new"]["depart"]) + intval($a["new"]["staff"]) > 0) {
				$message .= "<br>新增：";
			}
			if ($a["new"]["depart"] > 0) {
				$message .= $a["new"]["depart"] . "个部门;";
			}
			if ($a["new"]["staff"] > 0) {
				$message .= $a["new"]["staff"] . "位成员;";
			}
			if (intval($a["del"]["depart"]) + intval($a["del"]["staff"]) > 0) {
				$message .= "<br>删除：";
			}
			if ($a["del"]["depart"] > 0) {
				$message .= $a["del"]["depart"] . "个部门;";
			}
			if ($a["del"]["staff"] > 0) {
				$message .= $a["del"]["staff"] . "位成员;";
			}
			$message = empty($message) ? "同步成功,无更改;" : $message;
			if (empty($msg)) {
				return AjaxReturnMsg(1, $message);
			}
			return AjaxReturnMsg(0, $msg . "请稍后重试!");
		}
		$mid = $this->bus_id;
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$did = request()->param("did", "0");
		$sear = \request()->param("sear_name", '');
		$depart = db::name("ybmp_depart")->field("name,id,pid")->where("mch_id", $mid)->where("is_del", 2)->order("sort desc,pid asc,id asc")->select();
		for ($i = 0; $i < count($depart); $i++) {
			$count = db::name("ybmp_bus_card")->where("mch_id", $mid)->where("did", $depart[$i]["id"])->count();
			$depart[$i]["showgemao"] = $count == 0 ? 3 : $count;
		}
		$counts = db::name("ybmp_bus_card")->where("mch_id", $mid)->count();
		$this->assign("counts", $counts);
		if ($did == 0) {
			$this->assign("now_did", 0);
		} else {
			$con["d.id"] = $did;
			$this->assign("now_did", $con["d.id"]);
		}
		if (!empty($sear)) {
			$con["c.user_name"] = ["like", "%" . $sear . "%"];
		}
		$this->assign("sear_name", $sear);
		$con["c.mch_id"] = $mid;
		$this->assign("depart", $depart);
		$list = db::name("ybmp_bus_card")->alias("c")->join("ybmp_depart d", "c.did=d.id and c.mch_id=d.mch_id", "left")->field("c.id,c.user_name,c.position,d.name,c.head_photo,c.tel,c.radar,c.boss_radar")->where($con)->order("c.radar", "asc")->order("c.sort", "desc")->order("c.id")->paginate(10, false, $config = ["query" => ["s" => $url, "sear_name" => $sear]]);
		$this->assign("structure", $list);
		$this->assign("page", $list->render());
		return view();
	}
	public function get_child()
	{
		$data["pid"] = request()->post("id");
		$data["mch_id"] = $this->bus_id;
		$res = db::name("ybmp_depart")->field("name,id")->where($data)->select();
		if (!empty($res)) {
			return json_encode($res);
		} else {
			return $data["pid"];
		}
	}
	public function add_depart()
	{
		$mid = $this->bus_id;
		$name = request()->param("depart");
		$now = request()->param("now");
		$add_id = request()->param("add_id", '');
		$add_ids = request()->param("add_ids", '');
		$add_name = request()->param("add_name", '');
		$child = request()->param("child", '');
		if (!empty($add_id) && !empty($add_name)) {
			$this->assign("add_name", $add_name);
			$this->assign("add_id", $add_id);
			return view();
		}
		if (request()->isAjax() && request()->isPost()) {
			$now = $now == 0 ? 1 : $now;
			$da["pid"] = empty($child) ? 1 : $now;
			$data["pid"] = empty($child) ? 0 : $now;
			$data["mch_id"] = $mid;
			$data["name"] = $name;
			if (!empty($add_ids)) {
				$ser = new OffwebService();
				$res = $ser->syn_edit_depart($name, -1, 0, $add_ids);
				if ($res == "true") {
					$res = db::name("ybmp_depart")->where("mch_id", $mid)->where("id", $add_ids)->update(["name" => $name]);
					return AjaxReturn($res);
				} else {
					return AjaxReturnMsg(0, $res);
				}
			} else {
				$a = db::name("ybmp_depart")->field("id")->where("mch_id", $mid)->order("id desc")->find();
				$data["id"] = intval($a["id"] + 1);
				$ser = new OffwebService();
				$res = $ser->syn_edit_depart($name, $da["pid"], 0, $data["id"]);
				if ($res == "true") {
					$res = db::name("ybmp_depart")->insert($data);
					return AjaxReturn($res);
				} else {
					return AjaxReturnMsg(0, $res);
				}
			}
		} else {
			$ss = request()->get("ss", "-1");
			$this->assign("now", $ss);
			return view();
		}
	}
	public function on_del()
	{
		if (request()->isPost() && request()->isAjax()) {
			$id = request()->param("id");
			$type = request()->param("type");
			$l = explode(",", $id);
			if ($type == "user") {
				if (count($l) > 1) {
					$da["id"] = ["in", $id];
					$del = '';
					$re = db::name("ybmp_bus_card")->field("UserId")->where($da)->select();
					for ($i = 0; $i < count($re); $i++) {
						$del .= $re[$i]["UserId"] . ",";
					}
					$ser = new OffwebService();
					$res = $ser->syn_del_user(substr($del, 0, strlen($del) - 1), true);
					$where["staff_id"] = ["in", $id];
					$this->write_log("del_user", $id . $res);
				} else {
					$da["id"] = $id;
					$del = db::name("ybmp_bus_card")->field("UserId")->where($da)->find();
					$ser = new OffwebService();
					$res = $ser->syn_del_user($del["UserId"]);
					$where["staff_id"] = $id;
					$this->write_log("del_user", $id . $res);
				}
				if ($res == "true") {
					$da["mch_id"] = $this->bus_id;
					if ($id != 0) {
						db::name("ybmp_bus_card")->where($da)->delete();
						db::name("ybmp_information")->where($where)->where("mch_id", $this->bus_id)->delete();
						db::name("ybmp_follow")->where($where)->delete();
						db::name("ybmp_customer")->where($where)->where("mch_id", $this->bus_id)->delete();
						db::name("ybmp_user_oplog")->where($where)->where("mch_id", $this->bus_id)->delete();
						db::name("ybmp_messages")->where($where)->where("mch_id", $this->bus_id)->delete();
					}
					return AjaxReturn(1);
				} else {
					return AjaxReturnMsg(0, $res);
				}
			} elseif ($type == "depart") {
				$num = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("did", $id)->count();
				$child = db::name("ybmp_depart")->where("mch_id", $this->bus_id)->where("pid", $id)->count();
				if (!empty($num) || !empty($child)) {
					return 0;
				}
				$ser = new OffwebService();
				$res = check_work_err($ser->syn_del_depart($id));
				if ($res == "true") {
					$da["id"] = $id;
					$da["mch_id"] = $this->bus_id;
					db::name("ybmp_depart")->where($da)->delete();
					return 1;
				} else {
					return AjaxReturnMsg(0, $res);
				}
			}
		}
	}
	public function depart_list()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$mch_id = $this->bus_id;
		$list = db::name("ybmp_depart")->alias("d")->join("ybmp_bus_card c", "c.did=d.id and c.mch_id=d.mch_id", "left")->field("d.name,d.id,d.pid,count(c.did) num")->where("d.mch_id", $mch_id)->group("d.id")->order("d.sort desc,d.id")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$ss = request()->get("ss", "-1");
		$this->assign("now", $ss);
		$this->assign("list", $list);
		$this->assign("page", $list->render());
		return view();
	}
	public function radar()
	{
		$this->check_corp();
		$this->check_grant();
		$con["c.mch_id"] = $this->bus_id;
		$con["c.is_del"] = 0;
		$id = request()->post("id");
		$data["radar"] = request()->post("radar");
		if (!empty($id) && !empty($data["radar"])) {
			if ($data["radar"] == 2) {
				$data["radar"] = 1;
				$ch = db::name("ybmp_user_permission")->where("user_id", $this->uuid)->find();
				$no = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("radar", 1)->count();
				if (intval($ch["card_num"]) - 1 < $no && $ch["card_num"] > 0) {
					return AjaxReturnMsg(0, "名片数量不足");
				}
				$data["cid"] = 3;
			} else {
				$data["radar"] = 2;
				$data["boss_radar"] = 2;
				$data["state"] = 0;
			}
			db::name("ybmp_bus_card")->where("id", $id)->update($data);
			return AjaxReturnMsg(1);
		}
		if (!empty($id)) {
			unset($data["radar"]);
			$data["boss_radar"] = request()->post("boss_radar");
			if ($data["boss_radar"] == 2) {
				$ch = db::name("ybmp_user_permission")->where("user_id", $this->uuid)->find();
				$no = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("radar", 1)->count();
				$sa = Db::name("ybmp_bus_card")->where("id", $id)->value("radar");
				if ($sa == 2) {
					if (intval($ch["card_num"]) - 1 < $no && $ch["card_num"] > 0) {
						return AjaxReturnMsg(0, "名片数量不足");
					}
				}
				$data["cid"] = 3;
				$data["radar"] = 1;
				$data["boss_radar"] = 1;
			} else {
				$data["boss_radar"] = 2;
			}
			db::name("ybmp_bus_card")->where("id", $id)->update($data);
			return AjaxReturnMsg(1);
		}
		$list = db::name("ybmp_bus_card")->alias("c")->join("ybmp_depart d", "d.id=c.did and c.mch_id=d.mch_id", "left")->field("c.id,position,user_name,name,wxtel,radar,boss_radar")->where($con)->order("radar", "asc")->select();
		$this->assign("list", $list);
		$pp = db::name("ybmp_corp_conf")->where("mch_id", $this->bus_id)->find();
		$this->assign("picurl", $pp["wxqrcode"]);
		return view();
	}
	public function wx_code()
	{
		$id = Request::instance()->param("id");
		$type = Request::instance()->param("type");
		$id_show = 1;
		if (!empty($type) && $type == "ff") {
			$id_show = 2;
		}
		$rs["code"] = 0;
		$ACCESS_TOKEN = getWxAccessToken($this->bus_id);
		if ($ACCESS_TOKEN["errcode"] == 0) {
			$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
			$post_data = array("scene" => $id, "page" => "yb_mingpian/pages/cardinfo/index");
			$post_data = json_encode($post_data);
			$data = post_data($url, $post_data, false);
			$data2 = json_decode($data, true);
			if (empty($data2)) {
				$result = data_uri($data, "image/png");
				$rs["code_path"] = $result;
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "点击右上角，清理缓存后重试";
			}
		} else {
			$rs = $ACCESS_TOKEN;
			$rs["code"] = 1;
			$rs["msg"] = $ACCESS_TOKEN["msg"];
		}
		$this->assign("data", $rs);
		$this->assign("id", $id);
		$this->assign("id_show", $id_show);
		return view();
	}
	public function batch_edit()
	{
		$id = request()->param("id");
		$this->write_log("del_staff:", $id);
		$con["id"] = ["in", $id];
		$da["is_del"] = 1;
		$res = db::name("ybmp_bus_card")->where($con)->update($da);
		return AjaxReturn($res);
	}
	public function imp_exl()
	{
		$f = $_FILES["exl"];
		if (!empty($f)) {
			$ser = new OffwebService();
			$id = $this->bus_id;
			$type = explode(".", $f["name"]);
			if ($type[1] != "xlsx") {
				return AjaxReturnMsg(0, "请使用原模板！");
			}
			$xlsx = importExecl($f["tmp_name"], 7, 0);
			if ($xlsx[7]["A"] != "姓名") {
				return AjaxReturnMsg(0, "请使用原模板!");
			}
			if (count($xlsx) > 51) {
				return AjaxReturnMsg(0, "单次导入请少于50人!");
			}
			for ($i = 0; $i < count($xlsx) - 3; $i++) {
				$con["mch_id"] = $id;
				$con["name"] = $xlsx[$i + 8]["C"];
				$tel = str_replace(" ", '', $xlsx[$i + 8]["D"]);
				if (!preg_match_all("/^1[356789]\\d{9}\$/", $tel, $check)) {
					return AjaxReturnMsg(0, "绑定手机" . $tel . "格式错误");
				}
				$did = db::name("ybmp_depart")->field("did")->where($con)->find();
				if (empty($did["did"])) {
					return AjaxReturnMsg(0, "不存在" . $con["name"] . "部门");
				}
			}
			$num = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->count();
			$d = 0;
			$rename = '';
			for ($i = 0; $i < count($xlsx) - 3; $i++) {
				$con["mch_id"] = $id;
				$con["name"] = strval($xlsx[$i + 8]["C"]);
				$did = db::name("ybmp_depart")->field("id")->where($con)->find();
				$a = iconv("utf-8", "gb2312", $xlsx[$i + 8]["A"]);
				$a1 = iconv("utf-8", "gb2312", $xlsx[$i + 8]["B"]);
				$data["user_name"] = iconv("gb2312", "utf-8", $a);
				$data["position"] = iconv("gb2312", "utf-8", $a1);
				$data["wxtel"] = $xlsx[$i + 8]["D"];
				$data["tel"] = $xlsx[$i + 8]["E"];
				$data["gender"] = $xlsx[$i + 8]["F"] == "男" ? 1 : 2;
				$data["did"] = $did["id"];
				$data["mch_id"] = $id;
				$data["create_time"] = time();
				$data["UserId"] = strtoascii($data["user_name"] . ($num + $i + 1));
				$res = $ser->syn_add_user($data["UserId"], $data["user_name"], $data["wxtel"], $data["did"], $data["position"], '', '', $data["gender"]);
				if ($res == "true") {
					db::name("ybmp_bus_card")->insert($data);
				} else {
					$d++;
					$rename .= $data["user_name"] . ",";
				}
			}
			if ($d > 0) {
				return AjaxReturnMsg(1, "部分导入成功,以下" . $d . "名成员信息导入失败:" . $rename);
			} else {
				return AjaxReturnMsg(1, "导入成功，共导入" . (count($xlsx) - 3) . "名成员，请及时完善信息");
			}
		}
		$file = EXTEND_PATH . "down_file/import_template.xlsx";
		$url = explode("/addons/", $file);
		if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
			$host = $_SERVER["HTTP_X_REAL_HOST"];
		} else {
			$host = $_SERVER["HTTP_HOST"];
		}
		$file = "http://" . $host . "/addons/" . $url[1];
		$this->assign("file", $file);
		return view();
	}
	public function product()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$type = request()->param("type", '');
		if (!empty($type)) {
			$da["type"] = $type;
		}
		$da["c.mch_id"] = $this->bus_id;
		$info = db::name("ybmp_pro")->alias("c")->join("ybmp_pro_class a", "a.id=c.cid", "left")->field("c.name,c.id,pic,c.create_time,type")->where($da)->paginate(15, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$this->assign("type", $type);
		$this->assign("info", $info);
		$this->assign("page", $info->render());
		return view();
	}
	public function pro_edit()
	{
		$id = request()->param("id", '');
		if (request()->isAjax() && request()->isPost()) {
			$data["pic"] = request()->param("pic");
			$data["name"] = request()->param("name");
			$data["content"] = request()->param("content");
			$data["cid"] = request()->param("cid");
			$data["price"] = intval(request()->param("price"));
			$data["type"] = request()->param("type");
			$id = request()->param("id", '');
			if ($id) {
				db::name("ybmp_pro")->where("id", $id)->update($data);
			} else {
				$data["create_time"] = time();
				$data["mch_id"] = $this->bus_id;
				db::name("ybmp_pro")->insert($data);
			}
			return AjaxReturnMsg(1);
		}
		if ($id) {
			$ii = db::name("ybmp_pro")->where("id", $id)->find();
			$cc = db::name("ybmp_pro_class")->where("mch_id", $this->bus_id)->select();
			$this->assign("clas", $cc);
			$this->assign("info", $ii);
			return view();
		} else {
			$cc = db::name("ybmp_pro_class")->where("mch_id", $this->bus_id)->select();
			$this->assign("clas", $cc);
			return view();
		}
	}
	public function add_pro_clas()
	{
		return view();
	}
	public function pro_class()
	{
		$caodan = request()->param("caodan", '');
		if ($caodan) {
			return view("offweb/add_pro_clas");
		}
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$id = request()->param("id", '');
		if (request()->isAjax() && request()->isPost()) {
			$name = request()->param("name");
			$id = request()->param("id", '');
			if ($id) {
				db::name("ybmp_pro_class")->where("id", $id)->update(["name" => $name]);
			} else {
				db::name("ybmp_pro_class")->insert(["name" => $name, "ctime" => time(), "mch_id" => $this->bus_id]);
			}
			return AjaxReturnMsg(1);
		}
		if ($id) {
			$cc = db::name("ybmp_pro_class")->where("id", $id)->find();
			$this->assign("clas", $cc);
			return view("add_pro_clas");
		} else {
			$cc = db::name("ybmp_pro_class")->where("mch_id", $this->bus_id)->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
			$this->assign("clas", $cc);
			$this->assign("page", $cc->render());
			return view();
		}
	}
	public function del_pro_class()
	{
		$id = request()->param("class_id", '');
		if ($id) {
			db::name("ybmp_pro_class")->where(["mch_id" => $this->bus_id, "id" => $id])->delete();
			return AjaxReturnMsg(1);
		} else {
			return AjaxReturnMsg(0);
		}
	}
	public function language()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$id = request()->get("id");
		$up = request()->get("up");
		if (request()->isAjax() && request()->isPost()) {
			$wid = request()->param("wid", 0);
			$cid = request()->param("cid", 0);
			$vals = request()->param("vals", '');
			$clas = request()->param("clas", '');
			$type = request()->param("type", '');
			if ($type == "w_update") {
				db::name("ybmp_wordpool")->where("id", $wid)->update(["value" => $vals, "class_id" => $clas]);
			}
			if ($type == "w_insert") {
				db::name("ybmp_wordpool")->insert(["value" => $vals, "class_id" => $clas, "mch_id" => $this->bus_id, "create_time" => time()]);
			}
			if ($type == "c_update") {
				db::name("ybmp_wordpool_class")->where("id", $cid)->update(["name" => $vals]);
			}
			if ($type == "c_insert") {
				db::name("ybmp_wordpool_class")->insert(["name" => $vals, "mch_id" => $this->bus_id, "create_time" => time()]);
			}
			return AjaxReturnMsg(1);
		} else {
			if ($up == 1) {
				$class = db::name("ybmp_wordpool_class")->where("mch_id", $this->bus_id)->select();
				$this->assign("type", "w_insert");
				$this->assign("clas", $class);
				return view("add_lang");
			}
			if ($up == 3) {
				$id = request()->get("id");
				if (!empty($id)) {
					$class = db::name("ybmp_wordpool_class")->where("id", $id)->find();
					$this->assign("type", "c_update");
					$this->assign("class", $class);
					return view("add_lang");
				}
				$class = db::name("ybmp_wordpool_class")->where("mch_id", $this->bus_id)->select();
				$this->assign("class_list", $class);
				return view("lang_class");
			}
			if ($up == 4) {
				$this->assign("type", "c_insert");
				return view("add_lang");
			}
			if ($id > 0 && $up == 2) {
				$info = db::name("ybmp_wordpool")->where("id", $id)->find();
				$class = db::name("ybmp_wordpool_class")->where("mch_id", $this->bus_id)->select();
				$this->assign("type", "w_update");
				$this->assign("info", $info);
				$this->assign("clas", $class);
				return view("add_lang");
			} else {
				$list = db::name("ybmp_wordpool")->alias("c")->join("ybmp_wordpool_class a", "c.class_id=a.id", "left")->field("c.id,c.value,a.name,c.class_id,c.create_time")->where("c.mch_id", $this->bus_id)->order("c.id", "desc")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
				$this->assign("list", $list);
				$this->assign("page", $list->render());
				return view();
			}
		}
	}
	public function follow()
	{
		$mch_id = $this->bus_id;
		if (request()->isAjax() && request()->isPost()) {
			$data["text"] = \request()->param("text");
			$id = \request()->param("id");
			$data["mch_id"] = $mch_id;
			if (empty($id)) {
				$res = db::name("ybmp_lang")->insert($data);
			} else {
				$res = db::name("ybmp_lang")->where("id", $id)->update($data);
			}
			return AjaxReturn($res);
		} else {
			$url = request()->query();
			$url = explode("=/", $url);
			$url = explode("&", $url[1]);
			$url = "/" . $url[0];
			$list = db::name("ybmp_lang")->where("mch_id", $mch_id)->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
			$this->assign("list", $list);
			$this->assign("page", $list->render());
			return view();
		}
	}
	public function del_lang()
	{
		$id = request()->param("id");
		$class_id = request()->param("class_id");
		if (!empty($id)) {
			db::name("ybmp_wordpool")->where("id", $id)->delete();
		} else {
			db::name("ybmp_wordpool")->where("class_id", $class_id)->update(["class_id" => 0]);
			db::name("ybmp_wordpool_class")->where("id", $class_id)->delete();
		}
		return AjaxReturnMsg(1);
	}
	public function job()
	{
		$this->check_corp();
		if (request()->isAjax() && request()->isPost()) {
			$id1 = request()->post("id1");
			$id2 = request()->post("id2");
			if ($id1 == $id2) {
				return AjaxReturnMsg(0);
			}
			$userid = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->field("UserId")->where("id", $id1)->find();
			db::name("ybmp_customer")->where("staff_id", $id1)->update(["staff_id" => $id2]);
			db::name("ybmp_user_oplog")->where("staff_id", $id1)->update(["staff_id" => $id2]);
			db::name("ybmp_follow")->where("staff_id", $id1)->update(["staff_id" => $id2]);
			db::name("ybmp_bus_card")->where("id", $id1)->delete();
			$ser = new OffwebService();
			$res = $ser->syn_del_user($userid["UserId"]);
			if ($res == "true") {
				if ($id1 != 0) {
					$this->write_log("del_user_job", $id1);
					$where["staff_id"] = $id1;
					db::name("ybmp_bus_card")->where($where)->where("mch_id", $this->bus_id)->delete();
					db::name("ybmp_information")->where($where)->where("mch_id", $this->bus_id)->delete();
					db::name("ybmp_follow")->where($where)->where("mch_id", $this->bus_id)->delete();
					db::name("ybmp_customer")->where($where)->where("mch_id", $this->bus_id)->delete();
					db::name("ybmp_user_oplog")->where($where)->where("mch_id", $this->bus_id)->delete();
					db::name("ybmp_messages")->where($where)->where("mch_id", $this->bus_id)->delete();
				}
				return AjaxReturnMsg(1);
			} else {
				return AjaxReturnMsg(0, $res);
			}
		} else {
			$id2 = $id1 = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->select();
			$this->assign("id1", $id1);
			$this->assign("id2", $id2);
			return view();
		}
	}
	public function check_secret()
	{
		$needle = \request()->param("secret");
		$corp_id = \request()->param("corp_id");
		if (!empty($needle) && !empty($corp_id)) {
			$co = db::name("ybmp_corp_conf")->whereNotIn("mch_id", $this->bus_id)->where("tsecret", $needle)->count();
			$co += db::name("ybmp_corp_conf")->whereNotIn("mch_id", $this->bus_id)->where("corp_id", $corp_id)->count();
			return $co;
		}
	}
	public function area()
	{
		$big = db::name("ybmp_area")->where("pid", 1)->select();
		$this->assign("big", $big);
		return view();
	}
	public function areaa()
	{
		$id = \request()->param("id");
		return json_encode(db::name("ybmp_area")->where("pid", $id)->select());
	}
	public function getget()
	{
		$a1 = \request()->param("a1");
		$a2 = \request()->param("a2");
		$data["jw1"] = $this->jwd($a1);
		$data["jw2"] = $this->jwd($a2);
		return json_encode($data);
	}
	public function jwd($name)
	{
		$url = "http://api.map.baidu.com/geocoder/v2/?address={$name}&output=json&ak=Vl7HeA2d4hAhIzMpyl3tSCUG2LuvkdVG&callback=showLocation";
		$b = str_replace("/\\s\\r\\n/", '', strval(get_url_content($url)));
		$s = substr($b, 27, strlen($b) - 1);
		$w = json_decode(substr($s, 0, strlen($s) - 1), true);
		return $w["result"]["location"];
	}
	public function navigat()
	{
		$ser = new OffwebService();
		$user = db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->field("values")->order("id", "asc")->find();
		if (empty($user["values"])) {
			$data = $this->first_di();
		} else {
			if (strpos($user["values"], "no_select") !== false && strpos($user["values"], "is_select") !== false) {
				$data = json_decode($user["values"], true);
			} else {
				$data = $this->first_di();
			}
		}
		$data = $ser->navigat_save($data, false);
		$this->assign("list", $data);
		return view();
	}
	public function first_di()
	{
		$page = db::name("ybmp_bus_tmpl")->distinct("type")->where("mch_id", $this->bus_id)->where("default", 1)->order("type", "asc")->field("id,name")->select();
		$data = array(0 => array("name" => "名片", "no_select" => "/yb_mingpian/static/card/icon/white_0.png", "is_select" => "/yb_mingpian/static/card/icon/green_0.png"), 1 => array("name" => "商城", "no_select" => "/yb_mingpian/static/card/icon/white_1.png", "is_select" => "/yb_mingpian/static/card/icon/green_1.png", "page_id" => $page[1]["id"], "page_name" => $page[1]["name"]), 2 => array("name" => "动态", "no_select" => "/yb_mingpian/static/card/icon/white_2.png", "is_select" => "/yb_mingpian/static/card/icon/green_2.png"), 3 => array("name" => "官网", "no_select" => "/yb_mingpian/static/card/icon/white_3.png", "is_select" => "/yb_mingpian/static/card/icon/green_3.png", "page_id" => $page[0]["id"], "page_name" => $page[0]["name"]));
		return $data;
	}
	public function select_img()
	{
		$id = \request()->param("id");
		$val = \request()->param("value");
		$icon = SITE_PATH . "public/upload/navigat/*.png";
		$a = glob($icon);
		$all_img = array();
		for ($i = 0; $i < count($a); $i++) {
			$c = explode("/public/", $a[$i]);
			$all_img[$i] = "public/" . $c[1];
		}
		$this->assign("img", $all_img);
		$this->assign("ids", $id);
		$this->assign("value", $val);
		return view();
	}
	public function select_page()
	{
		$key = \request()->param("key");
		$id = \request()->param("id");
		$name = \request()->param("name");
		if ($key == 1) {
			$re = db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where("type", ["in", "-1,2,3"])->field("id,name,page_type,create_time")->select();
		}
		if ($key == 3) {
			$re = db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->where("type", 1)->field("id,name,page_type,create_time")->select();
		}
		$this->assign("value", $id);
		$this->assign("name", $name);
		$this->assign("ids", $key);
		$this->assign("list", $re);
		return view();
	}
	public function navigat_save()
	{
		$res = request()->post();
		$data[0]["name"] = $res["name0"];
		$data[0]["no_select"] = $res["no_select0"];
		$data[0]["is_select"] = $res["is_select0"];
		$data[1]["name"] = $res["name1"];
		$data[1]["no_select"] = $res["no_select1"];
		$data[1]["is_select"] = $res["is_select1"];
		$data[1]["page_id"] = $res["page_id1"];
		$data[1]["page_name"] = $res["page_name1"];
		$data[2]["name"] = $res["name2"];
		$data[2]["no_select"] = $res["no_select2"];
		$data[2]["is_select"] = $res["is_select2"];
		$data[3]["name"] = $res["name3"];
		$data[3]["no_select"] = $res["no_select3"];
		$data[3]["is_select"] = $res["is_select3"];
		$data[3]["page_id"] = $res["page_id3"];
		$data[3]["page_name"] = $res["page_name3"];
		db::name("ybmp_bus_tmpl")->where("mch_id", $this->bus_id)->update(["default" => 0]);
		db::name("ybmp_bus_tmpl")->where("id", ["in", $res["page_id1"] . "," . $res["page_id3"]])->update(["default" => 1]);
		$ser = new OffwebService();
		$data = $ser->navigat_save($data);
		$id = db::name("ybmp_user_tmpl")->where("mch_id", $this->bus_id)->field("id")->order("id", "asc")->find();
		if (empty($id["id"])) {
			db::name("ybmp_user_tmpl")->insert(["values" => $data, "mch_id" => $this->bus_id]);
		} else {
			db::name("ybmp_user_tmpl")->where("id", $id["id"])->update(["values" => $data]);
		}
		return AjaxReturnMsg(1);
	}
	public function clear_all()
	{
		$conf = db::name("ybmp_corp_conf")->where("mch_id", $this->bus_id)->order("id", "desc")->find();
		db::name("ybmp_synlog")->insert(["mch_id" => $this->bus_id, "media_id" => $this->uuid . ":all_clear", "create_time" => date("Ymd/His", time())]);
		db::name("ybmp_corp_conf")->where("id", $conf["id"])->delete();
		$ser = new OffwebService();
		$ser->all_clear($this->bus_id);
		return 1;
	}
	public function sub_send()
	{
		$list = Db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->value("other");
		if (!empty($list)) {
			$list = json_decode($list, true)["sub_send"];
		}
		if (empty($list) || !isset($list)) {
			$list = $this->send_list;
		}
		$user = Db::name("ybmp_bus_card")->where(["mch_id" => $this->bus_id, "isleader" => 1])->field("id,user_name")->select();
		$this->assign("user", $user);
		$this->assign("list", $list);
		return view();
	}
	public function edit_send()
	{
		if (request()->isPost() && request()->isAjax()) {
			$info = Db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->value("other");
			$list = \request()->param("list");
			$user = \request()->param("user");
			if (!empty($info)) {
				$info = json_decode($info, true);
			}
			$info["sub_send"] = json_decode($list, true);
			$res = Db::name("ybmp_business_about")->where("mch_id", $this->bus_id)->update(["other" => json_encode($info)]);
			$user = explode(",", $user);
			Db::name("ybmp_bus_card")->where(["mch_id" => $this->bus_id, "isleader" => 1])->update(["isleader" => 2]);
			for ($i = 0; $i < count($user); $i++) {
				$res += Db::name("ybmp_bus_card")->where(["mch_id" => $this->bus_id, "id" => $user[$i]])->update(["isleader" => 1]);
			}
			return AjaxReturn($res);
		}
	}
	public function select_user()
	{
		$user = Db::name("ybmp_bus_card")->where(["mch_id" => $this->bus_id, "default" => 2, "radar" => 1])->field("id,user_name")->select();
		$this->assign("list", $user);
		return view();
	}
	public function check_auth_code()
	{
		$res = db::name("ybmp_corp_conf")->where("mch_id", $this->bus_id)->find();
		if ($res["is_power_code"] == 1) {
			return AjaxReturn(1);
		} else {
			return AjaxReturn(2);
		}
	}
}
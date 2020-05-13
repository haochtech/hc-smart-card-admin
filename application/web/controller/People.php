<?php


namespace app\web\controller;

use app\web\service\PeopleData;
use think\Db;
use think\Request;
use app\web\service\QyWechat;
class People extends BaseController
{
	protected $from = array(1 => "搜索", 2 => "扫码", 3 => "分享");
	public function get_user_list()
	{
		$req = Request::instance();
		$rs = array("code" => 0, "info" => array(), "all_num" => 0);
		$uid = $req->param("uid");
		$page = $req->param("page", "1");
		$type = $req->param("type", "4");
		$mch_id = $req->param("mch_id", "4");
		$is_follow = $req->param("is_follow", 2);
		$search_ = $req->param("search_text", '');
		if (!empty($search_)) {
			$search_text["a.nick_name"] = ["like", "%" . $search_ . "%"];
		} else {
			$search_text = 1;
		}
		$staff_id = $this->getSId($uid);
		$ser = new PeopleData();
		$info = $ser->get_list($staff_id, $page, $type, $search_text, $is_follow);
		$lang = db::name("ybmp_lang")->field("class_id,text")->where("mch_id", $mch_id)->select();
		$rs["info"] = $info;
		$rs["follow_lang"] = $lang;
		return json_encode($rs);
	}
	public function del_follow()
	{
		$req = Request::instance();
		$id = $req->param("id");
		db::name("ybmp_follow")->where("id", $id)->delete();
		return 1;
	}
	public function get_user_statis()
	{
		$req = Request::instance();
		$user_id = $req->param("user_id");
		$id = $req->param("staff_user_id");
		$mch_id = $req->param("mch_id", "4");
		$staff_id = $this->getSId($id);
		$rs = array("code" => 0, "info" => array());
		$rs["eazy"] = db::name("ybmp_customer")->alias("c")->join("ybmp_user a", "c.user_id=a.uid", "left")->where("c.user_id", $user_id)->where("c.staff_id", $staff_id)->order("c.create_time", "desc")->field("c.remark,a.user_headimg,c.position,c.corp,c.tel,c.address,c.user_id,c.staff_id,c.id,c.deal")->find();
		if ($rs["eazy"]["remark"] == "昵称") {
			$name = Db::name("ybmp_user")->field("nick_name")->where("uid", $rs["eazy"]["user_id"])->find();
			$rs["eazy"]["remark_name"] = $name["nick_name"];
		} else {
			$rs["eazy"]["remark_name"] = $rs["eazy"]["remark"];
		}
		$rs["eazy"]["is_new_deal"] = $rs["eazy"]["deal"] == 2 ? false : true;
		$info = Db::name("ybmp_follow")->field("follow_time,content,id")->where("staff_id", $staff_id)->where("user_id", $user_id)->order("follow_time", "desc")->select();
		for ($i = 0; $i < count($info); $i++) {
			$info[$i]["time1"] = date("Y-m-d", $info[$i]["follow_time"]);
			$info[$i]["time2"] .= date("H:i:s", $info[$i]["follow_time"]);
		}
		$lang = db::name("ybmp_lang")->field("class_id,text")->where("mch_id", $mch_id)->select();
		$rs["info"] = $info;
		$rs["follow_lang"] = $lang;
		return json_encode($rs);
	}
	public function get_user_detail()
	{
		$req = Request::instance();
		$id = $req->param("id");
		$rs = array("code" => 0, "info" => array());
		$info = Db::name("ybmp_customer")->where("id", $id)->find();
		if ($info["remark"] == "昵称") {
			$name = Db::name("ybmp_user")->field("nick_name")->where("uid", $info["user_id"])->find();
			$info["remark_name"] = $name["nick_name"];
		} else {
			$info["remark_name"] = $info["remark"];
		}
		$info["from"] = $this->from[$info["source"]];
		$info["sax"] = $info["gender"] == 1 ? "男" : "女";
		$info["num"] = ceil((time() - $info["create_time"]) / 86400);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function save_user()
	{
		$req = Request::instance();
		$rs = array("code" => 0, "info" => array());
		$info["remark"] = $req->param("remark_name");
		$info["gender"] = $req->param("gender");
		$info["tel"] = $req->param("tel");
		$info["email"] = $req->param("email");
		$info["corp"] = $req->param("corp");
		$info["position"] = $req->param("position");
		$info["address"] = $req->param("address");
		$info["user_id"] = $req->param("user_id");
		$id = $req->param("id");
		db::name("ybmp_customer")->where("id", $id)->update($info);
		$info["id"] = $id;
		$info["remark_name"] = $info["remark"];
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function save_follow()
	{
		$req = Request::instance();
		$rs = array("code" => 0);
		$info["staff_id"] = $req->param("staff_id");
		$info["user_id"] = $req->param("user_id");
		$info["content"] = $req->param("lang_check");
		$info["follow_time"] = time();
		db::name("ybmp_follow")->insert($info);
		Db::name("ybmp_customer")->where("staff_id", $info["staff_id"])->where("user_id", $info["user_id"])->update(["is_follow" => 1]);
		return json_encode($rs);
	}
	public function dynamic_list()
	{
		$req = Request::instance();
		$rs = array("code" => 0, "info" => array());
		$uid = $req->param("uid");
		$__user = $req->param("user_dynamic", false);
		$is_user = $req->param("is_user", false);
		$page = $req->param("page", "1");
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		if ($__user == 1 || $is_user == 1) {
			$isuser = 1;
		} else {
			$isuser = 0;
		}
		$ser = new PeopleData();
		$info = $ser->return_list($mch_id, $staff_id, $page, $isuser);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function dynamic_del()
	{
		$req = Request::instance();
		$rs = array("code" => 0);
		$uid = $req->param("uid");
		$id = $req->param("id");
		$staff_id = $this->getSId($uid);
		db::name("ybmp_information")->where("staff_id", $staff_id)->where("id", $id)->update(["is_del" => 2]);
		return json_encode($rs);
	}
	public function dynamic_like()
	{
		$req = Request::instance();
		$rs = array("code" => 0);
		$mch_id = $req->param("mch_id");
		$user_id = $req->param("user_id");
		$id = $req->param("id");
		$ch = db::name("ybmp_bus_card_likes")->where("user_id", $user_id)->where("mch_id", $mch_id)->where("type", 2)->where("c_id", $id)->count();
		if ($ch) {
			db::name("ybmp_bus_card_likes")->where("user_id", $user_id)->where("mch_id", $mch_id)->where("type", 2)->where("c_id", $id)->delete();
			$rs["ok"] = "2";
		} else {
			db::name("ybmp_bus_card_likes")->insert(["user_id" => $user_id, "mch_id" => $mch_id, "c_id" => $id, "type" => 2, "create_time" => time(), "op_id" => 2]);
			$rs["ok"] = "1";
		}
		return json_encode($rs);
	}
	public function dynamic_sub()
	{
		$req = Request::instance();
		$rs = array("code" => 0, "info" => array());
		$mch_id = $req->param("mch_id");
		$user_id = $req->param("user_id");
		$id = $req->param("id");
		$comment = $req->param("comment");
		db::name("ybmp_information_comments")->insert(["user_id" => $user_id, "mch_id" => $mch_id, "information_id" => $id, "details" => $comment, "time" => time(), "op_id" => 2]);
		return json_encode($rs);
	}
	public function get_liulan()
	{
		$req = Request::instance();
		$rs = array("code" => 0, "info" => array());
		$user_id = $req->param("user_id");
		$staff_id = $req->param("staff_id");
		$ser = new PeopleData();
		$info = $ser->return_nidaye($user_id, $staff_id);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function send_comment()
	{
		$req = Request::instance();
		$rs = array("code" => 0);
		$uid = $req->param("uid");
		$data["content"] = $req->param("shaya_content");
		$shaya_sid = $req->param("shaya_sid");
		$data["staff_id"] = $this->getSId($uid);
		$data["mch_id"] = $this->get_mchid($uid);
		$data["pic_arr"] = '';
		if (!empty($shaya_sid)) {
			$shaya_sid = json_decode($shaya_sid, true);
			for ($i = 0; $i < count($shaya_sid); $i++) {
				$file_path = $shaya_sid[$i];
				$data["pic_arr"] .= $file_path . ",";
			}
			$data["pic_arr"] = substr($data["pic_arr"], 0, strlen($data["pic_arr"]) - 1);
		}
		$data["create_time"] = time();
		db::name("ybmp_information")->insert($data);
		return json_encode($rs);
	}
	public function upload_pic()
	{
		$rs = array("code" => 0);
		$req = Request::instance();
		$mch_id = $req->param("mch_id");
		$pic_path = Request::instance()->param("pic_path");
		$qyWechat = new QyWechat($mch_id);
		$file_path = $qyWechat->DownloadWeixinFile($pic_path, "dongtai");
		$rs["info"] = $file_path;
		return json_encode($rs);
	}
	public function get_order()
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
		$mch_id = \request()->param("mch_id");
		$uid = \request()->param("uid");
		$uid = $this->getBId($uid);
		$rs = array("code" => 0);
		$staff = db::name("ybmp_bus_card")->alias("c")->field("user_name,id,head_photo,UserId")->where("c.mch_id", $mch_id)->where("c.radar", 1)->order("id", "asc")->select();
		for ($i = 0; $i < count($staff); $i++) {
			$num = db::name("ybmp_customer")->alias("c")->join("ybmp_user s", "s.uid=c.user_id", "left")->where("staff_id", $staff[$i]["id"])->count("distinct user_id");
			if (empty($staff[$i]["head_photo"])) {
				$staff[$i]["head_photo"] = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/public/images/member/group_icon.png";
			}
			if ($num >= 1) {
				$staff[$i]["all_num"] = $num;
			} else {
				$staff[$i]["all_num"] = 0;
			}
		}
		array_multisort(array_column($staff, "all_num"), SORT_DESC, $staff);
		for ($i = 0; $i < count($staff); $i++) {
			if ($staff[$i]["id"] == $uid) {
				$rs["own"] = $staff[$i];
				$rs["own"]["order"] = $i + 1;
			}
		}
		$rs["info"] = $staff;
		return json_encode($rs);
	}
	public function get_staff_user()
	{
		$mch_id = \request()->param("mch_id");
		$staff_id = \request()->param("staff_id");
		$page = \request()->param("page", 1);
		$rs = array("code" => 0);
		$list = db::name("ybmp_customer")->alias("c")->where("staff_id", $staff_id)->where("c.mch_id", $mch_id)->join("ybmp_user u", "c.user_id=u.uid", "left")->group("c.user_id")->order("c.user_id", "desc")->field("c.create_time,c.source,c.remark,user_headimg,nick_name,user_id,staff_id")->page($page, 10)->select();
		$list_num = db::name("ybmp_customer")->alias("c")->where("staff_id", $staff_id)->where("c.mch_id", $mch_id)->join("ybmp_user u", "c.user_id=u.uid", "left")->group("c.user_id")->count();
		$source = array("1" => "搜索", "2" => "扫码", "3" => "分享");
		$list = array_u($list, "user_id");
		for ($i = 0; $i < count($list); $i++) {
			$num = db::name("ybmp_follow")->where("staff_id", $staff_id)->where("user_id", $list[$i]["user_id"])->count();
			$list[$i]["from"] = $source[$list[$i]["source"]];
			$list[$i]["num"] = $num == 0 ? "--" : $num;
			$list[$i]["time"] = date("Y-m-d", $list[$i]["create_time"]);
			$list[$i]["name"] = $list[$i]["remark"] == "昵称" ? $list[$i]["nick_name"] : $list[$i]["remark"];
		}
		$rs["info"] = $list;
		$rs["nums"] = $list_num;
		return json_encode($rs);
	}
	public function get_skins()
	{
		$mch_id = Request::instance()->param("mch_id");
		return $this->get_skin($mch_id);
	}
	public function deal_list()
	{
		$uid = \request()->param("uid", '');
		$mch_id = \request()->param("mch_id", '');
		$page = \request()->param("page", "1");
		$sear = \request()->param("sear", '');
		if (!empty($uid)) {
			$staff_id = $this->getSId($uid);
			$where["c.staff_id"] = $staff_id;
		}
		if (!empty($sear)) {
			$list = 50;
			$where["u.nick_name"] = ["like", "%" . $sear . "%"];
		} else {
			$list = 10;
		}
		$where["c.mch_id"] = $mch_id;
		$where["c.deal"] = 1;
		$where["u.uid"] = [">", 0];
		$li = Db::name("ybmp_customer")->alias("c")->join("ybmp_user u", "c.user_id=u.uid", "left")->field("u.user_headimg head,u.nick_name,u.uid user_id,c.staff_id")->where($where)->page($page, $list)->select();
		$info["info"] = $li;
		$info["deal_all"] = Db::name("ybmp_customer")->alias("c")->join("ybmp_user u", "c.user_id=u.uid", "left")->field("u.user_headimg head,u.nick_name,u.uid user_id,c.staff_id")->where($where)->count();
		$info["num"] = count($info["info"]);
		return json_encode($info);
	}
	public function deal_list_order()
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
		$mch_id = \request()->param("mch_id");
		$uid = \request()->param("uid");
		$staff_id = $this->getBId($uid);
		$staff = Db::name("ybmp_bus_card")->where("mch_id", $mch_id)->field("id staff_id,user_name,head_photo")->select();
		$res = array();
		for ($i = 0; $i < count($staff); $i++) {
			if (empty($staff[$i]["head_photo"])) {
				$staff[$i]["head_photo"] = "https://" . $host . $child_path[0] . "/addons/yb_mingpian/core/public/images/member/group_icon.png";
			}
			$res[$i] = $staff[$i];
			$res[$i]["num"] = Db::name("ybmp_customer")->where("staff_id", $staff[$i]["staff_id"])->where("deal", 1)->count();
			if ($staff[$i]["staff_id"] == $staff_id) {
				$info["own"] = $res[$i];
			}
		}
		array_multisort(array_column($res, "num"), SORT_DESC, $res);
		for ($i = 0; $i < count($res); $i++) {
			if ($res[$i]["staff_id"] == $staff_id) {
				$info["own"]["order"] = $i + 1;
			}
		}
		$info["info"] = $res;
		return json_encode($info);
	}
	public function do_deal()
	{
		$uid = \request()->param("uid", '');
		$staff_id = $this->getSId($uid);
		$user_id = \request()->param("user_id", '');
		$a = Db::name("ybmp_customer")->where("staff_id", $staff_id)->where("user_id", $user_id)->value("deal");
		$s = $a == 1 ? 2 : 1;
		Db::name("ybmp_customer")->where("staff_id", $staff_id)->where("user_id", $user_id)->update(["deal" => $s]);
		$res["code"] = $s;
		return json_encode($res);
	}
}
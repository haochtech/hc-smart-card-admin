<?php


namespace app\admin\service;

use think\db;
use think\Cache;
require_once APP_PATH . "api_common.php";
class OffwebService extends Base
{
	private $wx_conf = array("s_access_token" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token", "pre_auth_code" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token=", "auth_info" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info?suite_access_token=", "corp_token" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token=", "prement_code" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token=", "p_token" => "https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token");
	private $upda = array("add_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=", "edit_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=", "del_depart" => "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=", "batch_depart" => "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceparty?access_token=", "add_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=", "edit_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=", "del_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=", "batch_user" => "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceuser?access_token=", "batchdel_user" => "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=", "upload" => "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token=", "send_msg" => "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=", "send_invite" => "https://qyapi.weixin.qq.com/cgi-bin/batch/invite?access_token=");
	private $user_corp = array("cid" => '', "tsecret" => '', "asecret" => '');
	private $msg_type = array("text" => "文本消息", "image" => "图片消息", "video" => "视频消息", "file" => "文件消息", "textcard" => "文本卡片消息", "news" => "图文消息");
	protected $hold = array(0 => array("type" => "1", "postid" => "2"));
	private $kuai = array(0 => array("from" => "http://m.kuaidi100.com/autonumber/auto?num=", "datial" => "http://m.kuaidi100.com/query?type=&postid=&id=1&valicode=&temp=0.722990631975"));
	function __construct()
	{
		parent::__construct();
		$d = db::name("ybmp_corp_conf")->where("mch_id", $this->bus_id)->find();
		$this->user_corp["cid"] = $d["corp_id"];
		$this->user_corp["tsecret"] = $d["tsecret"];
		$this->user_corp["asecret"] = $d["asecret"];
	}
	public function first_get()
	{
		$token = $this->re_t_token();
		return $this->grant_info($token);
	}
	public function re_t_token()
	{
		$corpid = $this->user_corp["cid"];
		$secret = $this->user_corp["tsecret"];
		$a = Cache::get("t_token" . $corpid);
		$b = Cache::get("t_time_" . $corpid);
		if (!empty($a) && strlen($a) > 10 && $b > time()) {
			$token = Cache::get("t_token" . $corpid);
		} else {
			$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$corpid}&corpsecret={$secret}";
			$token = json_decode(get_url_content($url), true);
			if ($token["errcode"] == 0 && $token["errmsg"] == "ok") {
				Cache::set("t_token_" . $corpid, $token, 7000);
				Cache::set("t_time_" . $corpid, time() + 7000);
			} else {
				return '';
			}
		}
		return $token["access_token"];
	}
	public function sss()
	{
		$param["pre_auth_code"] = $this->return_pre_auth();
		$param["session_info"] = array("appid" => [], "auth_type" => 0);
		$url = "https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token=" . $this->return_token();
		return $this->re_t_token();
	}
	private function grant_info($access_token)
	{
		$d_url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=" . $access_token;
		$ress = json_decode(get_url_content($d_url), true);
		$da = array();
		$list = array();
		for ($i = 0; $i < count($ress["department"]); $i++) {
			$list = $ress["department"][$i];
			$da[$i]["id"] = $list["id"];
			$da[$i]["name"] = $list["name"];
			$da[$i]["sort"] = $list["order"];
			$da[$i]["pid"] = $list["parentid"];
			$da[$i]["mch_id"] = $this->bus_id;
		}
		db::name("ybmp_depart")->insertAll($da);
		$p_url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$access_token}&department_id=" . $ress["department"][0]["id"] . "&fetch_child=1";
		unset($res);
		$res = json_decode(get_url_content($p_url), true);
		$da = array();
		for ($i = 0; $i < count($res["userlist"]); $i++) {
			$list = $res["userlist"][$i];
			$da[$i]["mch_id"] = $this->bus_id;
			$da[$i]["UserId"] = $list["userid"];
			$da[$i]["user_name"] = preg_replace_callback("#\\\\u([0-9a-f]{4})#", "match_string", $list["name"]);
			$da[$i]["did"] = $res["userlist"][$i]["department"][0];
			$da[$i]["position"] = preg_replace_callback("#\\\\u([0-9a-f]{4})#", "match_string", $list["position"]);
			$da[$i]["wxtel"] = $list["mobile"];
			$da[$i]["tel"] = $list["mobile"];
			$da[$i]["email"] = $list["email"];
			$da[$i]["create_time"] = time();
			$da[$i]["head_photo"] = $list["avatar"];
			$da[$i]["status"] = $list["status"];
		}
		db::name("ybmp_bus_card")->insertAll($da);
		$n["errcode"] = $ress["errcode"];
		return check_work_err($n);
	}
	public function save_wechat()
	{
		$token = $this->re_t_token();
		$url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=" . $token;
		$ress = json_decode(get_url_content($url), true);
		$res = $ress["department"];
		array_multisort(array_column($res, "id"), SORT_ASC, $res);
		$now = db::name("ybmp_depart")->where("mch_id", $this->bus_id)->order("id", "asc")->select();
		$d_id = [];
		$d_name = [];
		for ($i = 0; $i < count($now); $i++) {
			array_push($d_id, $now[$i]["id"]);
			array_push($d_name, $now[$i]["name"]);
		}
		$info["new"]["depart"] = 0;
		$info["update"]["depart"] = 0;
		$n_id = '';
		for ($i = 0; $i < count($res); $i++) {
			$n_id .= $res[$i]["id"] . ",";
			if (in_array($res[$i]["id"], $d_id)) {
				$a = db::name("ybmp_depart")->where("mch_id", $this->bus_id)->where("id", $res[$i]["id"])->update(["name" => $res[$i]["name"], "pid" => $res[$i]["parentid"], "sort" => $res[$i]["order"]]);
				if ($a > 0) {
					$info["update"]["depart"]++;
				}
			} else {
				$info["new"]["depart"]++;
				db::name("ybmp_depart")->insert(["name" => $res[$i]["name"], "pid" => $res[$i]["parentid"], "sort" => $res[$i]["order"], "mch_id" => $this->bus_id, "id" => $res[$i]["id"]]);
			}
		}
		$info["del"]["depart"] = db::name("ybmp_depart")->where("mch_id", $this->bus_id)->whereNotIn("id", substr($n_id, 0, strlen($n_id) - 1))->delete();
		$info["depart"] = $ress["errmsg"];
		$depart = $res[0]["id"];
		unset($ress);
		unset($res);
		unset($now);
		$url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=" . $token . "&department_id=" . $depart . "&fetch_child=1";
		$ress = json_decode(get_url_content($url), true);
		$res = $ress["userlist"];
		array_multisort(array_column($res, "mobile"), SORT_ASC, $res);
		$now = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->order("wxtel", "asc")->select();
		$d_userid = [];
		$d_did = [];
		$d_name = [];
		$info["new"]["staff"] = 0;
		$info["update"]["staff"] = 0;
		for ($i = 0; $i < count($now); $i++) {
			array_push($d_userid, $now[$i]["UserId"]);
			array_push($d_name, $now[$i]["name"]);
			array_push($d_did, $now[$i]["did"]);
		}
		$b_userid = '';
		for ($i = 0; $i < count($res); $i++) {
			$b_userid .= $res[$i]["userid"] . ",";
			if (in_array($res[$i]["userid"], $d_userid)) {
				$a = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->where("UserId", $res[$i]["userid"])->update(["mch_id" => $this->bus_id, "user_name" => $res[$i]["name"], "position" => $res[$i]["position"], "head_photo" => $res[$i]["avatar"], "tel" => $res[$i]["mobile"], "phone" => $res[$i]["mobile"], "email" => $res[$i]["email"], "did" => $res[$i]["department"][0], "wxtel" => $res[$i]["mobile"], "sort" => $res[$i]["order"][0], "UserId" => $res[$i]["userid"], "status" => $res[$i]["status"], "gender" => $res[$i]["gender"]]);
				if ($a > 0) {
					$info["update"]["staff"]++;
				}
			} else {
				db::name("ybmp_bus_card")->insert(["mch_id" => $this->bus_id, "user_name" => $res[$i]["name"], "position" => $res[$i]["position"], "head_photo" => $res[$i]["avatar"], "tel" => $res[$i]["mobile"], "phone" => $res[$i]["mobile"], "email" => $res[$i]["email"], "did" => $res[$i]["department"][0], "wxtel" => $res[$i]["mobile"], "create_time" => time(), "sort" => $res[$i]["order"][0], "UserId" => $res[$i]["userid"], "status" => $res[$i]["status"], "gender" => $res[$i]["gender"]]);
				$info["new"]["staff"]++;
			}
		}
		$info["del"]["staff"] = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->whereNotIn("UserId", substr($b_userid, 0, strlen($b_userid) - 1))->delete();
		$info["user"] = $ress["errmsg"];
		return $info;
	}
	public function syn_add_user($userid, $name, $wxtel, $did, $position, $email, $sort = 0, $gender = 1)
	{
		$param["userid"] = $userid;
		$param["name"] = $name;
		if (!empty($wxtel)) {
			$param["mobile"] = $wxtel;
			$url = $this->upda["add_user"] . $this->re_t_token();
			$this->user_invite($userid);
		} else {
			$url = $this->upda["edit_user"] . $this->re_t_token();
		}
		$param["department"] = array();
		array_push($param["department"], $did);
		$param["position"] = $position;
		$param["email"] = $email;
		$param["gender"] = $gender;
		$param["order"] = array();
		array_push($param["order"], $sort);
		$re = $this->post_data($url, $param);
		return check_work_err($re);
	}
	public function syn_del_user($id, $batch = false)
	{
		if ($batch) {
			$param["useridlist"] = explode(",", $id);
			$url = $this->upda["batchdel_user"] . $this->re_t_token();
			$res = $this->post_data($url, $param);
		} else {
			$url = $this->upda["del_user"] . $this->re_t_token() . "&userid=" . $id;
			$s = Db::name("ybmp_bus_card")->where(["mch_id" => $this->bus_id, "UserId" => $id])->value("id");
			$where["staff_id"] = $s;
			$con["mch_id"] = $this->bus_id;
			$res = json_decode(get_url_content($url), true);
		}
		return check_work_err($res);
	}
	public function syn_edit_depart($name, $pid, $sort, $id)
	{
		$param["name"] = $name;
		$param["id"] = $id;
		if ($pid == -1) {
			$url = $this->upda["edit_depart"] . $this->re_t_token();
		} else {
			$url = $this->upda["add_depart"] . $this->re_t_token();
			$param["parentid"] = $pid;
			$param["order"] = $sort;
		}
		$res = $this->post_data($url, $param);
		return check_work_err($res);
	}
	public function syn_del_depart($id)
	{
		$url = $this->upda["del_depart"] . $this->re_t_token() . "&id=" . $id;
		return json_decode(get_url_content($url), true);
	}
	public function syn_all_update($mch_id)
	{
		$token = $this->re_t_token();
		$up_url = $this->upda["upload"] . $token . "&type=file";
		$info = $this->save_wechat();
		return $info;
		$db1 = db::name("ybmp_depart")->where("mch_id", $this->bus_id)->select();
		$db2 = db::name("ybmp_bus_card")->where("mch_id", $this->bus_id)->select();
		$path = SITE_PATH . "public/upload/mch_upload/";
		$file1 = $this->out_file($db1, '', 1);
		$re1 = $this->wx_upload($up_url, $path . $file1);
		$pa["media_id"] = $re1["media_id"];
		$bat = $this->upda["batch_depart"] . $this->re_t_token();
		$info["depart"] = $this->post_data($bat, $pa);
		db::name("ybmp_synlog")->insert(["mch_id" => $mch_id, "media_id" => $re1["media_id"], "filename" => $file1, "create_time" => time(), "jobid" => $info["depart"]["jobid"]]);
		$file2 = $this->out_file($db2, '', 2);
		$re12 = $this->wx_upload($up_url, $path . $file2);
		$media_id["media_id"] = $re12["media_id"];
		$bat = $this->upda["batch_user"] . $this->re_t_token();
		$info["user"] = $this->post_data($bat, $media_id);
		db::name("ybmp_synlog")->insert(["mch_id" => $mch_id, "media_id" => $re12["media_id"], "filename" => $file2, "create_time" => time(), "jobid" => $info["user"]["jobid"]]);
		return $info;
	}
	public function user_invite($user_id)
	{
		$url = $this->upda["send_invite"] . $this->re_t_token();
		$data["user"] = [$user_id];
		$this->post_data($url, $data);
	}
	public function post_data($url, $param, $is_file = false, $return_array = true)
	{
		if (!$is_file && is_array($param)) {
			$param = json_encode($param, true);
		}
		if ($is_file) {
			$header[] = "content-type: multipart/form-data; charset=UTF-8";
		} else {
			$header[] = "content-type: application/json; charset=UTF-8";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		$return_array && ($res = json_decode($res, true));
		return $res;
	}
	public function wx_upload($url = '', $path = '')
	{
		$curl = curl_init();
		if (class_exists("\\CURLFile")) {
			curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
			$data = array("media" => new \CURLFile($path));
		} else {
			curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
			$data = array("media" => "@" . $path);
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, "TEST");
		$result = curl_exec($curl);
		$res = json_decode($result, true);
		return $res;
	}
	public function check_grant($mch_id)
	{
		$a = db::name("ybmp_corp")->where("mch_id", $mch_id)->find();
		if ($a["corpid"]) {
			$url = $this->wx_conf["auth_info"] . $this->return_token();
			$param["auth_corpid"] = $a["corpid"];
			$param["permanent_code"] = $a["prement_code"];
			$data = $this->post_data($url, $param);
			if (!empty($data["auth_corp_info"])) {
				$this->update_grant($data, $mch_id);
				$res["code"] = 1;
			} else {
				db::name("ybmp_corp")->where("mch_id", $mch_id)->update(["grant" => 0]);
				$res["code"] = 2;
			}
		} else {
			$res["code"] = 2;
		}
		return $res;
	}
	public function out_file($data = array(), $name = '', $type = "1", $download = false)
	{
		$path = SITE_PATH . "public/upload/mch_upload/";
		$filename = time() . "_" . $this->bus_id . rand(100, 900) . ".CSV";
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		$ffname = $filename;
		$obj = new \PHPExcel();
		$obj->getActiveSheet(0)->setTitle($name);
		$cellName = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L");
		$_row = 1;
		if ($type == 1) {
			$title = ["部门名称", "部门ID", "父部门ID", "排序"];
			$i = 0;
			foreach ($title as $v) {
				$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
				$i++;
			}
			$_row++;
			foreach ($data as $k => $v) {
				$a = $v["pid"] == 0 ? 1 : $v["pid"];
				$obj->setActiveSheetIndex(0)->setCellValue("A" . ($k + $_row), $v["name"]);
				$obj->setActiveSheetIndex(0)->setCellValue("B" . ($k + $_row), $v["id"]);
				$obj->setActiveSheetIndex(0)->setCellValue("C" . ($k + $_row), $a);
				$obj->setActiveSheetIndex(0)->setCellValue("D" . ($k + $_row), $v["sort"]);
			}
		} elseif ($type == 2) {
			$title = ["姓名", "帐号", "手机号", "邮箱", "所在部门", "职位", "性别", "是否领导", "排序", "别名", "座机", "禁用"];
			$i = 0;
			foreach ($title as $v) {
				$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
				$i++;
			}
			$_row++;
			foreach ($data as $k => $v) {
				$obj->setActiveSheetIndex(0)->setCellValue("A" . ($k + $_row), $v["user_name"]);
				$obj->setActiveSheetIndex(0)->setCellValue("B" . ($k + $_row), $v["UserId"]);
				$obj->setActiveSheetIndex(0)->setCellValue("C" . ($k + $_row), $v["wxtel"]);
				$obj->setActiveSheetIndex(0)->setCellValue("D" . ($k + $_row), $v["email"]);
				$obj->setActiveSheetIndex(0)->setCellValue("E" . ($k + $_row), $v["did"]);
				$obj->setActiveSheetIndex(0)->setCellValue("F" . ($k + $_row), $v["position"]);
				$obj->setActiveSheetIndex(0)->setCellValue("G" . ($k + $_row), '');
				$obj->setActiveSheetIndex(0)->setCellValue("H" . ($k + $_row), '');
				$obj->setActiveSheetIndex(0)->setCellValue("I" . ($k + $_row), '');
				$obj->setActiveSheetIndex(0)->setCellValue("J" . ($k + $_row), '');
				$obj->setActiveSheetIndex(0)->setCellValue("K" . ($k + $_row), $v["tel"]);
				$obj->setActiveSheetIndex(0)->setCellValue("L" . ($k + $_row), '');
			}
		}
		$obj->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
		$obj->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
		if ($download) {
			header("Content-Type:application/csv;charset=UTF-8");
			header("Content-Disposition: attachment;filename=\"" . $filename . "\"");
			header("Cache-Control: No-cache");
		}
		$objWrite = \PHPExcel_IOFactory::createWriter($obj, "CSV");
		$objWrite->setUseBOM(true);
		if ($download) {
			$objWrite->save($path . $filename);
			exit;
		}
		$objWrite->save($path . $filename, true);
		return $ffname;
	}
	public function all_clear($mch_id)
	{
		$con["mch_id"] = $mch_id;
		$staff_id = db::name("ybmp_bus_card")->field("id")->where($con)->select();
		if (empty($staff_id)) {
			return 1;
		}
		$id = '';
		for ($i = 0; $i < count($staff_id); $i++) {
			$id .= $staff_id[$i]["id"] . ",";
		}
		$id = substr($id, 0, strlen($id) - 1);
		if (!empty($id) && $mch_id > 0) {
			$where["staff_id"] = ["in", $id];
			$re = db::name("ybmp_information")->where($where)->field("id")->select();
			$ids = '';
			for ($i = 0; $i < count($re); $i++) {
				$ids .= $re[$i]["id"] . ",";
			}
			$information["information_id"] = ["in", $ids];
			if (!empty($conf["file_path"])) {
				$f = explode("/", $conf["file_path"]);
				$a = $_SERVER["DOCUMENT_ROOT"] . "/" . $f[count($f) - 1];
				if (file_exists($a)) {
					@unlink($a);
				}
			}
			$a[0] = db::name("ybmp_follow")->where($where)->delete();
			$a[1] = db::name("ybmp_customer")->where($where)->where($con)->delete();
			$a[2] = db::name("ybmp_user_oplog")->where($where)->where($con)->delete();
			$a[3] = db::name("ybmp_information")->where($where)->where($con)->delete();
			$a[4] = db::name("ybmp_suggest")->where($where)->where($con)->delete();
			$a[5] = db::name("ybmp_messages")->where($where)->where($con)->delete();
			$a[6] = db::name("ybmp_information_comments")->where($information)->where($con)->delete();
			$a[7] = "ddd";
		}
		$a[8] = db::name("ybmp_bus_card")->where($con)->delete();
		$a[9] = db::name("ybmp_sendcard")->where($con)->delete();
		$a[10] = db::name("ybmp_sendlog")->where($con)->delete();
		$a[11] = db::name("ybmp_order_staff")->where($con)->delete();
		$a[12] = db::name("ybmp_synlog")->where($con)->delete();
		$a[13] = db::name("ybmp_bus_card_likes")->where($con)->delete();
		$a[14] = db::name("ybmp_depart")->where($con)->delete();
		$a[15] = db::name("ybmp_lang")->where($con)->delete();
		$a[16] = db::name("ybmp_offweb_join")->where($con)->delete();
		$a[17] = db::name("ybmp_pro")->where($con)->delete();
		$a[18] = db::name("ybmp_pro_class")->where($con)->delete();
		return 1;
	}
	public function navigat_save($data, $in = true)
	{
		if ($in) {
			foreach ($data as $k => $v) {
				$str = '';
				$a = explode("navigat/", $v["no_select"]);
				$str = "/yb_mingpian/static/card/icon/" . $a[1];
				$data[$k]["no_select"] = $str;
				$a = explode("navigat/", $v["is_select"]);
				$str = "/yb_mingpian/static/card/icon/" . $a[1];
				$data[$k]["is_select"] = $str;
			}
			$data = json_encode($data);
		} else {
			foreach ($data as $k => $v) {
				$str = '';
				$a = explode("/icon/", $v["no_select"]);
				$str = "public/upload/navigat/" . $a[1];
				$data[$k]["no_select"] = $str;
				$a = explode("/icon/", $v["is_select"]);
				$str = "public/upload/navigat/" . $a[1];
				$data[$k]["is_select"] = $str;
			}
		}
		return $data;
	}
	public function get_msg_list($mch_id)
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list_ = db::name("ybmp_sendmsg")->where("mch_id", $mch_id)->order("create_time", "desc")->order("send_time", "desc")->paginate(20, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
		$res = array();
		for ($i = 0; $i < count($list_); $i++) {
			$res["info"][$i] = $list_[$i];
			$res["info"][$i]["type_name"] = $this->msg_type[$list_[$i]["type"]];
			$res["info"][$i]["is_send"] = empty($list_[$i]["send_time"]) ? "-1" : 1;
			if ($list_[$i]["person"] == "@all") {
				$res["info"][$i]["receive"] = "全体成员";
			}
			if ($list_[$i]["person"] != "@all" && $list_[$i]["person"] != "--") {
				$name = db::name("ybmp_bus_card")->field("user_name")->whereIn("id", $list_[$i]["person"])->select();
				for ($j = 0; $j < count($name); $j++) {
					$res["info"][$i]["receive"] .= $name[$j]["user_name"] . ";";
				}
			}
			if ($list_[$i]["depart"] != "@all" && $list_[$i]["depart"] != "--") {
				$depart = db::name("ybmp_depart")->field("name")->where("mch_id", $mch_id)->whereIn("id", $list_[$i]["depart"])->select();
				for ($j = 0; $j < count($depart); $j++) {
					$res["info"][$i]["receive"] .= $depart[$j]["name"] . ";";
				}
			}
		}
		$res["page"] = $list_->render();
		return $res;
	}
	public function send_msg($id)
	{
		$aid = db::name("ybmp_corp_conf")->field("aid")->where("mch_id", $this->bus_id)->find();
		if (empty($aid["aid"])) {
			$res["code"] = 0;
			$res["message"] = "请设置应用ID";
			return $res;
		}
		if (empty($this->user_corp["asecret"])) {
			$res["code"] = 0;
			$res["message"] = "请设置应用secret";
			return $res;
		}
		$url = $this->upda["send_msg"] . $this->re_a_token();
		$msg = db::name("ybmp_sendmsg")->where("id", $id)->find();
		if ($msg["person"] != "--") {
			$data["touser"] = $msg["person"];
		}
		if ($msg["person"] != "@all" && $msg["person"] != "--") {
			$nn = db::name("ybmp_bus_card")->field("UserId")->whereIn("id", $msg["person"])->select();
			$ids = '';
			for ($i = 0; $i < count($nn); $i++) {
				$ids .= $nn[$i]["UserId"] . "|";
			}
			$ids = substr($ids, 0, strlen($ids) - 1);
			$data["touser"] = $ids;
		}
		if ($msg["depart"] != "--") {
			$data["toparty"] = str_replace(",", "|", $msg["depart"]);
		}
		$data["msgtype"] = $msg["type"];
		$data["agentid"] = $aid["aid"];
		if ($msg["type"] == "text") {
			$data["text"] = ["content" => $msg["content"]];
		}
		$cod = check_work_err($this->post_data($url, $data));
		if ($cod == "true") {
			$res["code"] = 1;
			db::name("ybmp_sendmsg")->where("id", $id)->update(["send_time" => time()]);
		} else {
			$res["code"] = 0;
			$res["message"] = $cod;
		}
		return $res;
	}
}
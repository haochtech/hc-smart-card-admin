<?php


namespace app\web\service;

use app\common\model\Config;
use think\Db;
use think\Cache;
require EXTEND_PATH . "php/WXBizMsgCrypt.php";
class PeopleData
{
	public $op = array(1 => "查看", 2 => "转发", 3 => "复制", 4 => "保存", 5 => "拨打", 6 => "浏览");
	private $wx = array("CorpID" => "wwbf62f70c2000d499", "secret" => "8_aQ-QR4pHfNylkgE4_y59Wfbd4ibvNyTe9n0-N7D9o");
	public function get_list($id, $page, $type, $search_text, $is_follow)
	{
		if ($search_text == 1) {
			$list = 100;
		} else {
			$list = 50;
		}
		$info = Db::name("ybmp_customer")->alias("c")->join("ybmp_user a", "c.user_id=a.uid", "left")->join("ybmp_user_oplog o", "c.user_id=o.user_id and c.staff_id=o.staff_id", "left")->join("(select user_id,staff_id,max(create_time) max_time from " . \think\Config::get("database.prefix") . "ybmp_user_oplog group by user_id,staff_id) as b", "o.user_id=b.user_id and o.staff_id=b.staff_id and o.create_time=b.max_time", "right")->where("c.staff_id", $id)->where($search_text)->where(["c.is_follow" => $is_follow, "c.deal" => 2, "(select count(uid) from " . \think\Config::get("database.prefix") . "ybmp_user where uid=c.user_id)" => [">", 0]])->order("o.create_time", "desc")->field("c.remark,a.user_headimg,c.id,c.user_id,o.create_time,a.nick_name")->page($page, $list)->group("a.uid")->select();
		for ($i = 0; $i < count($info); $i++) {
			if ($info[$i]["remark"] == "昵称") {
				$info[$i]["remark_name"] = $info[$i]["nick_name"];
			} else {
				$info[$i]["remark_name"] = $info[$i]["remark"];
			}
			$n = db::name("ybmp_follow")->where("user_id", $info[$i]["user_id"])->where("staff_id", $id)->count();
			$info[$i]["time"] = date("Y年m月d日H时i分", $info[$i]["create_time"]);
			$info[$i]["follow_numb"] = $n;
		}
		return $info;
	}
	public function return_list($mch_id, $staff_id, $page, $isuser)
	{
		if ($isuser) {
			$list_ = Db::name("ybmp_information")->alias("c")->join("ybmp_article a", "a.article_id=c.article_id", "left")->join("ybmp_bus_card d", "c.staff_id=d.id", "left")->field("c.staff_id,c.content u_con,c.id,c.pic_arr,c.create_time,a.title,a.content c_con,a.image,d.user_name,d.head_photo")->where("c.mch_id", $mch_id)->where("c.is_del", 1)->where("c.state", 1)->where("c.staff_id", $staff_id)->order("c.create_time", "desc")->page($page, PAGE_NUM)->select();
		} else {
			$list_ = Db::name("ybmp_information")->alias("c")->join("ybmp_article a", "a.article_id=c.article_id", "left")->join("ybmp_bus_card d", "c.staff_id=d.id", "left")->field("c.staff_id,c.content u_con,c.id,c.pic_arr,c.create_time,a.title,a.content c_con,a.image,d.user_name,d.head_photo")->where("c.mch_id", $mch_id)->where("c.is_del", 1)->where("c.state", 1)->order("c.create_time", "desc")->page($page, PAGE_NUM)->select();
		}
		$d = Db::name("ybmp_information")->getLastSql();
		$res = array();
		$bus_head = Db::name("ybmp_business_about")->field("logo,name")->where("mch_id", $mch_id)->find();
		for ($i = 0; $i < count($list_); $i++) {
			$res[$i] = $list_[$i];
			$res[$i]["is_like"] = false;
			$res[$i]["user_img"] = explode(",", $list_[$i]["pic_arr"]);
			$res[$i]["user_img_num"] = count($res[$i]["user_img"]) == 1 ? 1 : 2;
			if (empty($list_[$i]["pic_arr"])) {
				$res[$i]["user_img_num"] = 0;
			}
			$like_ = Db::name("ybmp_bus_card_likes")->alias("c")->join("ybmp_bus_card a", "c.user_id=a.id", "left")->distinct("a.user_name,c.user_id")->field("a.user_name,c.user_id")->where("c.type", 2)->where("c.op_id", 2)->where("c.c_id", $list_[$i]["id"])->order("c.create_time")->select();
			for ($s = 0; $s < count($like_); $s++) {
				if ($staff_id == $like_[$s]["user_id"]) {
					$res[$i]["is_like"] = true;
					break;
				}
			}
			$comment_ = Db::name("ybmp_information_comments")->where("information_id", $list_[$i]["id"])->count();
			$res[$i]["likes"] = $like_;
			$res[$i]["like_num"] = count($like_);
			$res[$i]["comments_num"] = $comment_;
			$commen_ = Db::name("ybmp_information_comments")->alias("c")->join("ybmp_bus_card a", "c.user_id=a.id", "left")->field("a.user_name,c.details")->where("c.state", 1)->where("c.op_id", 2)->where("c.information_id", $list_[$i]["id"])->order("c.time")->select();
			$res[$i]["comments"] = $commen_;
			if ($list_[$i]["staff_id"] == 0) {
				$res[$i]["staff"] = false;
				$res[$i]["logo"] = $bus_head["logo"];
				$res[$i]["bus_name"] = $bus_head["name"];
			} else {
				$res[$i]["staff"] = true;
			}
			if ($staff_id == $list_[$i]["staff_id"]) {
				$res[$i]["del"] = true;
			} else {
				$res[$i]["del"] = false;
			}
			$time_ = floor((time() - $list_[$i]["create_time"]) / 86400);
			if ($time_ == 0) {
				$res[$i]["time"] = "今天";
			} else {
				if ($time_ <= 15) {
					$res[$i]["time"] = $time_ . "天前";
				} else {
					$res[$i]["time"] = "很久之前";
				}
			}
			$res[$i]["like_staff"] = $staff_id;
			$res[$i]["mch_id"] = $mch_id;
		}
		return $res;
	}
	public function return_nidaye($user_id, $staff_id)
	{
		$res = Db::name("ybmp_user_oplog")->where("user_id", $user_id)->where("staff_id", $staff_id)->order("create_time", "desc")->select();
		$info = array();
		for ($i = 0; $i < count($res); $i++) {
			$info[$i] = $res[$i];
			$info[$i]["op_time"] = date("Y/m/d", $res[$i]["create_time"]);
			$info[$i]["op_time_cao"] = date("H:i", $res[$i]["create_time"]);
			$info[$i]["type"] = $this->op[$res[$i]["op_type"]];
			if ($res[$i]["de_id"] > 0) {
				$e = Db::name("ybmp_goods")->where("goods_id", $res[$i]["de_id"])->value("goods_name");
				$info[$i]["op_name"] = $info[$i]["op_name"] . ":" . $e;
			}
		}
		return $info;
	}
	public function quqiuba()
	{
		$re["id"] = $this->wx["CorpID"];
		$re["timestamp"] = time();
		$re["nonce"] = $this->wx["CorpID"];
		$re["url"] = "http://" . $_SERVER["SERVER_NAME"] . "/addons/yb_mingpian/core/web/message.html?" . $_SERVER["QUERY_STRING"];
		$a = $this->gettickt();
		$re["a"] = $a["ticket"];
		$re["b"] = "jsapi_ticket=" . $a["ticket"] . "&noncestr=" . $re["nonce"] . "&timestamp=" . $re["timestamp"] . "&url=" . $re["url"];
		$re["sign"] = sha1($re["b"]);
		$re["list"] = json_encode(["getLocalImgData", "chooseImage", "uploadImage", "downloadImage", "previewFile"]);
		return $re;
	}
	private function getNonceStr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	private function gettoken()
	{
		if (Cache::get("gagaga_token")) {
			return Cache::get("gagaga_token");
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->wx["CorpID"] . "&secret=" . $this->wx["secret"];
			$wawawa = json_decode($this->get_url_content($url), true);
			Cache::set("gagaga_token", $wawawa, 7200);
			return $wawawa;
		}
	}
	private function gettickt()
	{
		$to = $this->gettoken();
		$url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=" . $to["access_token"];
		$wawawa = json_decode($this->get_url_content($url), true);
		Cache::set("gagaga_tickt", $wawawa, 7200);
		return $wawawa;
	}
	function get_url_content($url, $method = true)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if ($method) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$cont = curl_exec($ch);
		curl_close($ch);
		$cont = mb_convert_encoding($cont, "UTF-8", "UTF-8,GBK,GB2312,BIG5");
		return $cont;
	}
}
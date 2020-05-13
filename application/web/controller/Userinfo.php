<?php


namespace app\web\controller;

use think\Db;
use think\Request;
use think\Session;
use think\Cache;
use think\Log;
use app\web\service\QyWechat;
require_once APP_PATH . "/api_common.php";
class Userinfo extends BaseController
{
	public function GetUserinfo()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$is_poster = Request::instance()->param("is_poster", 0);
		$staff_id = $this->getSId($uid);
		$mch_id = $this->get_mchid($uid);
		$info = Db::name("ybmp_bus_card")->where("id", $staff_id)->where("mch_id", $mch_id)->find();
		if (!empty($info["profile"])) {
			$info["profile"] = strip_tags($info["profile"]);
		}
		if (!empty($info["effect_tag"])) {
			$info["effect_tag"] = json_decode($info["effect_tag"]);
		}
		if (!empty($info["mch_id"])) {
			$mch = Db::name("ybmp_business_about")->where("mch_id", $info["mch_id"])->find();
			if (!empty($mch)) {
				$info["mch_name"] = $mch["name"];
				$info["mch_logo"] = $mch["logo"];
				$info["mch_address"] = $mch["address"];
			}
		}
		if ($is_poster == 1) {
			$rs["info"] = $info;
			return json_encode($rs);
		}
		$info["head_photo"] = $this->fileToBase64($info["head_photo"]);
		$rs["info"] = $info;
		if (!empty($_SESSION["USER_SHARE_CODE1" . $staff_id])) {
			$rs["info"]["share_code"] = $_SESSION["USER_SHARE_CODE1" . $staff_id];
			return json_encode($rs);
		} else {
			$ACCESS_TOKEN = getWxAccessToken($mch_id);
			if ($ACCESS_TOKEN["errcode"] == 0) {
				$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ACCESS_TOKEN["access_token"];
				$post_data = array("scene" => $staff_id, "page" => "yb_mingpian/pages/cardinfo/index");
				$data2 = post_data2($url, $post_data, false);
				$data3 = json_decode($data2, true);
				if (empty($data3)) {
					$result = $this->data_uri($data2, "image/png");
					$rs["info"]["share_code"] = $result;
					$_SESSION["USER_SHARE_CODE1" . $staff_id] = $result;
				}
			}
			return json_encode($rs);
		}
	}
	public function EditUserinfo()
	{
		$rs = array("code" => 0, "info" => array());
		$param = Request::instance()->param();
		$data = ["tel" => $param["tel"], "phone" => $param["phone"], "wechat_number" => $param["wechat_number"], "email" => $param["email"]];
		if (!empty($param["head_photo"]) && $param["head_photo"] != $param["old_head"]) {
			$mch_id = $this->get_mchid_cardid($param["id"]);
			$qyWechat = new QyWechat($mch_id);
			$file_path = $qyWechat->DownloadWeixinFile($param["head_photo"], "userinfo");
			$data["head_photo"] = $file_path;
		}
		$r = Db::name("ybmp_bus_card")->where("id", $param["id"])->update($data);
		if (!$r) {
			$rs["code"] = 1;
			$rs["msg"] = "修改失败！";
			return json_encode($rs);
		}
		$info = Db::name("ybmp_bus_card")->where("id", $param["id"])->find();
		if (!empty($info["profile"])) {
			$info["profile"] = strip_tags($info["profile"]);
		}
		if (!empty($info["effect_tag"])) {
			$info["effect_tag"] = json_decode($info["effect_tag"]);
		}
		if (!empty($info["mch_id"])) {
			$mch = Db::name("ybmp_business_about")->where("mch_id", $info["mch_id"])->find();
			if (!empty($mch)) {
				$info["mch_name"] = $mch["name"];
				$info["mch_logo"] = $mch["logo"];
				$info["mch_address"] = $mch["address"];
			}
		}
		$rs["info"] = $info;
		$rs["msg"] = "修改成功！";
		return json_encode($rs);
	}
	public function GetProposalGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("uid");
		$mch_id = $this->get_mchid($id);
		$card = Db::name("ybmp_bus_card")->where("id", $id)->where("mch_id", $mch_id)->find();
		if (!empty($card["proposal_goods_id"])) {
			$info = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images=i.img_id", "left")->field("g.goods_name,g.description,i.img_cover")->where("g.goods_id", "in", $card["proposal_goods_id"])->select();
			$info_new = [];
			foreach ($info as $v) {
				$v["description"] = strip_tags($v["description"]);
				$info_new[] = $v;
			}
			$rs["info"] = $info_new;
		} else {
			$rs["info"] = [];
		}
		if (!empty($card["proposal_product_id"])) {
			$info = Db::name("ybmp_product")->where("id", "in", $card["proposal_product_id"])->where("status", 1)->field("title,content,image")->select();
			$info_new = [];
			for ($i = 0; $i < count($info); $i++) {
				$info_new["goods_name"] = $info[$i]["title"];
				$info_new["description"] = strip_tags($info[$i]["content"]);
				$info_new["img_cover"] = $info[$i]["image"];
				array_push($rs["info"], $info_new);
			}
		}
		return json_encode($rs);
	}
	public function GetGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("uid");
		$page = Request::instance()->param("page", 1);
		$nav = Request::instance()->param("nav", 0);
		$card = Db::name("ybmp_bus_card")->where("id", $id)->find();
		if (!empty($card["proposal_goods_id"])) {
			$proposal_goods_id = explode(",", $card["proposal_goods_id"]);
		}
		if (!empty($card["proposal_product_id"])) {
			$proposal_product_id = explode(",", $card["proposal_product_id"]);
		}
		$where["g.mch_id"] = ["eq", $card["mch_id"]];
		if ($nav == 3) {
			$where["g.goods_id"] = ["in", $proposal_goods_id];
			$where2["p.id"] = ["in", $proposal_product_id];
			$where2["p.mch_id"] = ["eq", $card["mch_id"]];
		} elseif ($nav == 1) {
			$where["g.state"] = ["eq", 1];
		} elseif ($nav == 2) {
			$where["g.status"] = ["eq", 1];
		}
		if ($nav == 0) {
			$goods = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images=i.img_id", "left")->field("g.*,i.img_cover")->where($where)->page($page, PAGE_NUM)->order("goods_id", "desc")->select();
		} elseif ($nav == 2) {
			$goods = Db::name("ybmp_product")->where($where)->alias("g")->field("g.id goods_id,g.status state,g.create_time sale_date,g.image img_cover,g.title goods_name,3 tui")->page($page, PAGE_NUM)->select();
		}
		if ($nav == 3) {
			$goods = Db::name("ybmp_goods")->alias("g")->join("ybmp_images i", "g.images=i.img_id", "left")->field("g.*,i.img_cover")->where($where)->page($page, PAGE_NUM)->order("goods_id", "desc")->select();
			$goods2 = Db::name("ybmp_product")->where($where2)->alias("p")->field("p.id goods_id,p.status state,p.create_time sale_date,p.image img_cover,p.title goods_name,3 tui")->page($page, PAGE_NUM)->select();
			for ($i = 0; $i < count($goods2); $i++) {
				array_push($goods, $goods2[$i]);
			}
		}
		if (count($goods) <= 0 && $nav != 3) {
			$rs["info"] = [];
			return json_encode($rs);
		}
		$goods_new = [];
		foreach ($goods as $v) {
			if (in_array($v["goods_id"], $proposal_goods_id) || in_array($v["goods_id"], $proposal_product_id)) {
				$v["state_name"] = "推荐";
				$v["state"] = 2;
			} else {
				$v["state_name"] = $v["state"] == 1 ? "已发布" : "已下架";
			}
			if (empty($v["price"])) {
				$v["price"] = "展示产品";
			}
			if (empty($v["tui"])) {
				$v["tui"] = 2;
			}
			$v["sale_date"] = date("Y-m-d", $v["sale_date"]);
			$goods_new[] = $v;
		}
		$rs["info"] = $goods_new;
		return json_encode($rs);
	}
	public function ProposalGoods()
	{
		$rs = array("code" => 0, "info" => array());
		$id = Request::instance()->param("uid");
		$goods_id = Request::instance()->param("goods_id", 1);
		$tui = Request::instance()->param("tui", 2);
		$proposal = Request::instance()->param("proposal", 0);
		$card = Db::name("ybmp_bus_card")->where("id", $id)->find();
		if (!empty($card["proposal_goods_id"])) {
			$proposal_goods_id_arr = explode(",", $card["proposal_goods_id"]);
		}
		if (!empty($card["proposal_product_id"])) {
			$proposal_product_id_arr = explode(",", $card["proposal_product_id"]);
		}
		if ($tui == 2) {
			if ($proposal == 2) {
				$proposal_goods_id_arr = array_diff($proposal_goods_id_arr, [$goods_id]);
				$proposal_goods_id = implode(",", $proposal_goods_id_arr);
			} else {
				if (count($proposal_goods_id_arr) <= 0) {
					$proposal_goods_id = $goods_id;
				} else {
					array_push($proposal_goods_id_arr, $goods_id);
					$proposal_goods_id = implode(",", $proposal_goods_id_arr);
				}
			}
			$r = Db::name("ybmp_bus_card")->where("id", $id)->update(["proposal_goods_id" => $proposal_goods_id]);
		}
		if ($tui == 3) {
			if ($proposal == 2) {
				$proposal_product_id_arr = array_diff($proposal_product_id_arr, [$goods_id]);
				$proposal_goods_id = implode(",", $proposal_product_id_arr);
			} else {
				if (count($proposal_product_id_arr) <= 0) {
					$proposal_goods_id = $goods_id;
				} else {
					array_push($proposal_product_id_arr, $goods_id);
					$proposal_goods_id = implode(",", $proposal_product_id_arr);
				}
			}
			$r = Db::name("ybmp_bus_card")->where("id", $id)->update(["proposal_product_id" => $proposal_goods_id]);
		}
		if (!$r) {
			$rs["code"] = 1;
			$rs["msg"] = "修改失败！";
			return json_encode($rs);
		}
		$rs["msg"] = "修改成功！";
		return json_encode($rs);
	}
	public function GetWxConfig()
	{
		$rs = array("code" => 0, "info" => array());
		$url = Request::instance()->param("url", '');
		$uid = Request::instance()->param("uid", '');
		$qyWechat = new QyWechat($this->get_mchid($uid));
		$rs["info"] = $qyWechat->GetSignPackage($url);
		return json_encode($rs);
	}
	public function EditUserSummary()
	{
		$rs = array("code" => 0, "info" => array());
		$param = Request::instance()->param();
		$data = ["profile" => $param["profile"], "effect_tag" => $param["effect_tag"], "wall_photo" => $param["wall_photo"]];
		if (!empty($param["wall_photo"])) {
			$wall_photo = json_decode($param["wall_photo"], true);
			if (is_array($wall_photo)) {
				$wall_photo_arr = [];
				$mch_id = $this->get_mchid_cardid($param["id"]);
				$qyWechat = new QyWechat($mch_id);
				$card = Db::name("ybmp_bus_card")->where("id", $param["id"])->find();
				$wall_photo_old_arr = [];
				if (!empty($card["wall_photo"])) {
					$wall_photo_old_arr = json_decode($card["wall_photo"], true);
				}
				$wall_photo_new_arr = [];
				foreach ($wall_photo as $v) {
					if (strlen($v) <= 2) {
						array_push($wall_photo_new_arr, $wall_photo_old_arr[$v]);
						continue;
					}
					$file_path = $qyWechat->DownloadWeixinFile($v, "userinfo");
					array_push($wall_photo_arr, $file_path);
				}
				$wall_photo_arr = array_merge($wall_photo_arr, $wall_photo_new_arr);
				$data["wall_photo"] = json_encode($wall_photo_arr);
			} else {
				$data["wall_photo"] = '';
			}
		} else {
			$data["wall_photo"] = '';
		}
		$data["vioce_profile"] = $param["vioce_profile"];
		$r = Db::name("ybmp_bus_card")->where("id", $param["id"])->update($data);
		if (!$r) {
			$rs["code"] = 1;
			$rs["msg"] = "修改失败！";
			return json_encode($rs);
		}
		$info = Db::name("ybmp_bus_card")->where("id", $param["id"])->find();
		if (!empty($info["profile"])) {
			$info["profile"] = strip_tags($info["profile"]);
		}
		if (!empty($info["effect_tag"])) {
			$info["effect_tag"] = json_decode($info["effect_tag"]);
		}
		if (!empty($info["mch_id"])) {
			$mch = Db::name("ybmp_business_about")->where("mch_id", $info["mch_id"])->find();
			if (!empty($mch)) {
				$info["mch_name"] = $mch["name"];
				$info["mch_logo"] = $mch["logo"];
				$info["mch_address"] = $mch["address"];
			}
		}
		$rs["info"] = $info;
		$rs["msg"] = "修改成功！";
		return json_encode($rs);
	}
	public function uploadVoice()
	{
		$rs = array("code" => 0, "info" => array());
		$uid = Request::instance()->param("uid");
		$Record_serverId = Request::instance()->param("Record_serverId");
		if (empty($Record_serverId)) {
			$rs["code"] = 1;
			$rs["msg"] = "录音下载失败";
			return json_encode($rs);
		}
		$mch_id = $this->get_mchid($uid);
		$qyWechat = new QyWechat($mch_id);
		$file_path = $qyWechat->DownloadWeixinFile($Record_serverId, "voice", "amr");
		$file_path = amr2mp3($file_path, 0, 2, true);
		if (empty($file_path)) {
			$rs["code"] = 1;
			$rs["msg"] = "录音下载失败";
		} else {
			if (strpos($file_path, "download") && strpos($file_path, "online-convert")) {
				$con = get_url_content2($file_path);
				$path = SITE_PATH . "public/upload/vioce/";
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$name = $path . time() . rand(1000, 9999) . "_arliki.mp3";
				file_put_contents($name, $con);
				$vioce_profile = explode("/core/", $name);
				$file_path = get_child_url2(false) . "addons/yb_mingpian/core/" . $vioce_profile[1];
			}
		}
		$rs["info"] = $file_path;
		return json_encode($rs);
	}
	public function savejxhaibao()
	{
		$img = Request::instance()->param("img");
		$_SESSION["JX_HAIBAO"] = $img;
		$rs["code"] = 0;
		return json_encode($rs, true);
	}
	public function gethaibao()
	{
		exit($_SESSION["JX_HAIBAO"]);
	}
	public function randbgimg()
	{
		$mch_id = \request()->param("mch_id");
		$list = Db::name("ybmp_poster_source")->field("pic_url")->where("is_del", 1)->where("mch_id", $mch_id)->select();
		if (!empty($list)) {
			$index = mt_rand(0, count($list) - 1);
			$img = $list[$index]["pic_url"];
			$img = $this->fileToBase64($img);
			exit($img);
		} else {
			exit(" ");
		}
	}
	function post_data($url, $param, $return_array = true, $is_file = false)
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
}
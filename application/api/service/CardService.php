<?php


namespace app\api\service;

use think\Db;
use think\Exception;
use think\log;
require_once BASE_ROOT . "core/extend/DecryptXCX/wxBizDataCrypt.php";
class CardService
{
	private $g = "ybmp_goods";
	private $i = "ybmp_images";
	private $u = "ybmp_user";
	private $b = "ybmp_business";
	private $ba = "ybmp_business_about";
	private $a = "ybmp_article";
	private $source = array("1" => "搜索", "2" => "扫码", "3" => "分享");
	public function get_CardList($data, $page)
	{
		$de_card = Db::name("ybmp_bus_card")->where(["radar" => 1, "mch_id" => $data["mch_id"], "default" => 2])->order("is_head", "asc")->order("sort", "desc")->page($page, PAGE_NUM)->select();
		if (!empty($de_card)) {
			foreach ($de_card as $k1 => $v1) {
				$count = Db::name("ybmp_customer")->where(["user_id" => $data["user_id"], "staff_id" => $v1["id"]])->count();
				if ($count == 0) {
					$cus_data = [];
					$time = time();
					$where2["mch_id"] = $data["mch_id"];
					foreach ($de_card as $k => $v) {
						$where2["user_id"] = $data["user_id"];
						$where2["staff_id"] = $v["id"];
						if (!empty($data["user_id"])) {
							$num2 = Db::name("ybmp_customer")->where($where2)->count();
							if ($num2 == 0) {
								$cus_data[$k]["user_id"] = $data["user_id"];
								$cus_data[$k]["staff_id"] = $v["id"];
								$cus_data[$k]["source"] = 1;
								$cus_data[$k]["state"] = 1;
								$cus_data[$k]["create_time"] = $time;
								$cus_data[$k]["mch_id"] = $data["mch_id"];
							}
						}
					}
					if (!empty($cus_data)) {
						Db::name("ybmp_customer")->insertAll($cus_data);
					}
				}
			}
		}
		$where["cu.user_id"] = $data["user_id"];
		$where["cu.mch_id"] = $data["mch_id"];
		$where["ca.is_del"] = 0;
		$where["ca.radar"] = 1;
		$list = Db::name("ybmp_customer")->alias("cu")->join("ybmp_bus_card ca", "cu.staff_id=ca.id", "right")->where($where)->field("cu.is_like,cu.user_id,cu.is_like,cu.source,cu.create_time as add_time,ca.*")->page($page, PAGE_NUM)->order("ca.is_head", "asc")->order("ca.sort", "desc")->order("cu.id", "asc")->group("ca.id")->select();
		if (!empty($list)) {
			$new_list = [];
			foreach ($list as $k => $v) {
				$list[$k]["wd_num"] = Db::name("ybmp_messages")->where(["staff_id" => $v["id"], "user_id" => $v["user_id"], "type" => 2, "status" => 0])->count();
				$list[$k]["mch_name"] = Db::name($this->ba)->where("mch_id", $data["mch_id"])->value("name");
				$list[$k]["add_time"] = $this->__TIME($list[$k]["add_time"]);
				$list[$k]["source"] = $this->source[$v["source"]];
			}
		}
		return $list;
	}
	public function get_CardInfo($data)
	{
		$data["is_del"] = 0;
		$data["radar"] = 1;
		$info = Db::name("ybmp_bus_card")->where($data)->find();
		if (empty($info)) {
			return null;
		}
		$goods_info = array();
		$info["pic_arr"] = [];
		$info["goods"] = [];
		$info["zan"] = [];
		if (!empty($info)) {
			if (empty($info["cid"])) {
				$info["cid"] = 2;
			}
			Db::name("ybmp_bus_card")->where("id", $data["id"])->setInc("click");
			$info["bus"] = Db::name($this->ba)->where("mch_id", $data["mch_id"])->field("name,logo,address")->find();
			$info["create_time"] = $this->__TIME($info["create_time"]);
			$info["update_time"] = $this->__TIME($info["update_time"]);
			if (!empty($info["wall_photo"])) {
				$info["pic_arr"] = json_decode($info["wall_photo"], true);
			}
			if (!empty($info["effect_tag"])) {
				$info["effect_tag"] = json_decode($info["effect_tag"], true);
				$info["effect_tag_num"] = count($info["effect_tag"]);
			} else {
				$info["effect_tag"] = [];
			}
			if ($info["proposal_goods_id"]) {
				$goods_id = explode(",", $info["proposal_goods_id"]);
				foreach ($goods_id as $key => $value) {
					$goods_info[$key] = Db::name($this->g)->field("goods_id,goods_name,price,images,introduction")->where("goods_id", $value)->where("is_del", 0)->find();
					if (empty($goods_info[$key])) {
						unset($goods_info[$key]);
						continue;
					}
					$pic = Db::name($this->i)->where("img_id", $goods_info[$key]["images"])->value("img_cover");
					$goods_info[$key]["pic"] = $pic;
					$goods_info[$key]["type"] = "goods";
				}
				if (!empty($goods_info)) {
					$info["goods_num"] = count($goods_info);
					$info["goods"] = $goods_info;
				}
			}
			$goods_info2 = array();
			if ($info["proposal_product_id"]) {
				$goods_id = explode(",", $info["proposal_product_id"]);
				foreach ($goods_id as $key => $value) {
					$goods_ = Db::name("ybmp_product")->field("id goods_id,title goods_name,image")->where("id", $value)->where("status", 1)->find();
					if (empty($goods_)) {
						unset($goods_);
						continue;
					}
					$goods_["pic"] = $goods_["image"];
					$goods_["price"] = "展示产品";
					$goods_["type"] = "product";
					if (!empty($goods_)) {
						array_push($goods_info2, $goods_);
					}
				}
				$info["products_num"] = count($goods_info2);
				$info["products"] = $goods_info2;
			}
			$zan = Db::name("ybmp_user_oplog")->alias("c")->join($this->u . " u", "u.uid=c.user_id")->field("c.*,u.user_headimg")->where(["c.staff_id" => $data["id"]])->order("c.id", "desc")->group("c.user_id")->limit(10)->select();
		}
		if (!empty($zan)) {
			$info["zan"] = $zan;
		}
		$info["show_sea"] = Db::name("ybmp_corp_conf")->where("mch_id", $data["mch_id"])->value("show_sea");
		$red_conf = Db::name("ybmp_red")->where("mch_id", $data["mch_id"])->find();
		if ($red_conf["show_big"] == 1) {
			$info["show_red"] = 1;
		} else {
			$info["show_red"] = 0;
		}
		$info["red_logo"] = $red_conf["status"];
		$info["rid"] = $red_conf["id"];
		$info["cid_img"] = '';
		if ($info["cid"] == 5) {
			$info["cid"] = 3;
			$aq = Db::name("ybmp_config")->where("mch_id", $data["mch_id"])->value("value");
			$dw = json_decode($aq, true);
			$info["cid_img"] = $dw["card_img"];
		}
		return $info;
	}
	public function get_SaveEffectTag($data)
	{
		$data["is_del"] = 0;
		$new_data = $data["data"];
		unset($data["data"]);
		$rs = Db::name("ybmp_bus_card")->where($data)->update(["effect_tag" => $new_data]);
		return $rs;
	}
	public function get_Zan($data)
	{
		$info = 1;
		$num = Db::name("ybmp_bus_card_likes")->where($data)->count();
		if ($data["type"] == 2) {
			if ($num > 0) {
				return null;
			}
		}
		Db::startTrans();
		try {
			if ($num > 0) {
				$a = Db::name("ybmp_bus_card_likes")->where($data)->delete();
				if ($data["type"] == 1) {
					$info = Db::name("ybmp_bus_card")->where("id", $data["c_id"])->setDec("likes");
				}
				if (empty($a) || empty($info)) {
					throw new Exception("操作失败");
				}
			} else {
				$data["create_time"] = time();
				$data["op_id"] = 1;
				$a = Db::name("ybmp_bus_card_likes")->insert($data);
				if ($data["type"] == 1) {
					$info = Db::name("ybmp_bus_card")->where("id", $data["c_id"])->setInc("likes");
				}
				if (empty($a) || empty($info)) {
					throw new Exception("操作失败");
				}
			}
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return null;
		}
		return $info;
	}
	public function get_isLike($data)
	{
		$num = Db::name("ybmp_bus_card_likes")->where($data)->count();
		return $num;
	}
	public function get_message($data, $page)
	{
		$data["is_del"] = 1;
		$data["state"] = 1;
		$list = Db::name("ybmp_information")->where($data)->page($page, PAGE_NUM)->order(["sort" => "desc", "id" => "desc"])->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				Db::name("ybmp_information")->where("id", $v["id"])->setInc("click");
				if ($v["staff_id"] == 0) {
					$name = Db::name($this->ba)->where("mch_id", $data["mch_id"])->field("name,logo")->find();
				} else {
					$name = Db::name("ybmp_bus_card")->where("id", $v["staff_id"])->field("user_name,position,head_photo as logo")->find();
				}
				if ($v["pic_arr"]) {
					$list[$k]["pic_arr"] = explode(",", $v["pic_arr"]);
				} else {
					$list[$k]["pic_arr"] = [];
				}
				$list[$k]["mem_info"] = $name;
				if ($v["article_id"] != 0) {
					$list[$k]["article"] = Db::name($this->a)->where(["article_id" => $v["article_id"]])->field("title,image,link")->find();
				}
				$zan = Db::name("ybmp_bus_card_likes")->where(["c_id" => $v["id"], "type" => 2])->order("id", "desc")->select();
				if (!empty($zan)) {
					foreach ($zan as $k2 => $v2) {
						if ($v2["op_id"] == 1) {
							$user1 = Db::name("ybmp_user")->where("uid", $v2["user_id"])->find();
							$zan[$k2]["user_headimg"] = $user1["user_headimg"];
							$zan[$k2]["nick_name"] = $user1["nick_name"];
						} else {
							$user2 = Db::name("ybmp_bus_card")->where("id", $v2["user_id"])->find();
							$zan[$k2]["user_headimg"] = $user2["head_photo"];
							$zan[$k2]["nick_name"] = $user2["user_name"];
						}
					}
					$list[$k]["zan"] = $zan;
				} else {
					$list[$k]["zan"] = [];
				}
				$comment = Db::name("ybmp_information_comments")->where(["information_id" => $v["id"], "state" => 1])->order(["sort" => "desc", "id" => "desc"])->select();
				if (!empty($comment)) {
					foreach ($comment as $k3 => $v3) {
						if ($v3["op_id"] == 1) {
							$user3 = Db::name("ybmp_user")->where("uid", $v3["user_id"])->find();
							$comment[$k3]["user_headimg"] = $user3["user_headimg"];
							$comment[$k3]["nick_name"] = $user3["nick_name"];
						} else {
							$user3 = Db::name("ybmp_bus_card")->where("id", $v3["user_id"])->find();
							$comment[$k3]["user_headimg"] = $user3["head_photo"];
							$comment[$k3]["nick_name"] = $user3["user_name"];
						}
					}
					$list[$k]["comment"] = $comment;
				} else {
					$list[$k]["comment"] = [];
				}
			}
		}
		return $list;
	}
	public function get_Comment($data)
	{
		$data["time"] = time();
		$data["state"] = 1;
		$data["op_id"] = 1;
		$data["reply"] = '';
		$data["hf_time"] = '';
		$info = Db::name("ybmp_information_comments")->insert($data);
		return $info;
	}
	public function delete_Comment($data)
	{
		$data2["tatus"] = 2;
		$data2["id"] = $data["id"];
		$info = Db::name("ybmp_information_comments")->update($data);
		return $info;
	}
	public function save_behavior($data)
	{
		file_put_contents("dadada.json", json_encode($data) . PHP_EOL, 8);
		if (empty($data["user_id"]) || empty($data["staff_id"])) {
			return null;
		}
		if ($data["staff_id"] == 0) {
			return null;
		}
		$cou = Db::name("ybmp_user")->where(["uid" => $data["user_id"], "mch_id" => $data["mch_id"]])->count();
		if (empty($cou) || $cou == 0) {
			return null;
		}
		$num = Db::name("ybmp_user_oplog")->where($data)->count();
		$data["create_time"] = time();
		$data["times"] = $num + 1;
		$id = Db::name("ybmp_user_oplog")->insertGetId($data);
		return $id;
	}
	public function SaveCard($data, $fenfa_id)
	{
		$where_log = ["staff_id" => $data["staff_id"], "user_id" => $data["user_id"], "mch_id" => $data["mch_id"]];
		if ($data["user_id"] == '' || $data["user_id"] == 0) {
			return null;
		}
		if (empty($data["user_id"])) {
			return null;
		}
		if ($data["source"] > 3 || $data["source"] < 2) {
			return null;
		}
		$where = $data;
		unset($where["source"]);
		$num = Db::name("ybmp_customer")->where($where)->count();
		if ($num > 0) {
			return null;
		}
		$where_log["aid"] = $fenfa_id;
		$data["state"] = 1;
		$data["create_time"] = time();
		$id = Db::name("ybmp_customer")->insertGetId($data);
		if ($id && $fenfa_id > 0) {
			$num2 = Db::name("ybmp_sendlog")->where($where_log)->count();
			if ($num2 == 0) {
				$where_log["user_type"] = 1;
				$where_log["update_time"] = time();
				Db::name("ybmp_sendlog")->insert($where_log);
			}
		}
		return $id;
	}
	public function timely_news($data)
	{
		$a = Db::name("ybmp_messages")->where($data)->order("id desc")->limit(100)->select();
		$count = Db::name("ybmp_messages")->where($data)->count();
		$data["id"] = array(">", intval($a[$count - 1]["id"]) - 1);
		$list = Db::name("ybmp_messages")->where($data)->order("id")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				Db::name("ybmp_messages")->where(["id" => $v["id"], "status" => 0, "type" => 2])->update(["status" => 1]);
				$list[$k]["user_pic"] = Db::name("ybmp_user")->where("uid", $v["user_id"])->value("user_headimg");
				$pic = Db::name("ybmp_bus_card")->where("id", $v["staff_id"])->value("head_photo");
				$list[$k]["staff_pic"] = $pic ? $pic : "/yb_mingpian/static/card/defaultlogo.png";
			}
		}
		return $list;
	}
	public function addnews($data)
	{
		$data["type"] = 1;
		$data["status"] = 0;
		$data["create_time"] = time();
		$res = Db::name("ybmp_messages")->insert($data);
		return $res;
	}
	public function wd_news($data)
	{
		$count = Db::name("ybmp_messages")->where(["user_id" => $data["user_id"], "staff_id" => $data["staff_id"], "type" => 2, "status" => 0])->count();
		return $count;
	}
	public function zhaopin($data)
	{
		$info = Db::name("ybmp_offweb_join")->where($data)->find();
		return $info;
	}
	public function getPhone($data)
	{
		$info["phone"] = Db::name("ybmp_customer")->where($data)->value("tel");
		$info["status"] = Db::name("ybmp_corp_conf")->where("mch_id", $data["mch_id"])->field("txl_get_pho,index_get_pho,shop_get_pho,web_get_pho,dynamic_get_pho")->find();
		return $info;
	}
	public function savePhone($data)
	{
		$param = Db::name("account_wxapp")->where("uniacid", $data["mch_id"])->field("key,secret,name")->find();
		$appid = $param["key"];
		if (empty($appid)) {
			return null;
		}
		$pc = new \WXBizDataCrypt($appid, $data["sessionKey"]);
		$errCode = $pc->decryptData($data["encryptedData"], $data["iv"], $data2);
		if ($errCode == 0) {
			$info = json_decode($data2, true);
			$tel = $info["phoneNumber"];
			$res = Db::name("ybmp_customer")->where(["mch_id" => $data["mch_id"], "user_id" => $data["user_id"], "staff_id" => $data["staff_id"]])->update(["tel" => $tel]);
			return $res;
		} else {
			return null;
		}
	}
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
	public function decryptData($encryptedData, $sessionKey, $app_id, $iv)
	{
		if (strlen($sessionKey) != 24) {
			return self::$IllegalAesKey;
		}
		$aesKey = base64_decode($sessionKey);
		if (strlen($iv) != 24) {
			return self::$IllegalIv;
		}
		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($encryptedData);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$dataObj = json_decode($result);
		if ($dataObj == NULL) {
			return self::$IllegalBuffer;
		}
		if ($dataObj->watermark->appid != $app_id) {
			return self::$IllegalBuffer;
		}
		return $dataObj;
	}
	public function __TIME($time)
	{
		$date = '';
		if (!empty($time)) {
			$date = date("Y-m-d H:i:s", $time);
		}
		return $date;
	}
	public function add_share($mch_id, $user_id, $pid)
	{
		$code = 1;
		$msg = '';
		$conf = Db::name("ybmp_user_share_setting")->where("mch_id", $mch_id)->find();
		$p = Db::name("ybmp_user")->where("uid", $pid)->find();
		if (empty($conf) || $conf["level"] == 0) {
			$code = 0;
			$msg = "商家未开启分销";
		} else {
			$che = $this->__check_pid($pid, $conf["level"]);
			if ($che == 1) {
				Db::name("ybmp_user")->where(["uid" => $user_id, "pid" => 0])->update(["pid" => $pid]);
			}
			if ($che == 2) {
				Db::name("ybmp_user")->where(["uid" => $user_id, "pid" => 0])->update(["pid" => $p["pid"]]);
				$code = 2;
				$msg = "层级超出设置,已设置为末级用户下级";
			}
			if ($che == 0) {
				$code = 3;
				$msg = "上级用户或分销商不存在";
			}
		}
		$rs["code"] = $code;
		$rs["msg"] = $msg;
		return $rs;
	}
	public function __check_pid($pid, $level, $num = 1)
	{
		$pid1 = $pid;
		$che1 = Db::name("ybmp_user")->where("uid", $pid1)->find();
		if (empty($che1)) {
			return 0;
		}
		if ($che1["pid"] > 0) {
			$num++;
			if ($level > $num) {
				$res = $this->__check_pid($che1["pid"], $level, $num);
				return $res;
			} else {
				return 2;
			}
		}
		if ($che1["pid"] == 0) {
			if ($num < $level) {
				return 1;
			} else {
				if ($level == 1) {
					return 1;
				} else {
					return 2;
				}
			}
		}
	}
}
<?php


namespace app\api\controller;

use think\Request;
use think\Db;
use think\Cache;
require_once BASE_ROOT . "core/extend/Wxpay/WxPay.Api.php";
require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
class Paycontent extends BaseController
{
	public function paycontentClass()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id];
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$info = Cache::get($mch_id . "_paycontent_class");
		if ($info != false) {
			$rs["info"] = $info;
			json_encode($rs);
		}
		$info = array();
		$cate_list = Db::name("ybmp_paycontent_class")->where("mch_id", $mch_id)->order("sort asc")->select();
		foreach ($cate_list as $value) {
			$info[] = $value;
		}
		Cache::set($mch_id . "_paycontent_class", $rs, CACHE_TIME);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function paycontent_list()
	{
		$rs = array("code" => 0, "info" => array());
		$class_id = Request::instance()->param("class_id", 0);
		$app_id = Request::instance()->param("i");
		$page = Request::instance()->param("page", 1);
		$uid = Request::instance()->param("uid", 0);
		$this_group_id = Request::instance()->param("this_group_id", -1);
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "class_id" => $class_id];
		$data = array_filter($data);
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$where["p.mch_id"] = $mch_id;
		$where["p.status"] = 1;
		if ($class_id > 0) {
			$where["p.class_id"] = $class_id;
		}
		if ($this_group_id > 0) {
			$where["p.group_id"] = $this_group_id;
		}
		$info = Db::name("ybmp_paycontent")->alias("p")->join("ybmp_paycontent_group a", "a.id=p.group_id", "left")->join("ybmp_paycontent_orders o", "o.content_id = p.id and o.isPay = 1 and o.uid=" . $uid, "LEFT")->where($where)->field("p.id,p.sort,p.title,p.class_id,p.group_id,p.image,p.paycontent_type,p.price,p.create_time,p.free,p.view,p.likes,p.buy_count_init,p.buy_count_real,o.id as isbuy,a.price group_price")->order("p.sort asc,p.id desc")->page($page, 20)->select();
		$lis_ = Db::name("ybmp_paycontent_group")->where("mch_id", $mch_id)->order("id", "desc")->field("id,name")->select();
		$rs["pay_group"][0] = ["id" => -1, "name" => "全部"];
		$rs["pay_group"] = array_merge($rs["pay_group"], $lis_);
		$pay_list = Db::name("ybmp_paycontent_orders")->where("uid", $uid)->where(["group_id" => [">", 0]])->where("isPay", 1)->field("pay_id,id")->order("create_time", "desc")->select();
		$user = Db::name("ybmp_paycontent_user")->where(["uid" => $uid])->find();
		if (empty($user)) {
			$user["expire"] = 0;
		}
		$vip = $user["expire"] >= time();
		$ia = array();
		for ($i = 0; $i < count($info); $i++) {
			$ia[$i] = $info[$i];
			$a = $info[$i]["id"] . ",";
			$ia[$i]["is_lock"] = true;
			if ($info[$i]["free"] != 0 || $info[$i]["price"] != "0.00") {
				if ($info[$i]["isbuy"] > 0) {
					$ia[$i]["is_lock"] = false;
				} else {
					if (!$vip || $info[$i]["free"] != 2) {
						for ($i2 = 0; $i2 < count($pay_list); $i2++) {
							if (strpos($pay_list[$i2]["pay_id"], $a) !== false) {
								$ia[$i]["isbuy"] = $pay_list[$i2]["id"];
								$ia[$i]["is_lock"] = false;
								break;
							}
						}
					} else {
						$ia[$i]["is_lock"] = false;
					}
				}
			} else {
				$ia[$i]["is_lock"] = false;
			}
		}
		if (empty($ia)) {
			return json_encode($rs);
		}
		$rs["info"] = $ia;
		return json_encode($rs);
	}
	public function checkuser()
	{
		$rs = array("code" => 0, "info" => array());
		$class_id = Request::instance()->param("class_id", 0);
		$app_id = Request::instance()->param("i");
		$uid = Request::instance()->param("uid", 0);
		$mch_id = $this->getMchId($app_id);
		$data = ["mch_id" => $mch_id, "class_id" => $class_id];
		$data = array_filter($data);
		$rule = [["mch_id", "require", "不存在商户"]];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = Db::name("ybmp_paycontent_user")->where(["uid" => $uid])->find();
		if (empty($user)) {
			$user["expire"] = 0;
		}
		$rs["info"] = $user["expire"] >= time();
		return json_encode($rs);
	}
	public function paycontent_info()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$uid = Request::instance()->param("uid", 0);
		$info = Db::name("ybmp_paycontent")->alias("p")->join("ybmp_paycontent_orders o", "p.id = o.content_id and o.isPay = 1 ", "LEFT")->where(["p.id" => $id, "p.mch_id" => $mch_id, "o.uid" => $uid])->field("p.*,o.id as isbuy")->find();
		if (empty($info)) {
			$info = Db::name("ybmp_paycontent")->where(["id" => $id, "mch_id" => $mch_id])->field("*,null isbuy")->find();
		}
		Db::name("ybmp_paycontent")->where(["id" => $id, "mch_id" => $mch_id])->setInc("view");
		$info["view"] += 1;
		$samegroup = 0;
		if ($info["group_id"] > 0) {
			$samegroup = Db::name("ybmp_paycontent")->where(["group_id" => $info["group_id"], "mch_id" => $mch_id])->count();
		}
		$group = Db::name("ybmp_paycontent_group")->where(["id" => $info["group_id"], "mch_id" => $mch_id])->find();
		if (!empty($group)) {
			$count = Db::name("ybmp_paycontent")->where(["group_id" => $info["group_id"], "mch_id" => $mch_id])->field("sum(view) as view_count,sum(likes) as like_count,sum(buy_count_init+buy_count_real) as buy_count")->find();
			$group["view_count"] = $count["view_count"];
			$group["like_count"] = $count["like_count"];
			$group["buy_count"] = $group["buy_count_init"] + $group["buy_count_real"];
		} else {
			$group = array();
			$group["view_count"] = $info["view"];
			$group["like_count"] = $info["likes"];
			$group["buy_count"] = $info["buy_count_init"] + $info["buy_count_real"];
			$group["img"] = $info["image"];
		}
		$group["same_count"] = $samegroup;
		$user = Db::name("ybmp_paycontent_user")->where(["uid" => $uid, "mch_id" => $mch_id, "expire" => ["gt", time()]])->find();
		if (empty($info["isbuy"])) {
			$a = Db::name("ybmp_paycontent_orders")->where("uid", $uid)->where("isPay", 1)->where(["pay_id" => ["like", "%," . $id . ",%"]])->field("pay_id,id")->order("create_time", "desc")->find();
			if (!empty($a)) {
				$info["isbuy"] = $a["id"] . $uid;
			}
		}
		$info["is_lock"] = false;
		if (($info["free"] != 0 || $info["price"] != "0.00") && (empty($info["isbuy"]) || $info["free"] == 2 && empty($user))) {
			$info["is_lock"] = true;
			unset($info["content"]);
			unset($info["video_url"]);
			unset($info["audio_url"]);
		}
		$info["group"] = $group;
		$likes = Db::name("ybmp_paycontent_likes")->where(["uid" => $uid, "mch_id" => $mch_id, "paycontent_id" => $id])->find();
		$info["liked"] = !empty($likes);
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function samegroup()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$uid = Request::instance()->param("uid");
		$page = Request::instance()->param("page", 1);
		$info = Db::name("ybmp_paycontent")->where(["id" => $id, "mch_id" => $mch_id])->find();
		if (empty($info)) {
			$rs["msg"] = "暂无内容";
			return json_encode($rs);
		}
		$where = array();
		$where["mch_id"] = $mch_id;
		if ($info["group_id"] > 0) {
			$where["group_id"] = $info["group_id"];
		} else {
			$where["class_id"] = $info["class_id"];
		}
		$list = Db::name("ybmp_paycontent")->where($where)->page($page, 20)->select();
		for ($i = 0; $i < count($list); $i++) {
			if (!empty($uid)) {
				$list[$i]["isbuy"] = $this->check_buy($list[$i]["id"], $uid);
			} else {
				$list[$i]["isbuy"] = null;
			}
		}
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function check_buy($id, $uid)
	{
		$buy = null;
		$info_ = Db::name("ybmp_paycontent")->alias("a")->join("ybmp_paycontent_orders s", "a.id=s.content_id", "left")->where("a.id", $id)->where("s.uid", $uid)->where("isPay", 1)->order("s.id", "desc")->field("s.id")->find();
		$info__ = Db::name("ybmp_paycontent")->where("id", $id)->find();
		if ($info__["free"] == 0 && $info__["price"] == "0.00") {
			$buy = $info__["id"];
			return $buy;
		}
		if (isset($info_["id"]) && $info_["id"] > 0) {
			$buy = $info_["id"];
			return $buy;
		}
		$info_ = Db::name("ybmp_paycontent_orders")->where("uid", $uid)->where("isPay", 1)->where(["pay_id" => ["like", "%," . $id . ",%"]])->order("id", "desc")->field("id")->find();
		if (!empty($info_)) {
			$buy = $info_["id"];
		}
		return $buy;
	}
	public function dolike()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id");
		$uid = Request::instance()->param("uid", 0);
		$likes = Db::name("ybmp_paycontent_likes")->where(["uid" => $uid, "mch_id" => $mch_id, "paycontent_id" => $id])->find();
		if (empty($likes)) {
			$data["uid"] = $uid;
			$data["paycontent_id"] = $id;
			$data["mch_id"] = $mch_id;
			$res = Db::name("ybmp_paycontent_likes")->insert($data);
			Db::name("ybmp_paycontent")->where(["id" => $id, "mch_id" => $mch_id])->setInc("likes");
		} else {
			$res = Db::name("ybmp_paycontent_likes")->where(["id" => $likes["id"]])->delete();
		}
		$rs["code"] = $res;
		return json_encode($rs);
	}
	public function allprice()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("id", 0);
		$info = Db::name("ybmp_paycontent")->where(["mch_id" => $mch_id, "id" => $id])->find();
		if (!empty($info) && $info["price"] > 0) {
			$list = [["name" => "单独购买", "id" => 0, "price" => $info["price"], "info" => "购买后即可浏览查看此内容"]];
		} else {
			if ($info["free"] == 2) {
				$list = Db::name("ybmp_paycontent_prices")->where(["mch_id" => $mch_id, "status" => 1])->order("sort asc")->select();
			} else {
				$list2 = Db::name("ybmp_paycontent_group")->where(["id" => $info["group_id"]])->find();
				$num = Db::name("ybmp_paycontent")->where("group_id", $info["group_id"])->count();
				$list = [["name" => "合集购买", "id" => $info["group_id"], "price" => $list2["price"], "info" => "购买后即可浏览此合集下所有内容", "type" => "group", "num2" => $num]];
			}
		}
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function uinfo()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$uid = Request::instance()->param("uid", 0);
		$user = Db::name("ybmp_paycontent_user")->where(["uid" => $uid, "mch_id" => $mch_id])->find();
		if (!empty($user)) {
			$user["expire_day"] = date("Y-m-d", $user["expire"]);
			$user["isvip"] = $user["expire"] > time();
		}
		$rs["code"] = empty($user) ? 0 : 1;
		$rs["info"] = $user;
		return json_encode($rs);
	}
	public function order()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$uid = Request::instance()->param("uid", 0);
		$page = Request::instance()->param("page", 1);
		$list = Db::name("ybmp_paycontent_orders")->where(["uid" => $uid, "mch_id" => $mch_id])->page($page, 20)->select();
		foreach ($list as &$item) {
			$item["buy_time"] = date("Y-m-d H:i:s", $item["create_time"]);
			$item["buy_info"] = $item["price_name"] . " x " . $item["num"];
		}
		$rs["code"] = 1;
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function order_del()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$id = Request::instance()->param("order_id", 0);
		$uid = Request::instance()->param("uid", 0);
		if ($id > 0) {
			$info = Db::name("ybmp_paycontent_orders")->where(["id" => $id, "mch_id" => $mch_id, "uid" => $uid])->delete();
			$rs["info"] = $info;
			$rs["code"] = 1;
		}
		return json_encode($rs);
	}
	public function likes()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$uid = Request::instance()->param("uid", 0);
		$page = Request::instance()->param("page", 1);
		$list = Db::name("ybmp_paycontent_likes")->alias("l")->join("ybmp_paycontent c", "l.paycontent_id=c.id")->where(["l.uid" => $uid, "l.mch_id" => $mch_id])->field("c.*")->page($page, 20)->select();
		$rs["code"] = 1;
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function createOrder()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$price_id = Request::instance()->param("price_id", 0);
		$num = Request::instance()->param("num");
		$uid = Request::instance()->param("uid");
		$openid = Request::instance()->param("openid");
		$type = Request::instance()->param("type", '');
		$content_id = Request::instance()->param("content_id", 0);
		if ($price_id > 0) {
			if ($type == "group") {
				$price_info = Db::name("ybmp_paycontent_group")->where(["id" => $price_id, "mch_id" => $mch_id])->find();
				$price_info["name"] = "合集购买";
				$data["group_id"] = $price_id;
				$list_ = Db::name("ybmp_paycontent")->where("group_id", $price_id)->field("id")->select();
				$pay_id = ",";
				for ($i = 0; $i < count($list_); $i++) {
					$pay_id .= $list_[$i]["id"] . ",";
				}
				$data["pay_id"] = $pay_id;
				$content_id = 0;
				$price_info["day"] = 0;
				$price_id = 0;
			} else {
				$price_info = Db::name("ybmp_paycontent_prices")->where(["id" => $price_id, "mch_id" => $mch_id])->find();
			}
		} else {
			$info = Db::name("ybmp_paycontent")->where(["id" => $content_id, "mch_id" => $mch_id])->find();
			$price_info = ["price" => $info["price"], "name" => "内容单独购买", "day" => 0];
		}
		$time = time();
		$data["price"] = $price_info["price"];
		$data["uid"] = $uid;
		$data["content_id"] = $content_id;
		$data["price_id"] = $price_id;
		$data["price_name"] = $price_info["name"];
		$data["num"] = $num;
		if ($type == "group") {
			$data["totalPrice"] = round($data["price"], 2);
		} else {
			$data["totalPrice"] = round($data["num"] * $data["price"], 2);
		}
		$data["day"] = $price_info["day"];
		$data["create_time"] = $time;
		$data["mch_id"] = $mch_id;
		$data["order_no"] = $this->createOrderNo();
		$data["out_trade_no"] = $this->createOutTradeNo();
		Db::startTrans();
		try {
			$id = Db::name("ybmp_paycontent_orders")->strict(true)->insertGetId($data);
			$pay_data = array("out_trade_no" => $data["out_trade_no"], "pay_type" => Request::instance()->param("pay_type", "1"), "type_alis_id" => $id, "pay_body" => "平台支付", "pay_detail" => "平台购买商品", "pay_money" => $data["totalPrice"], "create_time" => $time);
			Db::name("ybmp_paycontent_payment")->insert($pay_data);
			Db::commit();
		} catch (\Exception $e) {
			$id = 0;
			Db::rollback();
		}
		if (empty($id)) {
			$rs["code"] = 1;
			$rs["msg"] = "生成订单失败";
			return json_encode($rs);
		}
		if (!empty($pay_data)) {
			$GLOBALS["mch_id"] = $mch_id;
			$input = new \WxPayUnifiedOrder();
			$input->SetBody($pay_data["pay_body"]);
			$input->SetOpenid($openid);
			$input->SetDetail($pay_data["pay_detail"]);
			$input->SetTotal_fee($pay_data["pay_money"] * 100);
			$input->SetOut_trade_no($pay_data["out_trade_no"]);
			$input->SetTrade_type("JSAPI");
			global $_W;
			$notice_url = $_W["siteroot"] . "addons/yb_mingpian/core/pay_paycontent.php";
			$input->SetNotify_url($notice_url);
			$unifiedorder = \WxPayApi::unifiedOrder($input);
			if ($unifiedorder["return_code"] == "FAIL") {
				$rs["code"] = 1;
				$rs["msg"] = $unifiedorder["return_msg"];
				exit(json_encode($rs, true));
			}
			if ($unifiedorder["result_code"] == "FAIL") {
				$rs["code"] = 1;
				$rs["msg"] = $unifiedorder["err_code_des"];
				exit(json_encode($rs, true));
			}
			$res = $this->weixinapp($unifiedorder);
			$pay_info = array();
			foreach ($this->objectArray($res) as $value) {
				$pay_info = $value;
			}
			$pay_info["paySign"] = $pay_info["sign"];
			unset($pay_info["sign"]);
			$rs["info"] = $pay_info;
			return json_encode($rs);
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "生成订单失败";
			return json_encode($rs);
		}
	}
	private function weixinapp($unifiedorder)
	{
		$param = Db::name("ybmp_config")->where("key", "WXPAY")->where("mch_id", $GLOBALS["mch_id"])->where("is_use", 1)->value("value");
		$param = json_decode($param, true);
		$input = new \WxPayJsApiPay();
		$input->SetAppid($param["APP_ID"]);
		$input->SetTimeStamp('' . time() . '');
		$input->SetNonceStr($this->createNoncestr());
		$input->SetPackage("prepay_id=" . $unifiedorder["prepay_id"]);
		$input->SetSignType("MD5");
		$input->SetSign();
		return $input;
	}
	private function createNoncestr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
}
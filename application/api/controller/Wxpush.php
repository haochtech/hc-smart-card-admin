<?php


namespace app\api\controller;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";
require_once BASE_ROOT . "core/application/api_common.php";
use think\Request;
use think\Db;
use think\Session;
use think\Cache;
class Wxpush extends BaseController
{
	protected $mch_id = 0;
	protected $config = array("url" => "https://api.weixin.qq.com/sns/jscode2session", "appid" => '', "secret" => '', "grant_type" => "authorization_code");
	function __construct()
	{
		parent::__construct();
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		if ($mch_id) {
			$this->mch_id = $mch_id;
		} else {
			die;
		}
	}
	public function CreateOrderPush()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["formid", "require"], ["mch_id", "require", "不存在商户"], ["uid", "require"], ["order_id", "require"]];
		$data = ["mch_id" => $mch_id, "formid" => Request::instance()->param("formid"), "uid" => Request::instance()->param("uid"), "order_id" => Request::instance()->param("order_id")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = Db::name("ybmp_order")->where(["mch_id" => $data["mch_id"], "order_id" => $data["order_id"]])->find();
		$user = Db::name("ybmp_user")->where("uid", $data["uid"])->find();
		$order_goods = Db::name("ybmp_order_goods")->where(["order_id" => $data["order_id"]])->select();
		if (empty($order) || empty($user) || empty($order_goods)) {
			return "fail_empty";
		}
		$temp = Db::name("ybmp_tmpl_dope")->where("mch_id", $data["mch_id"])->value("temp");
		if ($temp) {
			$temp = json_decode($temp, true);
			if ($temp["AT0210"]) {
				$template_id = $temp["AT0210"];
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "fail_template_id";
				return json_encode($rs);
			}
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "fail_template_id";
			return json_encode($rs);
		}
		$good_name = '';
		foreach ($order_goods as $k => $v) {
			if ($k == 0) {
				$good_name = $good_name . $v["goods_name"];
			} else {
				$good_name = $good_name . "," . $v["goods_name"];
			}
		}
		$data_arr = array("character_string2" => array("value" => $order["order_no"], "color" => "#434343"), "amount3" => array("value" => $order["pay_money"], "color" => "#949494"), "thing4" => array("value" => $good_name, "color" => "#949494"));
		$new_data["template_id"] = $template_id;
		$new_data["page"] = "yb_mingpian/pages/shop/index";
		$new_data["openid"] = $user["wx_openid"];
		$result = $this->push($new_data, $data_arr);
		return $result;
	}
	public function PayOrderPush()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["formid", "require"], ["mch_id", "require", "不存在商户"], ["uid", "require"], ["out_trade_no", "require"]];
		$data = ["mch_id" => $mch_id, "formid" => Request::instance()->param("formid"), "uid" => Request::instance()->param("uid"), "out_trade_no" => Request::instance()->param("out_trade_no")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = Db::name("ybmp_order")->where(["mch_id" => $data["mch_id"], "out_trade_no" => $data["out_trade_no"]])->find();
		$user = Db::name("ybmp_user")->where("uid", $data["uid"])->find();
		$order_goods = Db::name("ybmp_order_goods")->where(["order_id " => $order["order_id"]])->select();
		$integral = Db::name("ybmp_integral_detail")->where(["mch_id" => $data["mch_id"], "order_id " => $order["order_id"]])->find();
		$jifen_add = $integral["integral"] ? $integral["integral"] : 0;
		if (empty($order) || empty($user) || empty($order_goods)) {
			$rs["code"] = 1;
			$rs["msg"] = "fail_empty";
			return json_encode($rs);
		}
		$temp = Db::name("ybmp_tmpl_dope")->where("mch_id", $data["mch_id"])->value("temp");
		if ($temp) {
			$temp = json_decode($temp, true);
			if ($temp["AT0048"]) {
				$template_id = $temp["AT0048"];
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "fail_template_id";
				return json_encode($rs);
			}
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "fail_template_id";
			return json_encode($rs);
		}
		$good_name = '';
		foreach ($order_goods as $k => $v) {
			if ($k == 0) {
				$good_name = $good_name . $v["goods_name"];
			} else {
				$good_name = $good_name . "," . $v["goods_name"];
			}
		}
		$data_arr = array("character_string2" => array("value" => $order["order_no"], "color" => "#434343"), "name1" => array("value" => $user["nick_name"], "color" => "#949494"), "thing6" => array("value" => $good_name, "color" => "#949494"), "amount3" => array("value" => $order["pay_money"], "color" => "#949494"), "thing5" => array("value" => $order["buyer_message"] ? $order["buyer_message"] : "无", "color" => "#949494"));
		$new_data["template_id"] = $template_id;
		$new_data["page"] = "yb_mingpian/pages/shop/index";
		$new_data["openid"] = $user["wx_openid"];
		$result = $this->push($new_data, $data_arr);
		return $result;
	}
	public function RedPush()
	{
		$app_id = Request::instance()->param("i");
		$mch_id = $this->getMchId($app_id);
		$rule = [["formid", "require"], ["mch_id", "require", "不存在商户"], ["uid", "require"], ["out_trade_no", "require"]];
		$data = ["mch_id" => $mch_id, "formid" => Request::instance()->param("formid"), "uid" => Request::instance()->param("uid"), "out_trade_no" => Request::instance()->param("out_trade_no")];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$order = Db::name("ybmp_order")->where(["mch_id" => $data["mch_id"], "out_trade_no" => $data["out_trade_no"]])->find();
		$user = Db::name("ybmp_user")->where("uid", $data["uid"])->find();
		$order_goods = Db::name("ybmp_order_goods")->where(["order_id " => $order["order_id"]])->select();
		$integral = Db::name("ybmp_integral_detail")->where(["mch_id" => $data["mch_id"], "order_id " => $order["order_id"]])->find();
		$jifen_add = $integral["integral"] ? $integral["integral"] : 0;
		if (empty($order) || empty($user) || empty($order_goods)) {
			$rs["code"] = 1;
			$rs["msg"] = "fail_empty";
			return json_encode($rs);
		}
		$temp = Db::name("ybmp_tmpl_dope")->where("mch_id", $data["mch_id"])->value("temp");
		if ($temp) {
			$temp = json_decode($temp, true);
			if ($temp["AT0048"]) {
				$template_id = $temp["AT0048"];
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "fail_template_id";
				return json_encode($rs);
			}
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "fail_template_id";
			return json_encode($rs);
		}
		$good_name = '';
		foreach ($order_goods as $k => $v) {
			if ($k == 0) {
				$good_name = $good_name . $v["goods_name"];
			} else {
				$good_name = $good_name . "," . $v["goods_name"];
			}
		}
		$data_arr = array("keyword1" => array("value" => $order["order_no"], "color" => "#434343"), "keyword2" => array("value" => $user["nick_name"], "color" => "#949494"), "keyword3" => array("value" => $good_name, "color" => "#949494"), "keyword4" => array("value" => $order["pay_money"], "color" => "#949494"), "keyword5" => array("value" => $order["order_money"], "color" => "#949494"), "keyword6" => array("value" => $jifen_add, "color" => "#949494"), "keyword7" => array("value" => $user["integral"], "color" => "#949494"), "keyword8" => array("value" => date("Y-m-d H:i:s", $order["pay_time"]), "color" => "#949494"), "keyword9" => array("value" => $order["buyer_message"] ? $order["buyer_message"] : "无", "color" => "#949494"));
		$new_data["template_id"] = $template_id;
		$new_data["page"] = "yb_mingpian/pages/shop/index";
		$new_data["openid"] = $user["wx_openid"];
		$new_data["formid"] = $data["formid"];
		$result = $this->push($new_data, $data_arr);
		return $result;
	}
	public function Push($data, $data_arr)
	{
		$params = array("touser" => $data["openid"], "template_id" => $data["template_id"], "page" => $data["page"], "data" => $data_arr);
		$access_token = getWxAccessToken($this->mch_id);
		if ($access_token["errcode"] == 0) {
			$push_url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $access_token["access_token"];
			$data = json_encode($params, true);
			$result = post_data2($push_url, $data, false);
			return $result;
		} else {
			return null;
		}
	}
}
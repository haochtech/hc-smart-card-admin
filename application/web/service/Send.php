<?php


namespace app\web\service;

use think\Cache;
use think\Db;
use think\Session;
require_once APP_PATH . "api_common.php";
class Send
{
	protected $mch_id = 0;
	protected $config = array("url" => "https://api.weixin.qq.com/sns/jscode2session", "appid" => '', "secret" => '', "grant_type" => "authorization_code", "template_id" => '');
	public function __construct($mch_id)
	{
		$this->mch_id = $mch_id;
		$temp = Db::name("ybmp_tmpl_dope")->where("mch_id", $mch_id)->value("temp");
		if ($temp) {
			$temp = json_decode($temp, true);
			if ($temp["AT0891"]) {
				$this->config["template_id"] = $temp["AT0891"];
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
	}
	public function send_message($staff_id, $uid, $msg)
	{
		$rs = array("code" => 0, "info" => array());
		$name = Db::name("ybmp_bus_card")->where("id", $staff_id)->value("user_name");
		if (empty($name)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$openid = Db::name("ybmp_user")->where("uid", $uid)->value("wx_openid");
		if (empty($openid)) {
			$rs["code"] = 1;
			$rs["msg"] = "openid不存在";
			return json_encode($rs);
		}
		$list = Db::name("ybmp_user_formid")->where("open_id", $openid)->order("id", "asc")->select();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$time = time() - 60 * 60 * 24 * 7;
				if ($v["create_time"] < $time) {
					Db::name("ybmp_user_formid")->where("id", $v["id"])->delete();
				}
				if (isset($list[0]["form_id"])) {
					$form_id = $list[0]["form_id"];
				} else {
					$rs["code"] = 1;
					$rs["msg"] = "openid不存在";
					return json_encode($rs);
				}
				$info = $this->form_data($openid, $name, $staff_id, $msg);
				/*if ($info["errcode"]) {
					Db::name("ybmp_user_formid")->where("form_id", $list[0]["form_id"])->delete();
				}*/
				$info2 = json_decode($info, true);
				$rs["info"] = $info2;
				$rs["infofo"] = $info;
				//Db::name("ybmp_user_formid")->where("form_id", $list[0]["form_id"])->delete();
			}
		}
		Db::name("ybmp_synlog")->insert(["mch_id" => 999, "media_id" => "web:" . json_encode($rs), "create_time" => date("Ymd/His", time())]);
		return json_encode($rs);
	}
	public function form_data($openid, $name, $staff_id, $msg)
	{
		$time = date("Y-m-d H:i:s", time());
		$arr_good = mb_strlen($msg,'UTF8');
              if ( $arr_good > 15) {

                  $msg = mb_substr($goods_names,0,15,'UTF8')."...";
              }

		$data_arr = array("name3" => array("value" => $name, "color" => "#434343"), "date1" => array("value" => $time, "color" => "#949494"), "thing2" => array("value" => $msg, "color" => "#949494"));
		$data["template_id"] = $this->config["template_id"];
		$data["page"] = "yb_mingpian/pages/chats/chats?id=" . $staff_id;
		$data["openid"] = $openid;
		$result = $this->push($data, $data_arr);
		return $result;
	}
	public function Push($data, $data_arr)
	{
		$params = array("touser" => $data["openid"], "template_id" => $data["template_id"], "page" => $data["page"], "data" => $data_arr);
		$access_token = getWxAccessToken($this->mch_id);
		if ($access_token["errcode"] == 0) {
			$push_url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $access_token["access_token"];
			$data = json_encode($params, true);
			$result = post_data2($push_url, $data);
			return $result;
		} else {
			return null;
		}
	}
}
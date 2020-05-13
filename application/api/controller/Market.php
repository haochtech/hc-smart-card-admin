<?php





namespace app\api\controller;



use think\Request;

use think\Db;

use app\api\service\MarketService;

require_once BASE_ROOT . "core/application/api/controller/BaseController.php";

class Market extends BaseController

{

	public function ManJian()

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

		$goods = new MarketService();

		$info = $goods->ManJian($mch_id);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function mchInfo()

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

		$goods = new MarketService();

		$info = $goods->mchInfo($mch_id);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function booklist()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$page = Request::instance()->param("page", 1);

		$data = ["mch_id" => $mch_id];

		$rule = [["mch_id", "require", "不存在商户"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->booklist($data, $page);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function bookinfo()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$id = Request::instance()->param("id");

		$data = ["mch_id" => $mch_id, "id" => $id];

		$rule = [["mch_id", "require", "不存在商户"], ["id", "require"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->bookinfo($data);

		if (empty($info)) {

			$rs["code"] = 1;

			$s["msg"] = "未获取到预约信息";

			return json_encode($rs);

		}

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function UserBook()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$page = Request::instance()->param("page", 1);

		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id")];

		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->UserBook($data, $page);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function submitbook()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$param = Request::instance()->param("data");

		$thing_id = Request::instance()->param("thing_id");

		$user_id = Request::instance()->param("user_id");

		$form = Request::instance()->param("form");

		$mch_id = $this->getMchId($app_id);

		$data = ["mch_id" => $mch_id, "param" => $param, "user_id" => $user_id, "thing_id" => $thing_id, "form" => $form];

		$rule = [["mch_id", "require", "该商户不存在,请重新核对小程序配置"], ["param", "require", "内容不能为空"], ["thing_id", "require", "表单不存在"], ["form", "require", "表单不存在"], ["user_id", "require", "未获取到用户信息，请重试"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$bus_form_id = Db::name("ybmp_reserve_thing")->where(["mch_id" => $mch_id, "id" => $thing_id])->value("form_id");

		$limit_num = Db::name("ybmp_bus_form")->where("id", $bus_form_id)->value("limit_num");

		$num = Db::name("ybmp_reserve_point")->where(["mch_id" => $mch_id, "thing_id" => $thing_id, "user_id" => $user_id])->count();

		if ($limit_num != 0 && $num > $limit_num) {

			$rs["code"] = 1;

			$rs["msg"] = "提交次数已达到上线";

			return json_encode($rs);

		}

		$index = new MarketService();

		$info = $index->submit_form($data);

		if (empty($info)) {

			$rs["code"] = 1;

			$rs["msg"] = "操作失败";

			return json_encode($rs);

		}

		if ($info == -1) {

			$rs["code"] = 1;

			$rs["msg"] = "预约已提交，请勿重复添加";

			return json_encode($rs);

		}

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function BusCoupon()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$page = Request::instance()->param("page", 1);

		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id")];

		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "用户信息不能为空"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->BusCoupon($data, $page);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function GetCoupon()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id"), "coupon_id" => Request::instance()->param("coupon_id"), "get_count" => Request::instance()->param("get_count"), "get_time" => time(), "status" => 0, "key" => $this->createOutTradeNo()];

		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require"], ["coupon_id", "require"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->GetCoupon($data);

		if ($info == "exist") {

			$rs["code"] = 1;

			$rs["msg"] = "您已经领取过该优惠券了";

			$rs['info'] = $info;

			$rs['data'] = $data;

			return json_encode($rs);

		} elseif ($info == "empty") {

			$rs["code"] = 1;

			$rs["msg"] = "该优惠券已领完";

			return json_encode($rs);

		} else {

			if (empty($info)) {

				$rs["code"] = 1;

				$rs["msg"] = "领取失败";

				return json_encode($rs);

			} else {

				$rs["info"] = $info;

				return json_encode($rs);

			}

		}

	}

	public function UserCoupon()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$page = Request::instance()->param("page", 1);

		$data = ["mch_id" => $mch_id, "user_id" => Request::instance()->param("user_id")];

		$rule = [["mch_id", "require", "不存在商户"], ["user_id", "require", "未获取到用户信息"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->UserCoupon($data, $page);

		$rs["info"] = $info;

		return json_encode($rs);

	}

	public function getFormid()

	{

		$rs = array("code" => 0, "info" => array());

		$app_id = Request::instance()->param("i");

		$mch_id = $this->getMchId($app_id);

		$data = ["mch_id" => $mch_id, "open_id" => Request::instance()->param("openid"), "form_id" => Request::instance()->param("formid"), "user_name" => Request::instance()->param("username"), "create_time" => time()];

		$rule = [["mch_id", "require", "不存在商户"], ["open_id", "require"], ["form_id", "require"]];

		$result = $this->checkParam($rule, $data);

		if (!empty($result)) {

			$rs["code"] = 1;

			$rs["msg"] = $result;

			return json_encode($rs);

		}

		$goods = new MarketService();

		$info = $goods->getFormid($data);

		$rs["info"] = $info;

		return json_encode($rs);

	}

}
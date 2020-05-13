<?php


namespace app\app\controller;

use think\console\command\make\Model;
use think\Request;
use think\Db;
use app\common\model\Business;
use app\common\model\GoodsCate;
use app\common\model\ResOrder;
use app\common\model\ResDesk;
use app\common\model\BusinessStamping;
use app\app\service\UserService;
require EXTEND_PATH . "Netprint/printhelper.php";
class User extends BaseController
{
	public function getcopyright()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$info = Db::name("ybmp_copyright")->order("id", "desc")->field("content")->find();
		$res["code"] = 0;
		$res["info"] = empty($info["content"]) ? '' : $info["content"];
		exit(json_encode($res, true));
	}
	public function getshopinfo()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$info = Db::name("ybmp_business_about")->where(["mch_id" => $app_id])->find();
		$res["code"] = 0;
		$res["info"] = $info;
		exit(json_encode($res, true));
	}
	public function updateshopinfo()
	{
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$name = Request::instance()->param("name");
		$address = Request::instance()->param("address");
		$phone = Request::instance()->param("phone");
		$job_time = Request::instance()->param("job_time");
		$dd["name"] = $name;
		$dd["address"] = $address;
		$dd["phone"] = $phone;
		$dd["job_time"] = $job_time;
		$about = Db::name("ybmp_business_about")->where(["mch_id" => $app_id])->find();
		if (empty($about)) {
			$dd["mch_id"] = $app_id;
			$res = Db::name("ybmp_business_about")->insert($dd);
		} else {
			$res = Db::name("ybmp_business_about")->where(["mch_id" => $app_id])->update($dd);
		}
		if (!empty($res)) {
			$rs["code"] = 1;
			$rs["msg"] = "信息更新成功";
			exit(json_encode($rs, true));
		}
		$rs["code"] = 0;
		$rs["info"] = "信息更新失败";
		exit(json_encode($rs, true));
	}
	public function Login()
	{
		$rs = array("code" => 0);
		$phone = Request::instance()->param("phone", '');
		$password = Request::instance()->param("password", '');
		$yuming = WEB_HOST;
		$rule = [["phone", "require"], ["password", "require"]];
		$data = ["phone" => $phone, "password" => $password, "yuming" => $yuming];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$sign = MD5($password + $phone + $yuming . "lxm");
		$url = "https://vip.ly100.wang/api/app/User/wq_login?phone=" . $phone . "&password=" . $password . "&yuming=" . $yuming . "sign=" . $sign;
		return file_get_contents($url);
	}
	public function ModifyPassword()
	{
		$rs = array("code" => 0);
		$phone = Request::instance()->param("phone", '');
		$password = Request::instance()->param("password", '');
		$new_password = Request::instance()->param("new_password", '');
		$rule = [["phone", "require"], ["password", "require"], ["new_password", "require"]];
		$data = ["phone" => $phone, "password" => $password, "new_password" => $new_password];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->login($phone, $password);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "请核对账户密码";
			return json_encode($rs);
		}
		$data = array("password" => md5($new_password));
		$success = $user->ModifyPassword($phone, $data);
		if ($success !== false) {
			$rs["info"] = $success;
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "修改失败";
		}
		return json_encode($rs);
	}
	public function ResetPassword()
	{
		$rs = array("code" => 0);
		$phone = Request::instance()->param("phone", '');
		$code = Request::instance()->param("code", '');
		$new_password = Request::instance()->param("new_password", '');
		$rule = [["phone", "require"], ["code", "require"], ["new_password", "require"]];
		$data = ["phone" => $phone, "code" => $code, "new_password" => $new_password];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->resetPassword($phone, $code);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "验证码错误,请核对手机号和验证码";
			return json_encode($rs);
		}
		$data = array("password" => md5($new_password));
		$success = $user->ModifyPassword($phone, $data);
		if ($success !== false) {
			$rs["info"] = $success;
			$user->delCode($phone);
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "重置失败";
		}
		return json_encode($rs);
	}
	public function Sms()
	{
		$rs = array("code" => 0);
		$phone = Request::instance()->param("phone", '');
		$rule = [["phone", "require"]];
		$data = ["phone" => $phone];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$user = new UserService();
		$info = $user->Sms($phone);
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "手机号不存在";
			return json_encode($rs);
		}
		if ($info["code"] == 1) {
			$rs["code"] = 1;
			$rs["msg"] = $info["msg"];
			return json_encode($rs);
		}
		$rs["info"] = $info["info"];
		return json_encode($rs);
	}
	public function Check()
	{
		$rs = array("code" => 0, "info" => array());
		$user = new UserService();
		$info = $user->check();
		$rs["info"] = $info;
		return json_encode($rs);
	}
	public function addstamping()
	{
		$rs = array("code" => 0, "info" => array());
		$uuid = Request::instance()->param("uuid", '');
		$position = Request::instance()->param("position", '');
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$rule = [["uuid", "require"], ["position", "require"]];
		$data = ["uuid" => $uuid, "position" => $position];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$helper = new \PrintHelper();
		$res = $helper->userBind($uuid, $username . $app_id);
		$res = json_decode($res, true);
		$OpenUserId = $res["OpenUserId"];
		if (empty($OpenUserId)) {
			$rs["code"] = 1;
			$rs["msg"] = "设备编码有误,请核实";
			return json_encode($rs);
		}
		$q["mch_id"] = $app_id;
		$q["uuid"] = $uuid;
		$res = Db::name("ybmp_business_stamping")->where("position", $position)->find();
		if (!empty($res)) {
			$rs["code"] = 1;
			$rs["msg"] = "位置名称不可重复";
			return json_encode($rs);
		}
		$res = Db::name("ybmp_business_stamping")->where($q)->find();
		if (empty($res)) {
			$q["open_user_id"] = $OpenUserId;
			$q["position"] = $position;
			$res = Db::name("ybmp_business_stamping")->insert($q);
		} else {
			$id = $res["id"];
			$res = Db::name("ybmp_business_stamping")->where(["id" => $id])->update(["open_user_id" => $OpenUserId, "position" => $position]);
		}
		if ($res !== false) {
			$rs["info"] = "添加成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "添加打印机失败";
		}
		return json_encode($rs);
	}
	public function stampinglist()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id");
		$username = Request::instance()->param("uname");
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$q["mch_id"] = $app_id;
		$res = Db::name("ybmp_business_stamping")->where($q)->select();
		$rs["info"] = $res;
		exit(json_encode($rs, true));
	}
	public function delstamping()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$id = Request::instance()->param("id", '');
		$rule = [["phone", "require"], ["uid", "require"], ["id", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "id" => $id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$q["id"] = $id;
		$printer = new BusinessStamping();
		$res = $printer->where($q)->delete();
		if (!empty($res)) {
			$rs["info"] = "设备删除成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "设备删除失败";
		}
		return json_encode($rs);
	}
	public function getcate()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id", '');
		$username = Request::instance()->param("uname", '');
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$rule = [["app_id", "require"], ["uname", "require"]];
		$data = ["app_id" => $app_id, "uname" => $username];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$where = array();
		$where["mch_id"] = $app_id;
		$where["pid"] = 0;
		$where["level"] = 1;
		$m = Db::name("ybmp_goods_cate")->where($where)->field("cate_id,cate_name,short_name,sort")->order("sort desc")->select();
		$rs["info"] = $m;
		return json_encode($rs);
	}
	public function addcate()
	{
		$rs = array("code" => 0, "info" => array());
		$app_id = Request::instance()->param("app_id", '');
		$username = Request::instance()->param("uname", '');
		$name = Request::instance()->param("name", '');
		$mark = Request::instance()->param("mark", '');
		$cid = Request::instance()->param("cid", '');
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where = array();
		if (!empty($cid)) {
			$up["cate_name"] = $name;
			$up["short_name"] = $mark;
			$where["mch_id"] = $app_id;
			$where["cate_id"] = $cid;
			$res = Db::name("ybmp_goods_cate")->where($where)->update($up);
			if (!empty($res)) {
				$rs["info"] = "分类修改成功";
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "分类修改失败";
			}
			return json_encode($rs);
			return;
		}
		$where["mch_id"] = $app_id;
		$where["pid"] = 0;
		$where["level"] = 1;
		$where["cate_name"] = $name;
		$m = Db::name("ybmp_goods_cate")->where($where)->select();
		if (!empty($m)) {
			$rs["code"] = 1;
			$rs["msg"] = "分类已存在,请勿重复添加";
			return json_encode($rs);
		}
		$where["short_name"] = $mark;
		$where["is_visible"] = 1;
		$where["create_time"] = time();
		$res = Db::name("ybmp_goods_cate")->insert($where);
		if (empty($res)) {
			$rs["code"] = 1;
			$rs["msg"] = "分类添加失败";
			return json_encode($rs);
		}
		$rs["info"] = "分类添加成功";
		return json_encode($rs);
	}
	public function delcate()
	{
		$rs = array("code" => 0, "info" => array());
		$username = Request::instance()->param("uname", '');
		$app_id = Request::instance()->param("app_id", '');
		$cid = Request::instance()->param("cid", '');
		$user = Db::name("ybmp_business")->where(["id" => $app_id, "name" => $username])->find();
		if (empty($user)) {
			$rs["code"] = 1;
			$rs["msg"] = "无此用户";
			exit(json_encode($rs, true));
		}
		$where = array();
		$where["cate_id"] = $cid;
		$where["mch_id"] = $app_id;
		$m = Db::name("ybmp_goods")->where($where)->find();
		if (!empty($m)) {
			$rs["code"] = 1;
			$rs["msg"] = "分类下还有商品,无法删除";
			return json_encode($rs);
		}
		$m = Db::name("ybmp_goods_cate")->where($where)->delete();
		if (empty($m)) {
			$rs["code"] = 1;
			$rs["msg"] = "分类删除失败";
			return json_encode($rs);
		}
		$rs["info"] = "分类删除成功";
		return json_encode($rs);
	}
	public function sortcate()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$sort = Request::instance()->param("sort", '');
		$rule = [["phone", "require"], ["uid", "require"], ["sort", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "sort" => $sort];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$sort = json_decode($sort, true);
		$cate = new GoodsCate();
		$m = $cate->saveAll($sort);
		if (empty($m)) {
			$rs["code"] = 1;
			$rs["msg"] = "排序更新失败";
			return json_encode($rs);
		}
		$rs["info"] = "排序更新成功";
		return json_encode($rs);
	}
	public function getgoods()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$cid = Request::instance()->param("cid", '');
		$rule = [["phone", "require"], ["uid", "require"], ["cid", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "cid" => $cid];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$where = array();
		$where["mch_id"] = $uid;
		$where["cate_id"] = $cid;
		$where["is_del"] = 0;
		$sql = "select g.goods_id,g.goods_name,g.market_price,g.price,g.promotion_price,g.real_sales,g.introduction,i.img_cover_mid \r\n                from ims_ybmp_goods g inner JOIN ims_ybmp_images i on g.images = i.img_id\r\n                where g.mch_id={$uid} and g.cate_id={$cid} and g.is_del=0";
		$m = new GoodsCate();
		$list = $m->query($sql);
		foreach ($list as &$item) {
			$item["img_cover_mid"] = __IMG($item["img_cover_mid"]);
		}
		$rs["info"] = $list;
		return json_encode($rs);
	}
	public function addtable()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$name = Request::instance()->param("name", '');
		$id = Request::instance()->param("id", '');
		$rule = [["phone", "require"], ["uid", "require"], ["name", "require"], ["id", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "name" => $name, "id" => $id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$table = new ResDesk();
		if (!empty($id)) {
			$where["id"] = $id;
			$where["mch_id"] = $uid;
			$res = $table->where($where)->update(["name" => $name]);
			if (!empty($res)) {
				$rs["info"] = "修改成功";
			} else {
				$rs["code"] = 1;
				$rs["msg"] = "修改失败";
			}
			return json_encode($rs);
		}
		$up["name"] = $name;
		$up["create_time"] = time();
		$up["mch_id"] = $uid;
		$up["del"] = 1;
		$res = $table->insert($up);
		if (!empty($res)) {
			$rs["info"] = "添加成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "添加失败";
		}
		return json_encode($rs);
	}
	public function tablelist()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$rule = [["phone", "require"], ["uid", "require"]];
		$data = ["phone" => $phone, "uid" => $uid];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$q["mch_id"] = $uid;
		$q["del"] = 1;
		$table = new ResDesk();
		$res = $table->where($q)->select();
		$rs["info"] = $res;
		return json_encode($rs);
	}
	public function deltable()
	{
		$rs = array("code" => 0, "info" => array());
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$id = Request::instance()->param("id", '');
		$rule = [["phone", "require"], ["uid", "require"], ["id", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "id" => $id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$info = $buss->where($condition)->find();
		if (empty($info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$q["id"] = $id;
		$q["mch_id"] = $uid;
		$table = new ResDesk();
		$up["del"] = 2;
		$res = $table->where($q)->update($up);
		if (!empty($res)) {
			$rs["info"] = "餐桌删除成功";
		} else {
			$rs["code"] = 1;
			$rs["msg"] = "餐桌删除失败";
		}
		return json_encode($rs);
	}
	public function ttt()
	{
		$id = Request::instance()->param("id", '');
		$url = "https://detail.1688.com/offer/" . $id . ".html?sk=consignPrivate";
		$ch = curl_init();
		$headers = array();
		$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
		$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
		$headers[] = "Cache-Control: no-cache";
		$headers[] = "Connection:keep-alive";
		$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
		$headers[] = "Content-Type:application/json;charset=gbk";
		$headers[] = "Upgrade-Insecure-Requests:1";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		$content = curl_multi_getcontent($ch);
		exit($content);
	}
	public function printorder()
	{
		$rs = array("code" => 0);
		$phone = Request::instance()->param("phone", '');
		$uid = Request::instance()->param("uid", '');
		$id = Request::instance()->param("id", '');
		$rule = [["phone", "require"], ["uid", "require"], ["id", "require"]];
		$data = ["phone" => $phone, "uid" => $uid, "id" => $id];
		$result = $this->checkParam($rule, $data);
		if (!empty($result)) {
			$rs["code"] = 1;
			$rs["msg"] = $result;
			return json_encode($rs);
		}
		$condition = array("phone" => $phone, "id" => $uid);
		$buss = new Business();
		$user_info = $buss->where($condition)->find();
		if (empty($user_info)) {
			$rs["code"] = 1;
			$rs["msg"] = "用户不存在";
			return json_encode($rs);
		}
		$helper = new \PrintHelper();
		$print = new BusinessStamping();
		$print_list = $print->where("mch_id", $uid)->select();
		$res = '';
		foreach ($print_list as $item) {
			$print = $helper->printHtmlContent($item["uuid"], "https://vip.ly100.wang/wap/count/ResOrderDishList?app_id=ZPRZIJNPF2&order_id=" . $data["id"], $item["open_user_id"]);
			$v = json_decode($print, true);
			$v["position"] = $item["position"] ? $item["position"] : "默认位置";
			if ($v["Code"] == 200) {
				$res = $res . $v["position"] . ":打印成功。";
			} else {
				$res = $res . $v["position"] . ":" . $v["Message"] . "。";
			}
		}
		$rs["msg"] = $res;
		return json_encode($rs, true);
	}
}
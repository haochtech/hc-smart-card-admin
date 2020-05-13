<?php


namespace app\admin\controller;

use think\Db;
class Poster extends Base
{
	public function index()
	{
		$search_text = request()->param("name", '');
		$status = request()->param("status", "3");
		$page = request()->param("page", "1");
		$where["mch_id"] = $this->bus_id;
		$where["is_del"] = 1;
		$remote = THIS_URL . "index.php/api/index/getPoster?page=" . $page;
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		if (!empty($search_text)) {
			$where["name"] = ["like", "%" . $search_text . "%"];
		}
		if ($status == 3) {
			$list = Db::name("ybmp_poster_source")->where($where)->order("id desc")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
			$this->assign("name", $search_text);
			$this->assign("page", $list->render());
		}
		if ($status == 0) {
			$where["rid"] = [">", 0];
			$arr = db::name("ybmp_poster_source")->where($where)->field("rid")->select();
			$aa = '';
			for ($i = 0; $i < count($arr); $i++) {
				$aa .= $arr[$i]["rid"] . ",";
			}
			$aa = explode(",", substr($aa, 0, strlen($aa) - 1));
			$list_ = json_decode(get_url_content($remote), true);
			$list = $list_["info"];
			for ($i = 0; $i < count($list); $i++) {
				if (in_array($list[$i]["id"], $aa)) {
					$list[$i]["is_dow"] = 1;
				} else {
					$list[$i]["is_dow"] = 0;
				}
			}
			$this->assign("page", $page);
			$this->assign("count", $list_["count"]);
		}
		if ($status == 2) {
			$where["rid"] = [">", 0];
			$arr = db::name("ybmp_poster_source")->where($where)->field("rid")->distinct("rid")->order("rid", "asc")->select();
			$aa = '';
			for ($i = 0; $i < count($arr); $i++) {
				$aa .= $arr[$i]["rid"] . ",";
			}
			$aa = explode(",", substr($aa, 0, strlen($aa) - 1));
			$res = json_decode(get_url_content($remote), true);
			$list = $res["info"];
			for ($i = 0; $i < count($res["info"]); $i++) {
				if (in_array($list[$i]["id"], $aa)) {
					unset($list[$i]);
				}
			}
			$this->assign("page", $page);
			$this->assign("count", $res["count"]);
		}
		$isfounder = $status != 3 ? 1 : 0;
		if ($status == 1) {
			$where["rid"] = [">", 0];
			$list_ = Db::name("ybmp_poster_source")->where($where)->order("id desc")->paginate(10, false, $config = ["query" => ["s" => $url, "search_text" => '']]);
			$list = array();
			for ($i = 0; $i < count($list_); $i++) {
				$list[$i] = $list_[$i];
				$list[$i]["is_dow"] = 1;
			}
			$isfounder = 0;
			$this->assign("page", $list_->render());
		}
		$this->assign("isfounder", $isfounder);
		$this->assign("list", $list);
		$this->assign("status", $status);
		return view();
	}
	public function update_img()
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
		$mch_id = $this->bus_id;
		$id = request()->param("id", '');
		$url = request()->param("url", '');
		$name = request()->param("name", '');
		$na = explode("bargain/", $url);
		$f = file_get_contents($url);
		$ch_path = SITE_PATH . "public/upload/haibao/" . $this->bus_id . "/";
		if (!file_exists($ch_path)) {
			$mode = intval("0777", 8);
			mkdir($ch_path, $mode, true);
		}
		$wawa = "http://" . $host . $child_path[0] . "/addons/yb_mingpian/core/public/upload/haibao/" . $this->bus_id . "/" . $na[1];
		$co = db::name("ybmp_poster_source")->where("mch_id", $this->bus_id)->where("rid", $id)->where("pic_url", $wawa)->where("is_del", 1)->count();
		if ($co >= 1) {
			return AjaxReturnMsg(0, "已下载");
		}
		file_put_contents($ch_path . $na[1], $f);
		$res = db::name("ybmp_poster_source")->insert(["name" => $name, "pic_url" => $wawa, "create_time" => time(), "mch_id" => $mch_id, "rid" => $id]);
		return AjaxReturn($res);
	}
	public function add()
	{
		return view();
	}
	public function poster_save()
	{
		if (request()->isPost()) {
			$data["mch_id"] = $this->bus_id;
			$data["name"] = input("param.name", '');
			$data["pic_url"] = input("param.pic_url", '');
			$data["create_time"] = time();
			$res = db::name("ybmp_poster_source")->insertGetId($data);
			return AjaxReturn($res);
		} else {
			return 0;
		}
	}
	public function del_poster()
	{
		$id = input("param.id", "0");
		$rid = input("param.rid", "0");
		if ($rid > 0) {
			$re = db::name("ybmp_poster_source")->where("rid", $rid)->where("id", $id)->find();
			$path = $re["pic_url"];
			$path = explode("/core/", $path);
			if (file_exists($path[1])) {
				unlink($path[1]);
			}
		}
		$res = db::name("ybmp_poster_source")->where("id", $id)->update(["is_del" => 2]);
		return AjaxReturn($res);
	}
}
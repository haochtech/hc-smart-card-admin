<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
use app\common\model\Area;
require EXTEND_PATH . "PHPExcel/PHPExcel.php";
class User extends Base
{
	public function user_list()
	{
		$user = new \app\admin\service\User();
		$search_text = input("param.search_text");
		$star_time = input("param.star_time");
		$end_time = input("param.end_time");
		if (!empty($star_time)) {
			$star = strtotime($star_time);
			$condition["reg_time"] = ["between", [$star, $star + 86400]];
		}
		if (!empty($star_time) && !empty($end_time)) {
			$star = strtotime($star_time);
			$end = strtotime($end_time);
			$condition["reg_time"] = ["between", [$star, $end]];
		}
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$condition["u.mch_id"] = array("eq", $this->bus_id);
		$condition["u.nick_name"] = array("like", "%" . $search_text . "%");
		$sear = '';
		if ($search_text != '' && !empty($search_text)) {
			$sear = $search_text;
		}
		$user_list = Db::name("ybmp_user")->alias("u")->join("ybmp_user_level l", "u.level_id = l.id", "left")->where($condition)->order("reg_time")->field("u.*,l.level_name")->paginate(15, false, $config = ["query" => ["s" => $url, "search_text" => $sear]]);
		if (count($user_list) < 1) {
			$seant = "%" . $search_text . "%";
			$user_list = Db::name("ybmp_user")->alias("u")->join("ybmp_user_level l", "u.level_id = l.id", "left")->where("u.mch_id", $this->bus_id)->where("u.nick_name", "like", $seant)->order("reg_time")->field("u.*,l.level_name")->paginate(15, false, $config = ["query" => ["s" => $url, "search_text" => $sear]]);
		} else {
			$this->assign("hiden", 1);
		}
		$num = request()->param("page", 1);
		$this->assign("num", $num);
		$page = $user_list->render();
		$this->assign("user_list", $user_list);
		$this->assign("page", $page);
		$this->assign("search_text", $search_text);
		$this->assign("star_time", $star_time);
		$this->assign("end_time", $end_time);
		return view("user/user_list");
	}
	public function form_all_excel()
	{
		$id = input("param.id");
		$get_form_all = Db::name("ybmp_user_form")->where("bus_form_id", $id)->select();
		$this->FormexportExcel($get_form_all, getFormrName($id));
	}
	function FormexportExcel($data = array(), $fileName = '')
	{
		$obj = new \PHPExcel();
		$obj->getActiveSheet(0)->setTitle($fileName);
		$cellName = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ");
		$_row = 1;
		$title = ["会员名称"];
		$i = 0;
		foreach ($title as $v) {
			$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
			$i++;
		}
		$_row++;
		$z = 1;
		foreach ($data as $key => $value) {
			$json = json_decode($value["param"], true);
			$obj->setActiveSheetIndex(0)->setCellValue("A" . ($key + $_row), getUserName($value["user_id"]));
			$count = 0;
			foreach ($json as $aaa => $bbb) {
				$obj->setActiveSheetIndex(0)->setCellValue($cellName[$aaa + $_row - 1] . ($key + 2), $bbb["value"]);
				$obj->getActiveSheet()->getColumnDimension($cellName[$aaa + $_row - 1])->setWidth(30);
				if ($count == 1) {
					$obj->setActiveSheetIndex(0)->setCellValue($cellName[$aaa + 1] . "1", $bbb["title"]);
					$obj->getActiveSheet()->getStyle($cellName[$aaa + 1] . "1")->getFont()->setSize(16);
					continue;
				}
				$obj->setActiveSheetIndex(0)->setCellValue($cellName[$aaa + 1] . "1", $bbb["title"]);
				$obj->getActiveSheet()->getStyle($cellName[$aaa + 1] . "1")->getFont()->setSize(16);
				$count = 1;
			}
			$z++;
		}
		$obj->getActiveSheet()->getStyle("A1")->getFont()->setSize(16);
		$obj->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
		$obj->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
		if (!$fileName) {
			$fileName = uniqid(time(), true);
		}
		$objWrite = \PHPExcel_IOFactory::createWriter($obj, "Excel2007");
		header("pragma:public");
		header("Content-Disposition:attachment;filename={$fileName}.xlsx");
		$objWrite->save("php://output");
		exit;
	}
	public function form_excel()
	{
		$get_form_all = Db::name("ybmp_user_form")->where("mch_id", $this->bus_id)->select();
		$this->exportExcel($get_form_all, "表单数据");
	}
	function exportExcel($data = array(), $fileName = '')
	{
		$obj = new \PHPExcel();
		$obj->getActiveSheet(0)->setTitle("表单数据");
		$obj->getActiveSheet()->mergeCells("A1:C1");
		$obj->getActiveSheet()->getStyle("A1:C1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
		$obj->setActiveSheetIndex(0)->setCellValue("A1", "表单名称");
		$obj->getActiveSheet()->getStyle("A1")->getFont()->setSize(12);
		$obj->getActiveSheet()->mergeCells("D1:F1");
		$obj->getActiveSheet()->getStyle("D1:F1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
		$obj->setActiveSheetIndex(0)->setCellValue("D1", "用户名");
		$obj->getActiveSheet()->getStyle("D1")->getFont()->setSize(12);
		$obj->getActiveSheet()->mergeCells("G1:P1");
		$obj->getActiveSheet()->getStyle("G1:P1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
		$obj->setActiveSheetIndex(0)->setCellValue("G1", "提交内容");
		$obj->getActiveSheet()->getStyle("G1")->getFont()->setSize(12);
		$obj->getActiveSheet()->mergeCells("Q1:S1");
		$obj->getActiveSheet()->getStyle("Q1:S1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
		$obj->setActiveSheetIndex(0)->setCellValue("Q1", "提交时间");
		$obj->getActiveSheet()->getStyle("Q1")->getFont()->setSize(12);
		for ($i = 2; $i < count($data); $i++) {
			foreach ($data as $key => $value) {
				$json = json_decode($value["param"], true);
				$str = '';
				foreach ($json as $aaa => $bbb) {
					$str .= $bbb["title"] . "：" . $bbb["value"] . "，";
				}
				$obj->getActiveSheet()->mergeCells("A" . ($key + 2) . ":C" . ($key + 2));
				$obj->getActiveSheet()->getStyle("A" . ($key + 2) . ":C" . ($key + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
				$obj->setActiveSheetIndex(0)->setCellValue("A" . ($key + 2), getFormrName($value["bus_form_id"]));
				$obj->getActiveSheet()->mergeCells("D" . ($key + 2) . ":F" . ($key + 2));
				$obj->getActiveSheet()->getStyle("D" . ($key + 2) . ":F" . ($key + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
				$obj->setActiveSheetIndex(0)->setCellValue("D" . ($key + 2), getUserName($value["user_id"]));
				$obj->getActiveSheet()->mergeCells("G" . ($key + 2) . ":P" . ($key + 2));
				$obj->setActiveSheetIndex(0)->setCellValue("G" . ($key + 2), $str);
				$obj->getActiveSheet()->mergeCells("Q" . ($key + 2) . ":S" . ($key + 2));
				$obj->getActiveSheet()->getStyle("Q" . ($key + 2) . ":S" . ($key + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::Pa9c7)->setVertical(\PHPExcel_Style_Alignment::cjzbA);
				$obj->setActiveSheetIndex(0)->setCellValue("Q" . ($key + 2), date("Y-m-d H:i:s", $value["create_time"]));
			}
		}
		if (!$fileName) {
			$fileName = uniqid(time(), true);
		}
		$objWrite = \PHPExcel_IOFactory::createWriter($obj, "Excel2007");
		header("pragma:public");
		header("Content-Disposition:attachment;filename={$fileName}.xlsx");
		$objWrite->save("php://output");
		exit;
	}
	public function level()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$list = Db::name("ybmp_user_level")->where("mch_id", $this->bus_id)->order("level", "asc")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		$this->assign("list", $list);
		return view();
	}
	public function add_level()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$data["mch_id"] = $this->bus_id;
			$result = 1;
			$letun = Db::name("ybmp_user_level")->where("mch_id", $data["mch_id"])->order("level")->select();
			if (count($letun) > 0) {
				foreach ($letun as $key => $value) {
					if ($data["level"] == $value["level"]) {
						$result = 0;
						$message = "会员等级重复";
						break;
					}
					if ($data["level_name"] == $value["level_name"]) {
						$result = 0;
						$message = "等级名称重复";
						break;
					}
					if ($data["hierarchy"] == $value["hierarchy"]) {
						$result = 0;
						$message = "累计积分重复";
						break;
					}
				}
				foreach ($letun as $key => $value) {
					if ($data["hierarchy"] > $value["hierarchy"]) {
						if ($data["level"] < $value["level"]) {
							$result = 0;
							$message = "会员等级低于已有会员等级，且积分高于已有等级积分";
							break;
						}
					}
					if ($data["level"] > $value["level"]) {
						if ($data["hierarchy"] < $value["hierarchy"]) {
							$result = 0;
							$message = "会员等级高于已有会员等级，且积分低于已有等级积分";
							break;
						}
					}
				}
			}
			if ($result != 0) {
				$result = Db::name("ybmp_user_level")->insert($data);
				if ($result !== false) {
					$result = 1;
				} else {
					$result = 0;
				}
			}
			if ($message != '' && !empty($message)) {
				return AjaxReturn($result, $message);
			} else {
				return AjaxReturn($result);
			}
		}
		return view();
	}
	public function edit_level()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$letun = Db::name("ybmp_user_level")->where("mch_id", $this->bus_id)->select();
			$result = 1;
			foreach ($letun as $key => $value) {
				if ($data["level"] == $value["level"]) {
					if ($data["id"] != $value["id"]) {
						$result = 0;
						$message = "会员等级重复";
						break;
					}
				}
				if ($data["level_name"] == $value["level_name"]) {
					if ($data["id"] != $value["id"]) {
						$result = 0;
						$message = "等级名称重复";
						break;
					}
				}
				if ($data["hierarchy"] == $value["hierarchy"]) {
					if ($data["id"] != $value["id"]) {
						$result = 0;
						$message = "累计积分重复";
						break;
					}
				}
			}
			$leone = Db::name("ybmp_user_level")->where("mch_id", $this->bus_id)->whereNotIn("id", $data["id"])->order("level")->select();
			if (count($leone) > 0) {
				foreach ($leone as $key => $value) {
					if ($data["hierarchy"] > $value["hierarchy"]) {
						if ($data["level"] < $value["level"]) {
							$result = 0;
							$message = "会员等级低于已有会员等级，且积分高于已有等级积分";
							break;
						}
					}
					if ($data["level"] > $value["level"]) {
						if ($data["hierarchy"] < $value["hierarchy"]) {
							$result = 0;
							$message = "会员等级高于已有会员等级，且积分低于已有等级积分";
							break;
						}
					}
				}
			}
			if ($result != 0) {
				$result = Db::name("ybmp_user_level")->where("id", $data["id"])->where("mch_id", $this->bus_id)->update($data);
				if ($result !== false) {
					$result = 1;
				}
			}
			if ($message != '' && !empty($message)) {
				return AjaxReturn($result, $message);
			} else {
				return AjaxReturn($result);
			}
		}
		$id = request()->get("id");
		if ($id != '' && !empty($id)) {
			$list = Db::name("ybmp_user_level")->where("mch_id", $this->bus_id)->where("id", $id)->find();
			$this->assign("list", $list);
			return view();
		}
	}
	public function level_del()
	{
		if (request()->isAjax() && request()->isPost()) {
			$count = Db::name("ybmp_user")->where("level_id", request()->post("id"))->find();
			if ($count > 0) {
				return AjaxReturn(0, "此等级下已有用户，无法删除");
			}
			$result = Db::name("ybmp_user_level")->where("id", request()->post("id"))->where("mch_id", $this->bus_id)->delete();
			if ($result !== false) {
				return AjaxReturn(1);
			} else {
				return AjaxReturn(0);
			}
		}
	}
	public function integral_rule()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$id = $data["id"];
			unset($data["id"]);
			$lcount = Db::name("ybmp_integral_rule")->where("id", $id)->where("mch_id", $this->bus_id)->count();
			if ($lcount > 0) {
				$data = json_encode($data, true);
				$result = Db::name("ybmp_integral_rule")->where("id", $id)->where("mch_id", $this->bus_id)->update(["data" => $data]);
				if ($result !== false) {
					return AjaxReturn(1);
				} else {
					return AjaxReturn(0);
				}
			} else {
				$data = json_encode($data, true);
				$result = Db::name("ybmp_integral_rule")->insert(["data" => $data, "mch_id" => $this->bus_id]);
				return AjaxReturn($result);
			}
		}
		$list = Db::name("ybmp_integral_rule")->where("mch_id", $this->bus_id)->find();
		$todata = json_decode($list["data"], true);
		$this->assign("list", $todata);
		$this->assign("luid", $list["id"]);
		return view();
	}
	public function integral_details()
	{
		$url = request()->query();
		$url = explode("=/", $url);
		$url = explode("&", $url[1]);
		$url = "/" . $url[0];
		$soname = input("param.soname");
		if ($soname != '') {
			$sonames = "%" . $soname . "%";
			$list = Db::name("ybmp_integral_detail")->alias("ite")->join("ybmp_user us", "ite.user_id = us.uid", "left")->where("ite.mch_id", $this->bus_id)->where("us.nick_name", "like", $sonames)->order("ite.time", "desc")->field("us.nick_name,ite.*")->paginate(15, false, $config = ["query" => ["s" => $url, "soname", $soname]]);
		} else {
			$list = Db::name("ybmp_integral_detail")->alias("ite")->join("ybmp_user us", "ite.user_id = us.uid", "left")->where("ite.mch_id", $this->bus_id)->order("ite.time", "desc")->field("us.nick_name,ite.*")->paginate(15, false, $config = ["query" => ["s" => $url]]);
		}
		$this->assign("soname", $soname);
		$this->assign("list", $list);
		return view();
	}
	public function edit_integral()
	{
		if (request()->isAjax() && request()->isPost()) {
			$data = request()->post();
			$reta = Db::name("ybmp_user")->where("uid", $data["uid"])->where("mch_id", $this->bus_id)->find();
			if ($data["integral"] >= $reta["integral"]) {
				$itype = 1;
				$iiral = $data["integral"] - $reta["integral"];
			} else {
				$itype = 2;
				$iiral = $reta["integral"] - $data["integral"];
			}
			if ($data["consume"] >= $reta["consume"]) {
				$ctype = 1;
				$ciral = $data["consume"] - $reta["consume"];
			} else {
				$ctype = 2;
				$ciral = $reta["consume"] - $data["consume"];
			}
			$rotan["user_id"] = $reta["uid"];
			$rotan["integral"] = $iiral;
			$rotan["consume"] = $ciral;
			$rotan["explain"] = $data["explain"];
			$rotan["itype"] = $itype;
			$rotan["ctype"] = $ctype;
			$rotan["time"] = time();
			$rotan["mch_id"] = $this->bus_id;
			$oate = Db::name("ybmp_integral_detail")->insert($rotan);
			$soen = Db::name("ybmp_user_level")->where("mch_id", $this->bus_id)->order("level")->select();
			$uate["level_id"] = '';
			$str = '';
			foreach ($soen as $key => $value) {
				if ($soen[$key + 1]["hierarchy"] != '') {
					if ($data["consume"] >= $value["hierarchy"] && $data["consume"] < $soen[$key + 1]["hierarchy"]) {
						$uate["level_id"] = $value["id"];
						break;
					}
				} else {
					if ($data["consume"] >= $value["hierarchy"]) {
						$uate["level_id"] = $value["id"];
						break;
					}
				}
			}
			if ($uate["level_id"] == '') {
				$uate["level_id"] = 0;
			}
			if ($oate) {
				$result = Db::name("ybmp_user")->where("uid", $data["uid"])->where("mch_id", $this->bus_id)->update(["integral" => $data["integral"], "consume" => $data["consume"], "level_id" => $uate["level_id"]]);
				if ($result !== false) {
					return AjaxReturn(1);
				} else {
					return AjaxReturn(0);
				}
			}
		}
		$id = request()->get("id");
		if ($id != '') {
			$list = Db::name("ybmp_user")->where("uid", $id)->where("mch_id", $this->bus_id)->find();
			$this->assign("list", $list);
			return view();
		}
	}
}
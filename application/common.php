<?php


define("PAGESIZE", 10);
define("APP_URL", "https://vip.ly100.wang/api/api/Pay/PayCallback");
define("CateringPay", "https://vip.ly100.wang/api/api/CaterPay/PayCallback");
define("SHOP_GOODS_CATE", "/pages/goods/index/index?cate=");
define("SHOP_GOODS_DETAIL", "/pages/goods/detail/index?id=");
define("SHOP_ARTICLE_INDEX", "/pages/find/index");
define("SHOP_ARTICLE_DETAIL", "/pages/find_info/index?id=");
define("WEB_CASE", "/pages/case/index");
define("CLASS_ID", "/pages/find/index?class_id=");
define("IMAGES_ID", "/pages/image/index?group_id=");
define("IMG_URL", "https://ad.vip.ly100.wang/");
define("THIS_URL", "https://vip.ly100.wang/");
define("VIPAPI", "https://vip.ly100.wang/api/");
if (!function_exists("array_column")) {
	function array_column($input, $columnKey, $indexKey = NULL)
	{
		$columnKeyIsNumber = is_numeric($columnKey) ? TRUE : FALSE;
		$indexKeyIsNull = is_null($indexKey) ? TRUE : FALSE;
		$indexKeyIsNumber = is_numeric($indexKey) ? TRUE : FALSE;
		$result = array();
		foreach ((array) $input as $key => $row) {
			if ($columnKeyIsNumber) {
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = is_array($tmp) && !empty($tmp) ? current($tmp) : NULL;
			} else {
				$tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
			}
			if (!$indexKeyIsNull) {
				if ($indexKeyIsNumber) {
					$key = array_slice($row, $indexKey, 1);
					$key = is_array($key) && !empty($key) ? current($key) : NULL;
					$key = is_null($key) ? 0 : $key;
				} else {
					$key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}
}
function AjaxReturn($err_code, $data = array())
{
	$rs = ["code" => $err_code, "message" => getErrorInfo($err_code)];
	if (!empty($data)) {
		$rs["data"] = $data;
	}
	return $rs;
}
function img_zip($img, $size = 1)
{
	try {
		$IM = new IMG_COMPRESS($img, $size);
		$IM->compressImg($img);
		$s = file_put_contents("wxwx2.json", $img . PHP_EOL, 8);
	} catch (Exception $e) {
	}
}
function AjaxReturnMsg($err_code, $message)
{
	$rs = ["code" => $err_code, "message" => $message];
	return $rs;
}
function array_case(&$array, $case = CASE_LOWER)
{
	$array = array_change_key_case($array, $case);
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			array_case($array[$key], $case);
		}
	}
}
function array_u($array, $check)
{
	$res = array();
	for ($i = 0; $i < count($array); $i++) {
		if ($array[$i][$check] != $array[$i + 1][$check]) {
			array_push($res, $array[$i]);
		}
	}
	return $res;
}
function deldir($path)
{
	if (is_dir($path)) {
		$p = scandir($path);
		foreach ($p as $val) {
			if ($val != "." && $val != "..") {
				if (is_dir($path . $val)) {
					deldir($path . $val . "/");
					@rmdir($path . $val . "/");
				} else {
					unlink($path . $val);
				}
			}
		}
	}
}
function phpinfo_array()
{
	ob_start();
	phpinfo(-1);
	$pi = preg_replace(array("#^.*<body>(.*)</body>.*\$#ms", "#<h2>PHP License</h2>.*\$#ms", "#<h1>Configuration</h1>#", "#\r?\n#", "#</(h1|h2|h3|tr)>#", "# +<#", "#[ \t]+#", "#&nbsp;#", "#  +#", "# class=\".*?\"#", "%&#039;%", "#<tr>(?:.*?)\" src=\"(?:.*?)=(.*?)\" alt=\"PHP Logo\" /></a>" . "<h1>PHP Version (.*?)</h1>(?:\\n+?)</td></tr>#", "#<h1><a href=\"(?:.*?)\\?=(.*?)\">PHP Credits</a></h1>#", "#<tr>(?:.*?)\" src=\"(?:.*?)=(.*?)\"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#", "# +#", "#<tr>#", "#</tr>#"), array("\$1", '', '', '', "</\$1>" . "\n", "<", " ", " ", " ", '', " ", "<h2>PHP Configuration</h2>" . "\n" . "<tr><td>PHP Version</td><td>\$2</td></tr>" . "\n" . "<tr><td>PHP Egg</td><td>\$1</td></tr>", "<tr><td>PHP Credits Egg</td><td>\$1</td></tr>", "<tr><td>Zend Engine</td><td>\$2</td></tr>" . "\n" . "<tr><td>Zend Egg</td><td>\$1</td></tr>", " ", "%S%", "%E%"), ob_get_clean());
	$sections = explode("<h2>", strip_tags($pi, "<h2><th><td>"));
	unset($sections[0]);
	$pi = array();
	foreach ($sections as $section) {
		$n = substr($section, 0, strpos($section, "</h2>"));
		preg_match_all("#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#", $section, $askapache, PREG_SET_ORDER);
		foreach ($askapache as $m) {
			@($pi[$n][$m[1]] = !isset($m[3]) || $m[2] == $m[3] ? $m[2] : array_slice($m, 2));
		}
	}
	return $pi;
}
function urlQueryToArr()
{
	$queryString = $_SERVER["QUERY_STRING"];
	$arr = array();
	foreach (explode("&", $queryString) as $value) {
		$queryStringTemp = explode("=", $value);
		$arr[trim($queryStringTemp[0])] = trim($queryStringTemp[1]);
	}
	return $arr;
}
function get_child_url($http = true)
{
	if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
		$host = $_SERVER["HTTP_X_REAL_HOST"];
	} else {
		$host = $_SERVER["HTTP_HOST"];
	}
	if (isset($_SERVER["PHP_SELF"])) {
		$child_path = explode("addons/", $_SERVER["PHP_SELF"]);
	} else {
		$child_path = explode("addons/", $_SERVER["REQUEST_URI"]);
	}
	$http = $http === true ? "http://" : "https://";
	return $http . $host . $child_path[0];
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
function timeToChzh($time)
{
	$t = time();
	$start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
	$end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
	$monthstart = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
	$monthend = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
	$beginYesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("y"));
	$endYesterday = mktime(0, 0, 0, date("m"), date("d"), date("y")) - 1;
	if ($time >= $start && $time <= $end) {
		return date("H:i", $time);
	}
	if ($time >= $beginYesterday && $time <= $endYesterday) {
		return "昨天" . date("H:i", $time);
	}
	if ($time >= $monthstart && $time <= $monthend) {
		return "周" . mb_substr("日一二三四五六", date("w", $time), 1, "utf-8") . date("H:i", $time);
	}
	return date("Y-m-d", $time);
}
function removeImageFile($img_path)
{
	$a = explode("/core/", $img_path);
	$pa = SITE_PATH . $a[1];
	if (file_exists($pa)) {
		unlink($pa);
		return true;
	} else {
		return $pa;
	}
}
function __IMG($img_path)
{
	$path = '';
	if (!empty($img_path)) {
		if (stristr($img_path, "http://") === false && stristr($img_path, "https://") === false) {
			$path = "https://" . WEB_HOST . "/" . $img_path;
		} else {
			$path = $img_path;
		}
	}
	return $path;
}
function __TIME($time)
{
	$date = '';
	if (!empty($time)) {
		$date = date("Y-m-d H:i:s", $time);
	}
	return $date;
}
function __DATE__($time)
{
	$date = '';
	if (!empty($time)) {
		$date = date("Y-m-d", $time);
	}
	return $date;
}
function get_addon_class($name, $type = '', $class = null)
{
	$name = \think\Loader::parseName($name);
	if ($type == '' && $class == null) {
		$dir = ADDON_PATH . $name . "/core";
		if (is_dir($dir)) {
			$type = "addons_index";
		} else {
			$type = "addon_index";
		}
	}
	$class = \think\Loader::parseName(is_null($class) ? $name : $class, 1);
	switch ($type) {
		case "addon_index":
			$namespace = "\\addons\\" . $name . "\\" . $class;
			break;
		case "addon_controller":
			$namespace = "\\addons\\" . $name . "\\controller\\" . $class;
			break;
		case "addon_api":
			$namespace = "\\addons\\" . $name . "\\api\\" . $class;
			break;
		case "addons_index":
			$namespace = "\\addons\\" . $name . "\\core\\" . $class;
			break;
		case "addons_controller":
			$namespace = "\\addons\\" . $name . "\\core\\controller\\" . $class;
			break;
		default:
			$namespace = "\\addons\\" . $name . "\\" . $type . "\\controller\\" . $class;
	}
	return $namespace;
}
function hook($hook, $params = array())
{
	\think\Hook::listen($hook, $params);
}
function get_user_name($uid)
{
	$res = \think\Db::table("yb_user")->where("uid", $uid)->find();
	return $res["nick_name"];
}
function bargain_order_status($status)
{
	if ($status == "-1") {
		return "订单取消";
	}
	if ($status == "0") {
		return "待付款";
	}
	if ($status == "1") {
		return "已付款";
	}
	if ($status == "2") {
		return "已发货";
	}
	if ($status == "3") {
		return "已完成";
	}
	if ($status == "4") {
		return "退款中";
	}
	if ($status == "5") {
		return "已退款";
	}
}
function getCity($area_id)
{
	$rs = array();
	$res = \app\common\model\Area::get($area_id);
	$city = \app\common\model\Area::get($res["pid"]);
	$pro = \app\common\model\Area::get($city["pid"]);
	$rs["province_id"] = $pro["id"];
	$rs["city_id"] = $city["id"];
	$rs["district_id"] = $res["id"];
	$rs["province_name"] = $pro["name"];
	$rs["city_name"] = $city["name"];
	$rs["district_name"] = $res["name"];
	return $pro["name"] . $city["name"] . $res["name"];
}
function generate_password($length = 10)
{
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password = '';
	for ($i = 0; $i < $length; $i++) {
		$password .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	$res = \think\Db::table("yb_business")->where("app_key", $password)->find();
	if ($res) {
		generate_password();
	} else {
		return $password;
	}
}
function commet_img_array($id)
{
	$list = \think\Db::table("yb_res_comment")->where("id", $id)->find();
	if (!empty($list["array_pic"])) {
		$img = substr($list["array_pic"], 0, -1);
		$img_arr = explode(",", $img);
	} else {
		$img_arr = "-";
	}
	return $img_arr;
}
function subtext($text, $length)
{
	if (mb_strlen($text, "utf8") > $length) {
		return mb_substr($text, 0, $length, "utf8") . "…";
	}
	return $text;
}
function getWxCode($code)
{
	$msg = ["-1" => "系统繁忙", "0" => "成功", "61004" => "当前客户端ip未在开放平台白名单", "61007" => "当前公众号或者小程序已在公众平台解绑", "61023" => "授权已过期,请重新授权", "85001" => "微信号不存在或微信号设置为不可搜索", "85002" => "小程序绑定的体验者数量达到上限", "85003" => "微信号绑定的小程序体验者达到上限", "85004" => "微信号已经绑定", "85006" => "标签格式错误", "85007" => "页面路径错误", "85008" => "类目填写错误", "85009" => "已经有正在审核的版本", "85010" => "item_list有项目为空", "85011" => "标题填写错误", "85012" => "无效的审核id", "85013" => "无效的自定义配置", "85014" => "无效的模版编号", "85015" => "版本输入错误", "85019" => "没有审核版本", "85020" => "审核状态未满足发布", "85021" => "状态不可变-5以内", "85022" => "action非法", "85023" => "审核列表填写的项目数不在1-5以内", "85043" => "模版错误", "85044" => "代码包超过大小限制", "85045" => "导航设置错误,请重置导航再试,编号85045", "85046" => "tabBar中缺少path", "85047" => "pages字段为空", "85048" => "导航设置错误,请重置导航再试,编号85048", "85066" => "链接错误", "85068" => "测试链接不是子链接", "85069" => "校验文件失败", "85070" => "链接为黑名单", "85071" => "已添加该链接，请勿重复添加", "85072" => "该链接已被占用", "85073" => "二维码规则已满", "85074" => "小程序未发布, 小程序必须先发布代码才可以发布二维码跳转规则", "85075" => "个人类型小程序无法设置二维码规则", "85076" => "链接没有ICP备案", "85077" => "小程序类目信息失效（类目中含有官方下架的类目，请重新选择类目）", "85079" => "小程序没有线上版本，不能进行灰度", "85080" => "小程序提交的审核未审核通过", "85081" => "无效的发布比例", "85082" => "当前的发布比例需要比之前设置的高", "85085" => "当前平台近7天提交审核的小程序数量过多，请耐心等待审核完毕后再次提交", "86000" => "不是由第三方代小程序进行调用", "86001" => "不存在第三方的已经提交的代码", "86002" => "小程序还未设置昵称、头像、简介。请先设置完后再重新提交", "87011" => "现网已经在灰度发布，不能进行版本回退", "87012" => "该版本不能回退，可能的原因：1:无上一个线上版用于回退 2:此版本为已回退版本，不能回退 3:此版本为回退功能上线之前的版本，不能回退", "87013" => "撤回次数达到上限（每天一次，每个月10次）", "89031" => "小程序绑定的体验者数量达到上限"];
	if (array_key_exists($code, $msg)) {
		return $msg[$code];
	} else {
		return $code;
	}
}
function htmltotxt($string)
{
	$string = htmlspecialchars_decode($string);
	$string = str_replace("\"", "＂", $string);
	$string = str_replace("'", "＇", $string);
	$string = html_entity_decode(strip_tags($string));
	return $string;
}
function getOrderStatus($status)
{
	if ($status == -1) {
		return "已取消";
	}
	if ($status == 0) {
		return "待支付";
	}
	if ($status == 1) {
		return "待发货";
	}
	if ($status == 2) {
		return "待收货";
	}
	if ($status == 3) {
		return "已完成";
	}
	if ($status == 4) {
		return "退款中";
	}
	if ($status == 5) {
		return "已退款";
	}
}
function getUserName($id)
{
	$user_name = \Think\Db::name("ybmp_user")->where("uid", $id)->find();
	return $user_name["nick_name"];
}
function getFormrName($id)
{
	$user_name = \Think\Db::name("ybmp_bus_form")->where("id", $id)->find();
	return $user_name["title"];
}
function get_img_arr($str)
{
	if ($str == '') {
		return '';
	}
	$string_arr = explode(",", $str);
	return $string_arr;
}
function getArrayName($arr)
{
	$string_arr = explode(",", $arr);
	$return = '';
	foreach ($string_arr as $k => $v) {
		$res = \Think\Db::name("ybmp_bus_module")->where("module_id", $v)->find();
		$return .= $res["module_name"] . ",";
	}
	return substr($return, 0, -1);
}
function getUserAou($uid)
{
	$res = \Think\Db::name("ybmp_user_permission")->alias("p")->join("ybmp_user_role r", "r.role_id=p.role_id")->where("p.user_id", $uid)->find();
	return $res["role_name"];
}
function get_img_src($img)
{
	$img_cover = \think\Db::name("ybmp_images")->where("img_id", $img)->find();
	return $img_cover["img_cover"];
}
function get_pt_cname($id)
{
	$img_cover = \think\Db::name("ybmp_pt_category")->where("id", $id)->find();
	return $img_cover["name"];
}
function json_ecd_all($val)
{
	if (empty($val)) {
		return '';
	}
	$json = json_decode($val, true);
	return $json;
}
function get_group_order_status($status)
{
	if ($status == -1) {
		return "已取消";
	}
	if ($status == 1) {
		return "待支付";
	}
	if ($status == 2) {
		return "待成团";
	}
	if ($status == 3) {
		return "待发货";
	}
	if ($status == 4) {
		return "待收货";
	}
	if ($status == 5) {
		return "申请退款";
	}
	if ($status == 6) {
		return "已完成";
	}
	if ($status == 7) {
		return "已退款";
	}
}
function commonPage($table, $condition, $search_text, $page = 20, $order = '')
{
	$url = request()->query();
	$url = explode("=/", $url);
	$url = explode("&", $url[1]);
	$url = "/" . $url[0];
	if ($order == '') {
		$list = \Think\Db::name($table)->where($condition)->paginate($page, false, $config = ["query" => ["s" => $url, "search_text" => $search_text]]);
	} else {
		$list = \Think\Db::name($table)->where($condition)->order($order)->paginate($page, false, $config = ["query" => ["s" => $url, "search_text" => $search_text]]);
	}
	return $list;
}
function strtoascii($str)
{
	$str = mb_convert_encoding($str, "GB2312");
	$change_after = '';
	for ($i = 0; $i < strlen($str); $i++) {
		$temp_str = dechex(ord($str[$i]));
		$change_after .= $temp_str[1] . $temp_str[0];
	}
	return strtoupper($change_after);
}
function asciitostr($sacii)
{
	$asc_arr = str_split(strtolower($sacii), 2);
	$str = '';
	for ($i = 0; $i < count($asc_arr); $i++) {
		$str .= chr(hexdec($asc_arr[$i][1] . $asc_arr[$i][0]));
	}
	return mb_convert_encoding($str, "UTF-8", "GB2312");
}
function match_string($matches)
{
	return iconv("UCS-2", "UTF-8", pack("H4", $matches[1]));
}
function screen_id($db_name, $id_arr, $id_name, $check_num, $check_name)
{
	$res = array();
	is_array($id_arr) ? '' : ($id_arr = explode(",", $id_arr));
	for ($i = 0; $i < count($id_arr); $i++) {
		$verify = \Think\Db::name($db_name)->field($check_name)->where($id_name, $id_arr[$i])->find();
		if ($verify[$check_name] == $check_num) {
			array_push($res, $id_arr[$i]);
		}
	}
	return implode(",", $res);
}
function exportExcel($title = array(), $data = array(), $fileName = '', $savePath = "./", $isDown = false)
{
	$obj = new PHPExcel();
	$cellName = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ");
	$obj->getActiveSheet(0)->setTitle("sheet名称");
	$_row = 1;
	if ($title) {
		$_cnt = count($title);
		$obj->getActiveSheet(0)->mergeCells("A" . $_row . ":" . $cellName[$_cnt - 1] . $_row);
		$obj->setActiveSheetIndex(0)->setCellValue("A" . $_row, "数据导出：" . date("Y-m-d H:i:s"));
		$_row++;
		$i = 0;
		foreach ($title as $v) {
			$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
			$i++;
		}
		$_row++;
	}
	if ($data) {
		$i = 0;
		foreach ($data as $_v) {
			$j = 0;
			foreach ($_v as $_cell) {
				$obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), $_cell);
				$j++;
			}
			$i++;
		}
	}
	if (!$fileName) {
		$fileName = uniqid(time(), true);
	}
	$objWrite = PHPExcel_IOFactory::createWriter($obj, "Excel2007");
	if ($isDown) {
		header("pragma:public");
		header("Content-Disposition:attachment;filename={$fileName}.xls");
		$objWrite->save("php://output");
		exit;
	}
	$_fileName = iconv("utf-8", "gb2312", $fileName);
	$_savePath = $savePath . $_fileName . ".xlsx";
	$objWrite->save($_savePath);
	return $savePath . $fileName . ".xlsx";
}
function importExecl($file = '', $row = 1, $sheet = 0)
{
	$file = iconv("utf-8", "gb2312", $file);
	if (empty($file) or !file_exists($file)) {
		die("file not exists!");
	}
	$objRead = new PHPExcel_Reader_Excel2007();
	if (!$objRead->canRead($file)) {
		$objRead = new PHPExcel_Reader_Excel5();
		if (!$objRead->canRead($file)) {
			die("No Excel!");
		}
	}
	$cellName = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ");
	$obj = $objRead->load($file);
	$currSheet = $obj->getSheet($sheet);
	$columnH = $currSheet->getHighestColumn();
	$columnCnt = array_search($columnH, $cellName);
	$rowCnt = $currSheet->getHighestRow();
	$data = array();
	for ($_row = $row; $_row <= $rowCnt; $_row++) {
		for ($_column = 0; $_column <= $columnCnt; $_column++) {
			$cellId = $cellName[$_column] . $_row;
			$cellValue = $currSheet->getCell($cellId)->getValue();
			if ($cellValue instanceof PHPExcel_RichText) {
				$cellValue = $cellValue->__toString();
			}
			$data[$_row][$cellName[$_column]] = $cellValue;
		}
	}
	return $data;
}
function base64_file($base_str, $path)
{
	$image = $base_str;
	$imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . ".png";
	if (strstr($image, ",")) {
		$image = explode(",", $image);
		$image = $image[1];
	}
	$date_path = date("Y-m-d", time());
	$file_path = $_SERVER["DOCUMENT_ROOT"] . "/attachment/upload/" . $path . "/" . $date_path;
	if (!is_dir($path)) {
		mkdir($file_path, 0777, true);
	}
	$imageSrc = $file_path . "/" . $imageName;
	$r = file_put_contents($imageSrc, base64_decode($image));
	if (isset($_SERVER["HTTP_X_REAL_HOST"])) {
		$host = $_SERVER["HTTP_X_REAL_HOST"];
	} else {
		$host = $_SERVER["HTTP_HOST"];
	}
	if (!$r) {
		return '';
	} else {
		return "http://" . $host . "/attachment/upload/" . $path . "/" . $date_path . "/" . $imageName;
	}
}
if (!function_exists("exif_imagetype")) {
	function exif_imagetype($filename)
	{
		if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
			return $type;
		}
		return false;
	}
}
<?php


if (!function_exists("img_zip")) {
	function img_zip($img, $size = 1)
	{
		try {
			$IM = new IMG_COMPRESS($img, $size);
			$IM->compressImg($img);
			file_put_contents("wxwx1.json", $img . PHP_EOL, 8);
		} catch (Exception $e) {
		}
	}
}
function getWxAccessToken($mch_id)
{
	$param = think\Db::name("account_wxapp")->where("uniacid", $mch_id)->field("key,secret,name")->find();
	$appid = $param["key"];
	$appsecret = $param["secret"];
	if (empty($appid) || empty($appid)) {
		$rs["errcode"] = 1;
		$rs["msg"] = "未配置商户信息";
		return json_encode($rs);
	}
	if (think\Cache::get("access_token3_" . $appid) && think\Cache::get("expire_time３_" . $appid) > time()) {
		return think\Cache::get("access_token3_" . $appid);
	} else {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
		$access_token = makeRequest($url);
		$access_token = json_decode($access_token["result"], true);
		if ($access_token["errcode"]) {
			$access_token["msg"] = getErrCode($access_token["errcode"]);
			if ($access_token["msg"] === $access_token["errcode"]) {
				$access_token["msg"] = $access_token["errmsg"];
			}
		} else {
			think\Cache::set("access_token3_" . $appid, $access_token);
			think\Cache::set("expire_time3_" . $appid, time() + 7000);
			$access_token["errcode"] = 0;
			$access_token["access_token"];
		}
		return $access_token;
	}
}
function data_uri($contents, $mime)
{
	$base64 = base64_encode($contents);
	return "data:" . $mime . ";base64," . $base64;
}
function get_url_content2($url, $method = true)
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
function ImgToBase64($file)
{
	$base64_file = '';
	if ($file) {
		$base64_data = get_url_content2($file);
		$base64_file = data_uri($base64_data, "image/png");
	}
	return $base64_file;
}
function post_data2($url, $param, $return_array = true, $is_file = false)
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
function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
	if (empty($url)) {
		return array("code" => "100");
	}
	$_curl = curl_init();
	$_header = array("Accept-Language: zh-CN", "Connection: Keep-Alive", "Cache-Control: no-cache");
	if (!empty($hostIp)) {
		$urlInfo = parse_url($url);
		if (empty($urlInfo["host"])) {
			$urlInfo["host"] = substr(DOMAIN, 7, -1);
			$url = "http://{$hostIp}{$url}";
		} else {
			$url = str_replace($urlInfo["host"], $hostIp, $url);
		}
		$_header[] = "Host: {$urlInfo["host"]}";
	}
	if (!empty($params)) {
		curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($_curl, CURLOPT_POST, true);
	}
	if (substr($url, 0, 8) == "https://") {
		curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	}
	curl_setopt($_curl, CURLOPT_URL, $url);
	curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($_curl, CURLOPT_USERAGENT, "API PHP CURL");
	curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);
	if ($expire > 0) {
		curl_setopt($_curl, CURLOPT_TIMEOUT, $expire);
		curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire);
	}
	if (!empty($extend)) {
		curl_setopt_array($_curl, $extend);
	}
	$result["result"] = curl_exec($_curl);
	$result["code"] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
	$result["info"] = curl_getinfo($_curl);
	if ($result["result"] === false) {
		$result["result"] = curl_error($_curl);
		$result["code"] = -curl_errno($_curl);
	}
	curl_close($_curl);
	return $result;
}
function getErrCode($code)
{
	$msg = ["-1" => "系统繁忙", "0" => "成功", "40001" => "验证失败", "40002" => "不合法的凭证类型", "40003" => "不合法的OpenID", "40004" => "不合法的媒体文件类型", "40005" => "不合法的文件类型", "40006" => "不合法的文件大小", "40007" => "不合法的媒体文件id", "40008" => "不合法的消息类型", "40009" => "不合法的图片文件大小", "40010" => "不合法的语音文件大小", "40011" => "不合法的视频文件大小", "40012" => "不合法的缩略图文件大小", "40013" => "不合法的APPID", "40014" => "不合法的access_token", "40015" => "不合法的菜单类型", "40016" => "不合法的按钮个数", "40017" => "不合法的按钮个数", "40018" => "不合法的按钮名字长度", "40019" => "不合法的按钮KEY长度", "40020" => "不合法的按钮URL长度", "40021" => "不合法的菜单版本号", "40022" => "不合法的子菜单级数", "40023" => "不合法的子菜单按钮个数", "40024" => "不合法的子菜单按钮类型", "40025" => "不合法的子菜单按钮名字长度", "40026" => "不合法的子菜单按钮KEY长度", "40027" => "不合法的子菜单按钮URL长度", "40028" => "不合法的自定义菜单使用用户", "40125" => "无效的小程序秘钥,请重新配置", "41001" => "缺少access_token参数", "41002" => "缺少appid参数", "41003" => "缺少refresh_token参数", "41004" => "缺少secret参数", "41005" => "缺少多媒体文件数据", "41006" => "缺少media_id参数", "41007" => "缺少子菜单数据", "42001" => "access_token超时", "43001" => "需要GET请求", "43002" => "需要POST请求", "43003" => "需要HTTPS请求", "45009" => "调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成", "45010" => "创建菜单个数超过限制", "45029" => "生成码个数总和到达最大个数限制", "46001" => "不存在媒体数据", "46002" => "不存在的菜单版本", "46003" => "不存在的菜单数据", "40029" => "登录多次导致code重复/appid和secret对应不上，不是同一个小程序", "61004" => "当前客户端ip未在开放平台白名单", "61007" => "当前公众号或者小程序已在公众平台解绑", "61023" => "授权已过期,请重新授权", "85001" => "微信号不存在或微信号设置为不可搜索", "85002" => "小程序绑定的体验者数量达到上限", "85003" => "微信号绑定的小程序体验者达到上限", "85004" => "微信号已经绑定", "85006" => "标签格式错误", "85007" => "页面路径错误", "85008" => "类目填写错误", "85009" => "已经有正在审核的版本", "85010" => "item_list有项目为空", "85011" => "标题填写错误", "85012" => "无效的审核id", "85013" => "无效的自定义配置", "85014" => "无效的模版编号", "85015" => "版本输入错误", "85019" => "没有审核版本", "85020" => "审核状态未满足发布", "85021" => "状态不可变-5以内", "85022" => "action非法", "85023" => "审核列表填写的项目数不在1-5以内", "85043" => "模版错误", "85044" => "代码包超过大小限制", "85045" => "导航设置错误,请重置导航再试,编号85045", "85046" => "tabBar中缺少path", "85047" => "pages字段为空", "85048" => "导航设置错误,请重置导航再试,编号85048", "85066" => "链接错误", "85068" => "测试链接不是子链接", "85069" => "校验文件失败", "85070" => "链接为黑名单", "85071" => "已添加该链接，请勿重复添加", "85072" => "该链接已被占用", "85073" => "二维码规则已满", "85074" => "小程序未发布, 小程序必须先发布代码才可以发布二维码跳转规则", "85075" => "个人类型小程序无法设置二维码规则", "85076" => "链接没有ICP备案", "85077" => "小程序类目信息失效（类目中含有官方下架的类目，请重新选择类目）", "85079" => "小程序没有线上版本，不能进行灰度", "85080" => "小程序提交的审核未审核通过", "85081" => "无效的发布比例", "85082" => "当前的发布比例需要比之前设置的高", "85085" => "当前平台近7天提交审核的小程序数量过多，请耐心等待审核完毕后再次提交", "86000" => "不是由第三方代小程序进行调用", "86001" => "不存在第三方的已经提交的代码", "86002" => "小程序还未设置昵称、头像、简介。请先设置完后再重新提交", "87011" => "现网已经在灰度发布，不能进行版本回退", "87012" => "该版本不能回退，可能的原因：1:无上一个线上版用于回退 2:此版本为已回退版本，不能回退 3:此版本为回退功能上线之前的版本，不能回退", "87013" => "撤回次数达到上限（每天一次，每个月10次）", "89031" => "小程序绑定的体验者数量达到上限"];
	if (array_key_exists($code, $msg)) {
		return $msg[$code];
	} else {
		return $code;
	}
}
function check_work_err($arr, $return_err = true)
{
	$err = "\n        {\n            \"40001\":\"不合法的secret参数\",\n            \"40003\":\"无效的UserID\",\n            \"40004\":\"不合法的媒体文件类型\",\n            \"40005\":\"不合法的type参数\",\n            \"40006\":\"不合法的文件大小\",\n            \"40007\":\"不合法的media_id参数\",\n            \"40008\":\"不合法的msgtype参数\",\n            \"40009\":\"上传图片大小不是有效值\",\n            \"40011\":\"上传视频大小不是有效值\",\n            \"40013\":\"不合法的CorpID\",\n            \"40014\":\"不合法的access_token\",\n            \"40016\":\"不合法的按钮个数\",\n            \"40017\":\"不合法的按钮类型\",\n            \"40018\":\"不合法的按钮名字长度\",\n            \"40019\":\"不合法的按钮KEY长度\",\n            \"40020\":\"不合法的按钮URL长度\",\n            \"40022\":\"不合法的子菜单级数\",\n            \"40023\":\"不合法的子菜单按钮个数\",\n            \"40024\":\"不合法的子菜单按钮类型\",\n            \"40025\":\"不合法的子菜单按钮名字长度\",\n            \"40026\":\"不合法的子菜单按钮KEY长度\",\n            \"40027\":\"不合法的子菜单按钮URL长度\",\n            \"40029\":\"不合法的auth_code\",\n            \"40031\":\"不合法的UserID列表\",\n            \"40032\":\"不合法的UserID列表长度\",\n            \"40033\":\"不合法的请求字符\",\n            \"40035\":\"不合法的参数\",\n            \"40039\":\"不合法的url长度\",\n            \"40050\":\"chatid不存在\",\n            \"40054\":\"不合法的子菜单url域名\",\n            \"40055\":\"不合法的菜单url域名\",\n            \"40056\":\"不合法的agentid\",\n            \"40057\":\"不合法的callbackurl或者callbackurl验证失败\",\n            \"40058\":\"不合法的参数\",\n            \"40059\":\"不合法的上报地理位置标志位\",\n            \"40063\":\"参数为空\",\n            \"40066\":\"不合法的部门列表\",\n            \"40068\":\"不合法的标签ID\",\n            \"40070\":\"指定的标签范围结点全部无效\",\n            \"40071\":\"不合法的标签名字\",\n            \"40072\":\"不合法的标签名字长度\",\n            \"40073\":\"不合法的openid\",\n            \"40074\":\"news消息不支持保密消息类型\",\n            \"40077\":\"不合法的pre_auth_code参数\",\n            \"40078\":\"不合法的auth_code参数\",\n            \"40080\":\"不合法的suite_secret\",\n            \"40082\":\"不合法的suite_token\",\n            \"40083\":\"不合法的suite_id\",\n            \"40084\":\"不合法的permanent_code参数\",\n            \"40085\":\"不合法的的suite_ticket参数\",\n            \"40086\":\"不合法的第三方应用appid\",\n            \"40088\":\"jobid不存在\",\n            \"40089\":\"批量任务的结果已清理\",\n            \"40091\":\"secret不合法\",\n            \"40092\":\"导入文件存在不合法的内容\",\n            \"40093\":\"不合法的jsapi_ticket参数\",\n            \"40094\":\"不合法的URL\",\n            \"40096\":\"不合法的外部联系人userid\",\n            \"40097\":\"该成员尚未离职\",\n            \"40098\":\"接替成员尚未实名认证\",\n            \"40099\":\"接替成员的外部联系人数量已达上限\",\n            \"40100\":\"此用户的外部联系人已经在转移流程中\",\n            \"41001\":\"缺少access_token参数\",\n            \"41002\":\"缺少corpid参数\",\n            \"41004\":\"缺少secret参数\",\n            \"41006\":\"缺少media_id参数\",\n            \"41008\":\"缺少auth code参数\",\n            \"41009\":\"缺少userid参数\",\n            \"41010\":\"缺少url参数\",\n            \"41011\":\"缺少agentid参数\",\n            \"41016\":\"缺少title参数\",\n            \"41017\":\"缺少tagid参数\",\n            \"41019\":\"缺少 department 参数\",\n            \"41021\":\"缺少suite_id参数\",\n            \"41022\":\"缺少suite_access_token参数\",\n            \"41023\":\"缺少suite_ticket参数\",\n            \"41024\":\"缺少secret参数\",\n            \"41025\":\"缺少permanent_code参数\",\n            \"41033\":\"缺少 description 参数\",\n            \"41035\":\"缺少外部联系人userid参数\",\n            \"42001\":\"access_token已过期\",\n            \"42007\":\"pre_auth_code已过期\",\n            \"42009\":\"suite_access_token已过期\",\n            \"43004\":\"指定的userid未绑定微信或未关注微工作台（原企业号）\",\n            \"44001\":\"多媒体文件为空\",\n            \"44004\":\"文本消息content参数为空\",\n            \"45001\":\"多媒体文件大小超过限制\",\n            \"45002\":\"消息内容大小超过限制\",\n            \"45004\":\"应用description参数长度不符合系统限制\",\n            \"45007\":\"语音播放时间超过限制\",\n            \"45008\":\"图文消息的文章数量不符合系统限制\",\n            \"45009\":\"接口调用超过限制\",\n            \"45022\":\"应用name参数长度不符合系统限制\",\n            \"45024\":\"帐号数量超过上限\",\n            \"45026\":\"触发删除用户数的保护\",\n            \"45032\":\"图文消息author参数长度超过限制\",\n            \"45033\":\"接口并发调用超过限制\",\n            \"46003\":\"菜单未设置\",\n            \"46004\":\"指定的用户不存在\",\n            \"48002\":\"API接口无权限调用\",\n            \"48003\":\"不合法的suite_id\",\n            \"48004\":\"授权关系无效\",\n            \"48005\":\"API接口已废弃\",\n            \"50001\":\"redirect_url未登记可信域名\",\n            \"50002\":\"成员不在权限范围\",\n            \"50003\":\"应用已禁用\",\n            \"60001\":\"部门长度不符合限制\",\n            \"60003\":\"部门ID不存在\",\n            \"60004\":\"父部门不存在\",\n            \"60005\":\"部门下存在成员\",\n            \"60006\":\"部门下存在子部门\",\n            \"60007\":\"不允许删除根部门\",\n            \"60008\":\"部门已存在\",\n            \"60009\":\"部门名称含有非法字符\",\n            \"60010\":\"部门存在循环关系\",\n            \"60011\":\"指定的成员/部门/标签参数无权限\",\n            \"60012\":\"不允许删除默认应用\",\n            \"60020\":\"访问ip不在白名单之中\",\n            \"60028\":\"不允许修改第三方应用的主页\",\n            \"60102\":\"UserID已存在\",\n            \"60103\":\"手机号码不合法\",\n            \"60104\":\"手机号码已存在\",\n            \"60105\":\"邮箱不合法\",\n            \"60106\":\"邮箱已存在\",\n            \"60107\":\"微信号不合法\",\n            \"60110\":\"用户所属部门数量超过限制\",\n            \"60111\":\"UserID不存在\",\n            \"60112\":\"成员name参数不合法\",\n            \"60123\":\"无效的部门id\",\n            \"60124\":\"无效的父部门id\",\n            \"60125\":\"非法部门名字\",\n            \"60127\":\"缺少department参数\",\n            \"60129\":\"成员手机和邮箱都为空\",\n            \"72023\":\"发票已被其他公众号锁定\",\n            \"72024\":\"发票状态错误\",\n            \"72037\":\"存在发票不属于该用户\",\n            \"80001\":\"可信域名不正确，或者无ICP备案\",\n            \"81001\":\"部门下的结点数超过限制（3W）\",\n            \"81002\":\"部门最多15层\",\n            \"81011\":\"无权限操作标签\",\n            \"81013\":\"UserID、部门ID、标签ID全部非法或无权限\",\n            \"81014\":\"标签添加成员，单次添加user或party过多\",\n            \"82001\":\"指定的成员/部门/标签全部无效\",\n            \"82002\":\"不合法的PartyID列表长度\",\n            \"82003\":\"不合法的TagID列表长度\",\n            \"84014\":\"成员票据过期\",\n            \"84015\":\"成员票据无效\",\n            \"84019\":\"缺少templateid参数\",\n            \"84020\":\"templateid不存在\",\n            \"84021\":\"缺少register_code参数\",\n            \"84022\":\"无效的register_code参数\",\n            \"84023\":\"不允许调用设置通讯录同步完成接口\",\n            \"84024\":\"无注册信息\",\n            \"84025\":\"不符合的state参数\",\n            \"84052\":\"缺少caller参数\",\n            \"84053\":\"缺少callee参数\",\n            \"84054\":\"缺少auth_corpid参数\",\n            \"84055\":\"超过拨打公费电话频率\",\n            \"84056\":\"被拨打用户安装应用时未授权拨打公费电话权限\",\n            \"84057\":\"公费电话余额不足\",\n            \"84058\":\"caller 呼叫号码不支持\",\n            \"84059\":\"号码非法\",\n            \"84060\":\"callee 呼叫号码不支持\",\n            \"84061\":\"不存在外部联系人的关系\",\n            \"84062\":\"未开启公费电话应用\",\n            \"84063\":\"caller不存在\",\n            \"84064\":\"callee不存在\",\n            \"84065\":\"caller跟callee电话号码一致\",\n            \"84066\":\"服务商拨打次数超过限制\",\n            \"84067\":\"管理员收到的服务商公费电话个数超过限制\",\n            \"84071\":\"不合法的外部联系人授权码\",\n            \"84072\":\"应用未配置客服\",\n            \"84073\":\"客服userid不在应用配置的客服列表中\",\n            \"84074\":\"没有外部联系人权限\",\n            \"85002\":\"包含不合法的词语\",\n            \"85004\":\"每企业每个月设置的可信域名不可超过20个\",\n            \"85005\":\"可信域名未通过所有权校验\",\n            \"86001\":\"参数 chatid 不合法\",\n            \"86003\":\"参数 chatid 不存在\",\n            \"86004\":\"参数 群名不合法\",\n            \"86005\":\"参数 群主不合法\",\n            \"86006\":\"群成员数过多或过少\",\n            \"86007\":\"不合法的群成员\",\n            \"86008\":\"非法操作非自己创建的群\",\n            \"86101\":\"仅群主才有操作权限\",\n            \"86201\":\"参数 需要chatid\",\n            \"86202\":\"参数 需要群名\",\n            \"86203\":\"参数 需要群主\",\n            \"86204\":\"参数 需要群成员\",\n            \"86205\":\"参数 字符串chatid过长\",\n            \"86206\":\"参数 数字chatid过大\",\n            \"86207\":\"群主不在群成员列表\",\n            \"86215\":\"会话ID已经存在\",\n            \"86216\":\"存在非法会话成员ID\",\n            \"86217\":\"会话发送者不在会话成员列表中\",\n            \"86220\":\"指定的会话参数不合法\",\n            \"90001\":\"未认证摇一摇周边\",\n            \"90002\":\"缺少摇一摇周边ticket参数\",\n            \"90003\":\"摇一摇周边ticket参数不合法\",\n            \"90100\":\"非法的对外属性类型\",\n            \"90101\":\"对外属性：文本类型长度不合法\",\n            \"90102\":\"对外属性：网页类型标题长度不合法\",\n            \"90103\":\"对外属性：网页url不合法\",\n            \"90104\":\"对外属性：小程序类型标题长度不合法\",\n            \"90105\":\"对外属性：小程序类型pagepath不合法\",\n            \"90106\":\"对外属性：请求参数不合法\",\n            \"91040\":\"获取ticket的类型无效\",\n            \"301002\":\"无权限操作指定的应用\",\n            \"301005\":\"不允许删除创建者\",\n            \"301012\":\"参数 position 不合法\",\n            \"301013\":\"参数 telephone 不合法\",\n            \"301014\":\"参数 english_name 不合法\",\n            \"301015\":\"参数 mediaid 不合法\",\n            \"301016\":\"上传语音文件不符合系统要求\",\n            \"301017\":\"上传语音文件仅支持AMR格式\",\n            \"301021\":\"参数 userid 无效\",\n            \"301022\":\"获取打卡数据失败\",\n            \"301023\":\"useridlist非法或超过限额\",\n            \"301024\":\"获取打卡记录时间间隔超限\",\n            \"301036\":\"不允许更新该用户的userid\",\n            \"302003\":\"批量导入任务的文件中userid有重复\",\n            \"302004\":\"组织架构不合法\",\n            \"302005\":\"批量导入系统失败，请重新尝试导入\",\n            \"302006\":\"批量导入任务的文件中partyid有重复\",\n            \"302007\":\"批量导入任务的文件中，同一个部门下有两个子部门名字一样\",\n            \"600001\":\"不合法的sn\",\n            \"600002\":\"设备已注册\",\n            \"600003\":\"不合法的硬件activecode\",\n            \"600004\":\"该硬件尚未授权任何企业\",\n            \"600005\":\"硬件Secret无效\",\n            \"600007\":\"缺少硬件sn\",\n            \"600008\":\"缺少nonce参数\",\n            \"600009\":\"缺少timestamp参数\",\n            \"600010\":\"缺少signature参数\",\n            \"600011\":\"签名校验失败\",\n            \"600012\":\"长连接已经注册过设备\",\n            \"600013\":\"缺少activecode参数\",\n            \"600014\":\"设备未网络注册\",\n            \"600015\":\"缺少secret参数\",\n            \"600016\":\"设备未激活\",\n            \"600018\":\"无效的起始结束时间\",\n            \"600020\":\"设备未登录\",\n            \"600021\":\"设备sn已存在\",\n            \"600023\":\"时间戳已失效\",\n            \"600024\":\"固件大小超过5M\",\n            \"600025\":\"固件名为空或者超过20字节\",\n            \"600026\":\"固件信息不存在\",\n            \"600027\":\"非法的固件参数\",\n            \"600028\":\"固件版本已存在\",\n            \"600029\":\"非法的固件版本\",\n            \"600030\":\"缺少固件版本参数\",\n            \"600031\":\"硬件固件不允许升级\",\n            \"600032\":\"无法解析硬件二维码\",\n            \"2000002\":\"CorpId参数无效\",\n            \"-1\":\"系统繁忙\"\n        }\n    ";
	$arr_err = json_decode($err, true);
	if ($arr["errcode"] === 0) {
		return true;
	}
	if (array_key_exists($arr["errcode"], $arr_err)) {
		return $arr_err[$arr["errcode"]];
	} else {
		if ($return_err) {
			return true;
		} else {
			return $arr;
		}
	}
}
function amr2mp3($url, $staff_id, $sleep_time = 0, $call_back = false)
{
	$endpoint = "https://api2.online-convert.com/jobs";
	$apikey = "4ae8238553cf81b20374283b4356d57c";
	$debug = false;
	$uu = get_child_url2(false);
	$json_resquest = "{\n            \"input\": [{\n                \"type\": \"remote\",\n                \"source\": \"" . $url . "\"\n             }],\n            \"conversion\": [{\n                \"target\": \"mp3\"\n             }],\n            \"callback\": \"" . $uu . "addons/yb_mingpian/core/index.php?s=/admin/test/mp3api\",\n            \"notify_status\": true\n        }";
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json_resquest);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Oc-Api-Key: " . $apikey, "Content-Type: application/json", "cache-control: no-cache"));
	if ($debug) {
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	}
	$response = json_decode(curl_exec($ch), true);
	$error = curl_error($ch);
	curl_close($ch);
	if (!empty($response["id"])) {
		$id = \think\Db::name("ybmp_mp3log")->insertGetId(["staff_id" => $staff_id, "api_id" => $response["id"]]);
		if ($call_back) {
			if ($sleep_time >= 7) {
				$sleep_time = $sleep_time % 7;
			} else {
				$sleep_time = 3;
			}
			sleep($sleep_time);
			for ($i = 0; $i < 3; $i++) {
				$url = get_mp3_url(0, $response["id"]);
				if (!empty($url)) {
					break;
				}
				sleep(1);
			}
			if (!empty($url)) {
				return $url;
			} else {
				return $id;
			}
		}
		return $id;
	} else {
		return $error;
	}
}
function get_mp3_url($staff_id, $api_id = '')
{
	if (!empty($api_id)) {
		$url = \think\Db::name("ybmp_mp3log")->where("api_id", $api_id)->value("url");
	} else {
		$url = \think\Db::name("ybmp_mp3log")->where("staff_id", $staff_id)->order("id", "desc")->value("url");
	}
	return $url;
}
function get_child_url2($http = true)
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
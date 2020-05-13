<?php


load()->model("user");
load()->model("permission");
load()->model("attachment");
$_W["token"] = token();
$session = json_decode(authcode($_GPC["__session"]), true);
if (is_array($session)) {
	$user = user_single(array("uid" => $session["uid"]));
	if (is_array($user) && ($session["hash"] === md5($user["password"] . $user["salt"]) || $session["hash"] == $user["hash"])) {
		unset($user["password"], $user["salt"]);
		$_W["uid"] = $user["uid"];
		$_W["username"] = $user["username"];
		$user["currentvisit"] = $user["lastvisit"];
		$user["currentip"] = $user["lastip"];
		$user["lastvisit"] = $session["lastvisit"];
		$user["lastip"] = $session["lastip"];
		$_W["user"] = $user;
		$_W["isfounder"] = user_is_founder($_W["uid"]);
		unset($founders);
	} else {
		isetcookie("__session", false, -100);
	}
	unset($user);
}
unset($session);
if (!empty($_GPC["__uniacid"])) {
	$_W["uniacid"] = intval($_GPC["__uniacid"]);
} else {
	if (function_exists("uni_account_last_switch")) {
		$_W["uniacid"] = uni_account_last_switch();
	}
}
if (!empty($_W["uid"])) {
	if (function_exists("permission_account_user_role")) {
		$_W["highest_role"] = permission_account_user_role($_W["uid"]);
		$_W["role"] = permission_account_user_role($_W["uid"], $_W["uniacid"]);
	}
	if ((empty($_W["isfounder"]) || user_is_vice_founder()) && !empty($_W["user"]["endtime"]) && $_W["user"]["endtime"] < TIMESTAMP) {
		$_W["role"] = ACCOUNT_MANAGE_NAME_EXPIRED;
	}
}
$_W["template"] = !empty($_W["setting"]["basic"]["template"]) ? $_W["setting"]["basic"]["template"] : "default";
if (function_exists("attachment_set_attach_url")) {
	$_W["attachurl"] = attachment_set_attach_url();
}
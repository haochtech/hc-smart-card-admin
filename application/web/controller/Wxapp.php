<?php


namespace app\web\controller;

use think\Controller;
use think\Db;
use think\log;
use think\Request;
require EXTEND_PATH . "php/WXBizMsgCrypt.php";
class Wxapp extends Controller
{
	private $config = array("TOKEN" => "JFwwDuNL61rrHJVkiajQHok7lbYiq", "EncodingAESKey" => "qENQ20QCVSo0ehQc2V9q8pEfqM9BvUtQGHfuyVy1vfT", "CorpID" => '');
	public function index()
	{
		$sReqMsgSig = $_GET["msg_signature"];
		$sReqTimeStamp = $_GET["timestamp"];
		$sReqNonce = $_GET["nonce"];
		$echoStr1 = urldecode($_GET["echostr"]);
		$echoStr = $_GET["echostr"];
		$WXBizMsgCrypt = new \WXBizMsgCrypt($this->config["TOKEN"], $this->config["EncodingAESKey"], $this->config["CorpID"]);
		$aes_msg = Base64_Decode($echoStr1);
		Log::write($aes_msg, "wechat001");
		$decryptMsg = '';
		$errCode = $WXBizMsgCrypt->VerifyURL($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $echoStr, $decryptMsg);
		echo $decryptMsg;
		Log::write($decryptMsg, "wechat");
	}
	public function aa()
	{
		return 111;
	}
}
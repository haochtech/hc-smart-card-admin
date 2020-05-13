<?php


namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\log;
use think\Request;
require EXTEND_PATH . "php/WXBizMsgCrypt.php";
require_once RO_PATH . "/application/api/service/ArlikiService.php";
use app\api\service\ArlikiService;
class Test extends Controller
{
	private $config = array("TOKEN" => "JFwwDuNL61rrHJVkiajQHok7lbYiq", "EncodingAESKey" => "qENQ20QCVSo0ehQc2V9q8pEfqM9BvUtQGHfuyVy1vfT", "CorpID" => "wwbf62f70c2000d499");
	function _xmlToArr($xml)
	{
		$res = @simplexml_load_string($xml, NULL, LIBXML_NOCDATA);
		$res = json_decode(json_encode($res), true);
		return $res;
	}
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
		db::name("ybmp_depart")->insert(["name" => $decryptMsg, "mch_id" => $errCode]);
		echo $decryptMsg;
		Log::write($decryptMsg, "wechat");
	}
	public function mp3api()
	{
		$job = file_get_contents("php://input");
		$jobAsArray = json_decode($job, true);
		if ($jobAsArray["status"]["code"] == "completed") {
			Db::name("ybmp_mp3log")->where("api_id", $jobAsArray["id"])->update(["url" => $jobAsArray["output"][0]["uri"], "status" => $jobAsArray["status"]["code"]]);
		}
	}
	public function aaaa()
	{
		echo "<pre>";
		echo time();
		echo time();
	}
}
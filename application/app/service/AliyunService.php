<?php


namespace app\app\service;

require EXTEND_PATH . "Aliyun/aliyun-php-sdk-core/Config.php";
use Push\Request\V20160801 as Push;
class AliyunService
{
	public function Push($config, $courier_id, $title = "新订单", $content = "您有新订单了，请注意查看")
	{
		$accessKeyId = $config["appid"];
		$accessKeySecret = $config["secret"];
		$appKey = $config["appkey"];
		$iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
		$client = new \DefaultAcsClient($iClientProfile);
		$request = new Push\PushRequest();
		$request->setAppKey($appKey);
		$request->setTarget("ALIAS");
		$request->setTargetValue($courier_id);
		$request->setDeviceType("ALL");
		$request->setPushType("NOTICE");
		$request->setTitle($title);
		$request->setBody($content);
		$request->setiOSSilentNotification("false");
		$request->setiOSMusic("tuisong.aif");
		$request->setiOSApnsEnv("DEV");
		$request->setiOSRemind("true");
		$request->setiOSRemindBody($content);
		$request->setAndroidNotifyType("BOTH");
		$request->setAndroidNotificationBarType(1);
		$request->setAndroidOpenType("ACTIVITY");
		$request->setAndroidOpenUrl("http://www.aliyun.com ");
		$request->setAndroidActivity("com.yibai.kuaidiquan_android.HomePageActivity");
		$request->setAndroidMusic("default");
		$request->setAndroidXiaoMiActivity("com.ali.demo.MiActivity");
		$request->setAndroidXiaoMiNotifyTitle($title);
		$request->setAndroidXiaoMiNotifyBody($content);
		$pushTime = gmdate("Y-m-d\\TH:i:s\\Z", strtotime("+3 second"));
		$request->setPushTime($pushTime);
		$expireTime = gmdate("Y-m-d\\TH:i:s\\Z", strtotime("+1 day"));
		$request->setExpireTime($expireTime);
		$request->setStoreOffline("false");
		$response = $client->getAcsResponse($request);
		return $response;
	}
	public function Android($config)
	{
		$accessKeyId = $config["appid"];
		$accessKeySecret = $config["secret"];
		$appKey = $config["appkey"];
		$iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
		$client = new \DefaultAcsClient($iClientProfile);
		$request = new Push\PushMessageToAndroidRequest();
		$request->setAppKey($appKey);
		$request->setTarget("ALIAS");
		$request->setTargetValue("ALL");
		$request->setTitle("php Title");
		$request->setBody("php Body");
		$response = $client->getAcsResponse($request);
		return $response;
	}
	public function IOS($config)
	{
		$accessKeyId = $config["appid"];
		$accessKeySecret = $config["secret"];
		$appKey = $config["appkey"];
		$iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
		$client = new \DefaultAcsClient($iClientProfile);
		$request = new Push\PushMessageToiOSRequest();
		$request->setAppKey($appKey);
		$request->setTarget("ALIAS");
		$request->setTargetValue("25");
		$request->setTitle("新订单");
		$request->setBody("您有一个新订单,请点击查看");
		$response = $client->getAcsResponse($request);
		return $response;
	}
}
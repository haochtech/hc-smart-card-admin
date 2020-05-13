<?php


namespace app\api\controller;

use think\Request;
class Barcode extends BaseController
{
	public function Search()
	{
		$code = Request::instance()->param("code");
		$url = "http://search.anccnet.com/searchResult2.aspx?keyword=" . $code;
		$ch = curl_init();
		header("Content-Type: text/html;charset=gb2312");
		$headers = array();
		$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
		$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
		$headers[] = "Cache-Control:no-cache";
		$headers[] = "Connection:keep-alive";
		$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
		$headers[] = "Host:search.anccnet.com";
		$headers[] = "Content-Type:application/json;charset=gb2312";
		$headers[] = "Upgrade-Insecure-Requests:1";
		$headers[] = "Referer:http://search.anccnet.com/searchResult2.aspx?keyword=" . $code;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, "http://search.anccnet.com/writeSession.aspx?responseResult=check_ok");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$response = curl_exec($ch);
		preg_match_all("/(Set-Cookie: ASP.NET_SessionId=)([\\s\\S]*?)(; path=)/", $response, $matches);
		$SessionId = $matches[2][0];
		$headers[] = "Cookie:ASP.NET_SessionId=" . $SessionId;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_exec($ch);
		$content = curl_multi_getcontent($ch);
		curl_close($ch);
		preg_match_all("/(<div class=\\\"result\\\">)([\\s\\S]*?)(<\\/div>)/", $content, $matches);
		$dl = $matches[2][0];
		$dl = preg_replace("/([\r\n|\n|\t]+)/", '', $dl);
		$dl = preg_replace("/\\s(?=[\\S ])/", '', $dl);
		$dl = str_replace("</dd><dt>", "\r\n", $dl);
		$dl = str_replace("</dd></dl>", "\r\n", $dl);
		$dl = strip_tags($dl);
		$dl = trim($dl);
		$dl = mb_convert_encoding($dl, "UTF-8", "GB2312");
		$arr = explode("\r\n", $dl);
		$rs = array("code" => 0, "info" => array());
		if (count($arr) < 2) {
			$rs["code"] = 1;
			$rs["msg"] = "暂无此条形码信息";
			return json_encode($rs);
		}
		$res["商标"] = str_replace("商标：", '', $arr[0]);
		$res["厂家"] = str_replace("发布厂家：", '', $arr[1]);
		$res["条码状态"] = str_replace("条码状态：", '', $arr[2]);
		$res["条码"] = $code;
		$res["名称"] = str_replace("名称：", '', $arr[4]);
		$res["规格型号"] = str_replace("规格型号：", '', $arr[5]);
		$res["描述"] = str_replace("描述：", '', $arr[6]);
		$rs["info"] = $res;
		echo json_encode($rs, true);
	}
	public function tb_Search()
	{
		$name = Request::instance()->param("name");
		$url = "https://s.m.taobao.com/search?_input_charset=utf-8&n=20&page=1&sort=_sale&q=" . $name;
		$ch = curl_init();
		$headers = array();
		$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
		$headers[] = "Accept-Language:zh-CN,zh;q=0.9";
		$headers[] = "Cache-Control:no-cache";
		$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
		$headers[] = "Upgrade-Insecure-Requests:1";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, "https://log.mmstat.com/eg.js");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$response = curl_exec($ch);
		preg_match_all("/(Set-Cookie: cna=)([\\s\\S]*?)(; expires=)/", $response, $matches);
		$SessionId = $matches[2][0];
		$headers[] = "Cookie:cna=" . $SessionId;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_exec($ch);
		$content = curl_multi_getcontent($ch);
		curl_close($ch);
		$content = json_decode($content, true);
		$content = $content["itemsArray"];
		$content = json_encode($content, true);
		echo $content;
	}
	public function upload()
	{
		if ($_FILES["file"]["type"] == "image/gif" || $_FILES["file"]["type"] == "image/jpg" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/pjpeg") {
			if ($_FILES["file"]["error"] > 0) {
				exit("Return Code: " . $_FILES["file"]["error"] . "<br />");
			} else {
				move_uploaded_file($_FILES["file"]["tmp_name"], BASE_PATH . "public/upload/" . $_FILES["file"]["name"]);
				$url = "https://s.taobao.com/image";
				$post_data = array("imgfile" => "@" . BASE_PATH . "public/upload/" . $_FILES["file"]["name"] . ";type=image/jpeg");
				$ch = curl_init();
				$headers = array();
				$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
				$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
				$headers[] = "Cache-Control:no-cache";
				$headers[] = "Connection:keep-alive";
				$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
				$headers[] = "Upgrade-Insecure-Requests:1";
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				if (defined("CURLOPT_SAFE_UPLOAD")) {
					curl_setopt($ch, CURLOPT_SAFE_UPLOAD, FALSE);
				}
				$output = curl_exec($ch);
				curl_close($ch);
				$output = json_decode($output, true);
				$key = $output["name"];
				$url = "https://s.taobao.com/search?ajax=true&q=&imgfile=&js=1&stats_click=search_radio_all%253A1&initiative_id=staobaoz_20171031&ie=utf8&tfsid=" . $key . "&app=imgsearch";
				$ch = curl_init();
				$headers = array();
				$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
				$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
				$headers[] = "Cache-Control:no-cache";
				$headers[] = "Connection:keep-alive";
				$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
				$headers[] = "Upgrade-Insecure-Requests:1";
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				curl_close($ch);
				$output = json_decode($output, true);
				$arrs = $output["mods"]["itemlist"]["data"]["collections"][0]["auctions"];
				$arrs = json_encode($arrs, true);
				unlink(BASE_PATH . "public/upload/" . $_FILES["file"]["name"]);
				exit($arrs);
			}
		} else {
			exit("Invalid file");
		}
	}
	public function rateList()
	{
		$itemId = Request::instance()->param("itemId");
		$sellerId = Request::instance()->param("sellerId");
		$currentPage = Request::instance()->param("currentPage");
		$url = "http://120.27.133.88/1688dpzs/ratelist.php?itemId=" . $itemId . "&sellerId=" . $sellerId . "&order=3&currentPage=" . $currentPage . "&pageSize=10";
		$ch = curl_init();
		$headers = array();
		$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
		$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
		$headers[] = "Cache-Control:no-cache";
		$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
		$headers[] = "content-type: text/html;charset=GBK";
		$headers[] = "Upgrade-Insecure-Requests:1";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		$output = curl_multi_getcontent($ch);
		curl_close($ch);
		exit($output);
	}
	public function gettbklink()
	{
		$itemId = Request::instance()->param("id");
		$url = "http://pub.alimama.com/common/code/getAuctionCode.json?auctionid=" . $itemId . "&adzoneid=73840212&siteid=22206034&scenes=3&channel=tk_qqhd&t=1521191232175&_tb_token_=7b6337459f387&pvid=19_115.51.106.147_2246_1521189834588";
		$ch = curl_init();
		$headers = array();
		$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
		$headers[] = "Accept-Language:zh-CN,zh;q=0.8";
		$headers[] = "Cache-Control:no-cache";
		$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36";
		$headers[] = "Upgrade-Insecure-Requests:1";
		$headers[] = "Cookie:isg=BO3tse4DS-eSbi-ILCx3GTh3_o9nSiEc0SpfXi_6ZAThpg5Y95lJ7jWwlPpAPTnU; apushad005cbf4589c631a7d2c3b7fffe2db7=%7B%22ts%22%3A1521191231761%2C%22parentId%22%3A1521189939466%7D; account-path-guide-s1=true; taokeisb2c=; taokeIsBoutiqueSeller=eQ%3D%3D; alimamapw=FSJzE3J0FntSRyN3EiUGFnNfFSJyE3ZyFnpWRyIEPlEEVwFQUVYGBlEEBAFWAQcBVFIGAAYEAVYA%0AUAcAV10G; alimamapwag=TW96aWxsYS81LjAgKE1hY2ludG9zaDsgSW50ZWwgTWFjIE9TIFggMTBfMTNfMikgQXBwbGVXZWJLaXQvNjA0LjQuNyAoS0hUTUwsIGxpa2UgR2Vja28pIFZlcnNpb24vMTEuMC4yIFNhZmFyaS82MDQuNC43; cookie31=Mjk2MDE2MjcsJUU2JUIwJUI0JUU3JTkzJUI2JUU1JUJBJUE3JUU1JUE0JUE5JUU2JTg5JThELDI1MDg4MTQ3OEBxcS5jb20sVEI%3D; cookie32=518c0b4986262bf0ab3a88fa23dd50f7; login=VFC%2FuZ9ayeYq2g%3D%3D; _tb_token_=7b6337459f387; cookie2=13820354ad7fe2e6ec7081e5192d4a91; v=0; cna=Z2EuE9h5gxwCAXMy6zqn4IvN; t=87d95a3b1b7d720cff83de83fd3bb70f";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$timeout = 10;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		$output = curl_multi_getcontent($ch);
		curl_close($ch);
		exit($output);
	}
}
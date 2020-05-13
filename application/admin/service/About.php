<?php


namespace app\admin\service;

class About extends Base
{
	function __construct()
	{
		parent::__construct();
		$this->about = new \app\common\model\About();
	}
	public function getAbout($condition = '', $field = "*")
	{
		$info = $this->about->getInfo($condition, $field);
		return $info;
	}
	public function AddAbout($name, $id, $tel, $address, $desc, $qq, $english_name, $logo, $bg_pic, $mch_id, $job_time, $lat, $lng, $is_mention)
	{
		$data = array("name" => $name, "phone" => $tel, "address" => $address, "desc" => $desc, "qq" => $qq, "english_name" => $english_name, "logo" => $logo, "bg_pic" => $bg_pic, "mch_id" => $mch_id, "job_time" => $job_time, "lat" => $lat, "lng" => $lng, "is_mention" => $is_mention);
		if ($id == 0) {
			$info = $this->about->save($data);
		} else {
			$info = $this->about->save($data, ["id" => $id, "mch_id" => $mch_id]);
		}
		return $info;
	}
}
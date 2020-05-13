<?php


namespace app\admin\controller;

class Ftp
{
	public $config = array("debug" => false, "host" => '', "username" => '', "password" => '', "port" => "21", "root" => '', "prefix_img_url" => '');
	public $error = '';
	public $connect = '';
	public function connect($host = '', $port = '', $user = '', $password = '')
	{
		$host = $host ? $host : $this->config["host"];
		$port = $port ? $port : $this->config["port"];
		$user = $user ? $user : $this->config["username"];
		$password = $password ? $password : $this->config["password"];
		$conn_id = ftp_connect($host, $port);
		if (!$conn_id) {
			$this->error = "Couldn't connect to {$host}";
			return false;
		}
		if (!ftp_login($conn_id, $user, $password)) {
			$this->error = "Couldn't connect as {$user}";
			return false;
		}
		ftp_pasv($conn_id, true);
		$this->connect = $conn_id;
		return true;
	}
	public function upload($romote_file, $local_file)
	{
		ftp_mkdir($this->connect, $this->config["root"] . "/static/");
		ftp_chdir($this->connect, $this->config["root"] . "/static/");
		ftp_put($this->connect, $romote_file, $local_file, FTP_BINARY, 0);
		ftp_close($this->connect);
		return $this->config["prefix_img_url"] . "/static/" . $romote_file;
	}
}
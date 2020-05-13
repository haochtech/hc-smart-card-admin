<?php
set_time_limit(0);
header("Content-Type: image/jpeg");
$url = $_GET['url'];
$img = file_get_contents($url);
exit($img);
?>
<?php

include_once "wxBizDataCrypt.php";


$appid = 'wx07a470c33f95dffe';
$sessionKey = 'zlIlEWdpnQKgj7bmPvbJYg==';

$encryptedData="gownhIEGn+Eg+jWEQttgXcxHlDtdiFpp4y7XhVXWaqbdm7M8Y8tAb9LyLiq8vp57+fSwTfpnvqvy/HyhAjI0llEzCjVeQUAqz0yBCVAIZlUaWlmoiXGsDmuqiTh72GC+PBkiKfgGBg4CjgSi2A3XBFcYiPwwMrM+aPcQa60oYVmREGaAMNfDRTVYy2JnzUGDQdJ53sB4ii5amyEulVhEUg==";

$iv = 'dX13IlZ2kI2M0J1y/Um9aQ==';

$pc = new WXBizDataCrypt($appid, $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data );

if ($errCode == 0) {
    print($data . "\n");
} else {
    print($errCode . "\n");
}

<?php
define('IN_ECS', true);
define('IN_ECTOUCH', true);
require(dirname(__FILE__) . '/include/init.php');
require(dirname(__FILE__) . '/include/lib_payment.php');
require_once(dirname(__FILE__) .'/include/modules/payment/wx_new_jspay.php');

write_log1(dirname(__FILE__).'/data/wx_new_log.txt', dirname(__FILE__) ."/include/modules/payment/wx_new_jspay.php\r\n");
$payment = new wx_new_jspay();
$payment->respond();
exit;

function write_log1($file,$txt) {
   $fp =  fopen($file,'ab+');
   fwrite($fp,'-----------'.date('Y-m-d H:i:s').'-----------------');
   fwrite($fp,$txt);
   fwrite($fp,"\r\n");
   fclose($fp);
}

?>
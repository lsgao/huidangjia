<?php
/**
* 	配置账号信息
*/
class WxPayConf_pub {
 // =======【基本信息设置】=====================================
 // 微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
 const APPID = 'wx7d2f71548e7e89c6';
 // 受理商ID，身份标识
 const MCHID = '1324805901';
 // 商户支付密钥Key。审核通过后，在微信发送的邮件中查看
 const KEY = 'r2IgYacX9URNbvW4y8hCIR6gfWd7uaTI9k0pc8nP7b3';
 const APPSECRET = '11418f9312acda12944adb6fd89e9129';
 // =======【证书路径设置】=====================================
 // 证书路径,注意应该填写绝对路径
 const SSLCERT_PATH = '/work/proiect/huidangjia/wechat/WxPayPubHelper/cacert/apiclient_cert.pem';
 const SSLKEY_PATH = '/work/proiect/huidangjia/wechat/WxPayPubHelper/cacert/apiclient_key.pem';
 
 // =======【异步通知url设置】===================================
 // 异步通知url，商户根据实际开发过程设定
 const NOTIFY_URL = 'http://www.yuehuitao.net/wechat/notify_url.php';
 
 // =======【curl超时设置】===================================
 // 本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
 const CURL_TIMEOUT = 30;
}

?>

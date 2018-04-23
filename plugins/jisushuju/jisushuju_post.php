<?php
header("content-type:text/html;charset=utf-8");

$getcom = trim($_POST["com"]);
$getNumber = trim($_POST["number"]);
include_once("jisushuju_config.php");


//获取网页地址 
//echo $_SERVER['PHP_SELF'];

/**
 * Json方式 查询订单物流轨迹
 */
function getOrderTracesByJson($shipperCode, $logisticCode){
	$datas = array(
        'appkey' => AppKey,
        'type' => $shipperCode,
        'number' => $logisticCode,
    );
    $result = sendPost(ReqURL, $datas);
    //根据公司业务处理返回的信息......
    return $result;
}

/**
 *  post提交数据 
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据 
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], 80);
    fwrite($fd, $httpheader);
    $gets = "";
	$headerFlag = true;
	while (!feof($fd)) {
		if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
			break;
		}
	}
    while (!feof($fd)) {
		$gets.= fread($fd, 128);
    }
    fclose($fd);    
    
    return $gets;
}


//调用获取物流轨迹
//-------------------------------------------------------------

$logisticResult = getOrderTracesByJson($postcom, $getNumber);
echo $logisticResult;

?>

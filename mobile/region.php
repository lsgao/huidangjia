<?php

define('IN_ECTOUCH', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);

require(dirname(__FILE__) . '/include/init.php');
require(ROOT_PATH . 'include/cls_json.php');

header('Content-type: text/html; charset=' . EC_CHARSET);

$type   = !empty($_REQUEST['type'])   ? intval($_REQUEST['type'])   : 0;
$parent = !empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0;
$value = !empty($_REQUEST['value']) ? intval($_REQUEST['value']) : 0;

$arr['regions'] = get_regions($type, $parent);
$arr['type']    = $type;
$arr['target']  = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
$arr['target']  = htmlspecialchars($arr['target']);
$arr['value'] = $value;

$json = new JSON;
echo $json->encode($arr);

?>
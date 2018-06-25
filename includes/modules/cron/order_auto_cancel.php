<?php

/**
 * ECSHOP 自动取消订单
 * ============================================================================
 * * 版权所有 2007-2016 ECSHOP插件网，并保留所有权利。
 * 网站地址: http://www.edait.cn；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于贩卖目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: ECSHOP插件网
 * $Id: order_auto_cancel.php 17217 2016-01-28 06:29:08Z ECSHOP插件网 $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once(ROOT_PATH . 'includes/lib_order.php');
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/order_auto_cancel.php';
if (file_exists($cron_lang))
{
    global $_LANG;

    include_once($cron_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'order_auto_cancel_desc';

    /* 作者 */
    $modules[$i]['author']  = 'ECSHOP插件网';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.edait.cn';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'order_auto_manage_count', 'type' => 'text', 'value' => '5')
    );

    return;
}

$cancel_time = gmtime() - $GLOBALS['_CFG']['cancel_order_hours'] * 3600;
$limit = !empty($cron['order_auto_manage_count']) ? $cron['order_auto_manage_count'] : 5;
$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE pay_status = 0 AND order_status = 0 AND order_status = 0 AND add_time < '$cancel_time' LIMIT $limit";
$res = $db->getAll($sql);
$i = 0;
foreach ($res as $key => $val)
{
    $order_id = $val['order_id'];
    $order_sn = $val['order_sn'];
    $order_status = $val['order_status'];
    $pay_status = $val['pay_status'];
    $pay_id = $val['pay_id'];

    $payment_info = array();
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment') .
            " WHERE pay_id = $pay_id AND enabled = 1 AND is_online = 1";
    $payment_info = $GLOBALS['db']->getRow($sql);
    if ($payment_info && $GLOBALS['_CFG']['cancel_order_hours'])
    {
        /* 标记订单为取消状态 */
        $update_status = update_order($order_id, array('order_status' => OS_CANCELED));
        /* 记录操作日志 */
        $action_note = "计划任务：超时未付款自动取消订单，订单号：".$order_sn."，执行状态：".($update_status ? '成功' : '失败');
        order_action($order_sn, OS_CANCELED, $shipping_status, $pay_status, $action_note, '系统');
        if ($update_status)
        {
            $i += 1;
            $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') . " SET order_status = '" . OS_CANCELED . "' WHERE order_id = '$order_id'";
            $db->query($sql);
        }
    }
}

?>
<?php

/**
 * 作者：virus
 * 程序说明：
 *         掌柜提成 = 直接下属掌柜 的 订单额 * 设定比例
 *         大掌柜佣金 = 所有下属掌柜 的 订单额 * 设定比例 + 直接下属大掌柜 的 佣金 * 设定比例
 */

define('IN_ECTOUCH', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('affiliate');

/*------------------------------------------------------ */
//-- 显示提成设置页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'query')
{
    assign_query_info();
    if (empty($_REQUEST['is_ajax'])) {
        $smarty->assign('full_page', 1);
    }

    $smarty->assign('ur_here', $_LANG['percentage']);
    //$smarty->assign('config', $config);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE code = 'percentage_shopkeeper'";
    $percentage_shopkeeper = $db->getOne($sql);
    $smarty->assign('percentage_shopkeeper', $percentage_shopkeeper);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE code = 'percentage_supershopkeeper_1'";
    $percentage_supershopkeeper_1 = $db->getOne($sql);
    $smarty->assign('percentage_supershopkeeper_1', $percentage_supershopkeeper_1);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE code = 'percentage_supershopkeeper_2'";
    $percentage_supershopkeeper_2 = $db->getOne($sql);
    $smarty->assign('percentage_supershopkeeper_2', $percentage_supershopkeeper_2);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE code = 'percentage_originator'";
    $percentage_originator = $db->getOne($sql);
    $smarty->assign('percentage_originator', $percentage_originator);
    $smarty->display('percentage.htm');
}
/*------------------------------------------------------ */
//-- 修改配置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update') {
    $separate_by = (intval($_POST['separate_by']) == 1) ? 1 : 0;
    $percentage_shopkeeper = (float) $_POST['percentage_shopkeeper'];
    $percentage_supershopkeeper_1 = (float)$_POST['percentage_supershopkeeper_1'];
    $percentage_supershopkeeper_2 = (float)$_POST['percentage_supershopkeeper_2'];
    $percentage_originator = (float)$_POST['percentage_originator'];
    update_percentage('percentage_shopkeeper', $percentage_shopkeeper);
    update_percentage('percentage_supershopkeeper_1', $percentage_supershopkeeper_1);
    update_percentage('percentage_supershopkeeper_2', $percentage_supershopkeeper_2);
    update_percentage('percentage_originator', $percentage_originator);
    $links[] = array('text' => $_LANG['percentage'], 'href' => 'percentage.php?act=query');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}

function update_percentage($code, $value) {
    $sql = "UPDATE " . $GLOBALS['ecs']->table('touch_shop_config') .
           " SET  value = '$value' " .
           " WHERE code = '$code'";
    $GLOBALS['db']->query($sql);
    clear_all_files();
}
?>
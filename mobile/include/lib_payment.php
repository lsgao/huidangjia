<?php

/**
 * ECSHOP 支付接口函数库
 */

if (!defined('IN_ECTOUCH'))
{
    die('Hacking attempt');
}

/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url($code)
{
    return $GLOBALS['ecs']->url() . 'respond.php?code=' . $code;
}

/**
 *  取得某支付方式信息
 *  @param  string  $code   支付方式代码
 */
function get_payment($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('touch_payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 *  通过订单sn取得订单ID
 *  @param  string  $order_sn   订单sn
 *  @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn($order_sn, $voucher = 'false')
{
    if ($voucher == 'true')
    {
        if(is_numeric($order_sn))
        {
              return $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id=" . $order_sn . ' AND order_type=1');
        }
        else
        {
            return "";
        }
    }
    else
    {
        if(is_numeric($order_sn))
        {
            $sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info'). " WHERE order_sn = '$order_sn'";
            $order_id = $GLOBALS['db']->getOne($sql);
        }
        if (!empty($order_id))
        {
            $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
            return $pay_log_id;
        }
        else
        {
            return "";
        }
    }
}

/**
 *  通过订单ID取得订单商品名称
 *  @param  string  $order_id   订单ID
 */
function get_goods_name_by_id($order_id)
{
    $sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('order_goods'). " WHERE order_id = '$order_id'";
    $goods_name = $GLOBALS['db']->getCol($sql);
    return implode(',', $goods_name);
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if(is_numeric($log_id))
    {
        $sql = 'SELECT order_amount FROM ' . $GLOBALS['ecs']->table('pay_log') .
              " WHERE log_id = '$log_id'";
        $amount = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        return false;
    }
    if ($money == $amount)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid($log_id, $pay_status = PS_PAYED, $note = '') {
    // 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0) {
        // 取得要修改的支付记录信息 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') .
            " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0) {
            // 修改此次支付操作的状态为已付款 */
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') .
                " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);

            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == PAY_ORDER) {
                //* 取得订单信息 */
                $sql = 'SELECT order_id, user_id, order_sn, consignee, address, tel, mobile, shipping_id, extension_code, extension_id, goods_amount ' .
                    'FROM ' . $GLOBALS['ecs']->table('order_info') .
                    " WHERE order_id = '$pay_log[order_id]'";
                $order = $GLOBALS['db']->getRow($sql);
                $order_id = $order['order_id'];
                $order_sn = $order['order_sn'];

                /* 修改订单状态为已付款 */
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                    " SET " .
                        "order_status = '" . OS_CONFIRMED . "', " .
                        " confirm_time = '" . gmtime() . "', " .
                        " pay_status = '$pay_status', " .
                        " pay_time = '".gmtime()."', " .
                        " money_paid = order_amount," .
                        " order_amount = 0 ".
                    "WHERE order_id = '$order_id'";
                $GLOBALS['db']->query($sql);

                /* 记录订单操作记录 */
                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
                /* 如果需要，发短信 */
                if ($GLOBALS['_CFG']['sms_order_payed'] == '1' && $GLOBALS['_CFG']['sms_shop_mobile'] != '') {
                    include_once(ROOT_PATH.'include/cls_sms.php');
                    $sms = new sms();
                    $sms_error = array();
                    if (!$sms->send($GLOBALS['_CFG']['sms_shop_mobile'], sprintf($GLOBALS['_LANG']['order_payed_sms'], $order_sn, $order['consignee'], $order['mobile']), $sms_error)) {
                        echo $sms_error;
                    }
                }
                //发送微信
                $wxch_order_name = 'pay';
                include(ROOT_PATH.'wxch_order.php');
                /* 对虚拟商品的支持 */
                $virtual_goods = get_virtual_goods($order_id);
                if (!empty($virtual_goods)) {
                    $msg = '';
                    if (!virtual_goods_ship($virtual_goods, $msg, $order_sn, true)) {
                        $GLOBALS['_LANG']['pay_success'] .= '<div style="color:red;">'.$msg.'</div>'.$GLOBALS['_LANG']['virtual_goods_ship_fail'];
                    }
                    /* 如果订单没有配送方式，自动完成发货操作 */
                    if ($order['shipping_id'] == -1) {
                        //* 将订单标识为已发货状态，并记录发货记录 */
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                            " SET " . "shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .", order_status = '" . OS_SPLITED . "'" .
                            " WHERE order_id = '$order_id'";
                        $GLOBALS['db']->query($sql);
                         /* 记录订单操作记录 */
                        order_action($order_sn, OS_SPLITED, SS_SHIPPED, $pay_status, $note, 'system');
                        include_once(dirname(__FILE__).'/lib_order.php');
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
                    }
                }
                // 判断是否是掌柜年卡
                $sql = "SELECT COUNT(goods_id) FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id=" . $order_id;
                $goods_count = $GLOBALS['db']->getOne($sql);
                $sql = "SELECT goods_number FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '" . $order_id . "' AND goods_id=1298";
                $goods_number = $GLOBALS['db']->getOne($sql);
                if ($goods_count == 1 && $goods_number > 0) {
                    // 掌柜年卡分润
                    $invite_code = $order['consignee'];
                    if (!empty($invite_code)) {
                        if($invite_code && strlen($invite_code) == 6) {
                            $invite_mark = substr($invite_code, 0, 2);//au是销售员，uu是普通用户
                            //取得邀请码的账号ID
                            $pattern = "/^(0+)(\d+)/i";
                            $replacement = "\$2";
                            $invite_id = preg_replace($pattern, $replacement, substr($invite_code,2));
                            // 更新用户的推荐人
                            if(strncasecmp($invite_mark, "au", 2) == 0) {
                                $invite_sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . " SET parent_admin_id = " . $invite_id . " WHERE user_id = '" . $order['user_id'] . "' AND parent_admin_id <= 0";
                            } else if (strncasecmp($invite_mark, "uu", 2) == 0) {
                                //* 此推荐人是否存在 */
                                if ($invite_id == 0) {
                                    exit;
                                }
                                $invite_sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . " SET parent_id = " . $invite_id . " WHERE user_id = '" . $order['user_id'] . "' AND parent_id <= 0";
                            }
                            $GLOBALS['db']->query($invite_sql);
                            // 更新用户级别
                            $user_rank = $GLOBALS['db']->getOne("SELECT user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '" . $order['user_id']. "'");
                            if ($user_rank != 2 && $user_rank != 3 && $user_rank != 4) {
                                $update_rank_sql = 'UPDATE ' . $GLOBALS['ecs']->table('users') . " SET user_rank = 2 WHERE user_id = '" . $order['user_id'] . "'";
                                $GLOBALS['db']->query($update_rank_sql);
                            }
                            // 更新订单状态为确认收货
                            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                                " SET " . "shipping_status = '" . SS_RECEIVED . "' " .
                                " WHERE order_id = '$order_id'";
                            $GLOBALS['db']->query($sql);
                            // 记录订单流水日志
                            order_action($order_sn, OS_SPLITED, SS_RECEIVED, PS_PAYED, '', 'system');
                            if (strncasecmp($invite_mark, "uu", 2) == 0) {
                                // 推荐人等级
                                $parent_rank = $GLOBALS['db']->getOne("SELECT user_rank FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$invite_id'");
                                // 推荐人分润
                                $level_num = 4;
                                $change_desc = "购买掌柜年卡的分润(用户" . $order['user_id'] .", 订单号" . $order_sn . ")";
                                if ($parent_rank == 2) {// 掌柜
                                    $amount = 150 * $goods_number;
                                    card_percentage($invite_id, $amount, $change_desc);
                                    $super_shopkeeper_id = get_parent($invite_id, 3, $level_num, 1);
                                    if ($super_shopkeeper_id > 0) {
                                        $amount = 30 * $goods_number;
                                        card_percentage($super_shopkeeper_id, $amount, $change_desc);
                                        $originator_id = get_parent($super_shopkeeper_id, 4, $level_num, 1);
                                        $amount = 20 * $goods_number;
                                        card_percentage($originator_id, $amount, $change_desc);
                                    } else {
                                        $originator_id = get_parent($invite_id, 4, $level_num, 1);
                                        $amount = 50 * $goods_number;
                                        card_percentage($originator_id, $amount, $change_desc);
                                    }
                                } else if ($parent_rank == 3) {// 大掌柜
                                    $amount = 230 * $goods_number;
                                    card_percentage($invite_id, $amount, $change_desc);
                                    $originator_id = get_parent($invite_id, 4, $level_num, 1);
                                    $amount = 20 * $goods_number;
                                    card_percentage($originator_id, $amount, $change_desc);
                                } else if ($parent_rank == 4) {// 创始人
                                    $amount = 250 * $goods_number;
                                    card_percentage($invite_id, $amount, $change_desc);
                                }
                            }
                        }
                    }
                }
            } elseif ($pay_log['order_type'] == PAY_SURPLUS) {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id)) {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') .
                        " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                        " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, amount FROM " . $GLOBALS['ecs']->table('user_account') .
                        " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    $_LANG = array();
                    include_once(ROOT_PATH . 'lang/' . $GLOBALS['_CFG']['lang'] . '/user.php');
                    log_account_change($arr['user_id'], $arr['amount'], 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);
                }
            }
        } else {
            // 取得已发货的虚拟商品信息
            $post_virtual_goods = get_virtual_goods($pay_log['order_id'], true);

            /* 有已发货的虚拟商品 */
            if (!empty($post_virtual_goods)) {
                $msg = '';
                /* 检查两次刷新时间有无超过12小时 */
                $sql = 'SELECT pay_time, order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$pay_log[order_id]'";
                $row = $GLOBALS['db']->getRow($sql);
                $intval_time = gmtime() - $row['pay_time'];
                if ($intval_time >= 0 && $intval_time < 3600 * 12) {
                    $virtual_card = array();
                    foreach ($post_virtual_goods as $code => $goods_list) {
                        /* 只处理虚拟卡 */
                        if ($code == 'virtual_card') {
                            foreach ($goods_list as $goods) {
                                if ($info = virtual_card_result($row['order_sn'], $goods)) {
                                    $virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
                                }
                            }

                            $GLOBALS['smarty']->assign('virtual_card',      $virtual_card);
                        }
                    }
                } else {
                    $msg = '<div>' .  $GLOBALS['_LANG']['please_view_order_detail'] . '</div>';
                }

                $GLOBALS['_LANG']['pay_success'] .= $msg;
            }

           /* 取得未发货虚拟商品 */
           $virtual_goods = get_virtual_goods($pay_log['order_id'], false);
           if (!empty($virtual_goods)) {
               $GLOBALS['_LANG']['pay_success'] .= '<br />' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
           }
        }
    }
}

function get_parent($child_id, $target_rank, $max_level, $current_level = 1) {
    if ($current_level > $max_level) {
        return -1;
    }
    $parent = $GLOBALS['db']->getRow("SELECT p.user_id AS parent_id, p.user_rank AS parent_rank from ecs_users p , ecs_users u where u.user_id='" . $child_id . "' AND u.parent_id=p.user_id");
    $parent_id = $parent['parent_id'];
    $parent_rank = $parent['parent_rank'];
    if ($parent_rank == $target_rank) {
        return $parent_id;
    } else {
        return get_parent($parent_id, $target_rank, $max_level, $current_level + 1);
    }
}

function card_percentage($user_id, $amount, $change_desc) {
    log_account_change($user_id, $amount, 0, 0, 0, $change_desc, ACT_OTHER);
}

function write_log_in_lib_payment($txt) {
   $fp =  fopen('/work/project/huidangjia/data/wx_log_payment.txt','ab+');
   fwrite($fp,'write_log_in_lib_payment-----------'.date('Y-m-d H:i:s').'-----------------');
   fwrite($fp,$txt);
   fwrite($fp,"\r\n");
   fclose($fp);
}
?>
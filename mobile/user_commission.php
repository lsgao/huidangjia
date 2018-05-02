<?php
/* 
 * 惠当家分销中心
*/
define('IN_ECTOUCH', true);

require(dirname(__FILE__) . '/include/init.php');
require(ROOT_PATH . 'include/lib_weixintong.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'lang/' .$_CFG['lang']. '/user.php');
$user_id = $_SESSION['user_id'];
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';
$back_act='';

// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =
    array('login','act_login','register','act_register','act_edit_password','get_password','send_pwd_email','send_pwd_sms','password', 'signin', 'add_tag', 
    'collect', 'return_to_cart', 'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email','clear_history','qpassword_name', 
    'get_passwd_question', 'check_answer', 'oath', 'oath_login');

/* 显示页面的action列表 */
$ui_arr = array('default','percentage_history','myorder_detail','fenxiao1','fenxiao2','fenxiao3','fenxiao4','fenxiao5','account_apply','account_detail','account_log','account_apply');
/* 未登录处理 */
if (empty($_SESSION['user_id'])) {
    if (!in_array($action, $not_login_arr)) {
        if (in_array($action, $ui_arr)) {
            /* 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳转到相应操作 */
            if (!empty($_SERVER['QUERY_STRING'])) {
                $back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        } else {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}

/* 如果是显示页面，对页面进行相应赋值 */
if (in_array($action, $ui_arr)) {
    assign_template();
    $position = assign_ur_here(0, $_LANG['user_center']);
    $smarty->assign('page_title', "分销中心"); // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);
    $sql = "SELECT value FROM " . $ecs->table('touch_shop_config') . " WHERE id = 419";
    $row = $db->getRow($sql);
    $car_off = $row['value'];
    $smarty->assign('car_off',       $car_off);
    /* 是否显示积分兑换 */
    if (!empty($_CFG['points_rule']) && unserialize($_CFG['points_rule'])) {
        $smarty->assign('show_transform_points',     1);
    }
    $smarty->assign('helps',      get_shop_help());        // 网店帮助
    $smarty->assign('data_dir',   DATA_DIR);   // 数据目录
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
}

//用户分销中心欢迎页
if ($action == 'default') {
    include_once(ROOT_PATH .'include/lib_clips.php');
    if ($rank = get_rank_info()) {
        $smarty->assign('rank_name', sprintf($_LANG['your_level'], $rank['rank_name']));
        if (!empty($rank['next_rank_name'])) {
            $smarty->assign('next_rank_name', sprintf($_LANG['next_level'], $rank['next_rank'] ,$rank['next_rank_name']));
        }
    }
    $info = get_user_default($user_id);

    $sql = "SELECT wxid FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$user_id'";
    $wxid = $GLOBALS['db']->getOne($sql);
    if(!empty($wxid)) {
        $weixinInfo = $GLOBALS['db']->getRow("SELECT nickname, headimgurl FROM wxch_user WHERE wxid = '$wxid'");
        $info['avatar'] = empty($weixinInfo['headimgurl']) ? '':$weixinInfo['headimgurl'];
        $info['username'] = empty($weixinInfo['nickname']) ? $info['username']:$weixinInfo['nickname'];
    }
    /*微分销显示分销会员标准*/
    $level_register_up = 0;
    /*
    $rank_points =  $GLOBALS['db']->getOne("SELECT rank_points FROM " . $GLOBALS['ecs']->table('users')."where user_id=" . $_SESSION["user_id"]);
    if($rank_points>$level_register_up||$rank_points==$level_register_up) {
        $tianxin="资格：分销商";
    } else {
        show_message('您还不是分销商哦', '请先购买商品获取权限', 'user.php', 'error'); 
    }
    */
    $rank_name = $db->getOne(" select rank_name from " . $ecs->table('user_rank') . " where rank_id=" . $info['user_rank']);
    $userrank = "等级: " .  $rank_name;
    /*显示各级会员数量*/
    $user_list['user_list'] = array();
    $auid = $_SESSION['user_id'];
    $up_uid = "'$auid'";
    $res=array();
    $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";
    $count1 = $GLOBALS['db']->getOne($sql);
    $res[1]=$count1;
    $sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$up_uid";
    $up_uid_all_two=$GLOBALS['db']->getAll($sql);
    $count2 = 0;
    $count3 = 0;
    $count4 = 0;
    $count5 = 0;
    if(!empty($up_uid_all_two)){
        foreach($up_uid_all_two as $key =>$value) {
            $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
            $count = $GLOBALS['db']->getOne($sql);
            $count2=$count2+$count;
            $sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
            $up_uid_all_tree=$GLOBALS['db']->getAll($sql);
            if(!empty($up_uid_all_tree)) {
                foreach($up_uid_all_tree as $key =>$value ) {
                    $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
                    $count = $GLOBALS['db']->getOne($sql);
                    $count3=$count3+$count;
                    $sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
                    $up_uid_all_four=$GLOBALS['db']->getAll($sql);
                    if(!empty($up_uid_all_four)){
                        foreach($up_uid_all_four as $key =>$value) {
                            $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
                            $count = $GLOBALS['db']->getOne($sql);
                            $count4=$count4+$count;
                            $sql = "SELECT  user_id  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
                            $up_uid_all_five=$GLOBALS['db']->getAll($sql);
                            if(!empty($up_uid_all_five)){
                                foreach($up_uid_all_five as $key =>$value) {					
                                    $sql = "SELECT  count(*)  as num  FROM " . $ecs->table('users') . " WHERE parent_id=$value[user_id]";
                                    $count = $GLOBALS['db']->getOne($sql);
                                    $count5=$count5+$count;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $res[2] = $count2;
    $res[3] = $count3;
    $res[4] = $count4;
    $res[5] = $count5;
    //print_r($res);
    $smarty->assign('tianxin', $tianxin);
    $smarty->assign('userrank', $userrank);
    $smarty->assign('user_rank', $info['user_rank']);
    /*显示分销会员标准*/
    $smarty->assign('service_phone',        $_CFG['service_phone']);

    /*查询佣金*/
    //推荐注册分成
    $user_list['user_list'] = array();
    $auid = $_SESSION['user_id'];

    $percentage = get_percentage();

    $num =  4;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i <= $num; $i ++) {
        $count = 0;
        if ($up_uid) {
            $sql = "SELECT user_id,user_name FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query)) {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count ++;
            }
        }
        $all_count += $count;
    }
    $status_commission = array();
    if($info['user_rank'] == 2) { // 本人是掌柜
        $status_commission = get_shopkeeper_commission($auid, 1, $num, $percentage['percentage_shopkeeper']);
    } else if($info['user_rank'] == 3) { // 本人是大掌柜
        $status_commission = get_supershopkeeper_commission($auid, 1, $num, $percentage['percentage_shopkeeper'], $percentage['percentage_supershopkeeper_1'], $percentage['percentage_supershopkeeper_2']);
    } else if($info['user_rank'] == 4) { // 本人创始人
        $status_commission = get_originator_commission($auid, 1, $num, $percentage['percentage_originator']);
    } else { // 本人是其他等级的用户，不计算佣金
    }

    $total_commission = array();
    foreach($status_commission  as $key =>$value) {
        $total_commission['order_num'] += $value['order_num'];
        $total_commission['order_amount'] += $value['order_amount'];
        $total_commission['commission'] += $value['commission'];
    }
    $smarty->assign('total_commission', $total_commission);
    $smarty->assign('status_commission', $status_commission);

    /*查询佣金*/
    $smarty->assign('info', $info);
    $menuarr = array();
    for ($i = 1; $i<=$num; $i++){
        $smarty->assign('count'.$i, $res[$i]);
        $menuarr[$i]=$i;
    }
    $smarty->assign('menuarr', $menuarr);
    $smarty->assign('user_id', $_SESSION['user_id']);
    $smarty->assign('all_count', $all_count);
    $smarty->assign('user_notice', $_CFG['user_notice']);
    $smarty->assign('prompt', get_user_prompt($user_id));
    $smarty->display('user_commission.dwt');
}
//显示一级分销代理
elseif ($action == 'fenxiao1') {
    include_once(ROOT_PATH .'include/lib_clips.php');
    //推荐注册分成
    $user_list['user_list'] = array();
    $auid = $_SESSION['user_id'];
    $info = get_user_default($user_id);
    $percentage = get_percentage();
    $num = 4;
    $up_uid = "'$auid'";
    $all_count = 0;
    for ($i = 1; $i <= 1; $i++) {
        $count = 0;
        if ($up_uid) {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query)) {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;

        if ($count) {
            $sql = "SELECT user_id, user_name, user_rank, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time, wxid ".
                " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN ($up_uid)" .
                " ORDER by level, user_id";
            $user_info = $db->getAll($sql);
            foreach($user_info as $key=>$value) {
                $order_commission = 0;
                if ($info['user_rank'] == 2) {// 掌柜
                    if ($i == 1) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')." WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        $order_commission = round($order_info['order_amount'] * $percentage['percentage_shopkeeper'], 2);
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                    } else {
                    	continue;
                    }
                } else if ($info['user_rank'] == 3) {// 大掌柜
                    if ($i == 1) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')." WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        if ($value['user_rank'] == 2) { // 掌柜
                            $order_commission = round($order_info['order_amount'] * $percentage['percentage_originator'], 2);
                            //$user_commission = get_shopkeeper_commission($value['user_id'], $i, $num, $percentage['percentage_shopkeeper'], true);
                            //$order_commission = $user_commission['weifukuan']['order_amount'] + $user_commission['yifukuan']['order_amount'] + $user_commission['yishouhuo']['order_amount'];
                            //$order_commission = round($order_commission * $percentage['percentage_supershopkeeper_1'], 2);
                        } else if ($value['user_rank'] == 3) { // 大掌柜
                            $order_commission = 0;
                            //$user_commission = get_supershopkeeper_commission($value['user_id'], $i, $num, $percentage['percentage_shopkeeper'], true);
                            //$order_commission = $user_commission['weifukuan']['commission'] + $user_commission['yifukuan']['commission'] + $user_commission['yishouhuo']['commission'];
                            //$order_commission = round($order_commission * $percentage['percentage_supershopkeeper_2'], 2);
                        } else {
                            $order_commission = 0;
                        }
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                    } else {
                    	continue;
                    }
                } else if ($info['user_rank'] == 4) {// 创始人
                    if ($i == 1) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')." WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        if ($value['user_rank'] == 2) { // 掌柜
                            $order_commission = round($order_info['order_amount'] * $percentage['percentage_originator'], 2);
                            //$user_commission = get_shopkeeper_commission($value['user_id'], $i, $num, $percentage['percentage_shopkeeper'], true);
                            //$order_commission = $user_commission['weifukuan']['order_amount'] + $user_commission['yifukuan']['order_amount'] + $user_commission['yishouhuo']['order_amount'];
                            //$order_commission = round($order_commission * $percentage['percentage_supershopkeeper_1'], 2);
                        } else if ($value['user_rank'] == 3) { // 大掌柜
                            $order_commission = round($order_info['order_amount'] * $percentage['percentage_originator'], 2);
                            //$user_commission = get_supershopkeeper_commission($value['user_id'], $i, $num, $percentage['percentage_shopkeeper'], true);
                            //$order_commission = $user_commission['weifukuan']['commission'] + $user_commission['yifukuan']['commission'] + $user_commission['yishouhuo']['commission'];
                            //$order_commission = round($order_commission * $percentage['percentage_supershopkeeper_2'], 2);
                        } else {
                            $order_commission = 0;
                        }
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                    } else {
                    	continue;
                    }
                }
            }
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);	
        }
    }
    $new_arr = array();
    foreach($user_list['user_list'] as $key =>$value){
        if($value['level'] == 1){
            $wxid = $value['wxid'];
            $weixin_info = $GLOBALS['db']->getRow("SELECT nickname,headimgurl FROM wxch_user WHERE wxid = '$wxid'");
            $value['head_url'] = $weixin_info['headimgurl'];
            $value['nickname'] = $weixin_info['nickname'];
            $new_arr[] = $value;
        }
    }
    $count = count($new_arr);
    $smarty->assign('count',    $count);
    $smarty->assign('user_list',    $new_arr);
    $smarty->display('user_commission.dwt');
}
//显示二级以上分销代理
elseif ($action == 'fenxiao2' || $action == 'fenxiao3' || $action == 'fenxiao3' || $action == 'fenxiao4') {
    $level = 2;
    if ($action == 'fenxiao2') {
        $level = 2;
    } else if ($action == 'fenxiao3') {
        $level = 3;
    } else if ($action == 'fenxiao4') {
        $level = 4;
    } else if ($action == 'fenxiao5') {
        $level = 5;
    }
    include_once(ROOT_PATH .'include/lib_clips.php');
    //推荐注册分成
    $user_list['user_list'] = array();
    $auid = $_SESSION['user_id'];
    $info = get_user_default($user_id);
    $percentage = get_percentage();

    $num = 4;
    if ($level > $num) {
        $level = $num;
    }
    $up_uid = "'$auid'";
    $all_count = 0;
    $supershopkeeper_array = array();
    $percentage_originator_array = array();

    for ($i = 1; $i <= $level; $i++) {
        $count = 0;
        if ($up_uid) {
            $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN ($up_uid)";
            $query = $db->query($sql);
            $up_uid = '';
            while ($rt = $db->fetch_array($query)) {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                $count++;
            }
        }
        $all_count += $count;
        if ($count) {
            $sql = "SELECT user_id, user_name, user_rank, parent_id, '$i' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time,wxid ".
                " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN ($up_uid)" .
                " ORDER by level, user_id";
            $user_info = $db->getAll($sql);
            foreach($user_info as $key=>$value) {
                $order_commission = 0;
                if ($info['user_rank'] == 2) {// 掌柜
                    if ($i == $level) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        $has_supershopkeeper = 0;
                        $order_commission = 0;
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                        $user_info[$key]['has_supershopkeeper'] = $has_supershopkeeper;
                    } else {
                    	continue;
                    }
                } else if ($info['user_rank'] == 3) {// 大掌柜
                    if ($i < $level) {
                        if ($value['user_rank'] == 2) {
                            $supershopkeeper_array[$value['user_id']]['value'] = 0;
                            $supershopkeeper_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        } else if ($value['user_rank'] == 3) {
                            $supershopkeeper_array[$value['user_id']]['value'] = 1;
                            $supershopkeeper_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        } else {
                            $supershopkeeper_array[$value['user_id']]['value'] = 0;
                            $supershopkeeper_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        }
                    } else if ($i == $level) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        $has_supershopkeeper = 0;
                        if ($value['user_rank'] == 2 || $value['user_rank'] == 3) { // 掌柜或大掌柜
                            $has_supershopkeeper = get_supershopkeeper_num($supershopkeeper_array, $value['parent_id']);
                            if ($has_supershopkeeper == 0) {
                                $order_commission = round($order_info['order_amount'] * $percentage['percentage_supershopkeeper_1'], 2);
                            } else {
                                $percentage_rate = $percentage['percentage_supershopkeeper_1'];
                                for($k = 1; $k <= $has_supershopkeeper; $k ++) {
                                    $percentage_rate *= $percentage['percentage_supershopkeeper_2'];
                                }
                                $order_commission = round($order_info['order_amount'] * $percentage_rate, 2);
                            }
                        } else {
                            $order_commission = 0;
                        }
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                        $user_info[$key]['has_supershopkeeper'] = $has_supershopkeeper;
                    } else {
                    	continue;
                    }
                } else if ($info['user_rank'] == 4) {// 创始人
                    if ($i < $level) {
                        if ($value['user_rank'] == 2) {
                            $percentage_originator_array[$value['user_id']]['value'] = 0;
                            $percentage_originator_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        } else if ($value['user_rank'] == 3) {
                            $percentage_originator_array[$value['user_id']]['value'] = 1;
                            $percentage_originator_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        } else {
                            $percentage_originator_array[$value['user_id']]['value'] = 0;
                            $percentage_originator_array[$value['user_id']]['parent_id'] = $value['parent_id'];
                            continue;
                        }
                    } else if ($i == $level) {
                        $sql = "SELECT count(*) as order_num ,sum(fencheng)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $value['user_id'];
                        $order_info = $db->getRow($sql);
                        $has_supershopkeeper = 0;
                        if ($value['user_rank'] == 2 || $value['user_rank'] == 3) { // 掌柜或大掌柜
                            //$has_supershopkeeper = get_supershopkeeper_num($percentage_originator_array, $value['parent_id']);
                            $order_commission = round($order_info['order_amount'] * $percentage['percentage_originator'], 2);
                        } else {
                            $order_commission = 0;
                        }
                        $user_info[$key]['order_num'] = $order_info['order_num'];
                        $user_info[$key]['order_amount'] = $order_info['order_amount'];
                        $user_info[$key]['commission'] = $order_commission;
                        $user_info[$key]['has_supershopkeeper'] = $has_supershopkeeper;
                    } else {
                    	continue;
                    }
                } else {
                    continue;
                } 
            }
            $user_list['user_list'] = array_merge($user_list['user_list'], $user_info);
        }
    }
    $new_arr=array();
    foreach($user_list['user_list'] as $key =>$value) {
        if ($value['level'] == $level){
            $wxid = $value['wxid'];
            $weixin_info = $GLOBALS['db']->getRow("SELECT nickname,headimgurl FROM wxch_user WHERE wxid = '$wxid'");
            $value['head_url'] = $weixin_info['headimgurl'];
            $value['nickname'] = $weixin_info['nickname'];
            $new_arr[] = $value;
        }
    }
    $count = count($new_arr);
    $smarty->assign('count',    $count);
    $smarty->assign('user_list',    $new_arr);
    $smarty->display('user_commission.dwt');
}
//显示分销会员的分成记录
elseif ($action =='percentage_history') {
    include_once(ROOT_PATH .'include/lib_clips.php');
    $info = get_user_default($user_id);
    //显示分成记录
    $user_id=$_GET['user_id'];
    $level = $_GET['level'];
    $supershopkeeper_num = $_GET['num'];
    $logdb = get_percentage_ck($user_id, $level, $supershopkeeper_num, $info['user_rank']);
    $smarty->assign('logdb', $logdb['logdb']);
    $smarty->assign('level', $level);
    //显示分成记录结束
    $smarty->display('user_commission.dwt');
}
/* 会员账目明细界面 */
elseif ($action == 'account_detail')
{
    include_once(ROOT_PATH . 'include/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $account_type = 'user_money';
    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);
    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount)) {
        $surplus_amount = 0;
    }
    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res)) {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? $_LANG['account_inc'] : $_LANG['account_dec'];
        $row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_commission.dwt');
}
/* 会员充值和提现申请记录 */
elseif ($action == 'account_log') {
    include_once(ROOT_PATH . 'include/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('user_account').
        " WHERE user_id = '$user_id'" .
        " AND process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
    $record_count = $db->getOne($sql);
    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount)) {
        $surplus_amount = 0;
    }
    //获取余额记录
    $account_log = get_account_log($user_id, $pager['size'], $pager['start']);
    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_commission.dwt');
}
/* 会员退款申请界面 */
elseif ($action == 'account_apply') {
    $smarty->display('user_commission.dwt');
}





/* 对会员余额申请的处理 */
elseif ($action == 'act_account') {
    include_once(ROOT_PATH . 'include/lib_clips.php');
    include_once(ROOT_PATH . 'include/lib_order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0) {
        show_message($_LANG['amount_gt_zero']);
    }
    /*优化提现流程*/
    $real_name=$_POST['real_name'];//真实姓名
    $bank_name=$_POST['bank_name'];//开户行
    $bank_account=$_POST['bank_account'];//银行账户
    $mobile_phone=$_POST['mobile_phone'];//开户行
    $user_note=$_POST['user_note'];//留言
    $tianxin100="真实姓名:【" . $real_name."】开户行:【" . $bank_name."】银行账户:【" . $bank_account."】手机:【" . $mobile_phone."】留言:【" . $user_note."】";
    /*优化提现流程*/
    /* 变量初始化 */
    $surplus = array(
        'user_id'      => $user_id,
        'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
        'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
        'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
        'user_note'    => $tianxin100,
        'amount'       => $amount
    );

    /* 退款申请的处理 */
    if ($surplus['process_type'] == 1) {
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount) {
            $content = $_LANG['surplus_amount_error'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
        //插入会员账目明细
        $amount = '-'.$amount;
        $surplus['payment'] = '';
        $surplus['rec_id']  = insert_user_account($surplus, $amount);
        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0) {
            $content = $_LANG['surplus_appl_submit'];
            show_message($content, $_LANG['back_account_log'], 'user.php?act=account_log', 'info');
        } else {
            $content = $_LANG['process_false'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
    }
    /* 如果是会员预付款，跳转到下一步，进行线上支付的操作 */
    else {
        if ($surplus['payment_id'] <= 0) {
            show_message($_LANG['select_payment_pls']);
        }
        include_once(ROOT_PATH .'include/lib_payment.php');
        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);
        $surplus['payment'] = $payment_info['pay_name'];

        if ($surplus['rec_id'] > 0)
        {
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        }
        else
        {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus, $amount);
        }

        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号, 不足的时候补0
        $order = array();
        $order['order_sn']       = $surplus['rec_id'];
        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $amount;

        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $amount + $payment_info['pay_fee'];

        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'include/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
				//开始人人科技分销开发，兼容微信支付
		$order['pay_id']=4;
		$GLOBALS['db']->autoExecute($ecs->table('order_info'), $order, 'INSERT');
		$new_order_id = $db->insert_id();	
		$amount	=	$order['order_amount'];
		$type=PAY_SURPLUS;
		
		$is_paid=0;
		$sql = 'INSERT INTO ' .$GLOBALS['ecs']->table('pay_log')." (order_id, order_amount, order_type, is_paid)".
            " VALUES  ('$new_order_id', '$amount', '$type', '$is_paid')";
		$GLOBALS['db']->query($sql);		
		//结束
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($amount, false));
        $smarty->assign('order',   $order);
        $smarty->display('user_transaction.dwt');
        }
}

//生成随机数
function random($length = 6, $numeric = 0) {
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}

//定义，显示某个会员下面的分成订单情况
function get_percentage_ck($user_id, $level, $supershopkeeper_num, $ancestor_user_rank) {
    $separate_by = 0; //推荐注册分成
    $sqladd = '';
    if (isset($_REQUEST['status'])) {
        $sqladd = ' AND o.is_separate = ' . (int)$_REQUEST['status'];
        $filter['status'] = (int)$_REQUEST['status'];
    }
    if (isset($_REQUEST['order_sn'])) {
        $sqladd = ' AND o.order_sn LIKE \'%' . trim($_REQUEST['order_sn']) . '%\'';
        $filter['order_sn'] = $_REQUEST['order_sn'];
    }
    $sqladd = ' AND a.user_id=' . $_SESSION['user_id'];

    // 推荐注册分成
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " o".
        " LEFT JOIN" . $GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
        " LEFT JOIN " . $GLOBALS['ecs']->table('percentage_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND ((u.parent_id > 0 AND o.is_separate = 0) OR o.is_separate > 0) $sqladd";

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $logdb = array();
    // 分页大小
    $filter = page_and_size($filter);

    // 推荐注册分成
    // SQL解释： 列出同时满足以下条件的订单分成情况：
    // 1、有效订单o.user_id > 0
    // 2、满足以下情况之一：
    // a.有用户注册上线的未分成订单 u.parent_id > 0 AND o.is_separate = 0
    // b.已分成订单 o.is_separate > 0
    $sql = "SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $GLOBALS['ecs']->table('order_info') . " o".
        " LEFT JOIN" . $GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
        " LEFT JOIN " . $GLOBALS['ecs']->table('percentage_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND ((u.parent_id > 0 AND o.is_separate = 0) OR o.is_separate > 0) $sqladd".
        " ORDER BY order_id DESC" .
        " LIMIT " . $filter['start'] . ",$filter[page_size]";
    $query = $GLOBALS['db']->query($sql);
    //print_r($GLOBALS['db']->getALL($sql));
    while ($rt = $GLOBALS['db']->fetch_array($query)) {
        if(empty($separate_by) && $rt['up'] > 0) {
            //按推荐注册分成
            $rt['separate_able'] = 1;
        } elseif(!empty($separate_by) && $rt['parent_id'] > 0) {
            //按推荐订单分成
            $rt['separate_able'] = 1;
        }
        if(!empty($rt['suid'])) {
            //在percentage_log有记录
            $rt['info'] = sprintf($GLOBALS['_LANG']['separate_info2'], $rt['suid'], $rt['auser'], $rt['money'], $rt['point']);
            if($rt['separate_type'] == -1 || $rt['separate_type'] == -2) {
                //已被撤销
                $rt['is_separate'] = 3;
                $rt['info'] = "<s>" . $rt['info'] . "</s>";
            }
        }
        $logdb[] = $rt;
    }

    $logdbnew = array();
    foreach($logdb  as $key => $value) {
        if($value['user_id'] == $user_id) {
            $logdbnew[$key] = $value;
            $user_id = $value['user_id'];
            $sql = "SELECT wxid FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$user_id'";
            $wxid = $GLOBALS['db']->getOne($sql);
            if(!empty($wxid)){
                $weixinInfo = $GLOBALS['db']->getRow("SELECT nickname, headimgurl FROM wxch_user WHERE wxid = '$wxid'");
                $logdbnew[$key]['avatar'] = empty($weixinInfo['headimgurl']) ? '':$weixinInfo['headimgurl'];
                $logdbnew[$key]['username'] = empty($weixinInfo['nickname']) ? '':$weixinInfo['nickname'];
            }
            $percentage = get_percentage();
            $percentage_rate = 0;
            if ($level == 1) {
                if ($ancestor_user_rank == 2) { // 最上级是掌柜
                    $percentage_rate = $percentage['percentage_shopkeeper'];
                } else if($ancestor_user_rank == 3) { // 最上级是大掌柜
                    $percentage_rate = 0;
                } else {
                    $percentage_rate = 0;
                }
            } else if ($level >= 2) {
                if ($ancestor_user_rank == 2) { // 最上级是掌柜
                    $percentage_rate = 0;
                } else if($ancestor_user_rank == 3) { // 最上级是大掌柜
                    $percentage_rate = $percentage['percentage_supershopkeeper_1'];
                    for($k = 1; $k <= $supershopkeeper_num; $k ++) {
	                $percentage_rate *= $percentage['percentage_supershopkeeper_2'];
                    }
                } else if($ancestor_user_rank == 4) { // 最上级是大掌柜或创始人
                    $percentage_rate = $percentage['percentage_originator'];
                } else {
                    $percentage_rate = 0;
                }
            }

            $setmoney = round($value['fencheng'] * $percentage_rate, 2);
            $logdbnew[$key]['set_money'] = $setmoney;
            $logdbnew[$key]['level_money'] = $percentage_rate;
            $logdbnew[$key]['order_amount'] = $value['fencheng'];
        }
    }
    //print_r($logdbnew);
    $arr = array('logdb' => $logdbnew, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}	
function page_and_size($filter)
{
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
    {
        $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
    }
    else
    {
        $filter['page_size'] = 15;
    }

    // 每页显示
    $filter['page'] = (empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
    // page 总数 
    $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;
    // 边界处理
    if ($filter['page'] > $filter['page_count'])
    {
        $filter['page'] = $filter['page_count'];
    }

    $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];
    return $filter;
}
/**
 * 取得帐户明细
 * @param   int     $user_id    用户id
 * @param   string  $account_type   帐户类型：空表示所有帐户，user_money表示可用资金，
 *                  frozen_money表示冻结资金，rank_points表示等级积分，pay_points表示消费积分
 * @return  array
 */
function get_accountlist($user_id, $account_type = '')
{
    /* 检查参数 */
    $where = " WHERE user_id = '$user_id' ";
    if (in_array($account_type, array('user_money', 'frozen_money', 'rank_points', 'pay_points')))
    {
        $where .= " AND $account_type <> 0 ";
    }

    /* 初始化分页参数 */
    $filter = array(
        'user_id'       => $user_id,
        'account_type'  => $account_type
    );

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('account_log') . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('account_log') . $where .
            " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }

    return array('account' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/*
 * 查询创始人佣金及订单数据
 * order_num : 团队订单总数
 * order_amount : 团队销售总额
 * commission : 佣金 （创始人佣金 = 所有下属的团队销售总额 * 设定比例）
 */
function get_originator_commission($user_id, $level, $max_level, $percentage_originator, $is_team=true) {
    $commission = array();
    if ($level >$max_level) {
        return $commission;
    }
    // -- 1.未付款的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=0";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['weifukuan']['order_num'] = $order_info['order_num'];
    $commission['weifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['weifukuan']['commission'] = 0;
    // -- 2.已经付款且未收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=2 and   shipping_status  <> 2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yifukuan']['order_num'] = $order_info['order_num'];
    $commission['yifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['yifukuan']['commission'] = 0;
    // -- 3.已经收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id."  and  shipping_status  =2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yishouhuo']['order_num'] = $order_info['order_num'];
    $commission['yishouhuo']['order_amount'] = $order_info['order_amount'];
    $commission['yishouhuo']['commission'] = 0;

    if ($is_team) {
        $sql = "SELECT user_id, user_name,user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id  = " .$user_id;
        $direct_reports = $GLOBALS['db']->query($sql);
        while ($rt = $GLOBALS['db']->fetch_array($direct_reports)) {
            $child_commission = array();
            $child_commission = get_originator_commission($rt['user_id'], $level + 1, $max_level, $percentage_originator);
            $commission['weifukuan']['commission'] += round($child_commission['weifukuan']['order_amount'] * $percentage_originator, 2);
            $commission['yifukuan']['commission'] += round($child_commission['yifukuan']['order_amount'] * $percentage_originator, 2);
            $commission['yishouhuo']['commission'] += round($child_commission['yishouhuo']['order_amount'] * $percentage_originator, 2);

            $commission['weifukuan']['order_num'] += $child_commission['weifukuan']['order_num'];
            $commission['weifukuan']['order_amount'] += $child_commission['weifukuan']['order_amount'];
            $commission['yifukuan']['order_num'] += $child_commission['yifukuan']['order_num'];
            $commission['yifukuan']['order_amount'] += $child_commission['yifukuan']['order_amount'];
            $commission['yishouhuo']['order_num'] += $child_commission['yishouhuo']['order_num'];
            $commission['yishouhuo']['order_amount'] += $child_commission['yishouhuo']['order_amount'];
        }
    }
    return $commission;
}

/*
 * 查询大掌柜佣金及订单数据
 * order_num : 团队订单总数
 * order_amount : 团队销售总额
 * commission : 佣金 （大掌柜佣金 = 下属掌柜的团队销售总额 * 设定比例 + 直接下属大掌柜佣金 * 设定比例）
 */
function get_supershopkeeper_commission($user_id, $level, $max_level, $percentage_shopkeeper, $percentage_supershopkeeper_1, $percentage_supershopkeeper_2, $is_team=true) {
    $commission = array();
    if ($level >$max_level) {
        return $commission;
    }
    // -- 1.未付款的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=0";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['weifukuan']['order_num'] = $order_info['order_num'];
    $commission['weifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['weifukuan']['commission'] = 0;
    // -- 2.已经付款且未收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=2 and   shipping_status  <> 2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yifukuan']['order_num'] = $order_info['order_num'];
    $commission['yifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['yifukuan']['commission'] = 0;
    // -- 3.已经收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id."  and  shipping_status  =2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yishouhuo']['order_num'] = $order_info['order_num'];
    $commission['yishouhuo']['order_amount'] = $order_info['order_amount'];
    $commission['yishouhuo']['commission'] = 0;

    if ($is_team) {
        $sql = "SELECT user_id, user_name,user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id  = " .$user_id;
        $direct_reports = $GLOBALS['db']->query($sql);
        while ($rt = $GLOBALS['db']->fetch_array($direct_reports)) {
            $child_commission = array();
            if ($rt['user_rank'] == 3 || $rt['user_rank'] == 4) {
                $child_commission = get_supershopkeeper_commission($rt['user_id'], $level + 1, $max_level, $percentage_shopkeeper, $percentage_supershopkeeper_1, $percentage_supershopkeeper_2);
                $commission['weifukuan']['commission'] += round($child_commission['weifukuan']['commission'] * $percentage_supershopkeeper_2, 2);
                $commission['yifukuan']['commission'] += round($child_commission['yifukuan']['commission'] * $percentage_supershopkeeper_2, 2);
                $commission['yishouhuo']['commission'] += round($child_commission['yishouhuo']['commission'] * $percentage_supershopkeeper_2, 2);
            } else {
                $child_commission = get_shopkeeper_commission($rt['user_id'], $level + 1, $max_level, $percentage_shopkeeper);
                $commission['weifukuan']['commission'] += round($child_commission['weifukuan']['order_amount'] * $percentage_supershopkeeper_1, 2);
                $commission['yifukuan']['commission'] += round($child_commission['yifukuan']['order_amount'] * $percentage_supershopkeeper_1, 2);
                $commission['yishouhuo']['commission'] += round($child_commission['yishouhuo']['order_amount'] * $percentage_supershopkeeper_1, 2);
            }
            $commission['weifukuan']['order_num'] += $child_commission['weifukuan']['order_num'];
            $commission['weifukuan']['order_amount'] += $child_commission['weifukuan']['order_amount'];
            $commission['yifukuan']['order_num'] += $child_commission['yifukuan']['order_num'];
            $commission['yifukuan']['order_amount'] += $child_commission['yifukuan']['order_amount'];
            $commission['yishouhuo']['order_num'] += $child_commission['yishouhuo']['order_num'];
            $commission['yishouhuo']['order_amount'] += $child_commission['yishouhuo']['order_amount'];
        }
    }
    return $commission;
}

/*
 * 查询掌柜提成及订单数据
 * order_num : 订单总数
 * order_amount : 销售总额 = 自己的销售额 + 下属的团队销售总额
 * commission : 提成 = 直接下属的销售额 * 设定比例
 */
function get_shopkeeper_commission($user_id, $level, $max_level, $percentage_shopkeeper, $is_team=true) {
    $commission = array();
    if ($level >$max_level) {
        return $commission;
    }

    // -- 1.未付款的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=0";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['weifukuan']['order_num'] = $order_info['order_num'];
    $commission['weifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['weifukuan']['commission'] = 0;
    // -- 2.已经付款且未收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id." and  pay_status=2 and   shipping_status  <> 2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yifukuan']['order_num'] = $order_info['order_num'];
    $commission['yifukuan']['order_amount'] = $order_info['order_amount'];
    $commission['yifukuan']['commission'] = 0;
    // -- 3.已经收货的订单的佣金统计
    $sql = "SELECT count(*) as order_num ,sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $user_id."  and  shipping_status  =2";
    $order_info = $GLOBALS['db']->getRow($sql);
    $commission['yishouhuo']['order_num'] = $order_info['order_num'];
    $commission['yishouhuo']['order_amount'] = $order_info['order_amount'];
    $commission['yishouhuo']['commission'] = 0;
    if($is_team) {
        $sql = "SELECT user_id, user_name, user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id = " .$user_id;
        $direct_reports = $GLOBALS['db']->getAll($sql);
        foreach($direct_reports as $key => $value) {
            $child_commission = array();
            $child_commission = get_shopkeeper_commission($value['user_id'], $level + 1, $max_level, $percentage_shopkeeper);
            $commission['weifukuan']['order_num'] += $child_commission['weifukuan']['order_num'];
            $commission['weifukuan']['order_amount'] += $child_commission['weifukuan']['order_amount'];
            $commission['yifukuan']['order_num'] += $child_commission['yifukuan']['order_num'];
            $commission['yifukuan']['order_amount'] += $child_commission['yifukuan']['order_amount'];
            $commission['yishouhuo']['order_num'] += $child_commission['yishouhuo']['order_num'];
            $commission['yishouhuo']['order_amount'] += $child_commission['yishouhuo']['order_amount'];
            // 计算直接下属的订单销售额
            $sql = "SELECT sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=". $value['user_id'] ." and  pay_status=0";
            $order_info = $GLOBALS['db']->getRow($sql);
            $commission['weifukuan']['commission'] += round($order_info['order_amount'] * $percentage_shopkeeper, 2);
            $sql = "SELECT sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $value['user_id'] . " and  pay_status=2 and   shipping_status  <> 2";
            $order_info = $GLOBALS['db']->getRow($sql);
            $commission['yifukuan']['commission'] += round($order_info['order_amount'] * $percentage_shopkeeper, 2);
            $sql = "SELECT sum(goods_amount - discount)  as order_amount FROM " . $GLOBALS['ecs']->table('order_info')."WHERE user_id=" . $value['user_id']."  and  shipping_status  =2";
            $order_info = $GLOBALS['db']->getRow($sql);
            $commission['yishouhuo']['commission'] += round($order_info['order_amount'] * $percentage_shopkeeper, 2);
        }
    }
    return $commission;
}

/*
 * 查询提成/佣金比例设定
 * percentage_shopkeeper : 掌柜直接下属的销售额提成的设定比例
 * percentage_supershopkeeper_1 : 大掌柜下属的掌柜团队销售总额提成设定比例
 * percentage_supershopkeeper_2 : 大掌柜下属的大掌柜佣金提成的设定比例
 */
function get_percentage() {
    $percentage = array();
    $sql = "SELECT value FROM " . $GLOBALS['ecs']->table('touch_shop_config') . " WHERE code = 'percentage_shopkeeper'";
    $percentage_shopkeeper = $GLOBALS['db']->getOne($sql);
    $percentage_shopkeeper /= 100;
    $sql = "SELECT value FROM " . $GLOBALS['ecs']->table('touch_shop_config') . " WHERE code = 'percentage_supershopkeeper_1'";
    $percentage_supershopkeeper_1 = $GLOBALS['db']->getOne($sql);
    $percentage_supershopkeeper_1 /= 100;
    $sql = "SELECT value FROM " . $GLOBALS['ecs']->table('touch_shop_config') . " WHERE code = 'percentage_supershopkeeper_2'";
    $percentage_supershopkeeper_2 = $GLOBALS['db']->getOne($sql);
    $percentage_supershopkeeper_2 /= 100;
    $sql = "SELECT value FROM " . $GLOBALS['ecs']->table('touch_shop_config') . " WHERE code = 'percentage_originator'";
    $percentage_originator = $GLOBALS['db']->getOne($sql);
    $percentage_originator /= 100;
    $percentage['percentage_shopkeeper'] = $percentage_shopkeeper;
    $percentage['percentage_supershopkeeper_1'] = $percentage_supershopkeeper_1;
    $percentage['percentage_supershopkeeper_2'] = $percentage_supershopkeeper_2;
    $percentage['percentage_originator'] = $percentage_originator;
    return $percentage;
}

/*
 * 查询分校级别中间隔几个大掌柜
 */
function get_supershopkeeper_num($supershopkeeper_array, $parent_id) {
    $num = 0;
    if ($supershopkeeper_array[$parent_id]['value'] == 1) {
        $num ++;
    }
    if ($supershopkeeper_array[$parent_id]['parent_id']) {
        $num += get_supershopkeeper_num($supershopkeeper_array, $supershopkeeper_array[$parent_id]['parent_id']);
    } else {
        return $num;
    }
    return $num;
}
?>
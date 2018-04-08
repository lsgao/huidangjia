<?php

define ( 'IN_ECS', true );

require (dirname ( __FILE__ ) . '/includes/init.php');
$action = isset ( $_REQUEST ['act'] ) ? trim ( $_REQUEST ['act'] ) : 'default';

if ($action == 'default') {
 $smarty->display ( 'sign_up_now.dwt' );
} /* 用户报名的处理 */
elseif ($action == 'act_register') {
 $username = isset ( $_POST ['username'] ) ? trim ( $_POST ['username'] ) : '';
 $tel = isset ( $_POST ['tel'] ) ? trim ( $_POST ['tel'] ) : '';
 
 $sql = "INSERT INTO " . $ecs->table ( 'sign_up_now' ) . "(`user_name`, `tel`, `reg_time`) VALUES ('" . $username . "','" . $tel . "','" . gmtime () . "')";
 $db->query ( $sql );
 echo "<script>alert('报名成功');history.go(-1);</script>";
}
?>

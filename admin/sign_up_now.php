<?php
define ( 'IN_ECS', true );

require (dirname ( __FILE__ ) . '/includes/init.php');

/* ------------------------------------------------------ */
// -- 报名报名列表
/* ------------------------------------------------------ */

if ($_REQUEST ['act'] == 'list') {
 $sign_up_now_list = sign_up_now_list ();
 
 $smarty->assign ( 'sign_up_now_list', $sign_up_now_list ['sign_up_now_list'] );
 $smarty->assign ( 'filter', $sign_up_now_list ['filter'] );
 $smarty->assign ( 'record_count', $sign_up_now_list ['record_count'] );
 $smarty->assign ( 'page_count', $sign_up_now_list ['page_count'] );
 $smarty->assign ( 'full_page', 1 );
 assign_query_info ();
 $smarty->display ( 'sign_up_now_list.htm' );
} /* ------------------------------------------------------ */
// -- ajax返回报名列表
/* ------------------------------------------------------ */
elseif ($_REQUEST ['act'] == 'query') {
 $sign_up_now_list = sign_up_now_list ();
 
 $smarty->assign ( 'sign_up_now_list', $sign_up_now_list ['sign_up_now_list'] );
 $smarty->assign ( 'filter', $sign_up_now_list ['filter'] );
 $smarty->assign ( 'record_count', $sign_up_now_list ['record_count'] );
 $smarty->assign ( 'page_count', $sign_up_now_list ['page_count'] );
 
 $sort_flag = sort_flag ( $sign_up_now_list ['filter'] );
 $smarty->assign ( $sort_flag ['tag'], $sort_flag ['img'] );
 
 make_json_result ( $smarty->fetch ( 'sign_up_now_list.htm' ), '', array (
   'filter' => $sign_up_now_list ['filter'],
   'page_count' => $sign_up_now_list ['page_count'] 
 ) );
}

/**
 * 返回报名列表数据
 *
 * @access public
 * @param         
 *
 * @return void
 */
function sign_up_now_list() {
 $result = get_filter ();
 if ($result === false) {
  $filter ['record_count'] = $GLOBALS ['db']->getOne ( "SELECT COUNT(*) FROM " . $GLOBALS ['ecs']->table ( 'sign_up_now' ) );
  
  /* 分页大小 */
  $filter = page_and_size ( $filter );
  $sql = "SELECT user_name, tel, reg_time  FROM " . $GLOBALS ['ecs']->table ( 'sign_up_now' ) . " ORDER BY id ASC LIMIT " . $filter ['start'] . ',' . $filter ['page_size'];
  
  set_filter ( $filter, $sql );
 } else {
  $sql = $result ['sql'];
  $filter = $result ['filter'];
 }
 $sign_up_now_list = $GLOBALS ['db']->getAll ( $sql );
 
 $count = count ( $sign_up_now_list );
 for($i = 0; $i < $count; $i ++) {
  $sign_up_now_list [$i] ['reg_time'] = local_date ( $GLOBALS ['_CFG'] ['date_format'], $sign_up_now_list [$i] ['reg_time'] );
 }
 
 $arr = array (
   'sign_up_now_list' => $sign_up_now_list,
   'filter' => $filter,
   'page_count' => $filter ['page_count'],
   'record_count' => $filter ['record_count'] 
 );
 
 return $arr;
}

?>

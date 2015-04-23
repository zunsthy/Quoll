<?php
require_once("../start.php");
header("Content-type: text/html; charset=utf-8");

require_once("../class/browse.class.php");

$U = new USER();
$user = [ 'id' => $U->id, 'class' => $U->class ];

$class_edit = 170;
$class_banned = 11;

$ret = [];
if(isset($_REQUEST['action'])){
	if($_REQUEST['action'] == 'permission'){
		if($user['class'] >= $class_edit){
			$ret[] = 'edit';
		}
		if($user['class'] >= $class_banned){
			$ret[] = 'banned';
		}
	} else {
	}
} else {
	$B = new BROWSE($user);
	$B->init();
	$ret['field'] = $B->field;
	$ret['page'] = $B->page;
	$ret['qs'] = $B->url;
	$ret['data'] = $B->output();
}
	
echo json_encode($ret);
exit();


<?php
require_once("../start.php");
header("Content-type: text/html; charset=utf-8");

require_once("../class/calendar.class.php");

$U = new USER();
$user = [ 'class' => $U->class ];

$C = new CALENDAR($user);
$ret = "";
if(isset($_REQUEST['action'])){
if($_REQUEST['action'] == 'edit' && isset($_REQUEST['id']) && isset($_REQUEST['field']) && isset($_REQUEST['data'])){
	if($_REQUEST['id'] > 0 && $_REQUEST['field'] && $_REQUEST['data']){
	//	if(in_array($_REQUEST['field'], [ 'name', 'type', 'keywords', 'cover', 'note', 'ep', 'follow', 'status', 'tid', 'time' ])){
			if(isset($_REQUEST['ep']))
				$ret = $C->edit($_REQUEST['id'], $_REQUEST['ep'], $_REQUEST['field'], $_REQUEST['data']);
			else 
				$ret = $C->edit($_REQUEST['id'], '', $_REQUEST['field'], $_REQUEST['data']);
	}
} elseif($_REQUEST['action'] == 'new'){
	if(isset($_REQUEST['name']) && isset($_REQUEST['keywords'])){
		$ret = $C->add($_REQUEST['name'], $_REQUEST['keywords'],
			isset($_REQUEST['cover']) ? $_REQUEST['cover'] : '',
			isset($_REQUEST['time'])  ? $_REQUEST['time']  : '',
			isset($_REQUEST['type'])  ? $_REQUEST['type']  : 0,
			isset($_REQUEST['ep'])    ? $_REQUEST['ep']    : 1,
			isset($_REQUEST['status'])? $_REQUEST['status']: 'air');
	}
} elseif($_REQUEST['action'] == 'delete' && isset($_REQUEST['id'])){
	if($_REQUEST['id'] > 0){
		if(isset($_REQUEST['ep']))
			$ret = $C->del($_REQUEST['id'], $_REQUEST['ep']);
		else
			$ret = $C->del($_REQUEST['id']);
	}
} elseif($_REQUEST['action'] == 'query' && isset($_REQUEST['id']) && isset($_REQUEST['ep'])){
	$ret = $C->detail($_REQUEST['id'], $_REQUEST['ep']);
} elseif($_REQUEST['action'] == 'update' && isset($_REQUEST['date'])){
	$ret = true;
	if(is_array($_REQUEST['date'])){
		foreach($_REQUEST['date'] as $d)
		$ret = $ret && $C->update($d);
	} else
		$C->update($_REQUEST['date']);
} elseif($_REQUEST['action'] == 'permission'){
	$ret = $C->getPermission();
} else
	$ret = false;
} else {
	if(isset($_REQUEST['date'])){
		if(isset($_REQUEST['type']))
			$ret = $C->view($_REQUEST['date'], $_REQUEST['type']);
		else
			$ret = $C->view($_REQUEST['date']);
	} else 
		$ret = $C->view();
}
	
if($ret === false)
	echo json_encode("error");
elseif($ret === true)
	echo json_encode("success");
elseif(empty($ret))
	echo json_encode("empty");
else
	echo json_encode($ret);

exit();


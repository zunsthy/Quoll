<?php
require_once("start.php");

if(isset($_REQUEST['u']) && isset($_REQUEST['p'])
	&& $_REQUEST['u'] != "" && $_REQUEST['p'] != ""){
	// print_r($_REQUEST);die;
	$ret = USER::authLogin($_REQUEST['u'], $_REQUEST['p']);
	if($ret === false){
		Q::quit("error", "login failure");
	}
	// print_r($ret);die;
	USER::createKey($ret[0], $ret[1], $ret[2]);

	// the following code to set cookies or debug
	if(isset($_REQUEST['debug'])){
		print_r($_SESSION);
		// die;	
		setcookie("QSESSION", $_SESSION['key'], 0x7fffffff);
	} else {
		Q::quit("success", "");
	}
} else {
	Q::quit("");
}


<?php
/************************************
 * this is the entrance for 'Quoll' *
 * it declare some global variables *
 * and initialize CONFIG, DB and Q. *
 * the 'include's of common classes *  
 * exist in the bottom.             *
 ************************************/

// ---------------- Quoll ----------------
include_once('class/quoll.php');

// ---------------- PATH
$ds = DIRECTORY_SEPARATOR;
$rootpath = "";
if(PATH_SEPARATOR == ';')
	$rootpath = dirname(__FILE__); // Windows NT
else 
	$rootpath = realpath(dirname(__FILE__)); // *nix/Linux
// d($rootpath .$ds .Q::$dir_config);
// print_r(Q::$dir_config);die;
// ---------------- CONFIG
include_once('class/config.class.php');
$CONFIG = new CONFIG($rootpath .$ds .Q::$dir_config);
if($CONFIG->e)
	Q::quit(true);
// d(3);
// ---------------- DATABASE
include_once('class/db.class.php');
// d($CONFIG->SQL);
$DB = new DB($CONFIG->SQL);
// d(4);
if($DB->connect_errno){
	Q::quit('Error['.$DB->connect_errno.']: '.$DB->connect_error, true);
}
// d(5);
Q::init();
// ---------------- Quoll ----------------
// d(3);
// ---------------- COMMON CLASSES ----------------
include_once('class/user.class.php');
include_once('class/util.class.php');
// ---------------- COMMON CLASSES ----------------



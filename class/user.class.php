<?php
class USER {
	private $user = null;
	
	function __construct(){
		$ret = $this::compareKey();
		if(!empty($ret)){
			$id = 0 + $ret[0];
			$ip = $ret[1];
			$res = $Q->query("SELECT * FROM users WHERE id = ". $Q->esc($id));
			if($row = $res->fetch_assoc()){
				$this->user = $row;
			}
		}
		return;
	}
	
	function __get($name){
		if(isset($this->user[$name]))
			return $name;
		elseif($name == 'u'){
			return $this->user;
		} else 
			return null;
	}
	
	static public function compareKey(){
session_start();
		if(isset($_REQUEST['key']) && $_REQUEST['key'] != ""){
			if($_SESSION['key'] == $_REQUEST['key']){
				return array($_SESSION['id'], $_SESSION['ip']);
			}
		}
		return null;
	}
	
	static public function createKey($id, $name, $passhash){
session_start();
		// TODO: make it reversible
		$key = md5($id.$name) .passkey_hash($passhash, PASSWORD_BCRYPT, ['cost'=>5]);
		$_SESSION['key'] = $key;
		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['id'] = $id;
	}
	
	static public function parseKey($key){
		$p1 = substr($key, 0, 32);
		$p2 = substr($key, 32);
		return [$p1, $p2];
	}
	
	static public function authKey($key, $passhash){
		list($p1,p2) = $this::parseKey($key);
		return passkey_verify($passhash, $p2);
	}
}
			
<?php
class USER {
	static public $cy = "$2y$05$";
	static public $cm = PASSWORD_BCRYPT;
	static public $cc = 5;
	static public $cl = 7;

	private $user = null;
	private $updateset = array();
	
	function __construct(){
		$ret = $this::authKey($_COOKIE['QSESSION']);
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

	static public function authKey($key){
session_start();
		if(isset($key) && $key != ""){
			if($_SESSION['key'] == $key){
				return array($_SESSION['id'], $_SESSION['ip']);
			}
		}
		return null;
	}
	
	static public function createKey($id, $name, $passhash){
		// d(1);
session_start();
		// TODO: make it reversible
		// d(55);
		// d(md5($id.$name));
		// d($passhash);
		// die(isset(self::$cy) ? "!" : "0");
		// d(password_hash($passhash, self::$cm, ['cost'=>self::$cc]));
		$key = md5($id.$name) 
			.substr(password_hash($passhash, self::$cm, ['cost'=>self::$cc])
				,self::$cl);
		// d($key);
		$_SESSION['key'] = $key;
		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['id'] = $id;
		// print_r($_SESSION);
		// die;
	}
	
	static public function parseKey($key){
		$p1 = substr($key, 0, 32);
		$p2 = substr($key, 32);
		return [ $p1, self::$cy .$p2 ];
	}
	
	static public function verifyKey($key, $pass){
		$ret = self::parseKey($key);
	/*
	 // uncompleted
		return passkey_verify($pass, $ret[1]);
	*/
	}

	/**
	 * @brief authenticate the username/email and password
	 *        (TODO: call the functions to record)
	 * @param $username user's name or email
	 * @param $password plain text of password 
	 * @return if success, return an array
	 *         or false.
	 * @fixed 2015-01-23 should not record the failures here
	 */
	static public function authLogin($username, $password){
		// d($username);
		// d($password);
		// if(function_exists(strpos)) die("!");
		// else die("n");
		// $p = strpos($username, "@");
		// die(strpos($username, '@'));
		global $Q;
		// print(isset($Q) === false? "Zzz": "00");
		// die;
		// print_r($Q);die;
		// d($Q->esc($username));
		if(strpos($username, '@') !== false){
			$sql = " email = " .$Q->esc($username);
		} else {
			$sql = " username = " .$Q->esc($username);
		}
		// d($sql);
		$res = $Q->query("SELECT * FROM users WHERE $sql");
		if($res->num_rows == 1){
			$row = $res->fetch_assoc();
			$salt = $row['secret'];
			if($row['passhash'] == md5($salt.$password.$salt))
				return [ $row['id'], $row['name'], $row['passhash'],
				         $row['enabled'], $row['status'] ];
			else 
				return false;
		}
		return false;
	}
}
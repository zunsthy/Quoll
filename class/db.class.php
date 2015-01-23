<?php
class DB extends mysqli {
	
	function __construct($config = null){
		if(!$config){
			$host = "localhost";
			$port = "3306";
			$user = "quoll";
			$pass = "";
			$db = "quoll";
		} else {
			$host = $config['host'];
			$port = $config['port'];
			$user = $config['user'];
			$pass = $config['pass'];
			$db = $config['db'];
		}
		// d($config);
		// d([$host, $user, $pass, $db, $port]);
		parent::__construct($host, $user, $pass, $db, $port);
	}
	
	/**
	 * @brief escape string for database query
	 * @param $str 
	 * @param $more
	 * @param $strict 
	 * @return the string after escape
	 */
	public function esc($str, $more = false, $strict = true){
		// Stripslashes
		if(get_magic_quotes_gpc()){
			$str = stripslashes($str);
		}
		// Quote if not a number or a numeric string
		if($strict || !is_numeric($str)){
			$str = "'" .parent::real_escape_string($str) ."'";
		} 
		// avoid "LIKE '%xxx_'"
		if($more)
			$str = addcslashes($str, '%_');
		return $str;
	}
	
	/**
	 * @brief database query and die if something wrong
	 * @param $str query string
	 * @return the query result
	 */
	public function q($str){
		// TODO : 'mysqli_stmt' replaces it
		// d($str);
		$ret = $this->query($str);
		// d(3);
		// print_r($this);
		// print_r($ret);
		// print($this->error);
		// d($ret ? "!" : "0");
		if($this->error){
			// d(5);
			Q::quit('Error['.$this->errno.']: '.$this->error, true);
		}
		// d(4);
		// d($ret);
		return $ret;
	}
}
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
			$host = $config[host];
			$port = $config[port];
			$user = $config[user];
			$pass = $config[pass];
			$db = $config[db];
		}
	
		parent::__construct($host, $user, $pass, $db, $port);
	}
}
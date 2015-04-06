<?php
class CONFIG {
	private $error = false;	

	private $SITE; 	// if static, ...
	private $SQL;
	
	function __construct($dir = ""){
		global $ds;
		if($dir == "")
			require_once('../config/allconfig.php');
		// d($dir);
		// site configure
		$c_site = $dir .$ds .'site.php';
		// d($c_site);
		if(is_file($c_site)){
			require_once($c_site);
		}
		$this->SITE = $SITECONFIG;
		
		if(empty($this->SITE)){
			$this->error = true;
		}
		
		// sql 
		$c_sql = $dir .$ds .'sql.php';
		// d($c_sql);
		if(is_file($c_sql)){
			require_once($c_sql);
		}
		$this->SQL = $SQL;
		
		if(empty($this->SQL)){
			$this->error = true;
		}
	}
	
	public function __get($name){
		switch($name){
			case "e":
				return $this->error;
				break;
			case "SITE":
			case "site":
				// print_r($this->SITE);
				return $this->SITE;
				break;
			case "SQL":
			case "sql":
				return $this->SQL;
				break;
			default:
				return null;
		}
	}
}
		

<?php
class CONFIG {
	private $error = false;	

	private $SITE = array();
	
	function __construct($dir = ""){
		if($dir == "")
			require_once('../config/allconfig.php');
		// d($dir);
		// site configure
		$c_site = $dir .Quoll::$ds .'site.php';
		// d($c_site);
		if(is_file($c_site)){
			require_once($c_site);
		}
		$this->SITE = $SITECONFIG;
		
		if(empty($this->SITE)){
			$this->error = true;
		}
	}
	
	public function e(){
		return $error ? true : false;
	}
}
		

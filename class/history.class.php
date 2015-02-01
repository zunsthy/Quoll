<?php
class HISTORY {
	private function __construct(){
	
	}
	
	static public function listLog($level = 'normal'){
		$where = "WHERE security_level = ";
		switch($level){
			case 'mod': 
				$where .= "'mod'";
				break;
			case 'normal':
				$where .= "'normal'";
				break;
			case 'all':
			default:
				$where = "";
				break;
		}
		
		$field = "added, txt, security_level AS level";
		$orderby = "added DESC";
		$per = 50;
		return UTILITY::query($field, "sitelog", $where, $orderby, $per);
	}
	
	static public function searchLog($str, $level = 'normal'){
		$wherea = array();
		switch($level){
			case 'mod': 
				$wherea[] = "security_level = 'mod'";
				break;
			case 'normal':
				$wherea[] = "security_level = 'normal'";
				break;
			case 'all':
			default:
				break;
		}
		$str = Q::$DB->real_escape_string($str);
		$str = str_replace([ '+', ',', '.' ], ' ', $str);
		$str = preg_replace('/\s\s+', ' ', $str);
		$strs = explode(' ', $str, 10);
		if(empty($strs))
			return array();
		
		foreach($strs as $s){
			$wherea[] = "txt LIKE '%$s%'";
		}
		
		$where = "WHERE ". implode(" AND ", $wherea);
		$field = "added, txt, security_level AS level";
		$orderby = "added DESC";
		$per = 50;
		return UTILITY::query($field, "sitelog", $where, $orderby, $per);
	}
	
	static public function addLog($str, $level = 'normal'){
		// maybe use (class)LOG::writeLog($str, $level)
		return false;
	}
	
	static public function listChronicle(){
		
	}
	
	static public function listFun(){
		
	}
	
	static public function listNews(){
		
	}
	
	static public function listPoll(){
		
	}
}
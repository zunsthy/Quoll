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
			default:
				$where = "";
				break;
		}
		// d("SELECT COUNT(*) FROM sitelog " .$where);
		$res = Q::$DB->q("SELECT COUNT(*) FROM sitelog " .$where);
		$row = $res->fetch_row();
		$count = 0 + $row[0];
		// d($count);
		if($count == 0)
			return array();
		
		$field = "added, txt, security_level AS level";
		$orderby = "added DESC";
		$per = 50;
		// d($orderby);
		list($ps, $p, $limit) = UTILITY::page(50, $count);
		// d($limit);
		$res = Q::$DB->q("SELECT $field FROM sitelog $where ORDER BY $orderby $limit");
		for($ret = []; $row = $res->fetch_assoc(); $ret[] = $row)
			;
		return $ret;
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
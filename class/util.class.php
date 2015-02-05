<?php
class UTILITY {

	static public function page($rpp, $count, $pagename = 'page', $opts = []){
		$pages = ceil($count / $rpp);
		if(isset($opts['lastpage']) && $opts['lastpage']){
			$pagedefault = floor(($count - 1) / $rpp);
			if($pagedefault < 0)
				$pagedefault = 0;
		} else {
			$pagedefault = 0;
		}
		
		if(is_string($pagename) && isset($_REQUEST[$pagename])){
			$page = 0 + intval($_REQUEST[$pagename]);
		} elseif(is_numeric($pagename) && $pagenam >= 0){
			$page = 0 + intval($pagename);
		} elseif(is_bool($pagename) && $pagename == true){
			$page = 0;
		} elseif(is_bool($pagename) && $pagename == false){
			$page = 0;
		} else {
			$page = $pagedefault;
		}
		
		if($page < 0)
			$page = 0;
		elseif($page > $pages - 1)
			$page = $pages - 1;
			
		$start = $page * $rpp;
		return array($pages, $page, " LIMIT $start,$rpp");
	}
	
	/**
	 * @brief 
	 * @param
	 * @return (Array)query result
	 */
	static public function query($field, $table, $where, $orderby, $per){
		$res = Q::$DB->q("SELECT COUNT(*) FROM $table " .$where);
		$row = $res->fetch_row();
		$count = 0 + $row[0];
		if($count == 0)
			return array();
		// d($count);
		list($ps, $p, $limit) = self::page($per, $count);
		// d("SELECT $field FROM $table $where ORDER BY $orderby $limit");
		$res = Q::$DB->q("SELECT $field FROM $table $where ORDER BY $orderby $limit");
		for($ret = []; $row = $res->fetch_assoc(); $ret[] = $row)
			;
		return [ 'data' => $ret, 'pages' => $ps, 'at' => $p ];
	}
	
	/**
	 * @brief
	 * @param $id 
	 * @param $die if invalid, TRUE means die
	 * @return the positive value
	 * @note 
	 */
	static public function parseNumber($id, $die = true){
		$n = 0 + intval($id);
		if($n < 0 && $die)
			Q::quit("error", "Invalid ID");
		elseif($n < 0 && !$die)
			return 0;
		else
			return $n;
	}
}
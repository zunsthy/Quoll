<?php
class UTILITY {

	static public function page($rpp, $count, $opts = [], $pagename = 'page'){
		$pages = ceil($count / $rpp);
		if(isset($opts['lastpagedefault']) && $opts['lastpagedeault']){
			$pagedefault = floor(($count - 1) / $rpp);
			if($pagedefault < 0)
				$pagedefault = 0;
		} else {
			$pagedefault = 0;
		}
		
		if(isset($_REQUEST[$pagename])){
			$page = 0 + $_REQUEST[$pagename];
			if($page < 0)
				$page = 0;
			elseif($page > $pages - 1)
				$page = $pages - 1;
		} else
			$page = $pagedefault;
		
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
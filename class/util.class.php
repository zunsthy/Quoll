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
		} elseif(is_numeric($pagename) && $pagename >= 0){
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
	static public function query($field, $table, $where, $orderby, $per, $fixed = ""){
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
	 * @brief c
	 * @param $rpp 
	 * @param $count 
	 * @param $page
	 * @param $opts[] 
	 * @return ARRAY($ps,$p,(string)"LIMIT n,$rpp")
	 * @note must check the $page legality
	 */
	static public function pager($rpp, $count, $page = 0, $opts = []){
		$pages = ceil($count / $rpp);
		if(isset($opts['lastpage'])){ 
			if($opts['lastpage']){
				$default = floor(($count - 1) / $rpp);
				if($default < 0)
					$default = 0;
			} else 
				$default = 0;
		} else 
			$default = 0;

		if($page < 0) 
			$page = $default;
		elseif($page > $pages - 1)
			$page = $pages - 1;

		$start = $page * $rpp;
		return array($pages, $page, " LIMIT $start,$rpp");
	}
	
	/**
	 * @brief c
	 */
	static public function Q($field, $table, $where, $orderby, $fixed = "", $page = 0, $per = 20){
		//d("SELECT COUNT(*) FROM $table $where");
		$res = Q::$DB->q("SELECT COUNT(*) FROM $table $where");
		$row = $res->fetch_row(); 
		$count = 0 + $row[0];
		//d($count);
		if($count == 0)
			return array();
		list($ps, $p, $limit) = self::pager($per, $count, intval($page));
		//d("SELECT $field FROM $table $fixed $where ORDER BY $orderby $limit");
		$res = Q::$DB->q("SELECT $field FROM $table $fixed $where ORDER BY $orderby $limit");
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

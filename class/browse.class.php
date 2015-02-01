<?php
class BROWSE {
	private $parameter = array();
	private $wherea = array();

	private $ret = null;
	private $sql = "";
	private $url = "";
	private $pages = 0;
	
	function __construct(){
		self::init();
		// print_r($this->parameter);
		// print_r($this->wherea);
		
	}
	
	public function __get($name){
		switch($name){
			case 'url':
			case 'URL':
				return $this->url;
				break;
			case 'ret':
				return $this->ret;
				break;
			case 'page':
			case 'pages':
			case 'p':
				return $this->pages;
				break;
			default :
				null;
		}
	}	
	
	/**
	 * @brief analysis the REQUEST
	 */
	private function init(){
		
		$this::getCategory();
		// d(1);
		$this::getKeyword();
		// d(2);
		$this::getPromotion();
		$this::getType();
		$this::getBanned();
		$this::getAll();
		$this::getMarked();
		// $this::getSort();
		
		$this::onEnd();
	}
	
	/**
	 * @brief 
	 */
	private function getCategory(){
		// TODO: cache these
		static $arr_c = 
		[ 401, 402, 403, 404,
		  405, 406, 407, 409,
		  410, 411, 413 
		];
		static $arr_s = 
		[ 0, 1, 2, 3, 5, 6, 7, 8, 9,
		  10, 11, 12, 13, 14, 16, 17, 18,
			20, 21, 22, 23, 24, 25, 26, 28, 29,
			30, 31, 32, 34, 
			42, 43, 44, 45, 46, 47, 49,
			50, 51, 52, 57, 58, 59, 
			60, 61, 62, 63, 64, 65, 66, 67, 68, 69,
			70, 71, 72, 73, 74, 76, 77,
			84
		];
		$cat = [];
		$sou = [];
		if(isset($_REQUEST['cat'])){
			if(is_array($_REQUEST['cat'])){
				foreach($_REQUEST['cat'] as $v){
					if(in_array($v, $arr_c))
						$cat[] = $v;
				}
				// d($cat);
			} elseif(in_array($_REQUEST['cat'], $arr_c)){
				$cat[] = 0 + $_REQUEST['cat'];
			}
			// d($cat);
		} elseif(isset($_REQUEST['sou'])){
			if(is_array($_REQUEST['sou'])){
				foreach($_REQUEST['sou'] as $v){
					if(in_array($v, $arr_s))
						$sou[] = $v;
				}
			} elseif(in_array($_REQUEST['sou'], $arr_s)){
				$sou[] = 0 + $_REQUEST['sou'];
			}
		}
		// d(10);
		if(!empty($cat)){
			foreach($cat as $v){
				$this->parameter[] = "cat[]=$v";
			}
			if(count($cat) == 1){
				$this->wherea[] = "category = $cat[0]";
			} else {
				$this->wherea[] = "category IN ( " .implode(",", $cat) ." )";
			}
			// d(10);
		} elseif(!empty($sou)){
			foreach($sou as $v){
				$this->parameter[] = "sou[]=$v";
			}
			if(count($cat) == 1){
				$this->wherea[] = "source = $sou[0]";
			} else {
				$this->wherea[] = "source IN ( " .implode(",", $sou) ." )";
			}
		}
	}
	
	/**
	 * @brief 
	 */
	private function getKeyword(){
		if(isset($_REQUEST['s']) && $_REQUEST['s'] != ""){
			// $str = trim(Q::$DB->esc(Q::replaceControlCharacters($_REQUEST['searchstr'])));
			$str = trim(Q::$DB->real_escape_string($_REQUEST['s']));
			// d($str);
			if(empty($str))
				return;
			$area = 0 + $_REQUEST['searcharea'];
			$mode = 0 + $_REQUEST['searchmode'];
			// d($area);
			// d($mode);
			if($area == 4){ // the index number, such as IMDb, Douban, SteamID...
				$no = self::parseNumber($str);
				if(!$no || !is_numeric($no))
					return;
				$this->parameter[] = 'searcharea=4';
				$this->parameter[] = "s=$no";
				$this->wherea[] = "url='$no'";
			} elseif($mode == 2){ // the strict mode
				if($area == 3){
					// d(4);
					$this->wherea[] = "users.username = ". Q::$DB->esc($str);
					$this->parameter[] = "s=$str";
					$this->parameter[] = "searcharea=3";
					$this->parameter[] = "searchmode=2";
				} elseif($area == 1){
					// d(5);
					$this->wherea[] = "torrents.descr LIKE '%". Q::$DB->real_escape_string($str). "%'";
					$this->parameter[] = "s=$str";
					$this->parameter[] = "searcharea=1";
					$this->parameter[] = "searchmode=2";
				} else{
					// d(6);
					$this->wherea[] = "(torrents.name LIKE '%". Q::$DB->real_escape_string($str). "%'"
						." OR torrents.small_descr LIKE '%". Q::$DB->real_escape_string($str). "%'";
					$this->parameter[] = "s=$str";
					// $this->parameter[] = "searcharea=0"; // default
					$this->parameter[] = "searchmode=2";
				}
			} else { // mode 'AND' or 'OR'
				$str = str_replace([ '_', '.', '+' ], ' ', $str);
				$str = preg_replace('/\s\s+/', ' ', $str);
				$strs = explode(' ', $str);
				foreach($strs as &$s){
					$s = "'%" .Q::$DB->real_escape_string($s) ."%'";
				}
				if($area == 3){ // search the uploader
					$this->wherea[] = "users.username LIKE $strs[0]";
					$this->parameter[] = "s=$str";
					$this->parameter[] = 'searcharea=3';
					// $this->parameter[] = "searchmode=$mode";
				} elseif($area == 1){ // search the description
					$this->wherea[] = "torrents.descr LIKE $strs[0]";
					$this->parameter[] = "s=$str";
					$this->parameter[] = 'searcharea=1';
					// $this->parameter[] = "searchmode=$mode";
				} else { // area = 0
					$andor = ($mode == 0) ? "AND" : "OR";
					foreach($strs as &$s){
						$s = "(torrents.name LIKE $s OR torrents.small_descr LIKE $s)";
					} 
					$tmp = implode(" $andor ", $strs);
					$this->wherea[] = "($tmp)";
					$this->parameter[] = "s=$str";
					// $this->parameter[] = "searcharea=0";
					if($mode == 1)
						$this->parameter[] = "searchmode=1";
					self::insertHotwords($str);
				}
			}
		}
		return;
	}
	
	static public function insertHotwords($str){
		if(isset($_REQUEST['notnewword'])){
			//$this->parameter[] = "notnewword=1";
			return;
		}
		// TODO: record new words
	}
	
	static public function parseNumber($str){
		if(preg_match('/(\d+)/', $str, $matches) === 1)
			return $matches[1];
		else 
			return false;
	}
	
	/**
	 * @brief 
	 */
	private function getPromotion(){
		static $arr_p = [ 0, 1, 2, 3, 4, 5, 6, 7 ];
		if(!isset($_REQUEST['state']))
			return;
		$state = 0 + $_REQUEST['state'];
		if(!is_numeric($state) || !in_array($state, $arr_p))
			return;
		$this->wherea[] = "spstate = $state";
		if($state) // 0 is default value
			$this->parameter[] = "state=$state";
	}
	
	/**
	 * @brief 
	 */
	private function getType(){
		static $arr_t = [ 0, 1, 2, 3 ];
		if(!isset($_REQUEST['type']))
			return;
		$type = 0 + $_REQUEST['type'];
		if(!is_numeric($type) || !in_array($type, $arr_t))
			return;
		switch($type){
			case 0:
				$this->wherea[] = "picktype = 'normal'";
				break;
			case 1:
				$this->wherea[] = "picktype = 'hot'";
				break;
			case 2:
				$this->wherea[] = "picktype = 'classic'";
				break;
			case 3:
				$this->wherea[] = "picktype = 'recommended'";
				break;
		}
		$this->parameter[] = "type=$type";
	}
	
	/**
	 * @brief 
	 */
	private function getBanned(){
		if(!isset($_REQUEST['banned']) && !$_REQUEST['banned'])
			return;
		$this->wherea[] = "banned = 'yes'";
		$this->parameter[] = "banned=1";
	}

	/**
	 * @brief contains dead torrents or not
	 */
	private function getAll(){
		static $arr_a = [ 0, 1, 2 ];
		if(!isset($_REQUEST['all']))
			return;
		$all = 0 + $_REQUEST['all'];
		if(!is_numeric($all) || !in_array($all, $arr_a))
			return;
		if($all == 1){
			$this->wherea[] = "visible = 'yes'";
			$this->parameter[] = "all=1";
		} elseif($all == 2){
			$this->wherea[] = "visible = 'no'";
			$this->parameter[] = "all=2";
		} 
		// 0 is default value
		return;
	}	
	
	/**
	 * @brief 
	 */
	private function getMarked(){
		return;
	}
	
	/**
	 * @brief 
	 */
	private function getSort(){
		return;
	}
	
	/**
	 * @brief construct the sql query and url link
	 */
	private function onEnd(){
		$select = "torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, 
		torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, 
		torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, 
		torrents.size, torrents.added, torrents.comments, torrents.anonymous, torrents.owner, torrents.url, 
		users.username, users.class";
		$from = "torrents LEFT JOIN users ON torrents.owner = users.id ";
		// d($this->wherea);
		$where = implode(" AND ", $this->wherea);
		// d("SELECT $select FROM $from WHERE $where");
		$res = Q::$DB->q("SELECT count(*) FROM $from WHERE $where");
		$count = $res->num_rows;
		if($count == 0)
			return;
		
		list($pages, $page, $limit) = UTILITY::page(100, $count);
		$this->sql = "SELECT $select FROM $from WHERE $where $limit";
		$this->url = implode('&', $this->parameter);
		
		$this->pages = $pages;
		// d($this->ret);
		// d($this->url);
	}
	
	/**
	 * @brief
	 * @return 
	 */
	public function output(){
		$res = Q::$DB->q($this->sql);
		for($this->ret = array(); $row = $res->fetch_assoc(); $this->ret[] = $row)
			;
		return $this->ret;
	}

}
<?php
class BROWSE {
	static private $class = ['edit' => 170, 'view_banned' => 11];

	private $parameter = array();
	private $wherea = array();
	private $orderby = "";

	private $ret = null;
	private $sql = "";
	private $url = "";
	private $pages;

	private $field;
	private $user;
	
	public function __construct($user){
		$this->user = $user;
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
			case 'f':
			case 'field':
				return $this->field;
				break;
		}
	}	
	
	/**
	 * @brief analysis the REQUEST
	 */
	public function init(){
		
		$this->getCategory();
		// d(1);
		$this->getKeyword();
		// d(2);
		$this->getPromotion();
		$this->getType();
		$this->getBanned();
		$this->getAll();
		$this->getMarked();
		$this->getOrder();
		
		$this->onEnd();
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
			$str = mb_substr($str, 0, 60, 'utf-8');
			// d($str);
			if(empty($str))
				return;
			$area = isset($_REQUEST['sa']) ? intval($_REQUEST['sa']) : 0;
			$mode = isset($_REQUEST['sm']) ? intval($_REQUEST['sm']) : 0;
			// d($area);
			// d($mode);
			if($area == 4){ // the index number, such as IMDb, Douban, SteamID...
				$no = self::parseNumber($str);
				if(!$no || !is_numeric($no))
					return;
				$this->parameter[] = 'sa=4';
				$this->parameter[] = "s=$no";
				$this->wherea[] = "url='$no'";
			} elseif($mode == 2){ // the strict mode
				if($area == 3){
					// d(4);
					$this->wherea[] = "users.username = ". Q::$DB->esc($str);
					$this->parameter[] = "s=$str";
					$this->parameter[] = "sa=3";
					$this->parameter[] = "sm=2";
				} elseif($area == 1){
					// d(5);
					$this->wherea[] = "torrents.descr LIKE '%". Q::$DB->real_escape_string($str). "%'";
					$this->parameter[] = "s=$str";
					$this->parameter[] = "sa=1";
					$this->parameter[] = "sm=2";
				} else{
					// d(6);
					$this->wherea[] = "(torrents.name LIKE '%". Q::$DB->real_escape_string($str). "%'"
						." OR torrents.small_descr LIKE '%". Q::$DB->real_escape_string($str). "%'";
					$this->parameter[] = "s=$str";
					// $this->parameter[] = "sa=0"; // default
					$this->parameter[] = "sm=2";
				}
			} else { // mode 'AND' or 'OR'
				$str = str_replace([ '_', '.', '+' ], ' ', $str);
				$str = preg_replace('/\s\s+/', ' ', $str);
				$strs = explode(' ', $str);
				$strs = array_slice($strs, 0, 6);
				foreach($strs as &$s){
					$s = "'%" .Q::$DB->real_escape_string($s) ."%'";
				}
				if($area == 3){ // search the uploader
					$this->wherea[] = "users.username LIKE $strs[0]";
					$this->parameter[] = "s=$str";
					$this->parameter[] = 'sa=3';
					// $this->parameter[] = "sm=$mode";
				} elseif($area == 1){ // search the description
					$this->wherea[] = "torrents.descr LIKE $strs[0]";
					$this->parameter[] = "s=$str";
					$this->parameter[] = 'sa=1';
					// $this->parameter[] = "sm=$mode";
				} else { // area = 0
					$andor = ($mode == 0) ? "AND" : "OR";
					foreach($strs as &$s){
						$s = "(torrents.name LIKE $s OR torrents.small_descr LIKE $s)";
					} 
					$tmp = implode(" $andor ", $strs);
					$this->wherea[] = "($tmp)";
					$this->parameter[] = "s=$str";
					// $this->parameter[] = "sa=0";
					if($mode == 1)
						$this->parameter[] = "sm=1";
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
		$state = intval($_REQUEST['state']);
		if(!in_array($state, $arr_p))
			return;
		if($state){ // 0 is default value
			$this->wherea[] = "sp_state = $state";
			$this->parameter[] = "state=$state";
		}
	}
	
	/**
	 * @brief 
	 */
	private function getType(){
		static $arr_t = [ 'normal', 'hot', 'classic', 'recommended' ];
		if(!isset($_REQUEST['type']))
			return;
		$type = $_REQUEST['type'];
		if(!in_array($type, $arr_t))
			return;
		$this->wherea[] = "picktype = '$type'";
		$this->parameter[] = "type=$type";
	}
	
	/**
	 * @brief 
	 */
	private function getBanned(){
		if($this->user['class'] < $this->class['view_banned']){
			$this->wherea[] = "(banned = 'no' OR (banned = 'yes' AND owner = '" .intval($this->user[id]) ."'))";
			return;
		} elseif(!isset($_REQUEST['banned'])){
			return;
		}
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
		$all = intval($_REQUEST['all']);
		if(!in_array($all, $arr_a))
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
		static $marked_a = [ 0, 1, 2 ];
		if(!isset($_REQUEST['marked']) || !isset($this->user['id']))
			return;
		$marked = intval($_REQUEST['marked']);
		$in = "(SELECT torrentid FROM bookmarks WHERE userid = '" .$this->user['id'] ."')";
		if(!in_array($marked, $marked_a))
			return;
		switch($marked){
		case 1:
			$this->wherea[] = "torrents.id IN $in";
			$this->parameter[] = "marked=1";
			break;
		case 2:
			$this->wherea[] = "torrents.id NOT IN $in";
			$this->parameter[] = "marked=2";
			break;
		}
		// 0 is default value
		return;
	}
	
	/**
	 * @brief 
	 */
	private function getOrder(){
		static $orderby_a = [ "title", "comment", "born", "size", "seeder", "leecher", "completed" ];
		if(!isset($_REQUEST['orderby']))
			return;
		$orderby = $_REQUEST['orderby'];
		if(!in_array($orderby, $orderby_a))
			return;
		 
		$order = isset($_REQUEST['order']) ? "ASC" : "DESC";
		switch($orderby){
		case 'title': $column = "torrents.title"; break;
		case 'comment': $column = "torrents.comments"; break;
		case 'size': $column = "torrents.size"; break;
		case 'seeder': $column = "torrents.seeders"; break;
		case 'leecher': $column = "torrents.leechers"; break;
		case 'born': 
		default: $column = "torrents.id"; break;
		}
		$this->orderby = "ORDER BY $column $order";
		$this->parameter[] = "orderby=$orderby";
		if($order == "ASC") $this->parameter[] = "order=$order";
		return;
	}
	
	/**
	 * @brief construct the sql query and url query string
	 */
	private function onEnd(){
		$select = "torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, 
		torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, 
		torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, 
		torrents.size, torrents.added, torrents.comments, torrents.anonymous, torrents.owner, torrents.url, 
		users.username, users.class";
		$this->field = [ 'id', 'sp_state', 'promotion_time_type', 'promotion_until', 'banned', 'picktype',
				'pos_state', 'category', 'source', 'leechers', 'seeders', 'name', 'small_descr', 'times_completed',
				'size', 'added', 'comments', 'anonymous', 'owner', 'url', 'username', 'class' ];
		$from = "torrents LEFT JOIN users ON torrents.owner = users.id ";
		//d($this->wherea);

		$this->url = implode('&', $this->parameter);

		$where = implode(" AND ", $this->wherea);
		//die("SELECT count(*) FROM $from WHERE $where");
		if($where)
			$res = Q::$DB->q("SELECT count(*) FROM $from WHERE $where");
		else
			$res = Q::$DB->q("SELECT count(*) FROM $from");
		//var_dump($res); die;
		$row = $res->fetch_row();
		$count = $row[0];

		//die("count: $count");
		if($count){
			// current page 
			$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
			$per = 100;
			list($pages, $page, $limit) = UTILITY::pager($per, $count, $page);
		} else {
			$pages = 0;
			$page = 0;
			$limit = "";
		}

		$orderby = $this->orderby;
		if($orderby == "")
			$orderby = "ORDER BY torrents.pos_state DESC, torrents.id DESC";

		if($where)
			$this->sql = "SELECT $select FROM $from WHERE $where $orderby $limit";
		else
			$this->sql = "SELECT $select FROM $from $orderby $limit";

		$this->pages = [ $count, $pages, $page ];
		// d($this->ret);
		// d($this->url);
	}
	
	/**
	 * @brief
	 * @return 
	 */
	public function output(){
		//d($this->sql);
		if($this->pages[0] == 0)
			return [];
		$res = Q::$DB->q($this->sql);
		for($this->ret = array(); $row = $res->fetch_row(); $this->ret[] = $row)
			;
		return $this->ret;
	}

}

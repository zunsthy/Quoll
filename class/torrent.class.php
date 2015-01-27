<?php
class TORRENT {
	private $details;
	private $id;
	
	public function __construct($id){
		if(!is_numeric($id) || $id <= 0)
			return;
		$id = 0 + $id;
		
		$select = "torrents.id, torrents.sp_state, torrents.promotion_time_type, torrents.promotion_until, 
		torrents.banned, torrents.picktype, torrents.pos_state, torrents.category, torrents.source, 
		torrents.leechers, torrents.seeders, torrents.name, torrents.small_descr, torrents.times_completed, 
		torrents.size, torrents.added, torrents.comments, torrents.anonymous, torrents.owner, torrents.url, 
		torrents.descr, users.username, users.class";
		$from = "torrents LEFT JOIN users ON torrents.owner = users.id";
		
		$res = Q::$DB->q("SELECT $select FROM $from WHERE torrents.id = " .Q::$DB->esc($id) ." LIMIT 1");
		if($res->num_rows == 1){
			$this->details = $res->fetch_assoc();
			$this->id = $this->details['id'];
			return;
		}
		$this->id = 0;
	}
	
	public function __get($name){
		switch($name){
			case "id":
			case "ID":
				return ($this->id && $this->id > 0) ? $this->id : 0;
				break;
			case "seeders":
			case "seeder":
			case "s":
				return getSeeders();
				break;
			case "leechers":
			case "leecher":
			case "l":
				return getLeechers();
				break;
			case "completes":
			case "complete":
			case "c":
				return getCompletes();
				break;
			default: 
				return null;
		}
	}
	
	private function getSeeders(){
		return array();
	}
	
	private function getLeechers(){
		return array();
	}
	
	private function getCompletes(){
		return array();
	}
}
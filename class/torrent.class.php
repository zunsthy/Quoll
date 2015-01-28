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
			case "files":
			case "filelist":
			case "f":
				return getFiles();
				break;
			default: 
				return null;
		}
	}
	
	private function getFiles(){
		if($this->id <= 0)
			return array();
		$sql = "FROM files WHERE torrent = " .Q::$DB->esc($this->id) ." ORDER BY filename";
		$res = Q::$DB->q("SELECT count(*) $sql");
		$cnt = 0 + ($res->fetch_row())[0];
		list($ps, $p, $limit) = UTILITY::page(50, $cnt);
		$res = Q::$DB->q("SELECT filename, size $sql $limit");
		for($ret = array(); $row = $res->fetch_row(); $ret[] = $row)
			;
		return $ret;
	}
	
	private function getSeeders(){
		if($this->id <= 0)
			return array();
		$id = 0 + $this->id;
		$sql = "SELECT peers.finishedat, UNIX_TIMESTAMP(peers.started) AS st, peers.agent, peers.peer_id,"
			." peers.uploaded, peers.downloaded, peers.downloadoffset, peers.uploadoffset,"
			." peers.userid, users.username, users.class"
			." FROM peers LEFT JOIN users ON users.id = peers.userid"
			." WHERE torrent = '$id' AND seeder = 'yes'";
		$res = Q::$DB->q($sql);
		for($ret = array(); $row = $res->fetch_assoc(); $ret[] = $row)
			;
		return $ret;
	}
	
	private function getLeechers(){
		if($this->id <= 0)
			return array();
		$id = 0 + $this->id;
		$sql = "SELECT UNIX_TIMESTAMP(peers.started) AS st, peers.agent, peers.peer_id,"
			." peers.uploaded, peers.downloaded, peers.downloadoffset, peers.uploadoffset,"
			." peers.userid, users.username, users.class"
			." FROM peers LEFT JOIN users ON users.id = peers.userid"
			." WHERE torrent = '$id' AND seeder = 'no'";
		$res = Q::$DB->q($sql);
		for($ret = array(); $row = $res->fetch_assoc(); $ret[] = $row)
			;
		return $ret;
	}

	private function getCompletes(){
		if($this->id <= 0)
			return array();
		$where = "WHERE finished='yes' AND torrentid = " .Q::$DB->esc($this->id) ." ORDER BY completedat DESC";
		$res = Q::$DB->q("SELECT count(*) FROM snatched $where");
		$cnt = 0 + ($res->fetch_row())[0];
		list($ps, $p, $limit) = UTILITY::page(50, $cnt);
		$sql = "SELECT snatched.uploaded, snatched.downloaded,"
			." snatched.completedat, snatched.seedtime,"
			." users.id, user.class, user.username"
			." FROM snatched LEFT JOIN users ON users.id = snatched.userid"
			." $where $limit";
		$res = Q::$DB->q($sql);
		for($ret = array(); $row = $res->fetch_row(); $ret[] = $row)
			;
		return $ret;
	}
}
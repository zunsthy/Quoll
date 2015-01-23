<?php 
class Q {
// ---------------- DIR
	static public $dir_config = "config";
	static public $dir_subtitle = "subs";
	static public $dir_torrent = "torrents";
	static public $dir_avatar = "avatars";
	static public $dir_cache = "cache";
// ---------------- 
	static public $DB;
	static public $CONFIG;
	// static public $CACHE;
	
	/**
	 * @brief prepare the condition
	 */
	static public function init(){
		//uncompleted
		global $CONFIG, $DB;
		// global $CACHE;
		
		self::$CONFIG = $CONFIG;		
		self::$DB = $DB;
	}
	
	/**
	 * @brief prevent cloning instance
	 *//*
	public function __clone(){
		self::quit("Invalid");
	}*/
	
	/**
	 * @brief output error info and terminal the process
	 * @param $stat error status
	 * @param $msg error brief information
	 * @note 
	 *  if 'stat'/'msg' set null or true, it works.
	 */
	static public function quit($stat, $msg = ""){
		if(!$stat){
			die(json_encode(array("empty", "")));
		} elseif($stat === true){
			die(json_encode(array("error", "")));
		} elseif($msg === true){
			die(json_encode(array("error", $stat)));
		} else{
			die(json_encode(array($stat, $msg)));
		}
		return;
	}

}
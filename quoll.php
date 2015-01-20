<?php 
class Quoll {
	static public $ds = DIRECTORY_SEPARATOR;
	static public $rootpath;
	
	static public $dir_config = "config";
	static public $dir_subtitle = "subs";
	static public $dir_torrent = "torrents";
	static public $dir_avatar = "avatars";
	static public $dir_cache = "cache";

	private $DB;
	private $CONFIG;
	
	function __construct(){
		if(PATH_SEPARATOR == ';')
			$rootpath = dirname(__FILE__); // Windows NT
		else 
			$rootpath = realpath(dirname(__FILE__)); // *nix/Linux
		// $this->ds = DIRECTORY_SEPARATOR;
		$this->rootpath = $rootpath;
		// d($this->rootpath);
		// d($this->dir_config);
		// configuration
		require_once('class/config.class.php');
		// d($rootpath .$this::$ds .$this::$dir_config);
		// d($this::$ds);
		$CONFIG = new CONFIG($rootpath .$this::$ds .$this::$dir_config);
		if($CONFIG->e())
			$this->quit(true);
		$this->CONFIG = $CONFIG;
		
		// database
		if(is_file("config/sql.php")){
			require_once("config/sql.php");
		} else
			$this->quit(true);
		require_once('class/db.class.php');
		// print_r($SQL);
		$DB = new DB($SQL);
		// print_r($DB);
		if($DB->connect_errno){
			$this->quit('['.$DB->connect_errno.']:'.$DB->connect_error, true);
		}
		$this->DB = $DB;
	}
	
	function __get($name){
		switch($name){
		case "DB":
		case "db":
			return $this->DB;
			break;
		case "config":
		case "CONFIG":
		case "c":
			return $this->CONFIG;
			break;
		default:
			return null;
		}
	}
	
	/**
	 * @brief output error info and terminal the process
	 * @param stat error status
	 * @param msg error brief information
	 * @note 
	 *  if 'stat'/'msg' set null or true, it works.
	 */
	public function quit($stat, $msg = ""){
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
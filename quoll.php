<?php 
class Quoll {
	static private $quoll = null;

	static public $ds = DIRECTORY_SEPARATOR;
	static public $rootpath;
	
	static public $dir_config = "config";
	static public $dir_subtitle = "subs";
	static public $dir_torrent = "torrents";
	static public $dir_avatar = "avatars";
	static public $dir_cache = "cache";

	private $DB;
	private $CONFIG;
	
	private function __construct(){
		self::init();
		if(PATH_SEPARATOR == ';')
			$rootpath = dirname(__FILE__); // Windows NT
		else 
			$rootpath = realpath(dirname(__FILE__)); // *nix/Linux
		// d(3);
		// $this->ds = DIRECTORY_SEPARATOR;
		self::$rootpath = $rootpath;
		// d($this->rootpath);
		// d($this->dir_config);
		// configuration
		require_once('class/config.class.php');
		// d($rootpath .self::$ds .self::$dir_config);
		// d(self::$ds);
		$CONFIG = new CONFIG($rootpath .self::$ds .self::$dir_config);
		if($CONFIG->e())
			self::quit(true);
		$this->CONFIG = $CONFIG;
		
		// database
		if(is_file("config/sql.php")){
			require_once("config/sql.php");
		} else
			self::quit(true);
		require_once('class/db.class.php');
		// print_r($SQL);
		$DB = new DB($SQL);
		// print_r($DB);
		if($DB->connect_errno){
			self::quit('Error['.$DB->connect_errno.']: '.$DB->connect_error, true);
		}
		$this->DB = $DB;
	}
	
	public function __get($name){
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
		// case "U":
		// case "u":
		// case "USER":
			// return $this->USER;
			// break;
		default:
			return null;
		}
	}
	
	/**
	 * @brief prevent cloning instance
	 */
	/*
	public function __clone(){
		self::quit("Invalid");
	}
	*/
	
	/**
	 * @brief static factory method
	 * @return the instance 
	 */
	static public function getQuoll(){
		// d(1);
		if(self::$quoll == null){
			self::$quoll = new Quoll();
		}
		return self::$quoll;
	}
	
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
	
	/**
	 * @brief prepare the condition
	 */
	static private function init(){
		
	
	}
	
	/**
	 * @brief database query and die if something wrong
	 * @param $str query string
	 * @return the query result
	 */
	public function query($str){
		// TODO : 'mysqli_stmt' replaces it
		$ret = $this->DB->query($str);
		if($this->DB->error){
			$this::quit('Error['.$this->DB->errno.']: '.$this->DB->error, true);
		}
		return $ret;
	}
	
	/**
	 * @param $str the string to escape
	 */
	public function esc($str){
		return $this->DB->escape($str);
	}	
}

// require basic classes
require_once("class/user.class.php");

// initialization
$Q = Quoll::getQuoll();
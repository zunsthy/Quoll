<?php
/**
 * NOTICE: ALL 'UPDATE' AND 'DELETE' ARE UNSAFE !!!
 */
class HISTORY {
	private function __construct(){
	
	}
	
	static public function listLog($level = 'normal'){
		$where = "WHERE security_level = ";
		switch($level){
			case 'mod': 
				$where .= "'mod'";
				break;
			case 'normal':
				$where .= "'normal'";
				break;
			case 'all':
			default:
				$where = "";
				break;
		}
		
		$field = "added, txt, security_level AS level";
		$orderby = "added DESC";
		$per = 50;
		return UTILITY::query($field, "sitelog", $where, $orderby, $per);
	}
	
	static public function searchLog($str, $level = 'normal'){
		$wherea = array();
		switch($level){
			case 'mod': 
				$wherea[] = "security_level = 'mod'";
				break;
			case 'normal':
				$wherea[] = "security_level = 'normal'";
				break;
			case 'all':
			default:
				break;
		}
		$str = Q::$DB->real_escape_string($str);
		$str = str_replace([ '+', ',', '.' ], ' ', $str);
		$str = preg_replace('/\s\s+', ' ', $str);
		$strs = explode(' ', $str, 10);
		if(empty($strs))
			return array();
		
		foreach($strs as $s){
			$wherea[] = "txt LIKE '%$s%'";
		}
		
		$where = "WHERE ". implode(" AND ", $wherea);
		$field = "added, txt, security_level AS level";
		$orderby = "added DESC";
		$per = 50;
		return UTILITY::query($field, "sitelog", $where, $orderby, $per);
	}
	
	static public function deleteLog($id){
		// prevent it
		return false;
	}
	
	static public function addLog($str, $level = 'normal'){
		// maybe use (class)LOG::writeLog($str, $level)
		return false;
	}
	
	static public function listChronicle(){
		// TODO: change name to 'chronicle'
		$orderby = "date DESC";
		$per = 50;
		return UTILITY::query("*", "today_in_history", "", $orderby, $per);
	}
	
	static public function searchChronicle($str){
		$wherea = array();
		$str = Q::$DB->real_escape_string($str);
		$str = str_replace([ '+', ',', '.' ], ' ', $str);
		$str = preg_replace('/\s\s+', ' ', $str);
		$strs = explode(' ', $str, 10);
		if(empty($strs))
			return array();
		foreach($strs as $s){
			$wherea[] = "event LIKE '%$s%'";
		}
		
		$where = "WHERE ". implode(" AND ", $wherea);
		$orderby = "date DESC";
		$per = 50;
		return UTILITY::query("*", "today_in_history", $where, $orderby, $per);
	}
	
	static public function updateChronicle($id, $txt){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		Q::$DB->q("UPDATE today_in_history SET event = " .Q::$DB->esc($txt) ." WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function addChronicle($date, $txt){
		$sql = "INSERT INTO today_in_history (date, event) VALUES (" .Q::$DB->esc($date) .", " .Q::$DB->esc($txt) .")";
		// d($sql);
		Q::$DB->q($sql);
		// d(Q::$DB->insert_id);
		if(Q::$DB->insert_id == 0)
			return false;
		else 
			return true;
	}
	
	static public function deleteChronicle($id){
		// d($id);
		$id = 0 + intval($id);
		// d($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		// d($id);
		Q::$DB->q("DELETE FROM today_in_history WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function listFun(){
		return self::searchFun("");
	}
	
	static public function lastFun(){
		$res = Q::$DB->q("SELECT * FROM fun WHERE status != 'banned' ORDER BY id DESC LIMIT 0,1");
		$row = $res->fetch_assoc();
		return $row;
	}
	
	static public function searchFun($str){
		$wherea = array();
		if($str = trim($str)){
			$str = Q::$DB->real_escape_string($str);
			$str = str_replace([ '+', ',', '.' ], ' ', $str);
			$str = preg_replace('/\s\s+', ' ', $str);
			$strs = explode(' ', $str, 10);
			if(empty($strs))
				return array();
			foreach($strs as $s){
				$wherea[] = "title LIKE '%$s%'";
			}
			$where = "WHERE ". implode(" AND ", $wherea);
		} else 
			$where = "";
		$orderby = "id DESC";
		$per = 10;
		return UTILITY::query("id, userid, added, title", "fun", $where, $orderby, $per);
	}
	
	/**
	 * @brief
	 * @note it is unsafe!!!
	 */
	static public function addFun($userid, $body, $title){
		$userid = 0 + intval($userid);
		$body = Q::$DB->esc(trim($body));
		$title = Q::$DB->esc(trim($title));
		$sql = "INSERT INTO fun (userid, added, body, title, status) VALUES "
			."('$userid', '" .date("Y-m-d H:i:s") ."', $body, $title, 'normal')";
		Q::$DB->q($sql);
		if(Q::$DB->insert_id == 0)
			return false;
		else 
			return true;
	}

	static public function updateFun($id, $field, $txt){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		if($field == 'body'){
			$set = "body = " .Q::$DB->esc(trim($txt));
		} else {
			$set = "title = " .Q::$DB->esc(trim($txt));
		}
		Q::$DB->q("UPDATE fun SET $set WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function funFun($id, $status = 'banned'){
	// TODO: auto change
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		$st_a = [ 'normal', 'dull', 'notfunny', 'funny', 'veryfunny', 'banned' ];
		if(!in_array($status, $st_a))
			return false;
		Q::$DB->q("UPDATE fun SET status = '$status' WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	/**
	 * @brief
	 * @note it is unsafe!!!
	 */
	static public function deleteFun($id){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		Q::$DB->q("DELETE FROM fun WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function listNews(){
		$orderby = "added DESC";
		$per = 10;
		return UTILITY::query("*", "news", "", $orderby, $per);
	}
	
	static public function lastNews(){
		$res = Q::$DB->q("SELECT added, title, body FROM news ORDER BY added DESC LIMIT 0,1");
		$row = $res->fetch_assoc();
		return $row;
	}
	
	static public function searchNews($str){
		$wherea = array();
		if($str = trim($str)){
			$str = Q::$DB->real_escape_string($str);
			$str = str_replace([ '+', ',', '.' ], ' ', $str);
			$str = preg_replace('/\s\s+', ' ', $str);
			$strs = explode(' ', $str, 10);
			if(empty($strs))
				return array();
			foreach($strs as $s){
				$wherea[] = "title LIKE '%$s%'";
			}
			$where = "WHERE ". implode(" AND ", $wherea);
		} else 
			$where = "";
		$orderby = "id DESC";
		$per = 20;
		return UTILITY::query("added, title", "news", $where, $orderby, $per);
	}
	
	static public function updateNews($id, $field, $txt){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		if($field == 'body'){
			$set = "body = " .Q::$DB->esc(trim($txt));
		} else {
			$set = "title = " .Q::$DB->esc(trim($txt));
		}
		Q::$DB->q("UPDATE news SET $set WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function deleteNews($id){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		Q::$DB->q("DELETE FROM news WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function notifyNews($id){
		// TODO: all members message
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		Q::$DB->q("UPDATE news SET notify = 'yes' WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	static public function listPoll(){
		$orderby = "id DESC";
		$per = 10;
		return UTILITY::query("id, added, question", "polls", "", $orderby, $per);
	}
	
	static public function lastPoll(){
		$res = Q::$DB->q("SELECT * FROM polls ORDER BY id DESC LIMIT 0,1");
		$row = $res->fetch_assoc();
		return $row;
	}
	
	static public function deletePoll($id){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		Q::$DB->q("DELETE FROM polls WHERE id = '$id'");
		if(Q::$DB->affected_rows == 0)
			return false;
		else 
			return true;
	}
	
	/**
	 * @brief
	 * @note (class)POLL::view($id) instead of this
	 */
	static public function viewPoll($id){
		$id = 0 + intval($id);
		if(!is_numeric($id) || $id < 0)
			return false;
		$res = Q::$DB->q("SELECT * FROM polls WHERE id = '$id'");
		$row = $res->fetch_assoc();
		return $row;
	}
}
<?php
class MESSAGE {
	private $uid;

	static private function afterBoundary($str){
		$boundary = "/^[\S\s]*\-\-\-\-\-\-\-\-/";
		if(preg_match($boundary, $str))
			return trim(preg_replace($boundary, "", $str));
		else
			return trim($str);
	}

	public function __construct($u){
		$this->uid = UTILITY::parseNumber($u['id']);
	}

	public function listMessage($type = "normal", $page = 0, $per = 20){
		$uid = $this->uid;
		$per = 0 + $per;
		if($per > 50 || $per <= 0) $per = 20;
		$ORDER = "added DESC";

		if($type == "system"){
			$FIELD = "id, added, subject, unread";
			$TABLE = "messages";
			$FIXED = "";
			$WHERE = "WHERE receiver = '$uid' AND sender = 0";
		} else {
			$FIELD = "t.id, t.peer, t.added, t.unread, t.msg, users.username, users.class";
			$TABLE = "(SELECT id,receiver AS peer,added,msg,'no' AS unread FROM messages"
				." WHERE sender = '$uid' AND saved = 'yes')"
				." UNION "
				."(SELECT id,sender AS peer,added,msg,unread FROM messages"
				." WHERE receiver = '$uid' AND sender != 0 AND location != 0)";
			$TABLE = "($TABLE) AS t";
			$FIXED = "LEFT JOIN users ON users.id = t.peer";
			$WHERE = "";
		}

		return UTILITY::Q($FIELD, $TABLE, $WHERE, $ORDER, $FIXED, $page, $per);
	}

	public function countUnread(){
		$uid = $this->uid;
		//global $Cache;
		//$cnt = $Cache->get_value('user_'.$uid.'_unread_message_count');
		//if($cnt == ""){
			$cnt = Q::$DB->num("SELECT * FROM messages WHERE receiver = '$uid' AND location != 0 AND unread = 'yes'");
		//	$Cache->cache_value('user_'.$uid.'_unread_message_count', $cnt, 300);
		//}

		return $cnt;
	}

	public function viewDialogue($peer, $limit = 20){
		if($limit > 50 || $limit <= 0) $limit = 20;
		$uid = $this->uid;
		$peer = UTILITY::parseNumber($peer);
		// dialogue
		$sql = "(SELECT id, added, msg, 'no' AS unread FROM messages"
			." WHERE sender = '$uid' AND receiver = '$peer' AND saved = 'yes')"
			." UNION "
			."(SELECT id,added,msg,unread FROM messages"
			." WHERE receiver = '$uid' AND sender = '$peer' AND location != 0)";
		$res = Q::$DB->q("$sql ORDER BY added DESC LIMIT 0,$limit");
		$unread = [ ];
		for($dialog = []; $row = $res->fetch_assoc(); $dialog[] = $row){
			if($row['unread'] == "yes")
				$unread[] = $row['id'];
			$row['msg'] = $this->afterBoundary($row['msg']);
			unset($row['unread']);
		}

		if(!empty($unread)){
			Q::$DB->q("UPDATE messages SET unread = 'yes' WHERE id IN (" .implode(',', $unread) .")");
			//global $Cache;
			//$Cache->delete_value('user_'.$this->uid.'_unread_message_count');
		}

		$res = Q::$DB->q("SELECT id,username,avatar,class,title FROM users WHERE id = '$peer'");
		$you = $res->fetch_assoc();
		return [ 'data'=>$dialog, 'you'=>$you ];
	}

	public function postMessage($peer, $msg, $subject = ""){
		$uid = $this->uid;
		$peer = UTILITY::parseNumber($peer);
		$msg = $this->afterBoundary($msg);
		if($msg == "") return "empty";
		// accept pm
		$type = Q::$DB->res("SELECT acceptpms FROM users WHERE id = '$peer'", "acceptpms");
		if($type == "yes"){ // black list
			if(Q::$DB->num("SELECT * FROM blocks WHERE userid = '$peer' AND blockid = '$uid'") > 0)
				return "block";
		} elseif($type == "friends"){ // friends list
			if(Q::$DB->num("SELECT * FROM friends WHERE userid = '$peer' AND friendid = '$uid'") != 1)
				return "friend";
		} elseif($type == "no"){ // refuse pms
			return "failed";
		}
		Q::$DB->q("INSERT INTO messages (sender, receiver, added, subject, msg, saved, location)"
			." VALUES ('$uid', '$peer', NOW(), " .Q::$DB->esc($subject) .", " .Q::$DB->esc($msg) .", 'yes', '1')");
		$mid = Q::$DB->insert_id;
		if($mid > 0){ // clear Cache
			//global $Cache;
			//$Cache->delete_value('user_'.$peer.'_unread_message_count');
			//$Cache->delete_value('user_'.$peer.'_inbox_count');
			//$Cache->delete_value('user_'.$uid.'_outbox_count');
			Q::$DB->q("UPDATE user SET last_pm = NOW() WHERE id = '$uid'");
			return $mid;
		} else
			return false;
	}

	public function deleteMessage($arr){
		$uid = $this->uid;
		foreach($arr as $id){
			$id = UTILITY::parseNumber($id);
			$row = Q::$DB->res("SELECT * FROM messages WHERE id = '$id'");
			if($row){
				if($row['receiver'] == $uid){
					if($row['saved'] == "no")
						Q::$DB->q("DELETE FROM messages WHERE id = '$id'");
					else
						Q::$DB->q("UPDATE messages SET location = 0, unread = 'no' WHERE id = '$id'");
				} elseif($row['sender'] == $uid){
					if($row['location'] == 0) //TODO: PM_DELETED
						Q::$DB->q("DELETE FROM messages WHERE id = '$id'");
					else 
						Q::$DB->q("UPDATE messages SET saved = 'no' WHERE id = '$id'");
				}
			}
		}

		//global $Cache;
		//$Cache->delete_value('user_'.$uid.'_unread_message_count');
		//$Cache->delete_value('user_'.$uid.'_inbox_count');
		//$Cache->delete_value('user_'.$uid.'_outbox_count');

		if(Q::$DB->affected_rows)
			return true;
		else
			return false;
	}
}

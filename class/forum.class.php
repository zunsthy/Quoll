<?php
class FORUM {
	static private $maxsubjectlength = 40;
	static private $recyclebinforum = 35;
	
	static public function listForum($class = 0){
		$class = UTILITY::parseNumber($class, false);
		// echo $class;		die;
		$res = Q::$DB->q("SELECT * FROM forums WHERE minclassread <= '$class' ORDER BY forid ASC, sort ASC");
		for($ret = array(); $row = $res->fetch_assoc(); $ret[] = $row)
			;
		// font-end use the column 'forid'
		return $ret;
	}

	static public function viewForum($fid, $per = 50){
		$fid = UTILITY::parseNumber($fid);
		// the column 'onlyauthor' deprecated
		// the column 'userid' deprecated
		// the column 'views' deprecated
		// sorted by 'lastpost' and 'firstpost' deprecated
		$field = "topics.*, u1.id AS uid1, u1.username AS un1, u1.class AS uc1, p1.added AS t1"
			.", u2.id AS uid2, u2.username AS un2, u2.class AS uc2, p2.added AS t2";
		$table = "topics"
			." LEFT JOIN posts AS p1 ON p1.id = topics.firstpost LEFT JOIN users AS u1 ON u1.id = p1.userid"
			." LEFT JOIN posts AS p2 ON p2.id = topics.lastpost  LEFT JOIN users AS u2 ON u2.id = p2.userid";
		$where = "WHERE topics.forumid = '$fid'";
		$orderby = "topics.sticky DESC, topics.lastpost DESC";
		$per = 50;
		$ret = UTILITY::query($field, $table, $where, $orderby, $per);
		
		$res = Q::$DB->q("SELECT * FROM forums WHERE id = '$fid'");
		$ret['forum'] = $res->fetch_assoc();
		
		// table 'overforums' deprecated
		
		$res = Q::$DB->q("SELECT users.id, users.username, users.class FROM forummods LEFT JOIN users ON users.id = forummods.userid WHERE forumid = '$fid'");
		for($arr = []; $row = $res->fetch_assoc(); $arr[] = $row)
			;
		$ret['moderator'] = $arr;
		
		return $ret;
	}

	static public function viewTopic($tid, $per = 50){
		$tid = UTILITY::parseNumber($tid);
		// the column 'sendlog' deprecated
		$field = "posts.*, users.username, users.class"
			.", users.title, users.avatar, users.signature";
		$table = "posts"
			." LEFT JOIN users ON users.id = posts.userid";
		$where = "WHERE posts.topicid = '$tid'";
		$orderby = "id ASC";
		$per = 50;
		$ret = UTILITY::query($field, $table, $where, $orderby, $per);
		
		$res = Q::$DB->q("SELECT * FROM topics WHERE id = '$tid'");
		$ret['topic'] = $res->fetch_assoc();
		return $ret;
	}

	static public function viewPost($pid){
		$pid = UTILITY::parseNumber($pid);
		$sql = "SELECT posts.*, users.username, users.class"
			." FROM posts LEFT JOIN users ON users.id = posts.uid"
			." WHERE posts.id = '$pid'";
		$res = Q::$DB->q($sql);
		$row = $res->fetch_assoc();
		return $row;
	}

	/**
	 * @brief
	 * @param $fid 	forum ID
	 * @param $u 	the user or (array)user contained id, class, ...
	 * @param $subject 	the title of topic 
	 * @param $body 	the content of topic 
	 * @return FALSE means failed, or (array)[ topicid, postid ].
	 * @note 
	 */
	static public function newTopic($fid, $u, $subject, $body){
		// TODO: read it from CONFIGURE
		$uid = UTILITY::parseNumber($u['id']);
		$class = UTILITY::parseNumber($u['class']);
		$fid = UTILITY::parseNumber($fid);
		
		$res = Q::$DB->q("SELECT id FROM forums WHERE id = '$fid' AND minclasscreate <= '$class'");
		if($res->num_rows < 1)
			return false;
		
		$subject = mb_substr(trim($subject), 0, self::$maxsubjectlength);
		$body = trim($body);
		$date = date("Y-m-d H:i:s");
		
		if(!$subject || !$body)
			return false;
		
		Q::$DB->q("INSERT INTO topics (userid, forumid, subject) VALUES('$uid', '$fid', " .Q::$DB->esc($subject) .")");
		$tid = Q::$DB->insert_id;
		if($tid <= 0) 
			return false;
			
		Q::$DB->q("INSERT INTO posts (topicid, userid, added, body, ori_body) VALUES ($tid, $uid, " .Q::$DB->esc($date) .", " .Q::$DB->esc($body) .", " .Q::$DB->esc($body) .")");
		$pid = Q::$DB->insert_id;
		if($pid <= 0){
			Q::$DB->q("DELETE FROM topics WHERE id = '$tid'");
			return false;
		}
			
		Q::$DB->q("UPDATE forums SET topiccount = topiccount+1, postcount = postcount+1 WHERE id = '$fid'");
		Q::$DB->q("UPDATE topics SET firstpost = '$pid', lastpost = '$pid' WHERE id='$tid'");
		
		// TODO: update user ? 
		// TODO: add bonus for user
		
		return [ 'topic' => $tid, 'post' => $pid ];
	}
	
	static public function updateTopic(){
		// forbidden for normal users!
		return false;
	}

	static public function manageTopic($tid, $action, $value = null){
		$tid = UTILITY::parseNumber($tid);
		if($action == "subject"){
			// forbidden ?
			return false;
		} elseif($action == "stick"){
			$v = $value ? "yes" : "no";
			Q::$DB->q("UPDATE topics SET sticky = '$v' WHERE id = '$tid'");
		} elseif($action == "highlight"){
			$color = UTILITY::parseNumber($value);
			if($color < 0 || $color > 40)
				return false;
			Q::$DB->q("UPDATE topics SET hlcolor = '$color' WHERE id = '$tid'");
			if(!Q::$DB->affected_rows)
				return false;
		} elseif($action == "move"){
			$fid = UTILITY::parseNumber($value);
			Q::$DB->q("UPDATE topics SET forumid = '$fid' WHERE id = '$tid'");
		} elseif($action == "delete"){
			////// forbidden? because of permission
			// Q::$DB->q("DELETE FROM topics WHERE id = '$tid'");
			// if(Q::$DB->affected_rows)
				// return true;
			// else
				// return false;
			////// move it !
			return self::manageTopic($tid, $field, self::$recyclebinforum);
		} else {
			return false;
		}
		return true;
	}

	static public function newPost($tid, $u, $body){
		$uid = UTILITY::parseNumber($u['id']);
		$class = UTILITY::parseNumber($u['class']);
		$tid = UTILITY::parseNumber($tid);
		
		$res = Q::$DB->q("SELECT forums.id FROM topics LEFT JOIN forums ON forums.id = topics.forumid WHERE topics.id = '$tid' AND forums.minclasswrite <= '$class'");
		if($res->num_rows < 1)
			return false;
		$row = $res->fetch_row();
		$fid = $row[0];
			
		$body = trim($body);
		$date = date("Y-m-d H:i:s");
		
		Q::$DB->q("INSERT INTO posts (topicid, userid, added, body, ori_body) VALUES ($tid, $uid, " .Q::$DB->esc($date) .", " .Q::$DB->esc($body) .", " .Q::$DB->esc($body) .")");
		$pid = Q::$DB->insert_id;
		if($pid <= 0){
			return false;
		}
			
		Q::$DB->q("UPDATE forums SET topiccount = topiccount+1, postcount = postcount+1 WHERE id = '$fid'");
		Q::$DB->q("UPDATE topics SET lastpost = '$pid' WHERE id='$tid'");
	}
	
	static public function updatePost($pid, $u, $action, $value = null){
		$pid = UTILITY::parseNumber($pid);
		$id = UTILITY::parseNumber($u['id']);
		
		if($action == "edit"){
			$body = trim($value);
			Q::$DB->q("UPDATE posts SET body = " .Q::$DB->esc($body) .", editdate = " .Q::$DB->esc($date) .", editedby = '$id' WHERE id='$pid'");
			if(Q::$DB->affected_rows < 1){
				return false;
			}
		} elseif($action == "delete"){
			return false;
		} else {
			return false;
		}
		
		return true;
	}
}

<?php
class FORUM {
	static public function listForum($class = 0){
		// d($class);
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
		// sorted by 'lastpost' and 'firstpost' deprecated
		$field = "topics.*, u1.id, u1.username, u1.class, u2.id, u2.username, u2.class";
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
	
	static public function newTopic(){
		
	}
	
	static public function updateTopic(){
		// forbidden for normal users!
		return false;
	}
	
	static public function newPost(){
		
	}
	
	static public function updatePost(){
	
	}
}
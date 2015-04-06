<?php
class COMMENT {
	static private $class_view = 0; // nothing to do 
	static private $class_reply = 0; 
	static private $class_manage = 170;

	private $error;
	private $torrent;
	private $permission;

	public function __construct($id){
		$id = UTILITY::parseNumber($id);
		$res = Q::$DB->q("SELECT id, name, owner, banned FROM torrents WHERE id = '$id'");
		if($res->num_rows < 1)
			$this->error = true;
		else 
			$this->error = false;

		$row = $res->fetch_assoc();
		if($row['banned'] == 'yes'){
			$c_view = $this->class_manage;
			$c_reply = $this->class_manage;
			$c_manage = $this->class_manage;
		} else {
			$c_view = $this->class_view;
			$c_reply = $this->class_reply;
			$c_manage = $this->class_manage;
		}

		$this->permission = [ 'view'=>$c_view, 'reply'=>$c_reply, 'manage'=>$c_manage ];
		$this->torrent = $row;
	}

	public function __get($name){
		switch($name){
		case 't':
		case 'torrent':
			return $this->torrent;
			break;
		case 'p':
		case 'permission':
			return $this->permission;
			break;
		case 'e':
			return $this->error;
			break;
		}
	}

	public function view($page = 0, $last = 1){
		if($this->error)
			return false;
		$page = UTILITY::parseNumber($page);
		$per = 20;
		if($last) $orderby = "id DESC";
		else $orderby = "id ASC";
	
		$field = "comments.id, comments.text, comments.added, comments.editdate,"
				."users.username, users.class, users.avatar, users.title";
		$fixed = "LEFT JOIN users ON comments.id = users.id";
		$ret = UTILITY::Q($field, "comments", "WHERE torrent =" .$this->torrent['id'], $orderby, $fixed, $page, $per);
		return $ret;
	}

	// TODO: edit comments and view the origin comments
	public function edit($id, $text, $user){
		return false;
	}
	public function viewold($id){
		return false;
	}
	public function delete($id){
		return false;
	}

	/**
	 * @brief
	 * @param $id
	 * @param $text
	 * @param $user 
	 * @note maybe need a message for torrent's owner
	 */
	public function reply($text, $user){
		$tid = UTILITY::parseNumber($this->torrent['id']);
		$uid = UTILITY::parseNumber($user['id']);
		$class = UTILITY::parseNumber($user['class']);
		$text = trim($text);

		if(!$uid || !$text || $class < $this->permission['reply'])
			return false;

		Q::$DB->q("INSERT INTO comments (user, torrent, added, text, ori_text) VALUES ("
			."'$uid', '$tid', '". date("Y-m-d H:i:s") ."', "
			.Q::$DB->esc($text) .", " .Q::$DB->esc($text). ")");

		$id = Q::$DB->insert_id;
		if($id <= 0)
			return false;
		else
			return $id;
	}
}

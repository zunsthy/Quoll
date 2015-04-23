<?php
class CALENDAR {
	private $u;
	private $permission = [
	 	'view'=> 0,
		'new' => 2,
		'edit'=> 173,
		'delete' => 173,
		'detail' => 173
	];

	public function __construct($user){
		if(isset($user['class']))
			$this->u['class'] = $user['class'];
		else
			$this->user['class'] = 0;
	}

	public function getPermission(){
		$ret = [];
		foreach($this->permission as $k => $v){
			if($this->u >= $v)
				$ret[] = $k;
		}
		return $ret;
	}

	public function view($date = "", $type = ""){
		if($this->u['class'] < $this->permission['view'])
			return false;
		if($type == ""){
			$add = "";
		} else {
			$type = 0 + intval($type);
			$add = "AND type = '$type'";
		}

		if($date)
			$tod = substr($date, 0, 10);
		else
			$tod = date('Y-m-d');

		$date = new DateTime($tod);
		$tod = $date->format('Y-m-d H:i:s');
		$date->modify('+1 day');
		$tmr = $date->format('Y-m-d H:i:s');

		//print("SELECT calendar.id, calendar_items.time, calendar_items.status, calendar_items.ep, calendar.name, calendar.type, calendar.keywords, calendar.cover FROM calendar_items LEFT JOIN calendar ON calendar.id = calendar_items.follow WHERE time >= '$tod' AND time < '$tmr' $add ORDER BY time ASC");
		$res = Q::$DB->q("SELECT calendar.id, calendar_items.time, calendar_items.status, calendar_items.ep, calendar.name, calendar.type, calendar.keywords, calendar.cover FROM calendar_items LEFT JOIN calendar ON calendar.id = calendar_items.follow WHERE time >= '$tod' AND time < '$tmr' $add ORDER BY time ASC");
		for($ret = []; $row = $res->fetch_assoc(); $ret[] = $row)
			;

		if(empty($ret) && ($type == "" || $type == 0 || $type == 1)){
		//if($type == "" || $type == 0 || $type == 1){
			// automatic update
			$date->modify('-8 day');
			$now = new DateTime();
			$interval = $now->diff($date);
			if($interval->invert){ // before today(now)
				$tod = $date->format('Y-m-d H:i:s');
				$date->modify('+1 day');
				$tmr = $date->format('Y-m-d H:i:s');
				$insert_rows = [];
				$res = Q::$DB->q("SELECT * FROM calendar_items WHERE status = 'air' AND time >= '$tod' AND time < '$tmr' ORDER BY time ASC");
				while($row = $res->fetch_assoc()){
					$time = new DateTime($row['time']);
					$time->modify('+1 Week');
					$ep = $row['ep'] + 1;
					$insert_rows[] = "('" .$time->format('Y-m-d H:i:s') ."', '$ep', '$row[status]', '$row[follow]')";
				}
				if(!empty($insert_rows)){
					Q::$DB->q("INSERT INTO calendar_items (time, ep, status, follow) VALUES " .implode(",", $insert_rows));
				}
			}
		}
		return $ret;
		// return false; //??
	}

	public function update($cur){
		$date = new DateTime($cur);
		$tod = $date->format('Y-m-d H:i:s');
		$date->modify('+1 day');
		$tmr = $date->format('Y-m-d H:i:s');
		$cur_ids = [];
		$res = Q::$DB->q("SELECT * FROM calendar_items WHERE (status = 'air' OR status = 'fin') AND time >= '$tod' AND time < '$tmr'");
		while($row = $res->fetch_assoc()){
			$cur_ids[] = $row['follow'];
		}
		
		$date->modify('-8 day');
		$tod = $date->format('Y-m-d H:i:s');
		$date->modify('+1 day');
		$tmr = $date->format('Y-m-d H:i:s');
		$insert_rows = [];
		$res = Q::$DB->q("SELECT * FROM calendar_items WHERE status = 'air' AND time >= '$tod' AND time < '$tmr' AND follow NOT IN (". implode(',', $cur_ids) .")");
		while($row = $res->fetch_assoc()){
			$time = new DateTime($row['time']);
			$time->modify('+1 Week');
			$ep = $row['ep'] + 1;
			$insert_rows[] = "('" .$time->format('Y-m-d H:i:s') ."', '$ep', '$row[status]', '$row[follow]')";
		}
		if(!empty($insert_rows)){
			Q::$DB->q("INSERT INTO calendar_items (time, ep, status, follow) VALUES " .implode(",", $insert_rows));
			return true;
		}
	}

	public function detail($id, $ep){
		if($this->u['class'] < $this->permission['detail'])
			return false;
		$id = 0 + intval($id);
		$ep = 0 + intval($ep);
		$res = Q::$DB->q("SELECT calendar.id, calendar_items.time, calendar_items.status, calendar_items.ep, calendar.name, calendar.type, calendar.keywords, calendar.cover FROM calendar_items LEFT JOIN calendar ON calendar.id = calendar_items.follow WHERE follow = '$id' AND ep = '$ep'");
		if($res->num_rows < 1)
			return false;
		else 
			return $res->fetch_assoc();
	}

	public function del($id, $ep = ""){
		$id = 0 + intval($id);
		if($ep == ""){
			$res = Q::$DB->q("SELECT id FROM calendar WHERE id = '$id'");
			if($res->num_rows < 1)
				return false;
			Q::$DB->q("DELETE FROM calendar_items WHERE follow = '$id'");
			Q::$DB->q("DELETE FROM calendar WHERE id = '$id'");
		} else {
			$ep = 0 + intval($ep);
			Q::$DB->q("DELETE FROM calendar_items WHERE follow = '$id' AND ep = '$ep'");
		}
		return true;
	}

	public function edit($id, $ep, $field, $data){
		$id = 0 + intval($id);
		if($ep == ""){
			$res = Q::$DB->q("SELECT * FROM calendar WHERE id = '$id'");
			if($res->num_rows < 1)
				return false;
			$update = "";
			switch($field){
			case 'name': $update = "name = ".Q::$DB->esc($data); break;
			case 'type': $update = "type = '". (0 + intval($data)) ."'"; break;
			case 'keywords': $update = "keywords = ".Q::$DB->esc($data); break;
			case 'cover': $update = "cover = ".Q::$DB->esc($data); break;
			case 'note': $update = "note = ".Q::$DB->esc($data); break;
			default: return false;
			}
			Q::$DB->q("UPDATE calendar SET $update WHERE id = '$id'");
			//print($update);
			//print($id);
			//print("UPDATE calendar SET $update WHERE id = '$id'");
		} else {
			$ep = 0 + intval($ep);
			$res = Q::$DB->q("SELECT id FROM calendar_items WHERE follow = '$id' AND ep = '$ep'");
			if($res->num_rows < 1)
				return false;
			$row = $res->fetch_row();
			$iid = $row[0];
			$update = "";
			switch($field){
			case 'ep': $update = "ep = '" .(0 + intval($data)) ."'"; break;
			case 'follow': $update = "follow = '" .(0 + intval($data)) ."'"; break;
			case 'status': $update = "status = " .Q::$DB->esc($data); break;
			case 'tid': $update = "tid = '" .(0 + intval($data)) ."'"; break;
			case 'time': $update = "time = " .Q::$DB->esc($data); break;
			default: return false;
			}
			Q::$DB->q("UPDATE calendar_items SET $update WHERE id = '$iid'");
		}
		//print(Q::$DB->affected_rows);
		if(Q::$DB->affected_rows < 1)
			return false;
		else 
			return true;
	}

	public function add($name, $keywords, $cover = "", $time = "", $type = 0, $ep = 1, $st = "air"){
		$type = intval($type);
		$ep = intval($ep) > 0 ? intval($ep) : 1;
		if($time == "")
			$time = date('Y-m-d');
		
		Q::$DB->q("INSERT INTO calendar (name, type, keywords, cover) VALUES("
			      .Q::$DB->esc($name) .", '$type', " .Q::$DB->esc($keywords) .", " .Q::$DB->esc($cover) .")");
		$id = Q::$DB->insert_id;
		if($id < 1)
			return false;

		switch($type){
		case '0':
		case '1': $st = $st ? $st : 'air'; break;
		case '2': $st = $st ? $st : 'movie'; break;
		default: $st = 'others';
		}
		Q::$DB->q("INSERT INTO calendar_items (follow, time, ep, status) VALUES('$id', " .Q::$DB->esc($time) .", '$ep', " .Q::$DB->esc($st) .")");
		if(Q::$DB->insert_id > 0)
			return true;
		else
			return false;
	}
}
?>

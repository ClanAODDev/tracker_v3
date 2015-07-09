<?php

class Squad extends Application {
	
	public $id;
	public $platoon_id;
	public $leader_id;
	public $game_id;

	static $table = 'squad';
	static $id_field = 'id';

	public static function findAll($game_id, $platoon_id = false) {

		if ($platoon_id) {
			$conditions = array('game_id' => $game_id, 'platoon_id' => $platoon_id);
		} else {
			$conditions = array('game_id' => $game_id);
		}

		return arrayToObject(Flight::aod()->from('squad')->where($conditions)->SortAsc('platoon_id')->many());
		
	}

	public static function findByPlatoonId($platoon_id) {
		return self::find_each(array('platoon_id' => $platoon_id));
	}

	public static function members($squad_id) {
		// finds active, LOAs, and pending members
		$sql = "SELECT * FROM member WHERE squad_id = {$squad_id} AND (status_id = 1 OR status_id = 3 OR status_id = 999)";
		$sql .= " ORDER BY member.rank_id DESC, member.join_date DESC";
		return arrayToObject(Flight::aod()->sql($sql)->many());
	}

	public static function create($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->save($params);
	}

	public static function modify($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->update($params);
	}





	// deprecated functions
	// need to refactor

	public static function find($id, $division_structure_ordering = false) {
		$sql = "SELECT member.id, member.forum_name, member.member_id, member.last_activity, member.battlelog_name, member.forum_posts, member.join_date, member.rank_id, rank.abbr as rank FROM `member` LEFT JOIN `rank` on member.rank_id = rank.id WHERE member.squad_id = {$id} AND (member.status_id = 1 OR member.status_id = 999) AND member.position_id = 6";

		if ($division_structure_ordering) {
			$sql .= " ORDER BY member.rank_id DESC, member.join_date DESC ";
		} else {
			$sql .= " ORDER BY member.last_activity ASC ";
		}

		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function count($id) {
		$sql = "SELECT count(*) as count FROM `member` WHERE member.squad_id = {$id} AND (member.status_id = 1 OR member.status_id = 3 OR member.status_id = 999) AND member.position_id = 6";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}


}
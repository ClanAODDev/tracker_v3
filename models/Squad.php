<?php

class Squad extends Application {
	
	public $id;
	public $platoon_id;
	public $leader_id;

	static $table = 'squad';
	static $id_field = 'id';

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

	public static function find($mid, $division_structure_ordering = false) {
		$sql = "SELECT member.id, member.forum_name, member.member_id, member.last_activity, member.battlelog_name, member.forum_posts, member.join_date, member.rank_id, rank.abbr as rank FROM `member` LEFT JOIN `rank` on member.rank_id = rank.id WHERE member.squad_leader_id = {$mid} AND (member.status_id = 1 OR member.status_id = 999) AND member.position_id = 6";

		if ($division_structure_ordering) {
			$sql .= " ORDER BY member.rank_id DESC, member.join_date DESC ";
		} else {
			$sql .= " ORDER BY member.last_activity ASC ";
		}

		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function count($mid) {
		$sql = "SELECT count(*) as count FROM `member` WHERE member.squad_leader_id = {$mid} AND (member.status_id = 1 OR member.status_id = 999) AND member.position_id = 6";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

}
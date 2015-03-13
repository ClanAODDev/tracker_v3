<?php

class Platoon extends Division {

	public $id;
	public $number;
	public $name;
	public $game_id;
	public $leader_id;

	static $id_field = "id";
	static $table = "platoon";

	public static function find_all($gid) {
		$sql = "SELECT platoon.id, platoon.number, platoon.name, platoon.leader_id, member.forum_name, rank.abbr FROM platoon LEFT JOIN member on platoon.leader_id = member.member_id LEFT JOIN rank on member.rank_id = rank.id WHERE platoon.game_id = {$gid} ORDER BY number";
		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function findById($id) {
		$sql = "SELECT platoon.id, platoon.number, platoon.name, platoon.leader_id, member.forum_name, rank.abbr FROM platoon LEFT JOIN member on platoon.leader_id = member.member_id LEFT JOIN rank on member.rank_id = rank.id WHERE platoon.id = {$id} ORDER BY number";
		$params = Flight::aod()->sql($sql)->one();
		return arrayToObject($params);
	}

	public static function findLeader($leader_id) {
		$params = Member::findById($leader_id);
		return arrayToObject($params);
	}
}
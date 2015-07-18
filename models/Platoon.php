<?php

class Platoon extends Application {

	public $id;
	public $number;
	public $name;
	public $game_id;
	public $leader_id;

	static $id_field = "id";
	static $table = "platoon";

	public static function find_all($game_id) {
		$params = self::find_each(array('game_id' => $game_id));
		return arrayToObject($params);
	}

	public static function countPlatoons() {
		return self::count_all();
	}

	public static function findById($platoon_id) {
		$sql = "SELECT platoon.id, platoon.number, platoon.name, platoon.leader_id, member.forum_name, rank.abbr FROM platoon LEFT JOIN member on platoon.leader_id = member.member_id LEFT JOIN rank on member.rank_id = rank.id WHERE platoon.id = {$platoon_id}";
		$params = Flight::aod()->sql($sql)->one();
		return arrayToObject($params);
	}

	public static function Leader($leader_id) {
		$params = Member::findById($leader_id);
		return arrayToObject($params);
	}

	public static function SquadLeaders($game_id, $platoon_id = false, $order_by_rank = false) {
		$sql = "SELECT member.id, last_activity, rank.abbr, member_id, forum_name, platoon.name as platoon_name, member.battlelog_name FROM member LEFT JOIN platoon ON platoon.id = member.platoon_id LEFT JOIN rank ON rank.id = member.rank_id WHERE member.position_id = 5 AND member.game_id = {$game_id} AND member.id NOT IN (SELECT leader_id FROM squad)";

		if ($platoon_id) {
			$sql .= " AND platoon_id = {$platoon_id} ";
		}

		if ($order_by_rank) {
			$sql .= " ORDER BY member.rank_id DESC, member.forum_name ASC ";
		} else {
			$sql .= " ORDER BY platoon.id, forum_name";
		}

		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function members($platoon_id) {
		$conditions = array('platoon_id' => $platoon_id, 'status_id @' => array(1, 3, 999));
		$params = Flight::aod()->from('member')
		->join('position', array('position.id' => 'member.position_id'))
		->sortAsc('position.sort_order')
		->where($conditions)
		->select()->many();
		return $params;
	}

	public static function gameStats($platoon_id, $bdate, $edate) {
		$members = self::memberIdsList($platoon_id);
		$total = Activity::findTotalGamesByArray($members, $bdate, $edate);
		$AOD = Activity::findTotalAODGamesByArray($members, $bdate, $edate);
		return array(
			'pct' => round(array_sum($AOD) / array_sum($total)*100),
			'total' => array_sum($total), 
			'AOD' => array_sum($AOD)
			);
	}

	public static function forumActivity($platoon_id) {
		$conditions = "status_id IN (1,3,999) AND platoon_id = {$platoon_id}";		
		$underTwoWeeks = Flight::aod()->sql('SELECT count(*) as count FROM member WHERE '.$conditions.' AND last_activity BETWEEN DATE_ADD(CURDATE(), INTERVAL -2 WEEK) AND CURDATE();')->one();
		$twoWeeksMonth = Flight::aod()->sql('SELECT count(*) as count FROM member WHERE '.$conditions.' AND last_activity BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -2 WEEK);')->one();
		$oneMonth = Flight::aod()->sql('SELECT count(*) as count FROM member WHERE '.$conditions.' AND last_activity < DATE_ADD(CURDATE(), INTERVAL 30 DAY)')->one();
		$data = new stdClass();
		$data->underTwoWeeks = $underTwoWeeks['count'];
		$data->twoWeeksMonth = $twoWeeksMonth['count'];
		$data->oneMonth = $oneMonth['count'];
		return $data;
	}

	public static function unassignedMembers($platoon_id) {
		$conditions = array('platoon_id' => $platoon_id, 'status_id @' => array(1, 3, 999), 'squad_id' => 0, 'position_id @' => array(6,7,0));
		return arrayToObject(Flight::aod()->from('member')->where($conditions)->SortDesc('rank_id')->many());
	}

	public static function countSquadLeaders($platoon_id) {
		$sql = "SELECT count(*) as count FROM member WHERE position_id = 5 AND platoon_id = {$platoon_id}";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function countSquadMembers($platoon_id) {
		$sql = "SELECT count(*) as count FROM member WHERE position_id = 6 AND platoon_id = {$platoon_id}";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function countGeneralPop($platoon_id) {
		$sql = "SELECT count(*) as count FROM member WHERE member.position_id = 7 AND (status_id = 1 OR status_id = 999) AND platoon_id = {$platoon_id}";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function countPlatoon($platoon_id) {
		return count(self::members($platoon_id));
	}

	public static function getIdFromNumber($platoon_number, $division) {
		$sql = "SELECT id FROM platoon WHERE number = {$platoon_number} AND game_id = {$division}";
		$params = Flight::aod()->sql($sql)->one(); 
		return $params['id'];
	}

	public static function get_number_from_id($platoon_id) 	{
		$sql = "SELECT number FROM platoon WHERE id = {$platoon_id}";
		$params = Flight::aod()->sql($sql)->one();
		return $params['number'];
	}

	public static function memberIdsList($platoon_id) {
		$sql = "SELECT member_id FROM member WHERE platoon_id = {$platoon_id} AND status_id IN (1, 999)";
		$params = Flight::aod()->sql($sql)->many();
		if (count($params)) {
			foreach ($params as $member) { $memberIds[] = intval($member['member_id']); }
			return $memberIds;	
		} else {
			return false;
		}		
	}

	public static function modify($params) {
		$platoon = new self();
		foreach ($params as $key=>$value) {
			$platoon->$key = $value;
		}
		$platoon->update($params);
	}
}
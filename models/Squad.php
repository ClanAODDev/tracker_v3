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

	public static function mySquadId($leader_id) {
		$params = Flight::aod()->from('squad')->where(array('leader_id' => $leader_id))->one();
		if (isset($params['id'])) {
			return $params['id'];
		} else {
			return false;
		}
	}

	public static function findSquadMembers($squad_id, $div_struc_sort = false, $recruiter = false) {
		$conditions = ($recruiter) ? array('squad_id' => $squad_id, 'position_id' => 6, 'recruiter !%' => $recruiter, 'status_id @' => array(1, 3, 999)) : array('squad_id' => $squad_id, 'position_id' => 6, 'status_id @' => array(1, 3, 999));

		if ($div_struc_sort) {
			return Flight::aod()->from('member')->where($conditions)->sortDesc('rank_id')->many();
		} else {
			return Flight::aod()->from('member')->where($conditions)->sortAsc('last_activity')->many();
		}

	}

	public static function countSquadMembers($squad_id) {
		return count(self::findSquadMembers($squad_id));
	}

}
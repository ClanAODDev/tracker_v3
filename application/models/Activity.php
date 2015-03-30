<?php 

class Activity extends Application {
	
	public $id;
	public $member_id;
	public $server;
	public $datetime;
	public $hash;

	static $table = 'activity';
	static $id_field = 'member_id';
	static $name_field = 'server';

	public static function findPlayerGames($member_id, $bdate, $edate) {
		$sql = "SELECT count(*) as count FROM activity WHERE member_id = {$member_id} AND datetime between '{$bdate}' AND '{$edate}'";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function findPlayerAODGames($member_id, $bdate, $edate) {
		$sql = "SELECT count(*) as count FROM activity WHERE member_id = {$member_id} AND server LIKE '%aod%' AND datetime between '{$bdate}' AND '{$edate}'";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function findTotalGamesByArray($params, $bdate, $edate) {
		foreach ($params as $member) {
			$sql = "SELECT count(*) as count FROM activity WHERE member_id = {$member} AND datetime between '{$bdate}' AND '{$edate}'";
			$params = Flight::aod()->sql($sql)->one();
			foreach ($params as $count) {
				$games[] = $count;
			}
		}
		return $games;
	}

	public static function findTotalAODGamesByArray($params, $bdate, $edate) {
		foreach ($params as $member) {
			$sql = "SELECT count(*) as count FROM activity WHERE member_id = {$member} AND server LIKE '%aod%' AND datetime between '{$bdate}' AND '{$edate}'";
			$params = Flight::aod()->sql($sql)->one();
			foreach ($params as $count) {
				$games[] = $count;
			}
		}
		return $games;		
	}

/*	public static function findPlatoonGames($params) {
		foreach($params as $player) {

		}*/

	}
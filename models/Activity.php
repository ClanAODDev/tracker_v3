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

	public static function topList30DaysByDivision($game_id) {
		$sql = "SELECT forum_name, platoon.number as plt, member_id, ( SELECT count(*) FROM activity WHERE activity.member_id = member.member_id AND activity.server LIKE 'AOD%' AND activity.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 30 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM member LEFT JOIN platoon ON member.platoon_id = platoon.id WHERE member.game_id = {$game_id} ORDER BY aod_games DESC LIMIT 10";
		return arrayToObject(Flight::aod()->sql($sql)->many());

	}

	public static function topListTodayByDivision($game_id) {
		$sql = "SELECT forum_name, platoon.number as plt, member_id, ( SELECT count(*) FROM activity WHERE activity.member_id = member.member_id AND activity.server LIKE 'AOD%' AND activity.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 1 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM member LEFT JOIN platoon ON member.platoon_id = platoon.id WHERE member.game_id = {$game_id} ORDER BY aod_games DESC LIMIT 10";
		return arrayToObject(Flight::aod()->sql($sql)->many());
	}

	public static function cleanUpActivity() {
		Flight::aod()->sql("DELETE FROM activity WHERE datetime < (NOW() - INTERVAL 90 DAY)");
	}

}
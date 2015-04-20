<?php 

class Activity extends Application {
	
	public $id;
	public $member_id;
	public $server;
	public $datetime;
	public $hash;
	public $game_id;
	public $map_name;
	public $report_id;

	static $table = 'activity';
	static $id_field = 'id';
	static $name_field = 'server';

	public static function findAllGames($member_id, $limit=MAX_GAMES_ON_PROFILE) {
		$sql = "SELECT * FROM activity WHERE member_id = {$member_id} ORDER BY datetime DESC LIMIT {$limit}";
		return Flight::aod()->sql($sql)->many();
	}

	public static function countPlayerGames($member_id, $bdate, $edate) {
		$sql = "SELECT count(*) as count FROM activity WHERE member_id = {$member_id} AND datetime between '{$bdate}' AND '{$edate}'";
		$params = Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function newActivity($reports, $game, $member_id, $id) {
		foreach ($reports as $report) {
			$activity = new self();
			$activity->member_id = $member_id;
			$activity->server = $report->serverName;
			$activity->datetime = $report->date;
			$activity->map_name = $report->map;
			$activity->hash = hash("sha256", $member_id.$report->date);
			$activity->game_id = $game;
			$activity->report_id = $report->reportId;
			Flight::aod()->save($activity);
		}
	}

	public static function countPlayerAODGames($member_id, $bdate, $edate) {
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
		$sql = "SELECT forum_name, rank.abbr as rank, platoon.number as plt, member_id, ( SELECT count(*) FROM activity WHERE activity.member_id = member.member_id AND activity.server LIKE 'AOD%' AND activity.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 30 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM member LEFT JOIN rank ON rank.id = member.rank_id LEFT JOIN platoon ON member.platoon_id = platoon.id WHERE member.game_id = {$game_id} AND (status_id = 1 OR status_id = 999) ORDER BY aod_games DESC LIMIT 10";
		return arrayToObject(Flight::aod()->sql($sql)->many());

	}

	public static function topListTodayByDivision($game_id) {
		$sql = "SELECT forum_name, rank.abbr as rank, platoon.number as plt, member_id, ( SELECT count(*) FROM activity WHERE activity.member_id = member.member_id AND activity.server LIKE 'AOD%' AND activity.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 1 day) AND CURRENT_TIMESTAMP ) AS aod_games FROM member LEFT JOIN rank ON rank.id = member.rank_id LEFT JOIN platoon ON member.platoon_id = platoon.id WHERE member.game_id = {$game_id} AND (status_id = 1 OR status_id = 999) ORDER BY aod_games DESC LIMIT 10";
		return arrayToObject(Flight::aod()->sql($sql)->many());
	}

	public static function toplistMonthlyAODTotal() {
		$sql = "SELECT round((SELECT count(*) FROM activity WHERE (server LIKE '%AOD%') AND activity.datetime BETWEEN DATE_SUB(NOW(), INTERVAL 30 day) AND CURRENT_TIMESTAMP) / count(*)*100, 1) as pct FROM activity WHERE activity.datetime BETWEEN DATE_SUB( NOW(), INTERVAL 30 day ) AND CURRENT_TIMESTAMP";
		$params = Flight::aod()->sql($sql)->one();
		return $params['pct'];
	}

	public static function cleanUpActivity() {
		Flight::aod()->sql("DELETE FROM activity WHERE datetime < (NOW() - INTERVAL 90 DAY)");
	}

}
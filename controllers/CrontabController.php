<?php

/**
 * Crontab handler
 *
 * crontab counters:
 * bf4_next_player
 * bfh_next_player
 * battlelog_next_player
 */
class CrontabController {

	/**
	 * Update BF4 server history for players that own, play BF4
	 * @return null
	 */
	public static function _doBf4Update() {
		$next_player = Crontab::find("bf4_next_player")->value;
		$sql = "SELECT member_id, battlelog_id FROM member WHERE id = {$next_player} AND status_id = 1";
		$params = Flight::aod()->sql($sql)->one();
		if (empty($params)) {
			Crontab::modify(array("id" => 1, "value" => $next_player+1));
			self::_doBf4Update();
		} else {
			$reports = Member::parse_battlelog_reports($params['battlelog_id'], 'bf4');
			Activity::newActivity(arrayToObject($reports), "bf4", $params['member_id'], $next_player);
			Crontab::modify(array("id" => 1, "value" => $next_player+1));
		}
	}

	/**
	 * Update BFH server history for players that own, play BFH
	 * @return null 
	 */
	public static function _doBfhUpdate() {
		$next_player = Crontab::find("bfh_next_player")->value;
		$sql = "SELECT member_id, battlelog_id FROM member WHERE id = {$next_player} AND status_id = 1";
		$params = Flight::aod()->sql($sql)->one();
		if (empty($params)) {
			Crontab::modify(array("id" => 2, "value" => $next_player+1));
			self::_doBfhUpdate();
		} else {
			$reports = Member::parse_battlelog_reports($params['battlelog_id'], 'bfh');
			Activity::newActivity(arrayToObject($reports), "bfh", $params['member_id'], $next_player);
			Crontab::modify(array("id" => 2, "value" => $next_player+1));
		}
	}

	/**
	 * Update battlelog ID if none exists
	 * This is necessary for activity tracking
	 * @return string If a player cannot be found, an error log will generate
	 */
	public static function _doBattlelogIdUpdate() {
		$members = array();
		$battlelog_names = objectToArray(Member::find(array('status_id' => 1, 'battlelog_name !%' => 0, 'battlelog_id' => 0)));
		$countNames = count($battlelog_names);
		echo "Fetched battlelog names. ({$countNames})<br /><br />";
		foreach ($battlelog_names as $row) {
			$battlelog_id = Member::getBattlelogId($row['battlelog_name']);
			if (!$battlelog_id['error']) {
				$sql = "UPDATE member SET battlelog_id = {$battlelog_id['id']} WHERE battlelog_name = '{$row['battlelog_name']}'";
				Flight::aod()->sql($sql)->one();
				echo "Added ID {$battlelog_id['id']} to {$row['battlelog_name']}<br />";
			} else {
				echo "ERROR: {$row['battlelog_name']} - {$battlelog_id['message']}<br />";
			}
		}
		echo "done syncing battlelog ids.";
	}

	public static function _doArchUpdate($game) {
		echo(ArchUpdater::run($game));
	}



}
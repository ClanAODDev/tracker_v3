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
			echo "No member with this ID: {$next_player}\n";
		} else {
			var_dump(Member::parse_battlelog_reports($params['battlelog_id'], 'bf4'));
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
			echo "No member with this ID: {$next_player}\n";
		} else {
			var_dump(Member::parse_battlelog_reports($params['battlelog_id'], 'bfh'));
		}

	}

	/**
	 * Update battlelog ID if none exists
	 * This is necessary for activity tracking
	 * @return string If a player cannot be found, an error log will generate
	 */
	public static function _doBattlelogIdUpdate() {

		$next_player = Crontab::find("battlelog_next_player")->value;
	}
	
}
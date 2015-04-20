<?php

require 'lib.php';

global $pdo;

if (dbConnect()) {

	try {

		$next_player = $pdo->query("SELECT value FROM crontab WHERE name = 'bf4_next_player'")->fetch(); 
		$last_player = $pdo->query("SELECT max(id) as value FROM member")->fetch();

		if ($next_player['value'] > $last_player['value']) {
			$next_player['value'] = 1;
		}

		$params = $pdo->query("SELECT member_id, battlelog_id FROM member WHERE id = {$next_player['value']} AND status_id = 1")->fetch(); 

		if (empty($params)) {

			$pdo->prepare("UPDATE crontab SET value = {$next_player['value']}+1 WHERE name = 'bf4_next_player'")->execute();

		} else {

			$reports = parse_battlelog_reports($params['battlelog_id'], 'bf4');
			newActivity($reports, "bf4", $params['member_id'], $next_player['value']);
			$pdo->prepare("UPDATE crontab SET value = {$next_player['value']}+1 WHERE name = 'bf4_next_player'")->execute(); 

		}

	} catch (PDOException $e) {

		echo $e->getMessage();

	}

}
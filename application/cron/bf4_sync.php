<?php

require 'lib.php';

global $pdo;

if (dbConnect()) {

	try {

		$next_player = $pdo->query("SELECT value FROM crontab WHERE name = 'bf4_next_player'")->fetch(); 
		$limit = $pdo->query("SELECT max(id) as max, min(id) as min FROM member WHERE game_id = 2 AND status_id = 1")->fetch();

		if ($next_player['value'] > $limit['max']) {
			$next_player = $limit['min'];
		} else {
			$next_player = $next_player['value'];
		}

		$params = $pdo->query("SELECT member_id, battlelog_id FROM member WHERE id = {$next_player} AND status_id = 1 AND game_id = 2")->fetch(); 

		if (empty($params)) {

			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = 'bf4_next_player'")->execute();

		} else {

			$reports = parse_battlelog_reports($params['battlelog_id'], 'bf4');
			newActivity($reports, "bf4", $params['member_id'], $next_player);
			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = 'bf4_next_player'")->execute(); 

		}

	} catch (PDOException $e) {

		echo $e->getMessage();

	}

}
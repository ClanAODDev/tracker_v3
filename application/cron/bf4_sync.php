<?php

require 'lib.php';

global $pdo;

if (dbConnect()) {

	try {

		// fetch next player in queue
		$next_player = $pdo->query("SELECT value FROM crontab WHERE name = 'bf4_next_player'")->fetch(); 

		// determine player range
		$limit = $pdo->query("SELECT max(id) as max, min(id) as min FROM member WHERE game_id = 2 AND status_id = 1")->fetch();

		if ($next_player['value'] > $limit['max']) {
			$next_player = $limit['min'];
		} else {
			$next_player = $next_player['value'];
		}

		// fetch battlelog persona id
		$params = $pdo->query("SELECT m.member_id, h.handle_account_id FROM member m INNER JOIN member_handles h ON h.member_id = m.id WHERE m.id = {$next_player} AND m.status_id = 1 AND m.game_id = 2 AND h.handle_type = 2")->fetch(); 

		if (empty($params)) {

			// no member exists... move on
			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = 'bf4_next_player'")->execute();

		} else {

			// fetch battlelog data
			$reports = parse_battlelog_reports($params['handle_account_id'], 'bf4');
			newActivity($reports, "bf4", $params['member_id'], $next_player);
			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = 'bf4_next_player'")->execute(); 

		}

	} catch (PDOException $e) {

		echo $e->getMessage();

	}

}
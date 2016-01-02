<?php

require 'lib.php';

$cron_column = "tanks_na_next_player";

global $pdo;

if (dbConnect()) {

	try {

		// fetch next player in queue
		$next_player = $pdo->query("SELECT value FROM crontab WHERE name = '{$cron_column}'")->fetch(); 

		// determine player range
		$limit = $pdo->query("SELECT max(member_id) as max, min(member_id) as min FROM member_handles WHERE handle_type = 7")->fetch();

		if ($next_player['value'] > $limit['max']) {
			$next_player = $limit['min'];
		} else {
			$next_player = $next_player['value'];
		}

		// fetch battlelog persona id
		$params = $pdo->query("SELECT m.id, h.handle_value FROM member m INNER JOIN member_handles h ON h.member_id = m.id WHERE m.id = {$next_player} AND m.status_id = 1 AND m.game_id = 3 AND h.handle_type = 7")->fetch(); 

		if (empty($params)) {

			// no member exists... move on
			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = '{$cron_column}'")->execute();

		} else {

			$profile = download_tanks_profile($params['handle_value'], 7);

			// fetch tanks data
			$data = new stdClass();
			$data->member_id = $params['id'];
			$data->last_battle_time = $profile->last_battle_time;

			// parse
			parse_tanks_profile($data);

			// queue next player
			$pdo->prepare("UPDATE crontab SET value = {$next_player}+1 WHERE name = '{$cron_column}'")->execute(); 

		}

	} catch (PDOException $e) {
		echo $e->getMessage();	
	}

}

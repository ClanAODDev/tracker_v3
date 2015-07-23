<?php

require 'lib.php';

$members = array();

if (dbConnect()) {
	$query = $pdo->prepare(" SELECT handle_value FROM member_handles WHERE handle_account_id = '0' AND handle_type = '2' ");
	try {
		$query->execute();
		$battlelog_names = $query->fetchAll();
		$countNames = count($battlelog_names);
		echo date("Y-m-d H:i:s") . " - Fetched battlelog names. ({$countNames})\r\n";


		foreach ($battlelog_names as $row) {

			$battlelog_id = getBattlelogId($row['handle_value']);
			$query = $pdo->prepare("UPDATE member_handles SET handle_account_id = :battlelog_id WHERE handle_value = :battlelog_name");

			if (!$battlelog_id['error']) {
				try {
					$query->bindParam(':battlelog_id', $battlelog_id['id']);
					$query->bindParam(':battlelog_name', $row['handle_value']);
					$query->execute();
					echo "Added ID {$battlelog_id['id']} to {$row['handle_value']}\r\n";

					$pdo->prepare("UPDATE crontab SET last_updated = '" . date('Y-m-d H:i:s') . "' WHERE name = 'battlelog_sync'")->execute();
				} catch (PDOException $e) {
					echo "ERROR: " . $e->getMessage();			
				}
			} else {
				echo "ERROR: {$row['handle_value']} - {$battlelog_id['message']}\r\n";
			}
		}
		echo date("Y-m-d H:i:s") . " - done syncing battlelog ids.\r\n\r\n\r\n";


	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}
}
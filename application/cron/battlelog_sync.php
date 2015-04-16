<?php

require 'lib.php';

$members = array();

if (dbConnect()) {
	$query = $pdo->prepare(" SELECT battlelog_name FROM member WHERE status_id = 1 AND battlelog_name != '0' AND battlelog_id = '0' ");
	try {
		$query->execute();
		$battlelog_names = $query->fetchAll();
		$countNames = count($battlelog_names);
		echo date("Y-m-d H:i:s") . " - Fetched battlelog names. ({$countNames})\r\n";


		foreach ($battlelog_names as $row) {

			$battlelog_id = getBattlelogId($row['battlelog_name']);
			$query = $pdo->prepare("UPDATE member SET battlelog_id = :battlelog_id WHERE battlelog_name = :battlelog_name");

			if (!$battlelog_id['error']) {
				try {
					$query->bindParam(':battlelog_id', $battlelog_id['id']);
					$query->bindParam(':battlelog_name', $row['battlelog_name']);
					$query->execute();
					echo "Added ID {$battlelog_id['id']} to {$row['battlelog_name']}\r\n";
				} catch (PDOException $e) {
					echo "ERROR: " . $e->getMessage();			
				}
			} else {
				echo "ERROR: {$row['battlelog_name']} - {$battlelog_id['message']}\r\n";
			}
		}
		echo date("Y-m-d H:i:s") . " - done syncing battlelog ids.\r\n\r\n\r\n";


	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}
}
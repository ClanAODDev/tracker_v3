<?php

require 'lib.php';

$members = array();

if (dbConnect()) {
	$query = $pdo->prepare(" SELECT battlelog_name FROM member WHERE status_id = 1 AND battlelog_name != '0' AND battlelog_id = '0' ");
	try {
		$query->execute();
		$battlelog_names = $query->fetchAll();
		$countNames = count($battlelog_names);
		echo "Fetched battlelog names. ({$countNames})<br /><br />";

	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}

	foreach ($battlelog_names as $row) {

		$battlelog_id = get_battlelog_id($row['battlelog_name']);
		$query = $pdo->prepare("UPDATE member SET battlelog_id = :battlelog_id WHERE battlelog_name = :battlelog_name");

		if (!$battlelog_id['error']) {
			try {
				$query->bindParam(':battlelog_id', $battlelog_id['id']);
				$query->bindParam(':battlelog_name', $row['battlelog_name']);
				$query->execute();
				echo "Added ID {$battlelog_id['id']} to {$row['battlelog_name']}<br />";
			} catch (PDOException $e) {
				echo "ERROR: " . $e->getMessage();			
			}
		} else {
			echo "ERROR: {$row['battlelog_name']} - {$battlelog_id['message']}<br />";
		}
	}
	echo "done syncing battlelog ids.";
}
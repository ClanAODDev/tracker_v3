<?php

/**
 * cleanup cron
 */

require 'lib.php';

if (dbConnect()) {

	try {

		// removes flags for members who have already been processed out
		$pdo->prepare("DELETE FROM inactive_flagged WHERE member_id IN (SELECT member_id FROM member WHERE status_id = 4)")->execute();

		// cleans up activity older than 90 days
		$pdo->prepare("DELETE FROM activity WHERE datetime < (NOW() - INTERVAL 90 DAY)")->execute();

		// cleans up activity belonging to an ex-member
		$pdo->prepare("DELETE FROM activity WHERE member_id IN (SELECT member_id FROM member WHERE status_id = 4)")->execute();

	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}

}
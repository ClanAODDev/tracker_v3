<?php

/**
 * cleanup cron
 */

require 'lib.php';

if (dbConnect()) {

	try {

		// removes flags for members who have already been processed out
		$pdo->prepare("DELETE FROM inactive_flagged WHERE member_id IN (SELECT member_id FROM member WHERE status_id = 4)")->execute();

		// cleans up flagged members who have posted since being flagged (for inactivty)
		$pdo->prepare("DELETE FROM inactive_flagged WHERE inactive_flagged.member_id IN (SELECT member_id FROM member WHERE member.last_activity BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW())")->execute();

		// cleans up activity older than 90 days
		$pdo->prepare("DELETE FROM activity WHERE datetime < (NOW() - INTERVAL 90 DAY)")->execute();

		// cleans up activity belonging to an ex-member
		$pdo->prepare("DELETE FROM activity WHERE member_id IN (SELECT member_id FROM member WHERE status_id = 4)")->execute();

		// cleans up member games, deleting entries for non-members
		$pdo->prepare("DELETE FROM member_games WHERE member_id NOT IN (SELECT member.id FROM member WHERE status_id IN (1,999,3))")->execute();

	} catch (PDOException $e) {
		echo "ERROR: " . $e->getMessage();			
	}

}
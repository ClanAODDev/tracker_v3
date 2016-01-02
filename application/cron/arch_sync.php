<?php

require 'lib.php';

$divisions = getDivisions();

if (count($divisions)) { 
	foreach($divisions as $division) {

		$cred = ARCH_PASS;
		$cur_minute = floor(time()/60)*60;
		$auth_code = md5($cur_minute . $cred);

		$json_url = "http://www.clanaod.net/forums/aodinfo.php?type=json&division={$division['full_name']}&authcode={$auth_code}";
		$agent = "AOD Division Tracking Tool";

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_URL, $json_url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$postResult = curl_exec($ch);
		$json = json_decode($postResult);

		// 11 values in column sort as of 12/17/2014
		// 0 "userid",
		// 1 "username",
		// 2 "joindate",
		// 3 "lastvisit",
		// 4 "lastactivity",
		// 5 "lastpost",
		// 6 "postcount",
		// 7 "aodrank",
		// 8 "aodrankval",
		// 9 "aoddivision",
		// 10 "aodstatus"

		if (count($json->column_order) == 11 && ($json->column_order[0] == 'userid') && ($json->column_order[10] == 'aodstatus')) {

			$currentMembers = array();

			// loop through member records
			foreach ($json->data as $column) {

				$memberid = $column[0];
				$username = str_replace('AOD_', '', $column[1]);
				$joindate = $column[2];
				$lastvisit = $column[3];
				$lastactive = $column[4];
				$lastpost = $column[5];
				$postcount = $column[6];
				$aodrankval = $column[8]-2; 
				$aoddivision = $division['id'];
				$aodstatus = convertStatus($column[10]);

				global $pdo;
				$currentMembers[$username] = $memberid;

				if (dbConnect()) {

					$query = $pdo->prepare(
						"INSERT INTO member (forum_name, member_id, rank_id, status_id, game_id, join_date, last_forum_login, last_forum_post, forum_posts, last_activity)
						VALUES (:username, :memberid, :rank, :status, :division, :joindate, :last_visit, :last_post, :forum_posts, :last_active)
						ON DUPLICATE KEY UPDATE
						forum_name=:username, 
						rank_id=:rank,
						join_date=:joindate,
						status_id=:status, 
						game_id=:division,
						last_forum_login=:last_visit,
						last_activity=:last_active,
						last_forum_post=:last_post, 
						forum_posts=:forum_posts"
						);

					try {
						$query->execute(
							array(
								':username' => $username,
								':memberid' => $memberid,
								':rank' => $aodrankval,
								':status' => $aodstatus,
								':division' => $aoddivision,
								':joindate' => $joindate,
								':last_visit' => $lastvisit,
								':last_active' => $lastactive,
								':last_post' => $lastpost,
								':forum_posts' => $postcount
								)
							); 

					} catch (PDOException $e) {
						echo "ERROR: " . $e->getMessage();			
					}
				}

			}

			// fetch all existing db members for array comparison
			$query = $pdo->prepare("SELECT member_id, forum_name FROM member WHERE status_id = 1 AND game_id = :gid");
			$query->execute(array(':gid' => intval($division["id"])));
			$existingMemberArray = $query->fetchAll();
			$existingMembers = array();

			foreach($existingMemberArray as $member) {
				$existingMembers[$member['forum_name']] = $member['member_id'];
			}

			// select members that need to be removed
			$removals = array_diff($existingMembers, $currentMembers);

			if (count($removals)) {
				$removalIds = implode($removals, ", ");

				try {
					$query = $pdo->prepare("UPDATE member SET status_id = 4 WHERE member_id IN ({$removalIds}) AND game_id = :gid");
					$query->execute(array(':gid' => intval($division["id"])));
				} catch (PDOException $e) {
					echo "ERROR: " . $e->getMessage();			
				}

				echo date('Y-m-d h:i:s A') . " - Updated the following member ids to 'removed': " . $removalIds . "\r\n";
			}

			echo date('Y-m-d h:i:s A') . " - {$division['full_name']} sync done. \r\n";

			try {
				$pdo->prepare("UPDATE crontab SET last_updated = '" . date('Y-m-d H:i:s') . "' WHERE name = 'arch_sync'")->execute();
			} catch (PDOException $e) {
				echo "ERROR: " . $e->getMessage();			
			}

		} else {
			echo date('Y-m-d h:i:s A') . " - Error: Column count has changed. Parser needs to be updated.\r\n";
			die;
		}

	}

} else {
	echo "There are no divisions to sync.";
}


?>

<?php

class ArchUpdater {

	public static function convertDivision($division) {

		$division = strtolower($division);
		switch ($division) {

			case "battlefield":
			$id = 2;
			break;

		}
		return $id;
	}


	/**
	 * converts textual status to a usable id
	 * @param  string $status text based status
	 * @return [type]         [description]
	 */

	public static function convertStatus($status) {

		$status = (stristr($status, "LOA")) ? "LOA" : $status;

		switch ($status) {

			case "Active":
			$id = 1;
			break;
			case "On Leave":
			case "Missing in Action":
			case "LOA":
			$id = 3;
			break;
			case "Retired":
			$id = 4;
			break;

		}
		return $id;
	}
	
	public static function run($division) {

		$out = NULL;
/*
		if (isset($argv)) {
			$division = $argv[1];
			$linebreak = "\r\n";
		} else if (isset($_GET['division'])) {
			$division = $_GET['division'];
			$linebreak = "<br />";
		}*/

		if ($division) {

			$requested_division = self::convertDivision($division);

			if (!is_null($requested_division)) {


				$cred = ARCH_PASS;
				$cur_minute = floor(time()/60)*60;
				$auth_code = md5($cur_minute . $cred);

				$json_url = "http://www.clanaod.net/forums/aodinfo.php?type=json&division=Battlefield&authcode={$auth_code}";
				$agent = "AOD Squad Tracking Tool";

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_USERAGENT, $agent);
				curl_setopt($ch, CURLOPT_URL, $json_url );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

				$postResult = curl_exec($ch);
				$json = json_decode($postResult);

				if (count($json->column_order) == 11 && ($json->column_order[0] == 'userid') && ($json->column_order[10] == 'aodstatus')) {

					$currentMembers = array();

					// loop through member records
					foreach ($json->data as $column) {

						$member_id = $column[0];
						$forum_name = str_replace('AOD_', '', $column[1]);
						$joindate = $column[2];
						$lastvisit = $column[3];
						$lastactive = $column[4];
						$lastpost = $column[5];
						$postcount = $column[6];
						$aodrankval = $column[8]-2; 
						$aoddivision = self::convertDivision($column[9]);
						$status_id = self::convertStatus($column[10]);

						$currentMembers[$forum_name] = $member_id;
						$sql = "INSERT INTO member (forum_name, member_id, rank_id, status_id, game_id, join_date, last_forum_login, last_forum_post, forum_posts, last_activity) VALUES ('{$forum_name}', '{$member_id}', '{$aodrankval}', '{$status_id}', '{$aoddivision}', '{$joindate}', '{$lastvisit}', '{$lastpost}', '{$postcount}', '{$lastactive}') ON DUPLICATE KEY UPDATE forum_name='{$forum_name}', rank_id='{$aodrankval}', join_date='{$joindate}', status_id='{$status_id}',  game_id='{$aoddivision}', last_forum_login='{$lastvisit}', last_activity='{$lastactive}', last_forum_post='{$lastpost}', forum_posts='{$postcount}'";

						Flight::aod()->sql($sql)->one();
						// $out .= Flight::aod()->last_query;
					}

					// fetch all existing db members for array comparison
					$sql = "SELECT member_id, forum_name FROM member WHERE status_id = 1 AND game_id = {$requested_division}";
					$existingMemberArray = Flight::aod()->sql($sql)->many();
					$existingMembers = array();

					foreach($existingMemberArray as $member) {
						$existingMembers[$member['forum_name']] = $member['member_id'];
					}

					// select members that need to be removed
					$removals = array_diff($existingMembers, $currentMembers);

					if (count($removals)) {
						$removalIds = implode($removals, ", ");

						$sql = "UPDATE member SET status_id = 4 WHERE member_id IN ({$removalIds}) AND game_id = {$requested_division}";
						Flight::aod()->sql($sql)->one();

						$out .=  date('Y-m-d h:i:s A') . " - Updated the following member ids to 'removed': " . $removalIds . "{$linebreak}";
					}

					$out .=  date('Y-m-d h:i:s A') . " - sync done. {$linebreak}";

				} else {
					$out .=  date('Y-m-d h:i:s A') . " - Error: Column count has changed. Parser needs to be updated.{$linebreak}";
					die;
				}

			} else {
				$out .=  date('Y-m-d h:i:s A') . " - Error: Unsupported division.{$linebreak}";
			}

		} else {
			$out .=  date('Y-m-d h:i:s A') . " - Error: Must provide a division. Ex. ?division=Battlefield 4{$linebreak}";
		}

		return $out;

	}
}
<?php

if (isset($user)) {
	if (User::isLoggedIn()) {
		User::updateActivityStatus($member->id);
		$online_users = User::onlineList(); 
		if (!empty($online_users)) {
			$online_users = arrayToObject($online_users);
			$out = 'Users online: ';
			$usersArray = array();
			foreach ($online_users as $player) {
				$string = userColor(ucwords($player->username), $player->role, $player->last_seen);
				$usersArray[] = "<a href='member/{$player->member_id}'>{$string}</a>";
			}
			$users = implode(', ', $usersArray);
			$out .= $users;
		} else {
			$out = "No users are currently online.";
		}
	} 
} else {
	$out = "No active session.";
}

echo $out;

?>

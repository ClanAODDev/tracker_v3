<?php

if (isset($user)) {

	if (User::isLoggedIn()) {

		User::updateActivityStatus($member->id);
		$online_users = User::onlineList(); 

		if (!empty($online_users)) {

			$online_users = arrayToObject($online_users);

			$out = 'Users online: ';
			$usersArray = array();

			foreach ($online_users as $user) {
				$id = $user->member_id;
				$string = userColor(ucwords($user->username), $user->role, $user->last_seen);
				$usersArray[] = "<a href='/member/{$id}'>{$string}</a>";
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
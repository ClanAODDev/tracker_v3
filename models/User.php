<?php

class User extends Application {

	public $id;
	public $username;
	public $email;
	public $role;
	public $ip;	
	public $last_logged;
	public $credential;
	public $date_joined;
	public $last_seen;
	public $idle;
	public $developer;
	public $reset_flag;

	static $table = 'users';
	static $id_field = 'id';
	static $name_field = 'username';

	public static function isLoggedIn() {
		if (isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true)) {
			return true;
		}
		return false;
	}

	public static function isDev() {
		$id = $_SESSION['userid'];
		$params = Flight::aod()->sql("SELECT developer FROM users WHERE id = {$id} LIMIT 1")->one();
		return ($params['developer'] == 1) ? true : false;
	}

	public static function canEdit($mid, $user, $member)
	{

		$sql = "SELECT id, platoon_id, squad_leader_id, game_id FROM member WHERE member_id = {$mid}";
		$player = arrayToObject(Flight::aod()->sql($sql)->one());

    	// is the user the assigned squad leader?
		if (($user->role == 1) && ($member->member_id == $player->squad_leader_id)) {
			return true;
        // is the user the platoon leader of the user?
		} else if (($user->role == 2) && ($member->platoon_id == $player->platoon_id)) {
			return true;
        // is the user the division leader of the user?
		} else if (($user->role == 3) && ($member->game_id == $player->game_id)) {
			return true;
        // is the user a dev or clan administrator?        
		} else if (self::isDev()) {
			return true;
        // is the user editing someone of a lesser role, or himself?
		} else if ($mid == $member->member_id) {
			return true;
		} else {
			return false;
		}
	}
 
	public static function onlineList() {
		$params = Flight::aod()->sql("SELECT member.member_id, users.username, users.last_seen, users.role, users.idle FROM users LEFT JOIN member ON users.username = member.forum_name WHERE last_seen >= CURRENT_TIMESTAMP - INTERVAL 10 MINUTE ORDER BY idle, last_seen DESC")->many();
		return $params;
	}

	public static function exists($forum_name)	{
		$count = Flight::aod()->sql("SELECT count(*) as count FROM users WHERE `username`='{$forum_name}'")->one();
		if ($count['count'] > 0) { return true; } else {	return false; }
	}

	public static function validatePassword($pass, $user)
	{
		$user = strtolower($user);
		$params = self::find($user);
		$member = Member::find($user);

		if (!empty($params)) {
			if ($pass == hasher($pass, $params->credential)) {
				return array('userid'=>$params->id, 'memberid'=>$member->id);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function updateActivityStatus($id) {
		Flight::aod()->from(self::$table)
		->where(array('id' => $id))
		->update(array('last_seen' => date('Y-m-d H:i:s')))
		->one();
	}

	/**
	 * determines what user has permission to update
	 * @param  int $role User's role
	 * @return array       Array of values to determine field visibility
	 */
	public static function canUpdate($role) {

		switch ($role) {
			case 1:
			$allowPltAssignmentEdit = false;
			$allowSqdAssignmentEdit = false;
			$allowPosAssignmentEdit = false;
			break;

			case 2:
			$allowPltAssignmentEdit = false;
			$allowSqdAssignmentEdit = true;
			$allowPosAssignmentEdit = true;
			break;

			case 3:
			case 4:
			case 5:
			case 6:
			$allowPltAssignmentEdit = true;
			$allowSqdAssignmentEdit = true;
			$allowPosAssignmentEdit = true;
			break;
		}

		// allow developers to see all fields regardless of role
		if (self::isDev()) {
			$allowPltAssignmentEdit = true;
			$allowSqdAssignmentEdit = true;
			$allowPosAssignmentEdit = true;
		}

		// if assignment editing is allowed, show fields
		$pltField = ($allowPltAssignmentEdit) ? "block" : "none";
		$sqdField = ($allowSqdAssignmentEdit) ? "block" : "none";
		$posField = ($allowPosAssignmentEdit) ? "block" : "none";

		return (object) array( 'pltField' => $pltField,  'sqdField' => $sqdField, 'posField' => $posField );
	}

	public static function modify($params) {
		$user = new self();
		foreach ($params as $key=>$value) {
			$user->$key = $value;
		}
		$user->update($params);
	}

	public static function create($params) {
		$data = array(
			'credential'=>hasher($params['credential']),
			'username'=>$params['username'],
			'email'=>$params['email'],
			'date_joined'=>date("Y-m-d H:i:s"),
			'ip'=>$_SERVER['REMOTE_ADDR']
			);

		Flight::aod()->from(self::$table)->insert($data)->one();
		return Flight::aod()->insert_id;

	}


}

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
	public $member_id;

	static $table = 'users';
	static $id_field = 'id';
	static $name_field = 'username';

	public static function findByMemberId($member_id) {
		return self::find(array('member_id' => $member_id));
	}

	public static function findAll() {
		return self::fetch_all();
	}

	public static function isUser($member_id) {
		$user = self::find(array('member_id' => $member_id));
		if (!empty($user)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isLoggedIn() {
		if (isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true)) {
			return true;
		}
		return false;
	}

	public static function debugMode() {
		$id = $_SESSION['userid'];
		$params = Flight::aod()->sql("SELECT debug FROM ".self::$table." WHERE id = {$id} LIMIT 1")->one();
		return ($params['debug'] == 1) ? true : false;
	}

	public static function isDev() {
		$id = $_SESSION['userid'];
		$params = Flight::aod()->sql("SELECT developer FROM ".self::$table." WHERE id = {$id} LIMIT 1")->one();
		return ($params['developer'] == 1 || self::isOnSafeList($id)) ? true : false;
	}

	public static function isOnSafeList($id) {
		$params = Flight::aod()->sql("SELECT count(*) as count FROM dev_safelist WHERE user_id = {$id}")->one();
		return ($params['count'] > 0) ? true : false;
	}

	/**
	 * checks user's editing abilities for a specific member
	 * @param  int $mid    the member id of the member being edited
	 * @param  int $user   the user object of the user doing the editing
	 * @param  int $member the member object of the user doing the editing
	 * @return boolean      self explanatory
	 */
	public static function canEdit($mid, $myUser, $myMember)
	{

		$player = Member::findByMemberId($mid);
		$squad = ($player->squad_id != 0) ? Squad::find($player->squad_id) : false;

    	// is the user the assigned squad leader?
		if (($myUser->role == 1) && ($squad) && ($squad->leader_id == $myMember->id)) {
			return true;
        // is the user the platoon leader of the user?
		} else if (($myUser->role == 2) && ($myMember->platoon_id == $player->platoon_id)) {
			return true;
        // is the user the division leader of the user?
		} else if (($myUser->role == 3) && ($myMember->game_id == $player->game_id)) {
			return true;
        // is the user a dev or clan administrator?        
		} else if (self::isDev()) {
			return true;
        // is the user editing someone of a lesser role, or himself?
		} else if ($mid == $myMember->member_id) {
			return true;
		} else {
			return false;
		}
	}

	public static function onlineList() {
		$params = Flight::aod()->sql("SELECT member.member_id, users.username, users.last_seen, users.role, users.idle FROM ".self::$table." LEFT JOIN member ON users.username = member.forum_name WHERE last_seen >= CURRENT_TIMESTAMP - INTERVAL 10 MINUTE ORDER BY idle, last_seen DESC")->many();
		return $params;
	}

	public static function exists($forum_name)	{
		$count = Flight::aod()->sql("SELECT count(*) as count FROM ".self::$table." WHERE `username`='{$forum_name}'")->one();
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
			case 0:
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
		$user = new User;
		$user->username = $params['user'];
		$user->credential = $params['password'];
		$user->email = $params['email'];
		$user->date_joined = date("Y-m-d H:i:s");
		$user->ip = $_SERVER['REMOTE_ADDR'];
		$user->validation = md5(time() . rand());
		$user->member_id = $params['member_id'];
		$user->date_joined = date('Y-m-d H:i:s');
		$user->role = 0;
		$user->last_logged = 0;
		$user->last_seen = 0;
		$user->developer = 0;
		$user->save();
		//Email::validate($user);
	}

}

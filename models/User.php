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

	public static function isDev($id) {
		$params = Flight::aod()->sql("SELECT developer FROM users WHERE id = {$id} LIMIT 1")->one();
		return ($params['developer'] == 1) ? true : false;
	}

	public static function find($id) {
		$params = Flight::aod()->sql("SELECT * FROM users WHERE `id`='{$id}'")->one();
		return (object) $params;
	}

	public static function onlineList() {
		$params = Flight::aod()->sql("SELECT member.id, member.member_id, username, role, idle FROM users LEFT JOIN member ON users.username = member.forum_name WHERE last_seen >= CURRENT_TIMESTAMP - INTERVAL 10 MINUTE ORDER BY idle, last_seen DESC")->many();
		return $params;
	}

	public static function exists($forum_name)	{
		$count = Flight::aod()->sql("SELECT count(*) FROM users WHERE `username`='{$forum_name}'")->one();
		if ($count > 0) { return true; } else {	return false; }
	}

	public static function validatePassword($pass, $user)
	{
		$user = strtolower($user);
		$params = Flight::aod()->sql("SELECT id, credential FROM `users` WHERE `username`='{$user}'")->one();

		if (!empty($params)) {
			if ($pass == hasher($pass, $params['credential'])) {
				return $params['id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function updateActivityStatus($id)	{
        $params = Flight::aod()->sql("UPDATE `users` SET `last_seen` = CURRENT_TIMESTAMP() WHERE `id` = '{$id}'")->one();
    }

}
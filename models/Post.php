<?php

class Post extends Application {

	public $id;
	public $forum_id;
	public $title;
	public $content;
	public $date;
	public $user;
	public $reply_id;
	public $type;
	public $pinned;
	public $visibility;

	static $table = "posts";
	static $id_field = "id";

	/**
	 * fetches all posts visible to user's role
	 * @param  int $role role id
	 * @return array     array of all posts
	 */
	public static function find_all($role) {
		$sql = "SELECT * FROM `posts` WHERE `visibility` <= {$role} ORDER BY pinned DESC, date DESC LIMIT 5";
		return arrayToObject(Flight::aod()->using('Post')->sql($sql)->find());
	}
}
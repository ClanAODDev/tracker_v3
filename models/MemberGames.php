<?php

class MemberGames extends Application {

	public $id;
	public $member_id;
	public $subgame_id;

	static $id_field = 'id';
	static $table = 'member_games';

	public static function find_all($member_id) {
		return self::find_each($member_id);
	}

	public static function add($params) {
		$game = new self();
		foreach ($params as $key=>$value) {
			$game->$key = $value;
		}
		$game->save($params);
	}

	public static function delete($params) {
		$game = self::find($params['id']);
		self::remove($game)
	}

}
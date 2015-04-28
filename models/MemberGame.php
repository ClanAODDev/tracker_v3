<?php

class MemberGame extends Application {

	public $id;
	public $member_id;
	public $subgame_id;

	static $id_field = 'id';
	static $table = 'member_games';

	public static function get($member_id) {
		return self::find(array('member_id' => $member_id));
	}

	public static function plays($member_id, $game) {
		$params = self::find(array('member_id' => $member_id, 'subgame_id' => $game));
		return is_object($params) ? true : false;
	}

	public static function add($params) {
		$game = new self();
		$game->id = $params->id;
		foreach ($params->games as $game) {
			$game->$key = $value;
		}
		$game->save($params);
	}

	public static function delete($params) {
		$game = self::find($params['id']);
		self::remove($game);
	}

}
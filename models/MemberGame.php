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

	public static function add($member_id, $game_id) {
		$game = new self();
		$game->member_id = $member_id;
		$game->subgame_id = $game_id;
		$game->save();
		var_dump(Flight::aod()->last_query);die;
	}

	public static function delete($params) {
		$game = self::find($params['id']);
		self::remove($game);
	}

}


// problem currently is that there is no way to delete entries that are added, is this needed?
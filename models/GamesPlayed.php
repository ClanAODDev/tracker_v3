<?php

class GamesPlayed extends Application {

	public $id;
	public $bf4;
	public $bfh;

	static $id_field = 'id';
	static $table = 'games_played';

	public static function find($member_id) {
		return self::find($member_id);
	}

	public static function create($params) {
		$sql = "INSERT INTO {self::$table} (id, bf4, bfh) VALUES ({})"
	}



}
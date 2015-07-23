<?php

class RecruitingString extends Application {
	
	public $id;
	public $name;
	public $string;
	public $game_id;

	static $id_field = 'id';
	static $name_field = 'name';
	static $table = 'recruiting_strings';

	public static function findByName($name, $game_id) {
		return self::find(array('name' => $name, 'game_id' => $game_id));
	}
	
}
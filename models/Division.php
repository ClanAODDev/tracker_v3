<?php

class Division extends Application {

	public $id;
	public $description;
	public $short_name;
	public $full_name;
	public $subforum;
	public $short_descr;
	public $division_structure_thread;
	public $welcome_forum;

	static $table = 'divisions';
	static $id_field = 'id';
	static $name_field = 'short_name';

	public static function find_all() {
		return self::fetch_all();
	}

	public static function find($id) {
		$params = Flight::aod()->sql("SELECT * FROM games WHERE `id`='{$id}'")->one();
		return (object) $params;
	}
}
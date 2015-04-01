<?php

class Position extends Application {
	public $id;
	public $desc;
	public $abbr;

	static $id_field = 'id';
	static $table = 'position';
	
	public static function find_all() {
		return self::fetch_all();
	}
}
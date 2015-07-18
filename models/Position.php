<?php

class Position extends Application {
	public $id;
	public $desc;
	public $icon;
	public $class;
	public $sort_order;

	static $id_field = 'id';
	static $table = 'position';
	
	public static function find_all() {
		return self::fetch_all(true);
	}

	public static function convert($id) {
		return self::find($id);
	}
}
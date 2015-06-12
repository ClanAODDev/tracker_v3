<?php

class Action extends Application {

	public $id;
	public $desc;
	public $verbage;
	public $icon;

	static $id_field = 'id';
	static $table = 'actions';

	public static function find_all() {
		return self::fetch_all();
	}
	
}
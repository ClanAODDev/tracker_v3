<?php

class Crontab extends Application {
	
	public $id;
	public $name;
	public $value;

	static $id_field = 'id';
	static $name_field = 'name';
	static $table = 'crontab';

	public static function find_all() {
		return self::fetch_all();
	}

	public static function findByName($name) {
		return (object) self::find($name);
	}

	public static function modify($params) {

		$crontab = new self();
		foreach ($params as $key=>$value) {
			$crontab->$key = $value;
		}

		$crontab->update($params);
	}

}
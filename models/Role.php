<?php

class Role extends Application {

	public $id;
	public $role_name;

	static $table = 'roles';
	static $id_field = 'id';

	public static function find_all() {
		return self::fetch_all();
	}

}
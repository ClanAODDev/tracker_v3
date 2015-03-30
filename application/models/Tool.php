<?php

class Tool extends Application {

	public $id;
	public $tool_name;
	public $class;
	public $tool_descr;
	public $tool_path;
	public $role_id;
	public $icon;
	public $disabled;

	static $table = 'user_tools';
	static $id_field = 'id';

	/**
	 * Fetches user tools based on role
	 * @param  int $role role of user (role_id)
	 * @return array     array of tools
	 */
	public static function find_all($role) {
		return self::find(array("role_id <=" => $role, 'disabled' => 0));
	}

}
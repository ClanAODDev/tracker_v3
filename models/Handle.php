<?php

class Handles extends Application {
	public $id;
	public $handle_type;
	public $handle_profile_url;

	static $id_field = 'id';
	static $name_field = 'handle_type';
	static $table_name = 'handles';

	public static function create($params) {
		$handle = new self();
		foreach ($params as $key=>$value) {
			$handle->$key = $value;
		}
		$handle->save($params);
	}

	public static function modify($params) {
		$handle = new self();
		foreach ($params as $key=>$value) {
			$handle->$key = $value;
		}
		$handle->update($params);
	}

	public static function delete($id) {}
}
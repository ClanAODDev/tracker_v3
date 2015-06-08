<?php

class MemberHandle extends Application {
	
	public $id;
	public $member_id;
	public $handle_type;
	public $handle_value;

	static $id_field = 'id';
	static $table_name = 'member_handles';

	public static function find_all($member_id) {
		return self::find(array('member_id' => $member_id))->many();
	}

	public static function create($params) {
		$handle = new self();
		$handle->member_id = $params->$member_id;
		$handle->handle_type = $params->$handle_type;
		$handle->handle_value = $params->$handle_value;
		$handle->save();
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
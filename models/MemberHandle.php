<?php

class MemberHandle extends Application {
	
	public $id;
	public $member_id;
	public $battlelog_id;
	public $battlenet_username;
	public $steam_id;

	static $id_field = 'id';
	static $table_name = 'member_handles';

	public static function create() {}
	public static function delete() {}
	public static function modify() {}

}
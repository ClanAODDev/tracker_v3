<?php

class Status extends Application {
	
	public $id;
	public $desc;

	static $id_field = 'id';
	static $table = 'status';

	public static function convert($id) {
		return self::find($id);
	}

}
<?php

class Rank extends Application {
	
	public $id;
	public $desc;
	public $abbr;

	static $table = 'rank';
	static $id_field = 'id';

	public static function convert($id) {
		return self::find($id);
	}
}
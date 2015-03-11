<?php

class Application {
	
	static $id_field = 'id';
	static $name_field = 'name';
	
	public function __construct() {}
	
	public function load($params) {
		foreach ($params as $key => $value) {
			if (is_array($value)) $value = json_encode($value);
			$this->$key = $value;
		}
	}
	
	public function save($params = array()) {
		if (!empty($params)) $this->load($params);
		Flight::advising()->using(get_called_class())->save($this);
	}
	
	public static function find($params) {
		return Flight::advising()->using(get_called_class())->find($params);
	}
	
	public static function find_each($params) {
		$results = Flight::advising()->using(get_called_class())->find($params);
		return is_object($results) ? array($results) : $results;
	}
	
	public static function fetch_all() {
		$results = Flight::advising()->using(get_called_class())->sql("SELECT * FROM ".static::$table)->find();
		return is_object($results) ? array($results) : $results;
	} 
	
	public static function create($params) {
		$object = new static();
		if (property_exists(get_called_class(), 'created_at')) $object->created_at = timestamp();
		$object->save($params);
		$object->id = Flight::advising()->insert_id;
		return $object;
	}
	
	public static function update($params) {
		$object = new static();
		if (property_exists(get_called_class(), 'updated_at')) $object->updated_at = timestamp();
		$object->save($params);
		return $object;
	}
	
}

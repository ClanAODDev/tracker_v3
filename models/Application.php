<?php

class Application {
	
	static $id_field = 'id';
	static $name_field = 'name';
	
	public function __construct() {}
	
	public function load($params) {
		foreach ($params as $key => $value) {
			if ($value == " ") continue;
			if (is_array($value)) $value = json_encode($value);
			$this->$key = $value;
		}
	}
	
	public function save($params = array()) {
		$fields = NULL;
		if (!empty($params)) {
			$this->load($params);
			if (array_key_exists("id", $params)) {
				$fields = array_keys($params);
				unset($fields['id']);
			}
		}
		Flight::aod()->using(get_called_class())->save($this, $fields);
		if (!array_key_exists("id", $params)) {
			$this->id = Flight::aod()->insert_id;
		}
	}
	
	public function to_json() {
		return json_encode($this);
	}
	
	public static function find($params) {
		if (is_numeric($params)) $params = (int) $params;
		return Flight::aod()->using(get_called_class())->find($params);
	}
	
	public static function find_each($params) {
		$results = Flight::aod()->using(get_called_class())->find($params);
		return is_array($results) ? $results : array($results);
	}
	
	public static function sql_find($sql) {
		return Flight::aod()->using(get_called_class())->sql($sql)->find();
	}
	
	public static function fetch_active() {
		return self::find_each(array('active' => 1));
	}
	
	public static function fetch_all($sort=false) {
		if ($sort) $sort = " ORDER BY sort_order";
		$results = Flight::aod()->using(get_called_class())->sql("SELECT * FROM ".static::$table." {$sort}")->find();
		return is_object($results) ? array($results) : $results;
	} 

	public static function count_all() {
		$results = Flight::aod()->using(get_called_class())->sql("SELECT * FROM ".static::$table)->count();
		return is_object($results) ? array($results) : $results;
	} 
	
	public static function create($params) {
		$object = new static();
		if (property_exists(get_called_class(), 'created_at')) $object->created_at = timestamp();
		$object->save($params);
		return $object;
	}
	
	public static function update($params) {
		$object = new static();
		if (property_exists(get_called_class(), 'updated_at')) $object->updated_at = timestamp();
		$object->save($params);
		return $object;
	}
	
}

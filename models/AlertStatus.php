<?php

class AlertStatus extends Application {
	public $id;
	public $alert_id;
	public $user_id;
	public $read_date;

	static $table = "alerts_status";
	static $id_field = "id";

	public static function insert($params) {
		$alert = new self();
		$alert->alert_id = $params['id'];
		$alert->user_id = $params['user'];
		$alert->read_date = date('Y-m-d H:i:s');
		Flight::aod()->save($alert);

		echo Flight::aod()->last_query;
		die;
	}
}
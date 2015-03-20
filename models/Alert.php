<?php

class Alert extends Application {

	public $id;
	public $content;
	public $type;
	public $start_date;
	public $end_date;

	static $table = 'alerts';
	static $id_field = 'id';

	/**
	 * fetch all alerts not seen by user
	 * @return array array of alerts
	 */
	public static function find_all($user) {
		$sql = "SELECT DISTINCT * FROM alerts WHERE start_date < CURRENT_TIMESTAMP AND end_date > CURRENT_TIMESTAMP AND NOT EXISTS ( SELECT * FROM alerts_status WHERE alert_id = alerts.id AND user_id = {$user} )";
		$params = Flight::aod()->sql($sql)->many();
		echo(Flight::aod()->last_query);
		return arrayToObject($params);
	}

}
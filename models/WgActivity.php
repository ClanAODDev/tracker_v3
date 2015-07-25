<?php

class WgActivity extends Application {
	
	public $id;
	public $member_id;
	public $last_battle_time;

	static $id_field = 'id';
	static $table = 'wg_activity';

	public static function getLastBattleTime($member_id) {
		$params = self::find(array('member_id' => $member_id));
		if ($params) {
			return date('Y-m-d', strtotime($params->last_battle_time));
		} else {
			return '0000-00-00';
		}
	}

}
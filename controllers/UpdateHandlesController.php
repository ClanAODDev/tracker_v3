<?php

class UpdateHandlesController {

	public static function battlelog() {
		$conditions = array('game_id' => 2, 'battlelog_id !=' => 0, 'status_id @' => array(1,3,999));
		$members = Flight::aod()->from(Member::$table)->where($conditions)->select('battlelog_name, battlelog_id, id')->many();

		foreach($members as $member) {
			$handle = new MemberHandle;
			$handle->member_id = $member['id'];
			$handle->handle_type = 2;
			$handle->handle_value = $member['battlelog_name'];
			$handle->handle_account_id = $member['battlelog_id'];
			$handle->save();
			echo $handle->member_id . " done.";
		}

	}

}
<?php

class UpdateHandlesController {

	public static function battlelog() {
		$conditions = array('game_id' => 2, 'battlelog_id !=' => 0, 'status_id @' => array(1,3,999));
		$members = Flight::aod()->from(Member::$table)->where($conditions)->select('battlelog_name, battlelog_id, id')->many();

		foreach($members as $member) {

			$battlelogName = new MemberHandle;
			$battlelogName->member_id = $member['id'];
			$battlelogName->handle_type = 2;
			$battlelogName->handle_value = $member['battlelog_name'];
			$battlelogName->save();

			$battlelogId = new MemberHandle;
			$battlelogId->member_id = $member['id'];
			$battlelogId->handle_type = 1;
			$battlelogId->handle_value = $member['battlelog_id'];
			$battlelogId->save();

		}

	}

}
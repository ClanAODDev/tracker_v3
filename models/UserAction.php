<?php

class UserAction extends Application {

	public $id;
	public $type_id;
	public $date;
	public $user_id;
	public $target_id;

	static $id_field = 'id';
	static $table = 'user_actions';

	/**
	 * user action types:
	 * ------------------------
	 *  id | description
	 * ------------------------
	 * 	1  |  add a new recruit
	 *  2  |  remove a member
	 *  3  |  update a member
	 *  4  |  flag an inactive member
	 *  5  |  generate new division structure
	 *  6  |  unflag an inactive member
	 *  7  |  Approve an loa
	 *  8  |  Deny an loa
	 *  9  |  Revoke an loa
	 *  ----------------------- 
	 */

	public static function create($params) {
		$UserAction = new self();
		foreach ($params as $key=>$value) {
			$UserAction->$key = $value;
		}
		$UserAction->save($params);
		//echo Flight::aod()->last_query;
	}

}
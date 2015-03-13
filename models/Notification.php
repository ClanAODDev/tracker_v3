<?php

/**
 * Notification object
 * provides important notifications to the user
 */
class Notification extends Application {

	public $all = array(); 

	public function __construct($member) {

		// expired or pending leaves of absence
		if (count(LeaveOfAbsence::count_expired($member->game_id)) || count(LeaveOfAbsence::count_pending($member->game_id))) {
			array_push($this->all, "<div class='alert alert-info'><i class='fa fa-clock-o'></i> There are leaves of absence that require your attention <a href='manage/leaves-of-absence' class='alert-link'>Manage leaves of absence</a></div>");
		}

	}

}
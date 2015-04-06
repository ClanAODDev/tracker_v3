<?php

/**
 * Notification object
 * provides important notifications to the user
 */
class Notification extends Application {

	public $all = array(); 

	public function __construct($user, $member) {
		// leadership notifications
		if ($user->role > 1) {
			// expired or pending leaves of absence
			if (LeaveOfAbsence::count_expired($member->game_id) > 0 || LeaveOfAbsence::count_pending($member->game_id) > 0) {
				array_push($this->all, "<div class='alert alert-warning fade-in'><i class='fa fa-clock-o'></i> There are leaves of absence that require your attention <a href='manage/leaves-of-absence' class='alert-link'>Manage leaves of absence</a></div>");
			}
		}

	}

}
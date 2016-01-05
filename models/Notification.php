<?php

/**
 * Notification object
 * provides important notifications to the user
 */
class Notification extends Application
{
    public $messages = array();

    public function __construct($user, $member)
    {

        // leadership notifications
        if ($user->role > 1) {
            // expired or pending leaves of absence
            if (LeaveOfAbsence::count_expired($member->game_id) > 0 || LeaveOfAbsence::count_pending($member->game_id) > 0) {
                array_push($this->messages,
                    "<div class='alert alert-warning'><i class='fa fa-clock-o fa-lg'></i> There are leaves of absence that require your attention <a href='manage/leaves-of-absence' class='alert-link'>Manage Leaves of Absence</a></div>");
            }
        }

        // division CO / XO
        if ($user->role > 2) {
            if (count(Division::findUnassigned($member->game_id))) {
                array_push($this->messages,
                    "<div class=\"alert alert-warning\"><i class=\"fa fa-exclamation-circle\"></i> Your division has unassigned members. Visit your division page to resolve this.</div>");
            }
        }

        if (!User::isValidated()) {
            array_push($this->messages,
                "<div class='alert alert-info'><i class='fa fa-envelope fa-lg'></i> Your account email has not been verified. <a href='#' class='alert-link send-email-validation' data-email='{$user->email}'>Send Validation</a></div>");
        }
    }
}

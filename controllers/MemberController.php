<?php

class MembersController {

	public static function _index() {
		
	}

	public static function _profile() {
		Flight::render('users/profile', array('active' => 'profile'), 'content');
		Flight::render('layouts/application');
	}

}
<?php

class ApplicationController {

	public static function _index() {
		$user = User::find($_SESSION['username']);
		var_dump($user);die();
		Flight::render('home', array(''), 'content');
		Flight::render('layouts/application', array('user' => $user));
	}

	public static function _unavailable() {
		Flight::render('layouts/unavailable', array(), 'content');
		Flight::render('layouts/application');
	}

	public static function _404() {
		Flight::render('layouts/404', array(''), 'content');
		Flight::render('layouts/application');
	}
}
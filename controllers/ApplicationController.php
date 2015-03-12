<?php

class ApplicationController {

	public static function _index() {
		$user = User::find($_SESSION['username']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::getToolsByRole($user->role);
		$divisions = Division::find_all();
		Flight::render('home', array(''), 'content');
		Flight::render('layouts/application', 
			array(
			'user' => $user, 
			'member' => $member,
			'tools' => $tools,
			'divisions' => $divisions
			)
		);
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
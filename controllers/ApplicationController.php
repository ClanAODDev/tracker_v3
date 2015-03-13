<?php

class ApplicationController {

	public static function _index() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::getToolsByRole($user->role);
		$divisions = Division::find_all();
		$division = Division::find($member->game_id);
		$alerts = Alert::find_all($user->id);
		Flight::render('layouts/home', array('user' => $user, 'member' => $member, 'division' => $division, 'divisions' => $divisions, 'alerts' => $alerts), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _help() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::getToolsByRole($user->role);
		$divisions = Division::find_all();
		$division = Division::find($member->game_id);
		Flight::render('application/help', array('user' => $user, 'member' => $member, 'division' => $division), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _invalidLogin() {
		Flight::render('errors/invalid_login', array(), 'content');
		Flight::render('errors/application');
	}

	public static function _unavailable() {
		Flight::render('errors/unavailable', array(), 'content');
		Flight::render('errors/main');
	}

	public static function _404() {
		Flight::render('errors/404', array(), 'content');
		Flight::render('errors/main');
	}


}
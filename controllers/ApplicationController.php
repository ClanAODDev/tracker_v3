<?php

class ApplicationController {

	public static function _index() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::find($member->game_id);
		$alerts = Alert::find_all($user->id);
		$notifications = new Notification($member);
		$posts = Post::find_all($user->role);
		Flight::render('user/main_tools', array('user' => $user, 'tools' => $tools), 'main_tools');
		Flight::render('application/posts', array( 'posts' => $posts), 'posts_list');
		Flight::render('application/divisions', array('divisions' => $divisions), 'divisions_list');
		Flight::render('user/notifications', array('notifications' => $notifications->all, 'alerts' => $alerts), 'notifications_list');
		Flight::render('layouts/home', array('user' => $user, 'member' => $member, 'division' => $division), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _help() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
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
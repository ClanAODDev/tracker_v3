<?php

class ApplicationController {
	public static function _index() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$alerts = Alert::find_all($user->id);
		$notifications = new Notification($user, $member);
		$posts = Post::find_all($user->role);
		$squad = Squad::find($member->member_id);
		$platoon = Platoon::find($member->platoon_id);
		$genPop = Platoon::GeneralPop($member->platoon_id);

		if (isset($_SESSION['loggedIn'])) {
			User::updateActivityStatus($user->id);
		}

		Flight::render('user/main_tools', array('user' => $user, 'tools' => $tools), 'main_tools');
		Flight::render('application/posts', array( 'posts' => $posts), 'posts_list');
		Flight::render('member/personnel', array('member' => $member, 'squad' => $squad, 'platoon' => $platoon, 'genPop' => $genPop), 'personnel');
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
		$division = Division::findById(intval($member->game_id));
		Flight::render('application/help', array('user' => $user, 'member' => $member, 'division' => $division), 'content');
		Flight::render('layouts/application', array('js' => 'help', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _doUsersOnline() {
		if (isset($_SESSION['loggedIn'])) {
			$user = User::find($_SESSION['userid']);
			$member = Member::find($_SESSION['username']); 
			Flight::render('user/online_list', array('user' => $user, 'member' => $member));
		} else {
			Flight::render('user/online_list');
		}		
	}

	public static function _doSearch() {
		$name = trim($_POST['name']);
		$results = Member::search($name);
		Flight::render('member/search', array('results' => $results));
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


	public static function _doUpdateAlert() {
		$params = array('id' => $_POST['id'], 'user' => $_POST['user']);
		AlertStatus::insert($params);
	}

}
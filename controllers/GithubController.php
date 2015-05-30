<?php

class GithubController {
	
	public static function _index() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$issues = GitHub::getIssues();
		Flight::render('issues/index', array('issues' => $issues), 'content'); 
		Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _addIssue() {
		$user = User::find(intval($_SESSION['userid']));
		Flight::render('modals/add_issue', array('user' => $user)); 
	}

	public static function _doSubmitIssue() {
		$user = $_POST['user'];
		$title = $_POST['title'];
		$link = $_POST['link'];
		$body = $_POST['body'];
		$body .= "\r\n\r\nLink to problem area: {$link}";
		Github::createIssue($title, $body);
	}

}
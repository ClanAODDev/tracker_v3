<?php

class GithubController {
	
	public static function _index() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$open_issues = GitHub::getOpenIssues();
		Flight::render('issues/index', array('open_issues' => $open_issues), 'content'); 
		Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _closedIssues() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$closed_issues = GitHub::getClosedIssues();
		Flight::render('issues/closed', array('closed_issues' => $closed_issues), 'content'); 
		Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _view($id) {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$issue = GitHub::getSelectIssue($id);
		Flight::render('issues/id_issue', array('selectIssue' => $issue, 'id' => $id), 'content'); 
		Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _createIssue() {
		$user = User::find(intval($_SESSION['userid']));
		Flight::render('modals/create_issue', array('user' => $user)); 
	}

	public static function _doSubmitIssue() {
		$user = $_POST['user'];
		$title = $_POST['title'];
		$link = $_POST['link'];
		$body = $_POST['body'];
		$body .= "\r\n\r\nLink to problem area: {$link}";
		Github::createIssue($title, $body);
	}

	public static function _devIssues() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$dev_issues = GitHub::getDevIssues();
		Flight::render('issues/dev', array('dev_issue' => $dev_issues), 'content'); 
		Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

}
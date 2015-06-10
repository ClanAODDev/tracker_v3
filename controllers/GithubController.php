<?php

class GithubController {
	
	public static function _index($filter="open") {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);

		$filter = $filter ?: "open";

		switch ($filter) {
			case "open":
			$issues = GitHub::getOpenIssues();
			break;

			case "closed":
			$issues = GitHub::getClosedIssues();
			break;

			case "dev" && ($user->role > 2 || User::isDev()):
			$issues = GitHub::getDevIssues();
			break;

			default:
			$issues = GitHub::getOpenIssues();
			$filter = "Open";
			break;
		}

		Flight::render('issues/issues', array('issues' => $issues, 'filter' => $filter), 'issuesList');
		Flight::render('issues/filters', array('filters' => $filter, 'user' => $user), 'filters');
		Flight::render('issues/index', array('issues' => $issues), 'content'); 
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

		if ($issue = GitHub::getIssue($id)) {

			$user = User::find(intval($_SESSION['userid']));
			$member = Member::find(intval($_SESSION['memberid']));
			$tools = Tool::find_all($user->role);
			$divisions = Division::find_all();
			$division = Division::findById(intval($member->game_id));
			$platoons = Platoon::find_all($member->game_id);
			$comments = GitHub::getComments($id);

			// not sure if I am a big fan of doing it this way as
			// looping through the labels significantly increases the 
			// page load time. We need to find a better way to do this.
			// For now, let's leave this check out, presently it's causing
			// problems with issues that should be working

			/*  
			foreach($labels as $label) {
				if($label->getName() === "dev" && ($user->role > 2 || User::isDev())) {
					Flight::render('issues/view', array('user' => $user, 'issue' => $issue, 'comments' => $comments), 'content'); 
				} elseif($label->getName() === "dev" && !($user->role > 2 || User::isDev())) {
					Flight::render('issues/notAvailable', array('id' => $id), 'content');
				} elseif($label->getName() === "client") {
					Flight::render('issues/view', array('user' => $user, 'issue' => $issue, 'comments' => $comments), 'content'); 
				}
			}
			*/

			Flight::render('issues/view', array('user' => $user, 'issue' => $issue, 'comments' => $comments), 'content'); 
			Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

		} else {

			Flight::redirect('/404', 404);

		}
	}

	public static function _createIssue() {
		Flight::render('modals/create_issue', array('js' => 'issue')); 
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

	public static function _doSubmitIssue() {
		$user = Member::findById($_POST['user'])->forum_name;
		$title = $_POST['title'];
		$link = $_POST['link'];
		$body = $_POST['body'];
		$body .= "<hr /><strong>Page reported</strong>: {$link}<br />";
		$body .= "<strong>Reported by</strong>: {$user}";
		$issue = Github::createIssue($title, $body);

		if ($issue->getNumber()) {
			$data = array('success' => true, 'message' => "Your report has been submitted");	
		} else {
			$data = array('success' => false, 'message' => "Something went wrong");	
		}

		echo(json_encode($data));
	}

}
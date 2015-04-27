<?php

class RecruitingController {

	public static function _index() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		Flight::render('recruiting/index', array(), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _addNewMember() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		$platoon_id = (($user->role >= 2) && (!User::isDev($user->id))) ? $member->platoon_id : false;
		$squadLeaders = Platoon::SquadLeaders($member->game_id, $platoon_id);
		Flight::render('recruiting/new_member', array('user' => $user, 'member'=> $member, 'division' => $division, 'platoons' => $platoons, 'squadLeaders' => $squadLeaders), 'content');
		Flight::render('layouts/application', array('js' => 'recruit', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _doDivisionThreadCheck() {
		if (!empty($_POST['player'])) {
			$player = trim($_POST['player']);
			$member = Member::find(intval($_SESSION['memberid']));
			$gameThreads = DivisionThread::find_all($member->game_id);
			Flight::render('recruiting/thread_check', array('js' => 'check_threads', 'gameThreads' => $gameThreads, 'player' => $player));
		} else {
			echo "<span class='text-muted'>A valid player was not provided!</span>";
		}
	} 

}

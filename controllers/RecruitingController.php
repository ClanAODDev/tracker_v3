<?php

class RecruitingController {
	public static function _index() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));
		$platoons = Platoon::find_all($member->game_id);
		Flight::render('recruiting/index', array(), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'platoons' => $platoons));
	}
}
?>
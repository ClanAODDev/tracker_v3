<?php

class DivisionController {
	public static function _index($div) {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));
		$platoons = Platoon::find_all($member->game_id);
		$division_leaders = Division::findDivisionLeaders($member->game_id);
		Flight::render('division/main', array('user' => $user, 'member' => $member, 'division' => $division, 'division_leaders' => $division_leaders, 'platoons' => $platoons), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}

	public static function _create() {}
	public static function _modify() {}
	public static function _delete() {}

}
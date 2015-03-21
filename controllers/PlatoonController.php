<?php

class PlatoonController {

	public static function _index($div, $plt) {
		$division = Division::findByName(strtolower($div));
		$platoonId = Platoon::get_id_from_number($plt, $division->id);

		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$platoon = Platoon::findById(intval($platoonId));
		$members = arrayToObject(Platoon::members($platoonId));

		$memberIdList = Platoon::memberIdsList($platoonId);

		$right_now = new DateTime("now");
		$first_date_in_range = date("Y-m-d", strtotime("now - 30 days"));
		$last_date_in_range = date("Y-m-d", strtotime("now"));

		$overall_aod_percent = array();
		$overall_aod_games = array();
		$platoonPm = array();

		Flight::render('platoon/main/statistics', array('platoon' => $platoon), 'statistics');
		Flight::render('platoon/main/members', array('members' => $members, 'js' => 'platoon'), 'membersTable');
		Flight::render('platoon/main/index', array('user' => $user, 'member' => $member, 'division' => $division, 'platoon' => $platoon, 'memberIdList' => $memberIdList, 'plt' => $plt, 'div' => $division->id, 'members' => $members), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
		
	}

	public static function _create() {}
	public static function _modify() {}
	public static function _delete() {}	
}
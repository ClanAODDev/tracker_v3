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

		$topListMonthly = Activity::topList30DaysByDivision(intval($division->id));
		$topListToday = Activity::topListTodayByDivision(intval($division->id));

		Flight::render('division/statistics', array('monthly' => $topListMonthly, 'daily' => $topListToday), 'statistics');
		Flight::render('division/main', array('user' => $user, 'member' => $member, 'division' => $division, 'division_leaders' => $division_leaders, 'platoons' => $platoons), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'platoons' => $platoons, 'js' => 'division'));
	}

	public static function _manage_inactives() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));
		$platoons = Platoon::find_all($member->game_id);

		switch ($user->role) {
			case User::isDev($user->id): $type = "div"; $id = $member->game_id; break;
			case 1: $type = "sqd"; $id = $member->member_id; break;
			case 2: $type = "plt"; $id = $member->platoon_id; break;
			case 3: $type = "div";  $id = $member->game_id; break;
			default: $type = "div"; $id = $member->game_id; break;
		}

		$flagged_inactives = Member::findInactives($id, $type, true);
		$flaggedCount = (count($flagged_inactives)) ? count($flagged_inactives) : 0;

		$inactives = Member::findInactives($id, $type);
		$inactiveCount = (count($inactives)) ? count($inactives) : 0;

		Flight::render('manage/inactive_members', array('member' => $member, 'user' => $user, 'inactives' => arrayToObject($inactives), 'flagged' => arrayToObject($flagged_inactives), 'flaggedCount' => $flaggedCount, 'inactiveCount' => $inactiveCount), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'platoons' => $platoons, 'js' => 'manage'));

	}

	public static function _manage_loas() {
		
	}

	public static function _generateDivisionStructure() {
		$member = Member::find($_SESSION['username']);
		$division_structure = DivisionStructure::generate($member);
		Flight::render('modals/division_structure', array('division_structure' => $division_structure));
	}

}
<?php

class PlatoonController {

	public static function _index($div, $plt) {
		$division = Division::findByName(strtolower($div));
		$platoonId = Platoon::get_id_from_number($plt, $division->id);

		if (!is_null($platoonId)) {

			$user = User::find($_SESSION['userid']);
			$member = Member::find($_SESSION['username']);
			$tools = Tool::find_all($user->role);
			$divisions = Division::find_all();
			$platoon = Platoon::findById($platoonId);
			$members = arrayToObject(Platoon::members($platoonId));

			$memberIdList = Platoon::memberIdsList($platoonId);

			$bdate = date("Y-m-d", strtotime("now - 30 days"));
			$edate = date("Y-m-d", strtotime("now"));

			$gameStats = arrayToObject(Platoon::gameStats($platoonId, $bdate, $edate));

			Flight::render('platoon/main/statistics', array('platoon' => $platoon, 'gameStats' => $gameStats), 'statistics');
			Flight::render('platoon/main/members', array('members' => $members, 'js' => 'platoon', 'bdate' => $bdate, 'edate' => $edate), 'membersTable');
			Flight::render('platoon/main/index', array('user' => $user, 'member' => $member, 'division' => $division, 'platoon' => $platoon, 'memberIdList' => $memberIdList, 'plt' => $plt, 'div' => $division->id, 'members' => $members, 'platoonId' => $platoonId), 'content');
			Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

		} else {

			Flight::redirect('404/', 404); 

		}
	}

	public static function _create() {}
	public static function _modify() {}
	public static function _delete() {}	
}
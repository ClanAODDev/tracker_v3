<?php

class PlatoonController {

	public static function _index($div, $plt) {
		$division = Division::findByName(strtolower($div));
		$platoonId = Platoon::getIdFromNumber($plt, $division->id);

		if (!is_null($platoonId)) {

			$user = User::find(intval($_SESSION['userid']));
			$member = Member::find(intval($_SESSION['memberid']));
			$tools = Tool::find_all($user->role);
			$divisions = Division::find_all();
			$platoon = Platoon::findById($platoonId);
			$members = arrayToObject(Platoon::members($platoonId));

			$memberIdList = Platoon::memberIdsList($platoonId);
			$activity = arrayToObject(Platoon::forumActivity($platoonId));

			$bdate = date("Y-m-d", strtotime("now - 30 days"));
			$edate = date("Y-m-d", strtotime("now"));

			Flight::render('platoon/main/statistics', array('platoon' => $platoon, 'activity' => $activity), 'statistics');
			Flight::render('platoon/main/members', array('division' => $division, 'members' => $members, 'js' => 'platoon', 'bdate' => $bdate, 'edate' => $edate), 'membersTable');
			Flight::render('platoon/main/index', array('user' => $user, 'member' => $member, 'division' => $division, 'platoon' => $platoon, 'memberIdList' => $memberIdList, 'plt' => $plt, 'div' => $division->id, 'members' => $members, 'platoonId' => $platoonId), 'content');
			Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

		} else {

			Flight::redirect('404/', 404); 

		}
	}

	public static function _manage_platoon($div, $plt) {
		
		$division = Division::findByName(strtolower($div));
		$platoonId = Platoon::getIdFromNumber($plt, $division->id);

		if (!is_null($platoonId)) {

			$user = User::find(intval($_SESSION['userid']));
			$member = Member::find(intval($_SESSION['memberid']));

			if ($member->platoon_id == $platoonId || $user->role > 2 || User::isDev()) {

				$tools = Tool::find_all($user->role);
				$divisions = Division::find_all();
				$platoon = Platoon::findById($platoonId);
				$unassignedMembers = Platoon::unassignedMembers($platoonId, true);
				$squads = Squad::findByPlatoonId($platoonId);
				$memberCount = count((array) Platoon::members($platoonId));

				Flight::render('manage/platoon', array('division' => $division, 'platoon' => $platoon, 'squads' => $squads, 'unassignedMembers' => $unassignedMembers, 'memberCount' => $memberCount), 'content');
				Flight::render('layouts/application', array('js' => 'manage', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

			} else {

				// insufficient access
				Flight::redirect('404/', 404); 
			}

		} else {

			// nonexistent platoon
			Flight::redirect('404/', 404); 

		}

	}

	public static function _doUpdateMemberSquad() {

		$params = array();
		$params['id'] = $_POST['member_id'];
		$params['squad_id'] = $_POST['squad_id'];
		$params['position_id'] = 6;

		Member::modify($params);
		$data = array('success' => true);
		echo(json_encode($data));
	}

	public static function _create() {}
	public static function _modify() {}
	public static function _delete() {}	
}
<?php

class MemberController {

	public static function _profile($id) {

		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));
		$platoons = Platoon::find_all($member->game_id);

		// profile data
		$memberInfo = Member::profileData(intval($id));
		$divisionInfo = Division::findById(intval($memberInfo->game_id));
		$platoonInfo = Platoon::findById(intval($memberInfo->platoon_id));

		// member alerts
		if ($memberInfo->status_id == 999) {
			$alerts = Member::isPending();
		} else if ($memberInfo->status_id == 4) {
			$alerts = Member::isRemoved();
		}

		if (strtotime($memberInfo->last_activity) < strtotime('-30 days')) {
			$alerts .= Member::isInactive(strtotime($memberInfo->last_activity));
		}

		if ($platoonInfo->id != 0) {
			$platoonInfo->link = "<li><a href='divisions/{$divisionInfo->short_name}/{$platoonInfo->number}'>{$platoonInfo->name}</a></li>";
			$platoonInfo->item = "<li class='list-group-item text-right'><span class='pull-left'><strong>Platoon: </strong></span> <span class='text-muted'>{$platoonInfo->name}</span></li>";
		}


		Flight::render('member/profile', array('user' => $user, 'member' => $member, 'division' => $division, 'memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo, 'alerts' => $alerts), 'content');

		Flight::render('layouts/application', array('js' => 'member', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'platoons' => $platoons));
		
	}

	public static function _modify() {}
	public static function _delete() {}

}
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

		// game data
		$bdate = date("Y-m-d", strtotime("now - 30 days"));
		$edate = date("Y-m-d", strtotime("now"));
		$countTotalGames = Activity::countPlayerGames($memberInfo->member_id, $bdate, $edate);
		$countAODGames = Activity::countPlayerAODGames($memberInfo->member_id, $bdate, $edate);
		$allGames = Activity::findAllGames($memberInfo->member_id);
		$pctAod = ($countTotalGames>0) ? $countAODGames * 100 / $countTotalGames : 0;

		if ($platoonInfo->id != 0) {
			$platoonInfo->link = "<li><a href='divisions/{$divisionInfo->short_name}/{$platoonInfo->number}'>{$platoonInfo->name}</a></li>";
			$platoonInfo->item = "<li class='list-group-item text-right'><span class='pull-left'><strong>Platoon: </strong></span> <span class='text-muted'>{$platoonInfo->name}</span></li>";
		}

		Flight::render('member/alerts', array('memberInfo' => $memberInfo), 'alerts');
		Flight::render('member/member_data', array('memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo), 'member_data');
		Flight::render('member/profile', array('user' => $user, 'member' => $member, 'division' => $division, 'memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo, 'totalGames' => $countTotalGames, 'aodGames' => $countAODGames, 'games' => $allGames, 'pctAod' => $pctAod), 'content');
		Flight::render('layouts/application', array('js' => 'member', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'platoons' => $platoons));
		
	}

	public static function _edit() {

		$user = User::find($_SESSION['userid']);
		$member = Member::profileData($_POST['member_id']);
		$platoons = Platoon::find_all($member->game_id);
		$platoon_id = (($user->role >= 2) && (!User::isDev($user->id))) ? $member->platoon_id : false; 
		$squadleadersArray = Platoon::SquadLeaders($member->game_id, $platoon_id);
		$positionsArray = Position::find_all();

		Flight::render('modals/view_member', array('user' => $user, 'member' => $member, 'platoons' => $platoons, 'squadleadersArray' => $squadleadersArray, 'positionsArray' => $positionsArray));

	}


	public static function _doUpdateMember() {

		$user = User::find($_SESSION['userid']);
		$params = array("id" => $_POST['uid'], "forum_name" => $_POST['fname'], 'battlelog_name' => $_POST['blog'], 'member_id' => $_POST['mid'], 'recruiter' => $_POST['recruiter']);
		$member = Member::profileData($params['member_id']);

		// post values based on role since we can't be sure 
		// a hidden form element wasn't tampered with
		if ($user->role > 1 || User::isDev($user->id)) { $params = array_merge($params, array("squad_leader_id" => $_POST['squad'], "position_id" => $_POST['position'])); }
		if ($user->role > 2 || User::isDev($user->id)) {	$params = array_merge($params, array("platoon_id" => $_POST['platoon'])); }


		// only continue if we have permission to edit the user
		if (User::canEdit($params['member_id'], $user, $member) == true) {

			// attempt to fetch battlelog id from bf stats (bf4 or hardline)
			$battlelogId = Member::getBattlelogId($params['battlelog_name']);
			if (!$battlelogId['error']) {
				$params = array_merge($params, array("battlelog_id" => $battlelogId['id']));
				$result = Member::modify($params);
				$data = array('success' => true, 'message' => "Member information updated!");
			} else {
				$data = array('success' => false, 'message' => 'Battlelog name invalid', 'battlelog' => true);
			}

		} else {
			$data = array('success' => false, 'message' => 'You do not have permission to modify this player.');
		}

		// print out a pretty response
		echo(json_encode($data));
	}

	public static function _doValidateMember() {

		if (Member::exists($_POST['member_id'])) {
			$data = array('success' => false, 'message' => 'A division member already exists with that member id', 'memberExists' => true);
		} else {
			$data = array('success' => true);
		}
		echo(json_encode($data));

	}

	public static function _doAddMember() {
		$params = array('member_id'=>$_POST['member_id'],'forum_name'=>$_POST['forum_name'], 'platoon_id'=>$_POST['platoon_id'], 'squad_leader_id'=>$_POST['squad_leader_id'], 'battlelog_name'=>$_POST['battlelog_name'], 'game_id'=>$_POST['game_id']);
		$data = array('success' => false, 'message' => "Something went wrong.");
		echo(json_encode($data));
	}

	public static function _doUpdateFlag() {

		$action = $_POST['action'];
		$member_flagged = $_POST['id'];
		$flagged_by = $_POST['member_id'];

		if ($action == 1) {
			InactiveFlagged::add($member_flagged, $flagged_by);
			$data = array('success' => true, 'message' => 'Member {$member_flagged} flagged for removal.');
		} else {
			InactiveFlagged::remove($member_flagged);
			$data = array('success' => true, 'message' => 'Member {$member_flagged} no longer flagged for removal.');
		}

		echo(json_encode($data));
	}


	public static function _modify() {}
	public static function _delete() {}

}
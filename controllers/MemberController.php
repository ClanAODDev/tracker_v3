<?php

class MemberController {

	public static function _profile($id) {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$platoons = Platoon::find_all($member->game_id);

		// profile data
		$memberInfo = Member::profileData(intval($id));

		if (property_exists($memberInfo, 'id')) {

			$divisionInfo = Division::findById(intval($memberInfo->game_id));
			$platoonInfo = Platoon::findById(intval($memberInfo->platoon_id));
			$recruits = Member::findRecruits($memberInfo->member_id);
			$gamesPlayed = MemberGame::get($memberInfo->id);

			// game data
			$bdate = date("Y-m-d", strtotime("tomorrow - 30 days"));
			$edate = date("Y-m-d", strtotime("tomorrow"));
			$countTotalGames = Activity::countPlayerGames($memberInfo->member_id, $bdate, $edate);
			$countAODGames = Activity::countPlayerAODGames($memberInfo->member_id, $bdate, $edate);
			$allGames = Activity::find_allGames($memberInfo->member_id);
			$pctAod = ($countTotalGames>0) ? $countAODGames * 100 / $countTotalGames : 0;

			if (property_exists($platoonInfo, 'id')) {
				$platoonInfo->link = "<li><a href='divisions/{$divisionInfo->short_name}/platoon/{$platoonInfo->number}'>{$platoonInfo->name}</a></li>";
				$platoonInfo->item = "<li class='list-group-item text-right'><span class='pull-left'><strong>Platoon: </strong></span> <span class='text-muted'>{$platoonInfo->name}</span></li>";
			}

			// if squad leader, show recruits
			if ($memberInfo->position_id == 5) {
				Flight::render('member/sl-personnel', array('member' => $memberInfo), 'sl_personnel');
			}

			Flight::render('member/alerts', array('memberInfo' => $memberInfo), 'alerts');
			Flight::render('member/recruits', array('recruits' => $recruits), 'recruits');
			Flight::render('member/member_data', array('memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo), 'member_data');
			Flight::render('member/activity', array('totalGames' => $countTotalGames, 'aodGames' => $countAODGames, 'games' => $allGames, 'pctAod' => $pctAod), 'activity');
			Flight::render('member/history', array(), 'history');
			Flight::render('member/profile', array('user' => $user, 'member' => $member, 'memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo, 'gamesPlayed' => $gamesPlayed), 'content');
			Flight::render('layouts/application', array('js' => 'member', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

		} else {
			Flight::redirect('/404', 404);
		}
		
	}

	public static function _edit() {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::profileData($_POST['member_id']);
		$platoons = Platoon::find_all($member->game_id);

		// if user role lower than plt ld, show only own platoon's squads
		$platoon_id = (($user->role >= 2) && (!User::isDev())) ? $member->platoon_id : false; 
		$squads = Squad::findAll($member->game_id, $platoon_id);

		$positionsArray = Position::find_all();
		$rolesArray = Role::find_all();
		$memberGames = MemberGame::get($member->id);

		if (User::isUser($member->id)) {
			$userInfo = User::findByMemberId($member->id);
		} else {
			$userInfo = NULL;
		}

		Flight::render('modals/view_member', array('user' => $user, 'member' => $member, 'userInfo' => $userInfo, 'platoons' => $platoons, 'memberGames' => $memberGames, 'squads' => $squads, 'positionsArray' => $positionsArray, 'rolesArray' => $rolesArray));

	}

	public static function _doUpdateMember() {

		// user attempting to make changes
		$respUser = User::find(intval($_SESSION['userid']));
		$respMember = Member::find(intval($_SESSION['memberid']));

		// member being changed
		$memberData = $_POST['memberData'];
		$member = Member::profileData($memberData['member_id']);
		$user = User::findByMemberId(Member::findId($memberData['member_id']));

		// only update values allowed by role
		if (!User::isDev()) {
			if ($respUser->role < 2) { unset($memberData['squad_leader_id'], $memberData['position'], $memberData['platoon_id']);  }
			if ($respUser->role < 3) { unset($memberData['platoon_id']); }
		}

		// only continue if we have permission to edit the user
		if (User::canEdit($memberData['member_id'], $respUser, $member) == true) {

			// don't log if user edits their own profile
			if ($respMember->member_id != $member->member_id) {
				UserAction::create(array('type_id'=>3,'date'=>date("Y-m-d H:i:s"),'user_id'=>$respMember->member_id,'target_id'=>$member->member_id));
			}

			// validate recruiter
			if ($memberData['recruiter'] != 0 && !Member::exists($memberData['recruiter'])) {
				$data = array('success' => false, 'message' => "Recruiter id is invalid.");
			} else {
				// update member info
				Member::modify($memberData);
			}		

			// update games
			if (isset($_POST['played_games'])) {
				$games = $_POST['played_games'];
				foreach ($games as $game) {
					$params = new stdClass();
					$params->member_id = $member->id;
					$params->game_id = $game;
					MemberGame::add($params);
				}
			}

			// update user
			if (isset($_POST['userData'])) {
				$userData = $_POST['userData'];

				// wish I had a better way to do this... yuck
				$userData['developer'] = (isset($userData['developer'])) ? $userData['developer'] : 0;
				$userData['debug'] = (isset($userData['debug'])) ? $userData['debug'] : 0;

				if (!User::isDev()) {
					unset($userData['developer'], $userData['debug']);
				}

				if ($userData['role'] >= $respUser->role && !User::isDev()) {
					$data = array('success' => false, 'message' => "You are not authorized to make that change.");	
				} else {
					User::modify($userData);
				}
			}

		} else {
			$data = array('success' => false, 'message' => 'You do not have permission to modify this player.');
		}

		if (!isset($data['success'])) {
			$data = array('success' => true, 'message' => "Member information updated!");	
		}

		// print out a pretty response
		echo(json_encode($data));
	}

	public static function _doValidateMember() {

		if (Member::exists($_POST['member_id'])) {
			$data = array('success' => false, 'memberExists' => true);
		} else {
			$data = array('success' => true);
		}
		echo(json_encode($data));
	}

	public static function _doAddMember() {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$platoon_id = ($user->role >= 3 || User::isDev()) ? $_POST['platoon_id'] : $member->platoon_id;
		$squad_id = ($user->role >= 2 || User::isDev()) ? $_POST['squad_id'] : Squad::mySquadId($member->id)->id;
		$position_id = 6;

		$newParams = array('member_id'=>$_POST['member_id'],'forum_name'=>$_POST['forum_name'], 'battlelog_name'=>$_POST['battlelog_name'], 'recruiter'=>$member->member_id, 'game_id'=>$_POST['game_id'], 'status_id'=>999, 'join_date'=>date("Y-m-d H:i:s"), 'rank_id'=>1, 'battlelog_id'=>0, 'platoon_id' => $platoon_id, 'squad_id' => $squad_id, 'position_id' => $position_id);

		$existingParams = array('forum_name'=>$_POST['forum_name'], 'battlelog_name'=>$_POST['battlelog_name'], 'game_id'=>$_POST['game_id'], 'status_id'=>999, 'join_date'=>date("Y-m-d H:i:s"), 'rank_id'=>1, 'battlelog_id'=>0, 'platoon_id' => $platoon_id, 'squad_id' => $squad_id, 'position_id' => $position_id);

		if (Member::exists($_POST['member_id'])) {

			$existingParams = array_merge($existingParams, array('id' => Member::findId($_POST['member_id'])));
			Member::modify($existingParams);
			$insert_id = Flight::aod()->insert_id;
			UserAction::create(array('type_id'=>10,'date'=>date("Y-m-d H:i:s"),'user_id'=>$member->member_id,'target_id'=>$newParams['member_id']));
			$data = array('success' => true, 'message' => "Existing member successfully updated!");

		} else {

			Member::create($newParams);
			$insert_id = Flight::aod()->insert_id;
			UserAction::create(array('type_id'=>1,'date'=>date("Y-m-d H:i:s"),'user_id'=>$member->member_id,'target_id'=>$newParams['member_id']));
			$data = array('success' => true, 'message' => "Member successfully added!");

		}

		if (isset($_POST['played_games'])) {
			$games = $_POST['played_games'];
			foreach ($games as $game) {
				$params = new stdClass();
				$params->member_id = $insert_id;
				$params->game_id = $game;
				MemberGame::add($params);
			}
		}

		echo(json_encode($data));
	}

	public static function _doUpdateFlag() {

		$action = $_POST['action'];
		$member_flagged = $_POST['id'];
		$flagged_by = $_POST['member_id'];

		if ($action == 1) {

			InactiveFlagged::add($member_flagged, $flagged_by);
			$data = array('success' => true, 'message' => 'Member {$member_flagged} flagged for removal.');
			UserAction::create(array('type_id'=>4,'date'=>date("Y-m-d H:i:s"),'user_id'=>$flagged_by,'target_id'=>$member_flagged));

		} else {

			InactiveFlagged::remove($member_flagged);
			$data = array('success' => true, 'message' => 'Member {$member_flagged} no longer flagged for removal.');
			UserAction::create(array('type_id'=>6,'date'=>date("Y-m-d H:i:s"),'user_id'=>$flagged_by,'target_id'=>$member_flagged));
		}

		echo(json_encode($data));
	}

	public static function _doKickFromAod() {
		$user = Member::findMemberId($_SESSION['memberid']);
		$id = $_POST['id'];
		Member::kickFromAod($id);
		UserAction::create(array('type_id'=>2,'date'=>date("Y-m-d H:i:s"),'user_id'=>$user,'target_id'=>$id));
	}


	// api stuff

	public static function _getMemberData($game) {
		$membersQ = "SELECT battlelog_name, forum_name FROM member WHERE status_id = 1 AND game_id = {$game} ORDER BY forum_name ASC";
		$ptmembersQ = "SELECT battlelog_name, forum_name FROM part_timers WHERE game_id = {$game} ORDER BY forum_name ASC";


		$members = arrayToObject(Flight::aod()->sql($membersQ)->many());
		$partTimers = arrayToObject(Flight::aod()->sql($ptmembersQ)->many());

		$out = "<h1>Member list</h1><hr />";

		foreach ($members as $member) {
			$out .= "{$member->forum_name} - {$member->battlelog_name} <br />";
		}

		$out .= "<h1>Partimers list</h1><hr />";

		foreach($partTimers as $member) {
			$out .= "{$member->forum_name} - {$member->battlelog_name} <br />";
		}
		echo $out;
	}

}
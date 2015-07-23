<?php

class MemberController {

	public static function _profile($id) {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$platoons = Platoon::find_all($member->game_id);

		// profile data
		$memberInfo = Member::findByMemberId(intval($id));

		if (property_exists($memberInfo, 'id')) {

			$divisionInfo = Division::findById(intval($memberInfo->game_id));
			$platoonInfo = Platoon::findById(intval($memberInfo->platoon_id));
			$recruits = Member::findRecruits($memberInfo->member_id);
			$gamesPlayed = MemberGame::get($memberInfo->id);
			$aliases = MemberHandle::findByMemberId($memberInfo->id);

			// game data
			$bdate = date("Y-m-d", strtotime("tomorrow - 30 days"));
			$edate = date("Y-m-d", strtotime("tomorrow"));
			$totalGames = Activity::countPlayerGames($memberInfo->member_id, $bdate, $edate);
			$aodGames = Activity::countPlayerAODGames($memberInfo->member_id, $bdate, $edate);
			$games = Activity::find_allGames($memberInfo->member_id);
			$pctAod = ($totalGames>0) ? $aodGames * 100 / $totalGames : 0;

			switch ($divisionInfo->short_name) {
				case "bf":
				$activity = array('totalGames' => $totalGames, 'aodGames' => $aodGames, 'games' => $games, 'pctAod' => $pctAod);
				break;
				case "wg":
				$activity = array();
				break;
				default:
				$activity = array();
				break;
			}

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
			Flight::render('member/member_data', array('memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo, 'aliases' => $aliases), 'member_data');
			Flight::render('member/activity/'.$divisionInfo->short_name, $activity, 'activity');
			Flight::render('member/history', array(), 'history');
			Flight::render('member/profile', array('user' => $user, 'member' => $member, 'memberInfo' => $memberInfo, 'divisionInfo' => $divisionInfo, 'platoonInfo' => $platoonInfo, 'gamesPlayed' => $gamesPlayed), 'content');
			Flight::render('layouts/application', array('js' => 'member', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

		} else {
			Flight::redirect('/404', 404);
		}
		
	}

	public static function _edit() {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::findByMemberId($_POST['member_id']);
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
		$member = Member::findByMemberId($memberData['member_id']);
		$user = User::findByMemberId(Member::findId($memberData['member_id']));

		// only update values allowed by role
		if (!User::isDev()) {
			if ($respUser->role < 2) { unset($memberData['squad_id'], $memberData['position_id'], $memberData['platoon_id']);  }
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
			// validate squad leader / squad_id setting
			} else if ($respMember->member_id != $member->member_id && $memberData['position_id'] == 5 && $memberData['squad_id'] != 0 ) {
				$data = array('success' => false, 'message' => "Squad leaders cannot be in a squad.");
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

			// update aliases
			if (isset($_POST['userAliases'])) {
				$aliases = $_POST['userAliases'];
				//var_dump($aliases);die;

				foreach($aliases as $type => $value) {

					$type = Handle::findByName($type)->id;

					if ($value != '') {

						$params = array('member_id' => $memberData['id'], 'handle_type' => $type, 'handle_value' => $value, 'handle_account_id' => 0);
						$id = MemberHandle::hasAlias($type, $memberData['id']);

						if ($id) {

							$params['id'] = $id;
							MemberHandle::modify($params);

						} else {
							MemberHandle::add($params);
						}
					}
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
		$member_id = $_POST['member_id'];
		if (Member::exists($member_id)) {
			$data = array('success' => false, 'memberExists' => true);
		} else if (abs($member_id - Member::getLastRct()) > 200) {
			$data = array('success' => false, 'invalidId' => true);
		} else {
			$data = array('success' => true);
		}
		echo(json_encode($data));
	}

	public static function _doAddMember() {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$division = Division::findById($member->game_id);
		$platoon_id = ($user->role >= 3 || User::isDev()) ? $_POST['platoon_id'] : $member->platoon_id;
		$squad_id = ($user->role >= 2 || User::isDev()) ? $_POST['squad_id'] : (Squad::mySquadId($member->id)) ?: 0;
		$position_id = 6;

		$newParams = array('member_id'=>$_POST['member_id'],'forum_name'=>$_POST['forum_name'], 'recruiter'=>$member->member_id, 'game_id'=>$_POST['game_id'], 'status_id'=>999, 'join_date'=>date("Y-m-d H:i:s"), 'rank_id'=>1, 'platoon_id' => $platoon_id, 'squad_id' => $squad_id, 'position_id' => $position_id);

		$existingParams = array('forum_name'=>$_POST['forum_name'], 'game_id'=>$_POST['game_id'], 'status_id'=>999, 'join_date'=>date("Y-m-d H:i:s"), 'rank_id'=>1, 'platoon_id' => $platoon_id, 'squad_id' => $squad_id, 'position_id' => $position_id);

		if (Member::exists($_POST['member_id'])) {

			$existingParams = array_merge($existingParams, array('id' => Member::findId($_POST['member_id'])));
			$insert_id = Member::modify($existingParams);
			UserAction::create(array('type_id'=>10,'date'=>date("Y-m-d H:i:s"),'user_id'=>$member->member_id,'target_id'=>$newParams['member_id']));
			$data = array('success' => true, 'message' => "Existing member successfully updated!");

		} else {

			$insert_id = Member::create($newParams);
			UserAction::create(array('type_id'=>1,'date'=>date("Y-m-d H:i:s"),'user_id'=>$member->member_id,'target_id'=>$newParams['member_id']));
			$data = array('success' => true, 'message' => "Member successfully added!");
		}

		if ($insert_id != 0) {

			if (isset($_POST['played_games'])) {
				$games = $_POST['played_games'];
				foreach ($games as $game) {
					$memberGame = new stdClass();
					$memberGame->member_id = $insert_id;
					$memberGame->game_id = $game;
					MemberGame::add($memberGame);
				}
			}

			if (isset($_POST['ingame_name'])) {
				$ingame_name = $_POST['ingame_name'];
				$handle = new stdClass();
				$handle->member_id = $insert_id;
				$handle->handle_type = $division->primary_handle;
				$handle->handle_value = $ingame_name;
				MemberHandle::add($handle);
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
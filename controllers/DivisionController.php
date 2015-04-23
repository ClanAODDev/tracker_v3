<?php

class DivisionController {
	
	public static function _index($div) {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));
		$division_leaders = Division::findDivisionLeaders($division->id);

		$topListMonthly = Activity::topList30DaysByDivision($division->id);
		$topListToday = Activity::topListTodayByDivision($division->id);

		$personnelData = new stdClass();
		$personnelData->recruitsThisMonth = Division::recruitsThisMonth($division->id)->count;
		$personnelData->totalCount = Division::totalCount($division->id)->count;

		Flight::render('division/statistics', array('monthly' => $topListMonthly, 'daily' => $topListToday, 'personnelData' => $personnelData), 'statistics');
		Flight::render('division/main', array('user' => $user, 'member' => $member, 'division' => $division, 'division_leaders' => $division_leaders), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'js' => 'division'));
	}

	public static function _manage_inactives() {
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));

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
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'js' => 'manage'));

	}

	public static function _manage_loas() {

		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));

		Flight::render('manage/loas', array('division' => $division, 'member' => $member, 'user' => $user), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'js' => 'manage'));
		
	}

	public static function _generateDivisionStructure() {
		$member = Member::find($_SESSION['username']);
		$division_structure = DivisionStructure::generate($member);
		Flight::render('modals/division_structure', array('division_structure' => $division_structure));
	}

	public static function _updateLoa() {

		$user = User::find($_SESSION['userid']);
		$id = $_POST['id'];

		if (isset($_POST['remove'])) {
			if ($user->role < 2) {
				$data = array('success' => false, 'message' => "You are not authorized to perform that action.");
			} else {
				if ( $revoked = ( LeaveOfAbsence::remove($id) ) ) {
					if ( $revoked['success'] == false ) {
						$data = array('success' => false, 'message' => $revoked['message']);
					} else {
						$data = array('success' => true, 'message' => "Leave of absence successfully removed.");
					}
				}
			}
		} else if (isset($_POST['approve'])) {
			if ($user->role < 2) {
				$data = array('success' => false, 'message' => "You are not authorized to perform that action.");
			} else {
				// is LOA member id the same as user member id?
				if ($member_id != $id) {
					if ( $approved = LeaveOfAbsence::approve($id, $member_id) ) {
						$data = array('success' => true, 'message' => "Leave of absence successfully approved.");
					} else {
						$data = array('success' => false, 'message' => $loa['message']);
					}
				} else {
					$data = array('success' => false, 'message' => 'You can\'t approve your own leave of absence!');
				}
			}
		} else {
			$data = NULL;
			$date = date('Y-m-d', strtotime($_POST['date']));
			$reason = $_POST['reason'];
			$comment = htmlentities($_POST['comment'], ENT_QUOTES);
			$name = Member::findForumName($id);
			if ($name != false) {
				if (strtotime($date) > strtotime('now')) {
					if ( $loa = ( LeaveOfAbsence::add($id, $date, $reason, $comment) ) ) {
						if ( $loa['success'] == false ) {
							$data = array('success' => false, 'message' => $loa['message']);
						} else {
							$data = array('success' => true, 'Request successfully submitted!', 'id' => $id, 'name' => $name, 'date' => date('M d, Y', strtotime($date)), 'reason' => $reason);
						}
					} else {
						$data = array('success' => false, 'message' => $loa['message']);
					}
				} else {
					$data = array('success' => false, 'message' => "Date cannot be before today's date.");
				}
			} else {
				$data = array('success' => false, 'message' => 'Invalid member id');
			}
		}
		echo json_encode($data);
	}

}
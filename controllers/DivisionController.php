<?php

class DivisionController {
	
	public static function _index($div) {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findByName(strtolower($div));
		$division_leaders = Division::findDivisionLeaders($division->id);

		$topListMonthly = Activity::topList30DaysByDivision($division->id);
		$topListToday = Activity::topListTodayByDivision($division->id);

		$personnelData = new stdClass();
		$personnelData->recruitsThisMonth = Division::recruitsThisMonth($division->id)->count;
		$personnelData->totalCount = Division::totalCount($division->id)->count;

		Flight::render('division/main/statistics', array('monthly' => $topListMonthly, 'daily' => $topListToday, 'personnelData' => $personnelData), 'statistics');
		Flight::render('division/main/index', array('user' => $user, 'member' => $member, 'division' => $division, 'division_leaders' => $division_leaders), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'js' => 'division'));
	}

	public static function _manage_inactives() {
		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();

		switch ($user->role) {
			case User::isDev($user->id): $type = "div"; $id = $member->game_id; break;
			case 1: $type = "sqd"; $id = $member->member_id; break;
			case 2:
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

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));
		$tools = Tool::find_all($user->role);
		$divisions = Division::find_all();
		$division = Division::findById(intval($member->game_id));

		Flight::render('manage/loas', array('division' => $division, 'member' => $member, 'user' => $user), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions, 'js' => 'manage'));
		
	}

	public static function _generateDivisionStructure() {
		$member = Member::find(intval($_SESSION['memberid']));
		$division_structure = DivisionStructure::generate($member->game_id);
		Flight::render('modals/division_structure', array('division_structure' => $division_structure));
	}

	public static function _updateLoa() {

		$user = User::find(intval($_SESSION['userid']));
		$member = Member::find(intval($_SESSION['memberid']));

		//var_dump($member);die;

		if (isset($_POST['remove'])) {
			$loa_id = $_POST['loa_id'];

			if ($user->role < 2) {
				$data = array('success' => false, 'message' => "You are not authorized to perform that action.");
			} else {
				$revoked = LeaveOfAbsence::delete($loa_id);
				$data = array('success' => true, 'message' => "Leave of absence successfully removed.");
			}

		} else if (isset($_POST['approve'])) {

			$loa_id = $_POST['loa_id'];

			if ($user->role < 2) {
				$data = array('success' => false, 'message' => "You are not authorized to perform that action.");
			} else {
				$approved = LeaveOfAbsence::approve($loa_id, $member->member_id);
				$data = array('success' => true, 'message' => "Leave of absence successfully approved.");
			}

		} else {

			$date = date('Y-m-d', strtotime($_POST['date']));
			$reason = $_POST['reason'];
			$comment = htmlentities($_POST['comment'], ENT_QUOTES);
			$name = Member::findForumName($_POST['id']);

			if ($name != false) {
				if (strtotime($date) > strtotime('now')) {
					LeaveOfAbsence::add($_POST['id'], $date, $reason, $comment, $member->game_id);
					$data = array('success' => true, 'Request successfully submitted!', 'id' => $_POST[
						'id'], 'name' => $name, 'date' => date('M d, Y', strtotime($date)), 'reason' => $reason);
				} else {
					$data = array('success' => false, 'message' => "Date cannot be before today's date.");
				}
			} else {
				$data = array('success' => false, 'message' => 'The member id you provided appears to be invalid.');
			}
			
		}

		echo json_encode($data);
	}

}
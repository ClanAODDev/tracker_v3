<?php
class RecruitingController
{
	public static function _index()
	{
		$user = User::find($_SESSION['userid']);
		$member = Member::find($_SESSION['username']);
		$tools = Tool::getToolsByRole($user->role);
		$divisions = Division::find_all();
		$division = Division::find($member->game_id);
		Flight::render('recruiting/index', array(), 'content');
		Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
	}
}
?>
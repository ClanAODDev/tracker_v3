<?php

class ReportController
{

	public static function _retentionNumbers()
	{
		$user = User::find(intval($_SESSION['userid']));

		if ($user->role >= 3 || User::isDev()) {
			$member = Member::find(intval($_SESSION['memberid']));
			$tools = Tool::find_all($user->role);
			$divisions = Division::find_all();
			$division = Division::findById(intval($member->game_id));

			$recruited = Report::recruitedLast30days($member->game_id);
			$removed = Report::removedLast30days($member->game_id);
			$monthlyBreakdown = Report::recruitingWeekly($member->game_id);
			$byTheMonth = Report::recruitingByTheMonth($member->game_id);
			$js = 'report';

			Flight::render('reports/retention', compact('recruited', 'removed', 'js', 'monthlyBreakdown', 'byTheMonth'), 'content');
			Flight::render('layouts/application', array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
		} else {
			Flight::redirect('/404', 404);
		}
	}

}

<?php

class Report extends Application
{
	public static function findRecruitsThisMonth($game_id)
	{
		$sql = "SELECT forum_name, join_date, last_activity FROM ".Member::$table." WHERE rank_id = 1 AND status_id = 1 AND game_id = {$game_id} AND join_date <= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND last_activity >= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY)";
		$params = Flight::aod()->sql($sql)->many();
		return objectToArray($params);
	}

	public static function recruitedLast30days($game_id)
	{
		$sql = "SELECT * FROM
		(
			SELECT forum_name, count(*) recruited, m.member_id FROM user_actions a
			LEFT JOIN member m ON a.user_id = m.member_id
			WHERE a.date BETWEEN CURDATE() - INTERVAL 30 DAY AND NOW()
			AND m.game_id = {$game_id} AND type_id = 1 GROUP BY user_id
		) t
		ORDER BY
		recruited DESC;";

		$params = Flight::aod()->sql($sql)->many();
		return objectToArray($params);
	}

	public static function removedLast30days($game_id)
	{
		$sql = "SELECT forum_name, date FROM user_actions
				LEFT JOIN member ON target_id = member_id
				WHERE date BETWEEN CURDATE() - INTERVAL 30 DAY AND NOW()
				AND type_id = 2 AND game_id = {$game_id}";

		$params = Flight::aod()->sql($sql)->many();
		return objectToArray($params);
	}




}

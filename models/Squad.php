<?php

class Squad {
	
	public static function find($mid, $division_structure_ordering = false) {
		$sql = "SELECT member.id, member.forum_name, member.member_id, member.last_activity, member.battlelog_name, member.bf4db_id, member.forum_posts, member.join_date, member.rank_id, rank.abbr as rank FROM `member` LEFT JOIN `rank` on member.rank_id = rank.id WHERE member.squad_leader_id = {$mid} AND (member.status_id = 1 OR member.status_id = 999) AND member.position_id = 6";

		if ($division_structure_ordering) {
			$sql .= " ORDER BY member.rank_id DESC, member.join_date DESC ";
		} else {
			$sql .= " ORDER BY member.last_activity ASC ";
		}

		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

}
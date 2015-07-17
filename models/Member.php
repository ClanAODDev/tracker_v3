<?php

class Member extends Application {

	public $id;
	public $forum_name;
	public $member_id;
	public $battlelog_id;	
	public $battlelog_name;	
	public $platoon_id;
	public $rank_id;
	public $position_id;
	public $squad_id;
	public $status_id;
	public $game_id;
	public $join_date;
	public $last_forum_login;
	public $last_activity;
	public $last_forum_post;
	public $forum_posts;
	public $recruiter;

	static $table = 'member';
	static $id_field = 'id';
	static $name_field = 'forum_name';

	public static function findByName($forum_name) {
		return (object) self::find($forum_name);
	}

	public static function exists($member_id) {
		$params = self::find(array('member_id' => $member_id));
		if (count($params)) {
			return true;
		} else {
			return false;
		}
	}

	public static function findId($member_id) {
		return self::find(array('member_id' => $member_id))->id;
	}

	public static function findMemberId($id) {
		return self::find($id)->member_id;
	}

	public static function find_all($game_id) {
		return self::find(array('game_id' => $game_id, 'status_id' => 1));
	}

	public static function search($name) {
		$params = Flight::aod()->sql("SELECT * FROM member LEFT JOIN rank ON member.rank_id = rank.id WHERE `forum_name` LIKE '%{$name}%' OR `battlelog_name` LIKE '%{$name}%' ORDER BY member.rank_id DESC LIMIT 25")->many();
		return $params;
	}

	public static function findById($userId) {
		return (object) self::find($userId);
	}

	public static function findByMemberId($member_id) {
		return (object) self::find(array('member_id' => $member_id));
	}

	public static function profileData($member_id) {
		return (object) Flight::aod()->sql("SELECT member.id, forum_name, member.member_id, battlelog_name, rank_id, platoon_id, position_id, squad_id, status_id, game_id, join_date, recruiter, last_forum_login, last_activity, member.game_id, last_forum_post, forum_posts FROM member 
			LEFT JOIN users ON users.member_id = member.id 
			LEFT JOIN divisions ON divisions.id = member.game_id")->one();
	}

	public static function findForumName($member_id) {
		$params = self::find(array('member_id' => $member_id));
		return $params->forum_name;
	}

	public static function findRecruits($member_id) {
		return Flight::aod()->from(self::$table)
		->sortDesc(array('rank_id'))
		->where(array('recruiter' => $member_id, 'position_id' => 6))
		->join('rank', array('rank.id' => 'member.rank_id'))
		->select()->many();
	}

	public static function avatar($email, $type = "thumb") {
		$forum_img = self::GetGravatarUrl($email);
		$unknown   = "assets/images/blank_avatar.jpg";
		return "<img src='{$forum_img}' class='img-thumbnail avatar-{$type}' />";
	}

	public static function GetGravatarUrl( $email, $size = 128, $type = 'identicon', $rating = 'pg' ) {
		$gravatar = sprintf( 'http://www.gravatar.com/avatar/%s?d=%s&s=%d&r=%s',
			md5( $email ), $type, $size, $rating );
		return $gravatar;
	}

	public static function isOnLeave($member_id) {
		$params = LeaveOfAbsence::hasLOA($member_id);
		if (count($params)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isFlaggedForInactivity($member_id) {
		$params = Flight::aod()->sql("SELECT * FROM inactive_flagged WHERE `member_id`={$member_id}")->one();
		if (count($params)) {
			return true;
		} else {
			return false;
		}
	}

	public static function findInactives($id, $type, $flagged=false) {
		$sql = "SELECT member.forum_name, member.member_id, member.last_activity, member.battlelog_name, inactive_flagged.flagged_by, member.forum_posts, member.join_date, platoon.number as plt_number, platoon.name as plt_name 
		FROM `member` 
		
		LEFT JOIN `rank` ON member.rank_id = rank.id  
		LEFT JOIN `inactive_flagged` ON member.member_id = inactive_flagged.member_id 
		LEFT JOIN platoon on member.platoon_id = platoon.id 

		WHERE (status_id = 1) AND (last_activity < CURDATE() - INTERVAL 30 DAY) AND 
		member.member_id NOT IN (SELECT member_id FROM loa) AND ";

		switch ($type) {
			case "sqd": $args = "member.squad_leader_id = {$id}"; break;
			case "plt": $args = "member.platoon_id = {$id}"; break;
			case "div": $args = "member.game_id = {$id}"; break;
			default: $args = "member.game_id = {$id}"; break;
		}

		if ($flagged) {
			$sql .= "(member.member_id IN (SELECT member_id FROM inactive_flagged)) AND ";
			$sql .= $args . " ORDER BY inactive_flagged.flagged_by";
		} else {
			$sql .= "(member.member_id NOT IN (SELECT member_id FROM inactive_flagged)) AND ";
			$sql .= $args . " ORDER BY member.platoon_id, member.last_activity ASC";
		}

		return Flight::aod()->sql($sql)->many();
	}

	public static function getLastRct() {
		$params = (object) Flight::aod()->from('Member')->sortDesc('member_id')->where(array('status_id' => 1))->select('member_id')->one();
		return $params->member_id;
	}

	public static function create($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->save($params);
	}

	public static function kickFromAod($id) {
		$member = self::find(array('member_id' => $id));
		$member->status_id = 4;
		$member->save();
	}

	public static function modify($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->update($params);
	}

}




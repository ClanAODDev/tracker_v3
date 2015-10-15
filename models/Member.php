<?php

class Member extends Application {

	public $id;
	public $forum_name;
	public $member_id;
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
		$conditions = array('forum_name %' => "%{$name}%");
		$params = Flight::aod()->from('member')
		->limit(20)
		->sortDesc('rank_id')
		->join('rank', array('rank.id' => 'rank_id'))
		->where($conditions)->select()->many();
		return $params;
	}

	public static function findById($userId) {
		return (object) self::find($userId);
	}

	public static function findByMemberId($member_id) {
		return (object) self::find(array('member_id' => $member_id));
	}

	public static function findForumName($member_id) {
		$params = self::find(array('member_id' => $member_id));
		if (count($params)) {
			return $params->forum_name;
		} else {
			return false;
		}
	}

	public static function createAODlink($args) {
		$string = "[profile={$args['member_id']}]{$args['forum_name']}[/profile]";
		return $string;
	}

	public static function findRecruits($member_id, $platoon_id = false, $squad_id = false, $division_structure = false) {
		$conditions = array('recruiter' => $member_id, 'position_id' => 6);
		if ($platoon_id) $conditions = array_merge($conditions, array('platoon_id' => $platoon_id));
		if ($squad_id) $conditions = array_merge($conditions, array('squad_id' => $squad_id));
		if ($division_structure) $conditions = array_merge($conditions, array('status_id @' => array(1,3,999)));
		return Flight::aod()->from(self::$table)->sortDesc(array('rank_id'))->where($conditions)->select()->many();
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
		$params = Flight::aod()->sql("SELECT * FROM ".InactiveFlagged::$table." WHERE `member_id`={$member_id}")->one();
		if (count($params)) {
			return true;
		} else {
			return false;
		}
	}

	public static function findInactives($id, $type, $flagged=false) {
		$sql = "SELECT m.forum_name, m.member_id, m.last_activity, i.flagged_by, m.forum_posts, m.join_date, p.number as plt_number, p.name as plt_name
		FROM ".Member::$table." m

		LEFT JOIN ".InactiveFlagged::$table." i ON m.member_id = i.member_id
		LEFT JOIN ".Platoon::$table." p on m.platoon_id = p.id

		WHERE (status_id = 1) AND (last_activity < CURDATE() - INTERVAL 30 DAY) AND
		m.member_id NOT IN (SELECT member_id FROM ".LeaveOfAbsence::$table.") AND ";

		switch ($type) {
			case "sqd": $args = "m.squad_id = {$id}"; break;
			case "plt": $args = "m.platoon_id = {$id}"; break;
			case "div": $args = "m.game_id = {$id}"; break;
			default: $args = "m.game_id = {$id}"; break;
		}

		if ($flagged) {
			$sql .= "(m.member_id IN (SELECT member_id FROM ".InactiveFlagged::$table.")) AND ";
			$sql .= $args . " ORDER BY i.flagged_by";
		} else {
			$sql .= "(m.member_id NOT IN (SELECT member_id FROM ".InactiveFlagged::$table.")) AND ";
			$sql .= $args . " ORDER BY m.platoon_id, m.last_activity ASC";
		}
		return Flight::aod()->sql($sql)->many();
	}

	public static function getLastRct() {
		$params = (object) Flight::aod()->from(Member::$table)->sortDesc('member_id')->where(array('status_id' => 1))->select('member_id')->one();
		return $params->member_id;
	}

	public static function create($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->save($params);
		return Flight::aod()->insert_id;
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
		return Flight::aod()->insert_id;
	}

}




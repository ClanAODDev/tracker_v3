<?php

class Member extends Application {

	public $id;
	public $forum_name;
	public $member_id;
	public $bf4db_id;
	public $battlelog_id;	
	public $platoon_id;
	public $rank_id;
	public $position_id;
	public $squad_leader_id;
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

	public static function find($forum_name) {
		$params = Flight::aod()->sql("SELECT * FROM member WHERE `forum_name`='{$forum_name}'")->one();
		return (object) $params;
	}

	public static function search($name) {
		$params = Flight::aod()->sql("SELECT * FROM member WHERE `forum_name` LIKE '%{$name}%' ORDER BY member.rank_id DESC LIMIT 25")->many();
		return $params;
	}

	public static function findById($mid) {
		$params = Flight::aod()->sql("SELECT * FROM member WHERE `member_id`={$mid}")->one();
		return (object) $params;
	}

	public static function findForumName($mid) {
		$params = Flight::aod()->sql("SELECT forum_name FROM member WHERE `member_id`={$mid}")->one();
		return $params['forum_name'];
	}

	public static function avatar($mid, $type = "thumb")
	{
		$forum_img = "http://www.clanaod.net/forums/image.php?type={$type}&u={$mid}";
		$unknown   = "assets/images/blank_avatar.jpg";
		list($width, $height) = getimagesize($forum_img);

		if ($width > 10 && $height > 10) {
			return "<img src='{$forum_img}' class='img-thumbnail avatar-{$type}' />";
		} else {
			return "<img src='{$unknown}' class='img-thumbnail avatar-{$type}' />";
		}

	}
}




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

	public static function findByName($forum_name) {
		return (object) self::find($forum_name);
	}

	public static function search($name) {
		$params = Flight::aod()->sql("SELECT * FROM member WHERE `forum_name` LIKE '%{$name}%' ORDER BY member.rank_id DESC LIMIT 25")->many();
		return $params;
	}

	public static function findById($userId) {
		return (object) self::find($userId);
	}

	public static function findByMemberId($member_id) {
		return (object) Flight::aod()->sql("SELECT * FROM member WHERE `member_id`={$member_id}")->one();
	}

	public static function profileData($member_id) {
		return (object) Flight::aod()->sql("SELECT member.id, rank.abbr as rank, position.desc as position, forum_name, member_id, battlelog_name, bf4db_id, rank_id, platoon_id, position_id, squad_leader_id, status_id, game_id, join_date, recruiter, last_forum_login, last_activity, member.game_id, last_forum_post, forum_posts, status.desc FROM member 
			LEFT JOIN users ON users.username = member.forum_name 
			LEFT JOIN games ON games.id = member.game_id
			LEFT JOIN position ON position.id = member.position_id
			LEFT JOIN rank ON rank.id = member.rank_id
			LEFT JOIN status ON status.id = member.status_id WHERE member.member_id = {$member_id}")->one();
	}

	public static function findForumName($member_id) {
		$params = Flight::aod()->sql("SELECT forum_name FROM member WHERE `member_id`={$member_id}")->one();
		return $params['forum_name'];
	}

	public static function avatar($member_id, $type = "thumb")
	{
		$forum_img = "http://www.clanaod.net/forums/image.php?type={$type}&u={$member_id}";
		$unknown   = "assets/images/blank_avatar.jpg";
		list($width, $height) = getimagesize($forum_img);

		if ($width > 10 && $height > 10) {
			return "<img src='{$forum_img}' class='img-thumbnail avatar-{$type}' />";
		} else {
			return "<img src='{$unknown}' class='img-thumbnail avatar-{$type}' />";
		}

	}

	public static function isOnLeave($member_id) {
		$params = Flight::aod()->sql("SELECT * FROM loa WHERE `member_id`={$member_id}")->one();
		if (count($params)) {
			return true;
		} else {
			return false;
		}
	}

	public static function modify($params) {

		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}

		$member->update($params);
	}

	public static function getBf4dbId($user) {
		$url = "http://bf4db.com/players?name={$user}";
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$html = curl_exec($ch);
		curl_close($ch);
		$regexp = "/<a href=\"\/players\/(\d*)\" class=\"personaName-medium\">" . $user . "<\/a>/iU";
		if (preg_match_all($regexp, $html, $matches)) {
			$len = count($matches[0]);
			for ($i = 0; $i < $len; $i++) {
				$id = $matches[1][$i];
			}
		}
		if (isset($id)): return $id; else: return false; endif;
	}

	/**
	 * fetch battlelog persona id (bf4, bfh)
	 * @param  string $battlelogName player's battlelog / ingame name
	 * @return array                 error code, id if successful, error message if failed
	 */
	public static function getBattlelogId($battlelogName) {
		// check for bf4 entry
		$url = "http://api.bf4stats.com/api/playerInfo?plat=pc&name={$battlelogName}";
		$headers = get_headers($url); 
		if (stripos($headers[0], '40') !== false || stripos($headers[0], '50') !== false) { 
			// check for hardline entry
			$url = "http://api.bfhstats.com/api/playerInfo?plat=pc&name={$battlelogName}";
			$headers = get_headers($url);
			if (stripos($headers[0], '40') !== false || stripos($headers[0], '50') !== false) { 
				$result = array('error' => true, 'message' => 'Player not found, or BF Stats server down.');
			} else {
				$json = file_get_contents($url);
				$data = json_decode($json);
				$personaId = $data->player->id;
				$result = array('error' => false, 'id' => $personaId);
			}
		} else {
			$json = file_get_contents($url);
			$data = json_decode($json);
			$personaId = $data->player->id;
			$result = array('error' => false, 'id' => $personaId);
		}
		return $result;
	}

}




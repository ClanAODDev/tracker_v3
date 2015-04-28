<?php

class DivisionThread extends Application {
	public $game_id;
	public $thread_url;
	public $thread_title;

	static $table = "games_threads";
	static $id = "id";

	public static function find_all($game_id) {
		return self::find_each(array("game_id" => $game_id, "|game_id" => 0));
	}

	/**
	 * checks rules threads for a player's post
	 * @param  string $player player name
	 * @param  string $thread url to be dug
	 * @return boolean        true if an instance found, false if not
	 */
	public static function checkForPost($player, $thread)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $thread . "&goto=newpost");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$getPosts = curl_exec($ch);
		$countPosts = stripos($getPosts, $player);
		if (!$countPosts) {
			$url   = parse_url(curl_last_url($ch));
			$query = $url['query'];
			parse_str($query, $url_array);
			$page = @$url_array['page'] - 1;
			curl_setopt($ch, CURLOPT_URL, $thread . "&page={$page}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$getPosts = curl_exec($ch);
			$countPosts = stripos($getPosts, $player);
		}
		return ($countPosts) ? true : false;
	}
}
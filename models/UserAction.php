<?php

class UserAction extends Application {

	public $id;
	public $type_id;
	public $date;
	public $user_id;
	public $target_id;

	static $id_field = 'id';
	static $table = 'user_actions';

	/**
	 * user action types:
	 * ------------------------
	 *  id | description
	 * ------------------------
	 *  1  |  add a new recruit
	 *  2  |  remove a member
	 *  3  |  update a member
	 *  4  |  flag an inactive member
	 *  5  |  generate new division structure
	 *  6  |  unflag an inactive member
	 *  7  |  Approve an loa
	 *  8  |  Deny an loa
	 *  9  |  Revoke an loa
	 *  10 |  recruit former member
	 *  11 |  request an loa
	 *  ----------------------- 
	 */

	public static function create($params) {
		$UserAction = new self();
		foreach ($params as $key=>$value) {
			$UserAction->$key = $value;
		}
		$UserAction->save($params);
	}

	public static function activityIcon($type) {
		switch ($type) {
			case 1:
			$icon = "user-plus text-success";
			break;
			case 2:
			$icon = "user-times text-danger";
			break;
			case 3:
			$icon = "pencil-square-o text-info";
			break;
			case 4:
			$icon = "flag text-danger";
			break;
			case 5:
			$icon = "cog";
			break;
			case 6:
			$icon = "flag text-warning";
			break;
			case 7:
			$icon = "thumbs-o-up text-success";
			break;
			case 8:
			$icon = "thumbs-o-down text-danger";
			break;
			case 9:
			$icon = "user-times text-danger";
			break;
			case 10:
			$icon = "refresh text-success";
			break;
			case 11:
			$icon = "question-circle";
			break;
		}
		return $icon;
	}

	public static function find_all() {
		return arrayToObject(Flight::aod()
			->from(self::$table)
			->limit(10)
			->sortDesc('date')
			->join('actions', array('actions.id' => 'user_actions.type_id'))
			->select(array('date','user_id', 'type_id', 'target_id', 'verbage'))->many()
		);
	}

	public static function humanize($type_id, $target_id, $user_id, $verbage) {
		$user = "<a href='member/{$user_id}'>" . Member::findForumName($user_id) . "</a>";
		$player = (!is_null($target_id)) ? "<a href='member/{$target_id}'>" . Member::findForumName($target_id) . "</a>" : NULL;
		switch ($type_id) {
			case 1:
			$text = "{$user} {$verbage} {$player} into the division";
			break;
			case 2: 
			$text = "{$user} {$verbage} {$player} from the division";
			break;
			case 3: 
			$text = "{$user} {$verbage} {$player}'s profile information";
			break;
			case 4: 
			$text = "{$player} was {$verbage} by {$user}";
			break;
			case 5:
			case 11:
			$text = "{$user} {$verbage}";
			break;
			case 6: 
			$text = "{$player} was {$verbage} by {$user}";
			break;
			case 7: 
			case 8:
			case 9:
			$text = "{$user} {$verbage} for {$player}";
			break;
			case 10:
			$text = "{$user} {$verbage} former member {$player} back into the division";
			break;
		}
		return $text;
	}

}

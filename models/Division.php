<?php

class Division extends Application {

	public $id;
	public $description;
	public $short_name;
	public $full_name;
	public $subforum;
	public $short_descr;
	public $division_structure_thread;
	public $welcome_forum;
	public $primary_handle;

	static $table = 'divisions';
	static $id_field = 'id';
	static $name_field = 'short_name';

	public static function find_all() {
		return self::fetch_all();
	}

	//public static function hasUnassignedMembers()

	public static function findUnassigned( $game_id ) {
		return ( object ) self::find( array( 'position_id' => 0, 'game_id' => $game_id ) );
	}

	public static function findById( $id ) {
		return (object) self::find( $id );
	}

	public static function findByName( $short_name ) {
		return (object) self::find( $short_name );
	}

	public static function findDivisionLeaders( $gid ) {
		$conditions = array(
			'position_id @' => array( 1, 2 ),
			'game_id' => $gid
			);
		$params = arrayToObject( Flight::aod()->from( Member::$table )->sortAsc( 'position_id' )->sortDesc( 'rank_id' )->where( $conditions )->select()->many() );
		foreach ( $params as $member ) {
			$position = Position::find( $member->position_id );
			$member->position_desc = $position->desc;
		}
		return $params;
	}

	public static function findGeneralSergeants( $gid ) {
		$conditions = array(
			'position_id' => 3,
			'game_id' => $gid,
			'status_id' => 1
			);
		return arrayToObject( Flight::aod()->from( Member::$table )->sortDesc( 'rank_id' )->where( $conditions )->select()->many() );
	}

	public static function findSquadLeaders( $gid, $order_by_rank = false ) {
		$sql = "SELECT last_activity, rank.abbr, member_id, forum_name, platoon.name, member.battlelog_name FROM " . Member::$table . " LEFT JOIN platoon ON platoon.id = member.platoon_id LEFT JOIN rank ON rank.id = member.rank_id WHERE member.game_id = {$gid} AND position_id = 5";

		if ( $order_by_rank ) {
			$sql .= " ORDER BY member.rank_id DESC, member.forum_name ASC ";
		} else {
			$sql .= " ORDER BY platoon.id, forum_name";
		}

		$params = Flight::aod()->sql( $sql )->one();
		return arrayToObject( $params );

	}

	public static function countSquadLeaders( $game_id ) {
		$sql = "SELECT count(*) as count FROM " . Member::$table . " WHERE position_id = 5 AND game_id = {$game_id}";
		$params = Flight::aod()->sql( $sql )->one();
		return $params['count'];
	}

	public static function recruitsThisMonth( $game_id ) {
		$sql = "SELECT count(*) as count FROM " . Member::$table . " WHERE join_date >= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND game_id = {$game_id}";
		return arrayToObject( Flight::aod()->sql( $sql )->one() );
	}

	public static function recruitingStats( $game_id ) {
		$days_30 = Flight::aod()->sql( "SELECT count(*) as count FROM " . Member::$table . " WHERE join_date >= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND game_id = {$game_id}" )->one();
		$days_60 = Flight::aod()->sql( "SELECT count(*) as count FROM " . Member::$table . " WHERE YEAR(join_date) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(join_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND game_id = {$game_id};" )->one();
		$days_90 = Flight::aod()->sql( "SELECT count(*) as count FROM " . Member::$table . " WHERE YEAR(join_date) = YEAR(CURDATE() - INTERVAL 2 MONTH) AND MONTH(join_date) = MONTH(CURDATE() - INTERVAL 2 MONTH) AND game_id = {$game_id};" )->one();

		$stats = new stdClass();
		$stats->days_30 = $days_30['count'];
		$stats->days_60 = $days_60['count'];
		$stats->days_90 = $days_90['count'];
		return $stats;

	}

	public static function totalCount( $game_id ) {
		$sql = "SELECT count(*) as count FROM " . Member::$table . " WHERE member.game_id = {$game_id} AND status_id IN (1,3,999)";
		return arrayToObject( Flight::aod()->sql( $sql )->one() );
	}

	public static function _create() {
	}
	public static function _modify() {
	}
	public static function _delete() {
	}
}

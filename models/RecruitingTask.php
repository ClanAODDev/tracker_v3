<?php

class RecruitingTask extends Application {

	public $id;
	public $content;
	public $game_id;

	static $id_field = 'id';
	static $table = 'recruiting_tasks';

	public static function findAll( $game_id ) {
		return self::find( array( 'game_id @' => array( 0, $game_id ) ) );
	}

}

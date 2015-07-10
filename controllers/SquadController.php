<?php

class SquadController {
	
	public static function _doCreateSquad() {
		$params = array(
			'game_id' => $_POST['division_id'], 
			'platoon_id' => $_POST['platoon_id'], 
			'leader_id' => $_POST['leader_id']);
		Squad::create($params);
	}

	public static function _doModifySquad() {
		$params = array(
			'id' => $_POST['squad_id'],
			'leader_id' => $_POST['leader_id']
			);
		Squad::modify($params);
	}


	public static function  _createSquad() {
		Flight::render('modals/create_squad');
	}

	public static function  _modifySquad() {
		Flight::render('modals/modify_squad');
	}

}
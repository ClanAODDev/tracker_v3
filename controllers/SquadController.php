<?php

class SquadController {
	
	public static function _doCreateSquad() {

		$params = array(
			'game_id' => $_POST['division_id'], 
			'platoon_id' => $_POST['platoon_id'], 
			'leader_id' => $_POST['leader_id']);

		Squad::create($params);
	}


	public static function  _createSquad() {

		$user = User::find(intval($_SESSION['userid']));
		Flight::render('modals/create_squad', array('user' => $user));
	}

}
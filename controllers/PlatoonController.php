<?php

class Platoon extends Application {

	public $id;
	public $number;
	public $name;
	public $game_id;
	public $leader_id;

	static $table = "platoon";
	static $id_field = "id";
	
}
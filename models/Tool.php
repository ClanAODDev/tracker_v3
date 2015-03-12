<?php

class Tool extends Application {

	public $id;
	public $tool_name;
	public $class;
	public $tool_descr;
	public $tool_path;
	public $role_id;
	public $icon;
	public $disabled;

	static $table = 'user_tools';


	public static function buildUserTools() {

        try {
            $query = "SELECT tool_name as title, tool_descr as descr, tool_path as link, icon, class, disabled FROM user_tools WHERE role_id <= :role";
            $query = $pdo->prepare($query);
            $query->bindParam(':role', $role);
            $query->execute();
            $query = $query->fetchAll();
        }

        catch (PDOException $e) {
            return "ERROR:" . $e->getMessage();
        }
  
	}



}
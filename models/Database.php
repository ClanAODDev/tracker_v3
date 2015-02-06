<?php

class Database extends Sparrow {
  
  static $type = 'pdomysql';
  
  public function __construct($database) {
    $config = [
      'type' => self::$type,
      'hostname' => DB_HOST,
      'database' => $database,
      'username' => DB_USER,
      'password' => DB_PASS
    ];
    $this->setDb($config);
  }
  
}

?>
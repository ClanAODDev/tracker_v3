<?php

date_default_timezone_set('America/New_York');
require_once 'flight/Flight.php';

// Autoload models and controllers
Flight::path('models');
Flight::path('controllers');

// Set views path and environment
Flight::set('flight.views.path', 'views');
Flight::set('root_dir', dirname(__FILE__));
Flight::set('base_url', '/tracker-v2/');

// Set database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

Flight::register('aod', 'Database', array('aod'));
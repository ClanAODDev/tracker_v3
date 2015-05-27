<?php

date_default_timezone_set('America/New_York');
require_once 'flight/Flight.php';

// Autoload models and controllers
Flight::path('models');
Flight::path('controllers');

// Set views path and environment
Flight::set('flight.views.path', 'views');
Flight::set('root_dir', dirname(__FILE__));
Flight::set('base_url', '/Division-Tracker/');

// Set database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

Flight::register('aod', 'Database', array('aod'));

// defines for website URLs
define('CLANAOD', 'http://www.clanaod.net/forums/member.php?u=');
define('BATTLELOG', 'http://battlelog.battlefield.com/bfh/user/');
define('PRIVMSG', 'http://www.clanaod.net/forums/private.php?do=newpm&u=');
define('EMAIL', 'http://www.clanaod.net/forums/sendmessage.php?do=mailmember&u=');
define('REMOVE', 'http://www.clanaod.net/forums/modcp/aodmember.php?do=remaod&u=');

// defines for BF4 division activity status display
define('PERCENTAGE_CUTOFF_GREEN', 75);
define('PERCENTAGE_CUTOFF_AMBER', 50);
define('INACTIVE_MIN', 0);
define('INACTIVE_MAX', 25);

// global settings
define('MAX_GAMES_ON_PROFILE', 25);

// gitHub username and password
define('GITHUB_USER', '');
define('GITHUB_PASS', '');
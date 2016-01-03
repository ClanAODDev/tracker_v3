<?php

date_default_timezone_set('America/New_York');
require_once 'flight/Flight.php';
require_once 'vendor/github/GitHubClient.php';

// Autoload models and controllers
Flight::path('models');
Flight::path('controllers');

// Set views path and environment
Flight::set('flight.views.path', 'views');
Flight::set('root_dir', dirname(__FILE__));
Flight::set('base_url', '/Division-Tracker/');

// Set database credentials
define('DB_HOST', '');
define('DB_USER', 'root');
define('DB_PASS', '');

define('ARCH_PASS', '');

Flight::register('aod', 'Database', array('aodwebhost_tracker'));



// defines for website URLs
define('CLANAOD', 'http://www.clanaod.net/forums/member.php?u=');
define('BATTLELOG', 'http://battlelog.battlefield.com/bfh/agent/');
define('BATTLEREPORT', 'http://battlelog.battlefield.com/bf4/battlereport/show/1/');
define('BF4DB', 'http://bf4db.com/players/');
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
define('MAX_SQUADS_IN_PLT', 10);

// gitHub username and password
// need this for github API integration
define('GITHUB_USER', '');
define('GITHUB_PASS', '');

// wargaming API key
// 10 requests per second
define('WG_NA_API_KEY', '');
define('WG_EU_API_KEY', '');

// handle errors privately
Flight::set('flight.log_errors', true);
// Flight::map('error', function(Exception $ex){
//     // Handle error
//     Flight::redirect('/error', 500);
// });

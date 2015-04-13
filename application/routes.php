<?php

// maintenance mode
// Flight::route('*', array('ApplicationController', '_unavailable'));
// 

if (empty($_SESSION['userid'])) {

	Flight::route('/', array('UserController', '_login'));
	Flight::route('/register', array('UserController', '_register'));
	Flight::route('POST /do/login', array('UserController', '_doLogin'));
	Flight::route('/invalid-login', array('ApplicationController', '_invalidLogin'));
	Flight::route('POST /do/online-list', array('ApplicationController', '_doUsersOnline'));

	// crontab
	Flight::route('/update/bf4-server-activity', array('CrontabController', '_doBf4Update'));
	Flight::route('/update/bfh-server-activity', array('CrontabController', '_doBfhUpdate'));

} else {

	// user views
	Flight::route('/', array('ApplicationController', '_index'));
	Flight::route('/logout', array('UserController', '_doLogout'));
	Flight::route('/help', array('ApplicationController', '_help'));
	Flight::route('/recruiting', array('RecruitingController', '_index'));
	Flight::route('/recruiting/new-member', array('RecruitingController', '_addNewMember'));

	// view
	Flight::route('/divisions/@div', array('DivisionController', '_index'));
	Flight::route('/divisions/@div/@plt', array('PlatoonController', '_index'));
	Flight::route('/member/@id', array('MemberController', '_profile'));

	// updates
	Flight::route('POST /do/search-members', array('ApplicationController', '_doSearch'));
	Flight::route('POST /do/online-list', array('ApplicationController', '_doUsersOnline'));
	Flight::route('POST /do/update-alert', array('ApplicationController', '_doUpdateAlert'));
	Flight::route('POST /do/update-member', array('MemberController', '_doUpdateMember'));
	Flight::route('POST /do/validate-member', array('MemberController', '_doValidateMember'));

	// modals
	Flight::route('POST /edit/member', array('MemberController', '_edit'));

	// GETs
	Flight::route('POST /do/check-division-threads', array('RecruitingController', '_doDivisionThreadCheck'));




	/*	
	Flight::route('/settings', array('UserController', '_settings'));

	// view screens
	Flight::route('/member/[0-9]+', array('MemberController', '_profile'));
	
	

	// manage
	Flight::route('/manage/inactive', array('DivisionController', '_manage_inactives'));
	Flight::route('/manage/division', array('DivisionController', '_manage_division'));
	Flight::route('/manage/loas', array('DivisionController', '_manage_loas'));

	// recruiting
	

	// admin
	Flight::route('/admin', array('AdminController', '_show'));
*/
}

// 404 redirect
Flight::map('notFound', array('ApplicationController', '_404'));
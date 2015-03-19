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

} else {

	// user views
	Flight::route('/', array('ApplicationController', '_index'));
	Flight::route('/logout', array('UserController', '_doLogout'));
	Flight::route('/help', array('ApplicationController', '_help'));
	Flight::route('/recruiting', array('RecruitingController', '_index'));
	Flight::route('POST /do/search-members', array('ApplicationController', '_doSearch'));
	Flight::route('POST /do/online-list', array('ApplicationController', '_doUsersOnline'));

	// view
	Flight::route('/divisions/[A-Z][a-z]+', array('DivisionController', '_index'));

	/*	
	Flight::route('/settings', array('UserController', '_settings'));

	// view screens
	Flight::route('/member/[0-9]+', array('MemberController', '_profile'));
	Flight::route('/platoon/[0-9]+', array('PlatoonController', '_show'));
	

	// manage
	Flight::route('/manage/inactive', array('DivisionController', '_manage_inactives'));
	Flight::route('/manage/division', array('DivisionController', '_manage_division'));
	Flight::route('/manage/loas', array('DivisionController', '_manage_loas'));

	// recruiting
	Flight::route('/recruiting/new-member', array('RecruitingController', '_add_new_member'));

	// admin
	Flight::route('/admin', array('AdminController', '_show'));
*/
}

// 404 redirect
Flight::map('notFound', array('ApplicationController', '_404'));
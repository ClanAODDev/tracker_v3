<?php

class UserController {

	public static function _login() {
		Flight::render('layouts/login', array(''), 'content');
		Flight::render('layouts/application');
	} 

	public static function _register() {
		Flight::render('layouts/register', array(''), 'content');
		Flight::render('layouts/application');
	}

	public static function _doLogout() {
		forceEndSession();
		Flight::redirect('./');
	}

	public static function _doLogin() {
		$user = trim(htmlspecialchars($_POST['user']));
		$pass = $_POST['password'];

		if (!User::exists($user)) { 
			$error = true;
		} else {
			$id = User::validatePassword($pass, $user);

			if (!$id) {
				$error = true; 

			} else {
				// updateLoggedInTime($user);  
				$_SESSION['loggedIn'] = true;
				$_SESSION['userid'] = $id;
				$_SESSION['username'] = $user;
			}
		}

		if (isset($error)) {
			Flight::redirect('/invalid-login');
		} else {
			Flight::redirect('./');
		}
	}




}


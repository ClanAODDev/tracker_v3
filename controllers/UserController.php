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

	public static function _logout() {
		forceEndSession();
		header('Location: /');
	}

	public static function _doLogin() {
		$data = NULL;
		$user = trim(htmlspecialchars($_POST['user']));
		$pass = $_POST['password'];
		if (!self::userExists($user)) { 
			$data['error'] = true;
		} else {
			$id = self::validatePassword($pass, $user);
			if (!$id) {
				$data['error'] = true;    
			} else {
				session_start();
				// updateLoggedInTime($user);  
				$_SESSION['loggedIn'] = true;
				$_SESSION['user_id'] = $id;
			}
		}

		if (!is_null($data['error'])) {
			header('Location: /error/invalid-login');
		} else {
			header('Location: /');
		}
	}

}


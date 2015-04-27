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
			$invalid_login = true;
		} else {
			$params = User::validatePassword($pass, $user);
			if (empty($params)) {
				$invalid_login = true; 
			} else {
				// updateLoggedInTime($user);  
				$_SESSION['loggedIn'] = true;
				$_SESSION['userid'] = $params['userid'];
				$_SESSION['memberid'] = $params['memberid'];
			}
		}
		if (isset($invalid_login)) {
			Flight::redirect('/invalid-login');
		} else {
			Flight::redirect('./');
		}
	}

	public static function _doRegister() {
		$user = $_POST['user'];
		$pass = $_POST['password'];
		$passVerify = $_POST['passVerify'];
		$email = $_POST['email'];

		if (stristr($user, 'aod_')) {
			$data['success'] = false;
			$data['message'] = "Please do not use 'AOD_' in your username";
		} else if ($pass != $passVerify) {
			$data['success'] = false;
			$data['message'] = "Passwords must match.";
		} else if (User::exists($user)) {
			$data['success'] = false;
			$data['message'] = "That username has already been used.";
		} else {
			$params = array('username' => $user, 'email' => $email, 'credential' => $pass);
			User::create($params);
			$data['success'] = true;
			$data['message'] = "Your account was created!";
		}

		echo json_encode($data);
		exit;
	}

}


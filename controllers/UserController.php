<?php

class UserController
{

    public static function _login()
    {
        Flight::render('layouts/login', [], 'content');
        Flight::render('layouts/application');
    }

    public static function _register()
    {
        Flight::render('layouts/register', [], 'content');
        Flight::render('layouts/application');
    }

    public static function _doLogout()
    {
        forceEndSession();
        Flight::redirect('./');
    }

    public static function _doLogin()
    {
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
            Flight::redirect('/');
        }
    }

    public static function _authenticate()
    {
        if (User::isLoggedIn()) {
            $user = User::find(intval($_SESSION['userid']));
            $member = Member::find(intval($_SESSION['memberid']));
            $tools = Tool::find_all($user->role);
            $divisions = Division::find_all();
            Flight::render('layouts/auth', [], 'content');
            Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions'));
        } else {
            Flight::render('layouts/auth', [], 'content');
            Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions'));
        }
    }

    public static function _doAuthenticate()
    {
        $email = ($_POST['email']) ? $_POST['email'] : false;
        $validation = ($_POST['validation']) ? $_POST['validation'] : false;

        if ($email) {
            $params = array('email' => $email, 'validation' => $validation);

            if (empty($params)) {
                $data['success'] = false;
                $data['message'] = "The email you provided is not recognized";
            } else {
                if (!$validation || !User::validateCode($params)) {
                    $data['success'] = false;
                    $data['message'] = "Invalid authentication code.";
                } else {
                    $data['success'] = true;
                    $data['message'] = "Your account and email address have been authenticated.";
                }
            }
            echo json_encode($data);

        }
    }

    public static function _doResetAuthentication()
    {
        $email = ($_POST['email']) ? $_POST['email'] : false;
        if ($email) {
            if (!User::resetValidation($email)) {
                $data['success'] = false;
                $data['message'] = "The email you provided is not recognized.";
            } else {
                $data['success'] = true;
                $data['message'] = "A new validation code has been sent to your email.";
            }
            echo json_encode($data);
        }
    }

    public static function _doRegister()
    {
        $user = $_POST;
        $memberObj = Member::findByName($user['user']);

        if (stristr($user['user'], 'aod_')) {
            $data['success'] = false;
            $data['message'] = "Please do not use 'AOD_' in your username";
        } else {
            if ($user['password'] != $user['passVerify']) {
                $data['success'] = false;
                $data['message'] = "Passwords must match.";
            } else {
                if (User::exists($user['user'])) {
                    $data['success'] = false;
                    $data['message'] = "That username has already been used.";
                } else {
                    if (!property_exists($memberObj, 'id')) {
                        $data['success'] = false;
                        $data['message'] = "No AOD member exists with that forum name.";
                    } else {
                        $user['member_id'] = $memberObj->id;
                        User::create($user);
                        $data['success'] = true;
                        $data['message'] = "Your account was created!";
                    }
                }
            }
        }
        echo json_encode($data);
        exit;
    }

}


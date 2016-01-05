<?php

class ApplicationController
{

    public static function _index()
    {

        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));
        $notifications = new Notification($user, $member);

        $squad = Squad::find($member->member_id);
        $platoon = Platoon::find($member->platoon_id);
        $squads = Squad::findAll($member->game_id, $member->platoon_id);

        Flight::render('user/main_tools', array('user' => $user, 'tools' => $tools), 'main_tools');
        Flight::render('member/personnel',
            array('member' => $member, 'squad' => $squad, 'platoon' => $platoon, 'squads' => $squads), 'personnel');
        Flight::render('application/divisions', array('divisions' => $divisions), 'divisions_list');
        Flight::render('user/notifications', array('notifications' => $notifications->messages), 'notifications_list');
        Flight::render('layouts/home', array('user' => $user, 'member' => $member, 'division' => $division), 'content');
        Flight::render('layouts/application',
            array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
    }

    public static function _activity()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));
        $platoons = Platoon::find_all($member->game_id);
        Flight::render('application/activity', array('division' => $division), 'content');
        Flight::render('layouts/application',
            array('js' => 'help', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));

    }

    public static function _help()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::findById(intval($member->game_id));
        $platoons = Platoon::find_all($member->game_id);

        Flight::render('application/help', array('user' => $user, 'member' => $member, 'division' => $division),
            'content');
        Flight::render('layouts/application',
            array('js' => 'help', 'user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
    }

    public static function _doUsersOnline()
    {
        if (isset($_SESSION['loggedIn'])) {
            $user = User::find(intval($_SESSION['userid']));
            $member = Member::find(intval($_SESSION['memberid']));
            Flight::render('user/online_list', array('user' => $user, 'member' => $member));
        } else {
            Flight::render('user/online_list');
        }
    }

    public static function _doSearch()
    {
        $name = trim($_POST['name']);
        $results = Member::search($name);
        Flight::render('member/search', array('results' => $results));
    }

    public static function _invalidLogin()
    {
        Flight::render('errors/invalid_login', array(), 'content');
        Flight::render('layouts/application');
    }

    public static function _unavailable()
    {
        Flight::render('errors/unavailable', array(), 'content');
        Flight::render('errors/main');
    }

    public static function _404()
    {
        Flight::render('errors/404', array(), 'content');
        Flight::render('errors/main');
    }

    public static function _error()
    {
        Flight::render('errors/error', array(), 'content');
        Flight::render('errors/main');
    }

    public static function _doUpdateAlert()
    {
        $params = array('id' => $_POST['id'], 'user' => $_POST['user']);
        AlertStatus::create($params);
    }

}

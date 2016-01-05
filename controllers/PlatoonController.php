<?php

class PlatoonController
{

    public static function _index($div, $plt)
    {
        $division = Division::findByName(strtolower($div));
        $platoonId = Platoon::getIdFromNumber($plt, $division->id);

        if (!is_null($platoonId)) {

            $user = User::find(intval($_SESSION['userid']));
            $member = Member::find(intval($_SESSION['memberid']));
            $tools = Tool::find_all($user->role);
            $divisions = Division::find_all();
            $platoon = Platoon::findById($platoonId);
            $members = arrayToObject(Platoon::members($platoonId));
            $js = 'platoon';

            $memberIdList = Platoon::memberIdsList($platoonId);
            $activity = arrayToObject(Platoon::forumActivity($platoonId));

            $bdate = date("Y-m-d", strtotime("now - 30 days"));
            $edate = date("Y-m-d", strtotime("now"));

            Flight::render('platoon/main/statistics', compact('platoon', 'activity'), 'statistics');
            Flight::render('platoon/main/members', compact('division', 'members', 'js', 'bdate', 'edate'), 'membersTable');
            Flight::render('platoon/main/index', compact('user', 'member', 'division', 'platoon', 'memberIdList', 'plt', ['div' => $division->id], 'members', 'platoonId'), 'content');
            Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions'));

        } else {

            Flight::redirect('404/', 404);

        }
    }

    public static function _manage_platoon($div, $plt)
    {

        $division = Division::findByName(strtolower($div));
        $platoonId = Platoon::getIdFromNumber($plt, $division->id);

        if (!is_null($platoonId)) {

            $user = User::find(intval($_SESSION['userid']));
            $member = Member::find(intval($_SESSION['memberid']));

            if ($member->platoon_id == $platoonId || $user->role > 2 || User::isDev()) {

                $tools = Tool::find_all($user->role);
                $divisions = Division::find_all();
                $platoon = Platoon::findById($platoonId);
                $unassignedMembers = Platoon::unassignedMembers($platoonId, true);
                $squads = Squad::findByPlatoonId($platoonId);
                $memberCount = count((array)Platoon::members($platoonId));

                Flight::render('manage/platoon', compact('division', 'platoon', 'squads', 'unassignedMembers', 'memberCount'), 'content');
                Flight::render('layouts/application', compact(['js' => 'manage'], 'user', 'member', 'tools', 'divisions'));

            } else {

                // insufficient access
                Flight::redirect('404/', 404);
            }

        } else {

            // nonexistent platoon
            Flight::redirect('404/', 404);

        }

    }

    public static function _doUpdateMemberSquad()
    {
        $params = array();
        $params['id'] = $_POST['member_id'];
        $params['squad_id'] = $_POST['squad_id'];
        $params['position_id'] = 6;

        Member::modify($params);
        $data = ['success' => true];
        echo(json_encode($data));
    }

    public static function _create()
    {
    }

    public static function _modify()
    {
    }

    public static function _delete()
    {
    }
}

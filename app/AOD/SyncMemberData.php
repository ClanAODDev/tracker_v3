<?php

namespace App\AOD;

use App\Division;
use App\Member;
use App\Reports\Slack;

class SyncMemberData
{
    protected static $activeMembers = [];

    /**
     * Performs update operation on divisions and members and also
     * syncs division membership (adds, removes)
     */
    public static function execute()
    {
        foreach (Division::all() as $division) {

            self::$activeMembers = [];
            $divisionInfo = new GetDivisionInfo($division->name);

            foreach ($divisionInfo->data as $member) {
                self::doMemberUpdate($member, $division);
            }

            $division->members()->sync(self::$activeMembers, false);
        }

        $responsibleUser = (request()->user_name) ?: 'System';
        Slack::info(\Carbon::now() . " - Member sync performed [{$responsibleUser}]");
    }

    /**
     * Updates an individual member and queues as an active primary member
     *
     * @param $item
     * @param Division $division
     */
    private static function doMemberUpdate($item, Division $division)
    {
        $member = Member::firstOrCreate([
            'clan_id' => $item['userid'],
        ]);

        // have they been recently promoted?
        if ($member->rank_id < ($item['aodrankval'] - 2) && $member->rank_id > 0) {
            $member->last_promoted = \Carbon::now();
        }

        $member->name = str_replace('AOD_', '', $item['username']);
        $member->join_date = $item['joindate'];
        $member->last_forum_login = $item['lastvisit'];

        // accounts for forum member, prospective member ranks which we don't use
        $member->rank_id = $item['aodrankval'] - 2;

        $member->save();

        self::$activeMembers[$member->id] = [
            'primary' => true,
        ];
    }
}

<?php

namespace App\AOD;

use App\Division;
use App\Member;

class SyncMemberData
{
    protected static $activeMembers = [];

    /**
     * Performs update operation on divisions and members
     * and also syncs division membership (adds, removes)
     */
    public static function execute()
    {
        foreach (Division::all() as $division) {
            $divisionInfo = new GetDivisionInfo($division->name);

            foreach ($divisionInfo->data as $item) {
                self::doMemberUpdate($item, $division);
            }

            $division->members()->sync(self::$activeMembers, false);
        }
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

        if ($member->rank_id < ($item['aodrankval'] - 2) && $member->rank_id > 0) {
            $member->last_promoted = \Carbon::now();
        }

        $member->name = str_replace('AOD_', '', $item['username']);
        $member->join_date = $item['joindate'];
        $member->last_forum_login = $item['lastvisit'];

        // accounts for forum member, prospective member ranks
        $member->rank_id = $item['aodrankval'] - 2;

        $member->save();

        self::$activeMembers[$member->id] = [
            'primary' => true,
        ];
    }

}

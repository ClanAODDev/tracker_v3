<?php

namespace App\AOD\MemberSync;

use App\Member;
use App\Division;
use Illuminate\Support\Facades\Log;

class SyncMemberData
{

    protected static $activeMembers = [];

    /**
     * Performs update operation on divisions and members and also
     * syncs division membership (adds, removes)
     */
    public static function execute()
    {
        foreach (Division::active()->get() as $division) {
            // log activity
            Log::info(date('Y-m-d h:i:s') . " - MEMBER SYNC - fetching {$division->name}");

            self::$activeMembers = [];
            $divisionInfo = new GetDivisionInfo($division->name);

            foreach ($divisionInfo->data as $member) {
                self::doMemberUpdate($member, $division);
            }

            $members = $division->members()->sync(self::$activeMembers);
            self::doRemovalCleanup($members);
        }
    }

    /**
     * Updates an individual member and queues as an active primary member
     *
     * @param $item
     * @param $division
     */
    private static function doMemberUpdate($item, Division $division)
    {
        $member = Member::firstOrCreate([
            'clan_id' => $item['userid'],
        ]);

        echo $member->clan_id;

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

    /**
     * Handles cleanup of members removed from a division (platoon, squad info wiped)
     *
     * @param array $members
     */
    private static function doRemovalCleanup(array $members)
    {
        $detached = $members['detached'];
        foreach ($detached as $index => $id) {
            $member = Member::find($id);
            if ($member instanceof Member) {
                $member->squad_id = 0;
                $member->platoon_id = 0;
                $member->save();
            }
        }
    }
}

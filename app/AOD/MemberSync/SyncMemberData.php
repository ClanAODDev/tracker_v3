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

            if ( ! is_object($divisionInfo)) {
                Log::critical(date('Y-m-d H:i:s') . " - Could not sync $division->name");
                Log::critical($divisionInfo);
                exit;
            }

            foreach ($divisionInfo->data as $member) {
                self::doMemberUpdate($member, $division);
            }

            // add new members, detach removed members
            $members = $division->members()->sync(self::$activeMembers);

            // trash removed members
            self::doRemovalCleanup($members);
        }

    }

    /**
     * Updates an individual member and queues as an active primary member
     *
     * @param $record
     * @param $division
     */
    private static function doMemberUpdate($record)
    {
        // are we updating or creating?
        $member = Member::firstOrCreate([
            'clan_id' => $record['userid'],
        ]);

        // have they been recently promoted?
        if ($member->rank_id < ($record['aodrankval'] - 2) && $member->rank_id > 0) {
            $member->last_promoted = \Carbon::now();
        }

        // drop aod prefix
        $member->name = str_replace('AOD_', '', $record['username']);

        // handle timestamps
        $member->join_date = $record['joindate'];
        $member->last_activity = "{$record['lastactivity']} {$record['lastactivity_time']}";

        // accounts for forum member, prospective member ranks which we don't use
        $member->rank_id = ($record['aodrankval'] - 2 <= 0) ? 1 : $record['aodrankval'] - 2;

        $member->save();

        // set member's active division
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

                // unassign member from squad / platoon
                $member->squad_id = 0;
                $member->platoon_id = 0;
                $member->save();
            }
        }
    }
}

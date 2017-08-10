<?php

namespace App\AOD\MemberSync;

use App\Division;
use App\Member;
use App\Platoon;
use App\Squad;
use Illuminate\Support\Facades\Log;

class SyncMemberData
{

    protected static $activeMembers = [];

    protected static $currentMembers = [];

    /**
     * Performs update operation on divisions and members and also
     * syncs division membership (adds, removes)
     */
    public static function execute()
    {
        foreach (Division::active()->get() as $division) {
            // log activity
            Log::info(date('Y-m-d h:i:s') . " - MEMBER SYNC - fetching {$division->name}");

            // reset array
            self::$activeMembers = [];

            self::$currentMembers = Member::whereDivisionId($division->id)
                ->get()->pluck('id')->toArray();

            $divisionInfo = new GetDivisionInfo($division->name);

            if ( ! is_object($divisionInfo)) {
                Log::critical(date('Y-m-d H:i:s') . " - Could not sync $division->name");
                Log::critical($divisionInfo);
                exit;
            }

            foreach ($divisionInfo->data as $member) {
                self::doMemberUpdate($member, $division);
            }

            echo "{$division->name} members synced" . PHP_EOL;

            // trash removed members
            self::doRemovalCleanup();
        }
    }

    /**
     * Updates an individual member and queues as an active primary member
     *
     * @param $record
     * @param $division
     */
    private static function doMemberUpdate($record, $division)
    {
        // are we updating or creating?
        $member = Member::firstOrCreate([
            'clan_id' => $record['userid'],
        ]);

        $member->division_id = $division->id;

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

        // forum post count
        $member->posts = $record['postcount'];

        // teamspeak activity data
        $member->last_ts_activity = "{$record['lastts_connect']} {$record['lastts_connect_time']}";

        // persist
        $member->save();


        // populate our active members
        self::$activeMembers[] = $member->id;

    }

    /**
     * Handles cleanup of members removed from a division (platoon, squad info wiped)
     */
    private static function doRemovalCleanup()
    {
        $removed = array_diff(
            self::$currentMembers,
            self::$activeMembers
        );

        foreach ($removed as $index => $id) {
            $member = Member::find($id);

            if ($member instanceof Member) {
                self::resetMember($member);
            }
        }
    }

    private static function resetMember($member)
    {

        // reset member data
        $member->squad_id = 0;
        $member->platoon_id = 0;
        $member->position_id = 1;
        $member->division_id = 0;

        $member->save();

        // reset any leadership assignments
        $assignments = collect([
            Squad::whereLeaderId($member->leader_id)->first(),
            Platoon::whereLeaderId($member->leader_id)->first()
        ]);

        if ($assignments->count()) {
            $assignments->each(function ($model) use ($member) {
                if ($model) {
                    $model->leader()->dissociate($member->leader_id)->save();
                }
            });
        }
    }
}

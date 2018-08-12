<?php

namespace App\AOD\MemberSync;

use App\Division;
use App\Member;
use App\Platoon;
use App\Squad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncMemberData
{

    protected static $activeClanMembers = [];

    protected static $activeDivisionMembers = [];

    protected static $currentDivisionMembers = [];

    /**
     * Performs update operation on divisions and members and also
     * syncs division membership (adds, removes)
     */
    public static function execute()
    {
        $divisionInfo = new GetDivisionInfo();

        $syncData = collect($divisionInfo->data)->groupBy(function ($item, $key) {
            return strtolower($item['aoddivision']);
        });

        self::$activeClanMembers = collect($divisionInfo->data)->pluck('userid');

        if (! count($syncData)) {
            Log::critical(date('Y-m-d H:i:s') . " - MEMBER SYNC - No data available");
            exit;
        }

        foreach (Division::active()->get() as $division) {
            if ($syncData->keys()->contains(strtolower($division->name))) {
                // log activity
                Log::info(date('Y-m-d h:i:s') . " - MEMBER SYNC - syncing {$division->name}");

                // reset array
                self::$activeDivisionMembers = [];

                self::$currentDivisionMembers = Member::whereDivisionId($division->id)->get()
                    ->pluck('id')
                    ->toArray();

                foreach ($syncData[strtolower($division->name)] as $member) {
                    self::doMemberUpdate($member, $division);
                }

                echo "{$division->name} members synced" . PHP_EOL;

                // trash removed members
                self::doRemovalCleanup();
            }
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

        // are we dealing with a transfer?
        // if so, clean up position, assignments, but retain
        // part-time divisions
        if ($member->division_id !== $division->id) {
            self::wipePositionAndAssignment($member);
        }

        // begin the assignment process
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
        $member->last_ts_activity = "{$record['lastts_connect']} {$record['lastts_connect_time']}";

        // accounts for forum member, prospective member ranks which we don't use
        $member->rank_id = ($record['aodrankval'] - 2 <= 0) ? 1 : $record['aodrankval'] - 2;

        $member->posts = $record['postcount'];
        $member->ts_unique_id = $record['tsid'];
        $member->pending_member = false;

        // persist
        $member->save();

        if ($member->user) {
            $user = $member->user;
            $user->name = $member->name;
            $user->save();
        }

        // populate our active members
        self::$activeDivisionMembers[] = $member->id;
    }

    /**
     * @param $member
     */
    private static function wipePositionAndAssignment($member)
    {
        $member->squad_id = 0;
        $member->platoon_id = 0;
        $member->position_id = 1;
    }

    /**
     * Handles cleanup of members removed from a division (platoon, squad info wiped)
     */
    private static function doRemovalCleanup()
    {
        $removed = array_diff(
            self::$currentDivisionMembers,
            self::$activeDivisionMembers
        );

        foreach ($removed as $index => $id) {
            $member = Member::find($id);

            // did they transfer to another division?
            if (self::$activeClanMembers->contains($member->clan_id)) {
                self::hardResetMember($member);
            } else {
                if ($member instanceof Member && ! $member->isPending) {
                    $member->partTimeDivisions()->sync([]);
                    self::hardResetMember($member);
                }
            }
        }
    }

    private static function hardResetMember(Member $member)
    {

        // reset member data
        $member->resetPositionAndAssignments();

        // reset any leadership assignments
        $assignments = collect([
            Squad::whereLeaderId($member->clan_id)->first(),
            Platoon::whereLeaderId($member->clan_id)->first()
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

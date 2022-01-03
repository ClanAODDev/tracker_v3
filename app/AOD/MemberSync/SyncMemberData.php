<?php

namespace App\AOD\MemberSync;

use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\RankAction;
use App\Models\Squad;
use App\Models\Transfer;
use Carbon\Carbon;

class SyncMemberData
{
    public static function execute($verbose = false)
    {
        $divisionInfo = new GetDivisionInfo();

        if (!$syncData = collect($divisionInfo->data)) {
            \Log::critical(date('Y-m-d H:i:s') . ' - MEMBER SYNC - No data available');

            exit;
        }

        $divisionIds = \App\Models\Division::active()->pluck('name', 'id')->flip();

        $syncTable = \DB::connection('sqlite')->table('aod_member_sync');

        $syncTable->truncate();

        date_default_timezone_set('UTC');

        foreach ($syncData->chunk(50) as $chunk) {
            $syncTable->insert($chunk->toArray());
        }

        // complete any outstanding member requests
        self::processMemberRequests($syncTable->pluck('userid'));

        // iterating over members we know exist in the tracker
        $members = Member::whereNotIn('division_id', [0, 7])->get();

        foreach ($members as $member) {
            $syncTable = \DB::connection('sqlite')->table('aod_member_sync');

            $newData = $syncTable->where('userid', $member->clan_id)->first();

            if (!$newData) {
                // member does not exist in sync data, so must be removed
                self::hardResetMember($member);

                continue;
            }

            $oldData = $member->toArray();

            $oldData = collect([
                'allow_pm'     => $oldData['allow_pm'],
                'discord'      => $oldData['discord'],
                'division_id'  => $oldData['division_id'],
                'name'         => $oldData['name'],
                'posts'        => $oldData['posts'],
                'privacy_flag' => $oldData['privacy_flag'],
                'rank_id'      => $oldData['rank_id'],
                'ts_unique_id' => $oldData['ts_unique_id'],

                // these can be null, and they piss me off
                'last_activity' => $oldData['last_activity'] != null
                    ? Carbon::createFromTimeString($oldData['last_activity'])->format('Y-m-d H:i:s')
                    : null,
                'last_ts_activity' => $oldData['last_ts_activity'] != null
                    ? Carbon::createFromTimeString($oldData['last_ts_activity'])->format('Y-m-d H:i:s')
                    : null,
            ]);

            try {
                $newData = collect([
                    'allow_pm'     => $newData->allow_pm,
                    'discord'      => $newData->discordtag,
                    'division_id'  => $divisionIds[$newData->aoddivision],
                    'name'         => str_replace('AOD_', '', $newData->username),
                    'posts'        => $newData->postcount,
                    'privacy_flag' => 'yes' !== $newData->allow_export ? 0 : 1,
                    'rank_id'      => ($newData->aodrankval - 2 <= 0) ? 1 : $newData->aodrankval - 2,
                    'ts_unique_id' => $newData->tsid,

                    // these can be null, and they piss me off
                    'last_activity' => '' != $newData->lastactivity
                        ? "{$newData->lastactivity} {$newData->lastactivity_time}"
                        : '',
                    'last_ts_activity' => '' != $newData->lastts_connect
                        ? "{$newData->lastts_connect} {$newData->lastts_connect_time}"
                        : '',
                ]);
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage() . " - Error syncing {$member->name} - {$member->clan_id}");
            }

            $differences = $newData->diff($oldData)->filter()->all();


            if (\count($differences) > 0) {
                echo ("Found updates for {$oldData['name']}") . PHP_EOL;

                $updates = [];

                // only update things that have changed
                foreach ($differences as $key => $value) {

                    $updates[$key] = $newData[$key];

                    if ('rank_id' == $key) {
                        \Log::debug('Saw a rank change!');
                        $member->last_promoted_at = now();
                        RankAction::create([
                            'member_id' => $member->id,
                            'rank_id'   => $newData[$key],
                        ]);
                    }

                    if ('division_id' == $key) {
                        \Log::debug('Saw a division change!');
                        Transfer::create([
                            'member_id'   => $member->id,
                            'division_id' => $newData[$key],
                        ]);
                    }

                    if ('name' == $key && $user = $member->user) {
                        \Log::debug('Saw a name change!');
                        $user->name = $newData[$key];
                        $user->save();
                    }
                }

                $member->update($updates);
            }
        }

        // handle new members not in the tracker
        $syncTable = \DB::connection('sqlite')->table('aod_member_sync');
        $activeIds = \App\Models\Member::where('division_id', '!=', 0)->pluck('clan_id');
        $membersToAdd = $syncTable->where('aoddivision', '!=', 'None')
            ->whereNotIn('userid', $activeIds)->get();

        foreach ($membersToAdd as $member) {
            \App\Models\Member::create([
                'allow_pm'     => $member->allow_pm,
                'clan_id'      => $member->userid,
                'discord'      => $member->discordtag,
                'division_id'  => $divisionIds[$member->aoddivision],
                'name'         => str_replace('AOD_', '', $member->username),
                'posts'        => $member->postcount,
                'privacy_flag' => 'yes' !== $member->allow_export ? 0 : 1,
                'rank_id'      => ($member->aodrankval - 2 <= 0) ? 1 : $member->aodrankval - 2,
                'ts_unique_id' => $member->tsid,

                // these can be null, and they piss me off
                'last_activity' => '' !== $member->lastactivity
                    ? \Carbon::createFromTimeString("{$member->lastactivity} {$member->lastactivity_time}")
                        ->format('Y-m-d')
                    : '',
                'last_ts_activity' => '' !== $member->lastts_connect
                    ? \Carbon::createFromTimeString("{$member->lastts_connect} {$member->lastts_connect_time}")
                        ->format('Y-m-d')
                    : '',
            ]);
        }

        $syncTable->truncate();
    }

    private static function hardResetMember(Member $member)
    {
        // reset member data
        $member->resetPositionAndAssignments();

        // reset any leadership assignments
        $assignments = collect([
            Squad::whereLeaderId($member->clan_id)->first(),
            Platoon::whereLeaderId($member->clan_id)->first(),
        ]);

        if ($assignments->count()) {
            $assignments->each(function ($model) use ($member) {
                if ($model) {
                    $model->leader()->dissociate($member->leader_id)->save();
                }
            });
        }

        if ($user = $member->user) {
            $user->role_id = 1;
            $user->save();
        }
    }

    /**
     * Purge pending requests for active members.
     *
     * @param mixed $user_ids
     */
    private static function processMemberRequests($user_ids)
    {
        $requestsToProcess = MemberRequest::approved()
            ->whereIn('member_id', $user_ids)
            ->get();

        $requestsToProcess->each->process();
    }
}

<?php

namespace App\AOD\MemberSync;

use App\Channels\Messages\DiscordMessage;
use App\Models\Division;
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

        if (! $syncData = collect($divisionInfo->data)) {
            \Log::critical(date('Y-m-d H:i:s') . ' - MEMBER SYNC - No data available');

            exit;
        }

        $divisionIds = Division::active()->pluck('name', 'id')->flip();
        $requestIds = MemberRequest::pending()->pluck('member_id');

        $syncTable = \DB::connection('sqlite')->table('aod_member_sync');

        $syncTable->truncate();

        date_default_timezone_set('UTC');

        foreach ($syncData->chunk(50) as $chunk) {
            $syncTable->insert($chunk->toArray());
        }

        // complete any outstanding member requests
        self::processMemberRequests($syncTable->pluck('userid'));

        // iterating over members we know exist in the tracker
        $members = Member::whereNotIn('division_id', [0])
            // skip pending member requests
            ->whereNotIn('clan_id', $requestIds)
            ->get();

        foreach ($members as $member) {
            $syncTable = \DB::connection('sqlite')->table('aod_member_sync');

            $newData = $syncTable->where('userid', $member->clan_id)
                ->first();

            if (! $newData) {
                // member does not exist in sync data, so must be removed
                self::hardResetMember($member);

                continue;
            }

            $oldData = $member->toArray();

            $oldData = collect([
                'allow_pm' => $oldData['allow_pm'],
                'discord' => $oldData['discord'],
                'discord_id' => $oldData['discord_id'],
                'division_id' => $oldData['division_id'],
                'name' => $oldData['name'],
                'posts' => $oldData['posts'],
                'privacy_flag' => $oldData['privacy_flag'],
                'rank_id' => $oldData['rank_id'],
                'ts_unique_id' => $oldData['ts_unique_id'],
                'last_voice_status' => $oldData['last_voice_status'],

                // these can be null, and they piss me off
                'last_activity' => $oldData['last_activity'] !== null
                    ? Carbon::createFromTimeString($oldData['last_activity'])->format('Y-m-d H:i:s')
                    : null,
                'last_ts_activity' => $oldData['last_ts_activity'] !== null
                    ? Carbon::createFromTimeString($oldData['last_ts_activity'])->format('Y-m-d H:i:s')
                    : null,
                'last_voice_activity' => $oldData['last_voice_activity'] !== null
                    ? Carbon::createFromTimeString($oldData['last_voice_activity'])->format('Y-m-d H:i:s')
                    : null,
            ]);

            /**
             * Discord states
             *  - connected
             *  - disconnected
             *  - never_connected | null
             */
            try {
                $newData = collect([
                    'allow_pm' => $newData->allow_pm,
                    'discord' => $newData->discordtag,
                    'discord_id' => $newData->discordid,
                    'division_id' => $divisionIds[$newData->aoddivision],
                    'name' => str_replace('AOD_', '', $newData->username),
                    'posts' => $newData->postcount,
                    'privacy_flag' => $newData->allow_export !== 'yes' ? 0 : 1,
                    'rank_id' => ($newData->aodrankval - 2 <= 0) ? 1 : $newData->aodrankval - 2,
                    'ts_unique_id' => $newData->tsid,
                    'last_voice_status' => $newData->lastdiscord_status,

                    // these can be null, and they piss me off
                    'last_activity' => $newData->lastactivity !== ''
                        ? "{$newData->lastactivity} {$newData->lastactivity_time}"
                        : '',

                    'last_ts_activity' => $newData->lastts_connect !== ''
                        ? "{$newData->lastts_connect} {$newData->lastts_connect_time}"
                        : '',

                    'last_voice_activity' => (self::isBadDate($newData))
                        ? "{$newData->lastdiscord_connect} {$newData->lastdiscord_connect_time}"
                        : 'never_configured',
                ]);

            } catch (\Exception $exception) {
                if ($newData->aoddivision === 'None') {
                    // ignore these because they're dumb
                    continue;
                }

                \Log::error($exception->getMessage() . " - Error syncing {$member->name} - {$member->clan_id}");

                // @TODO: figure out why this isn't working
                (new DiscordMessage())
                    ->to('admin')
                    ->message(sprintf('Tracker sync error: %s - Error syncing %s - %d',
                        $exception->getMessage(),
                        $member->name,
                        $member->clan_id,
                    ))->error()
                    ->send();

                continue;
            }

            $differences = $newData->diffAssoc($oldData)->filter()->all();

            if (\count($differences) > 0) {
                echo sprintf('Found updates for %s %s', $oldData['name'], PHP_EOL);

                $updates = [];

                // only update things that have changed
                foreach ($differences as $key => $value) {
                    $updates[$key] = $newData[$key];

                    if ($key === 'rank_id') {
                        \Log::debug(sprintf('Saw a rank change for %s to %s', $oldData['name'], $newData[$key]));
                        $updates['last_promoted_at'] = now();
                        RankAction::create([
                            'member_id' => $member->id,
                            'rank_id' => $newData[$key],
                        ]);
                    }

                    if ($key === 'division_id') {
                        \Log::debug(sprintf('Saw a division change for %s to %s', $oldData['name'], $newData[$key]));
                        Transfer::create([
                            'member_id' => $member->id,
                            'division_id' => $newData[$key],
                        ]);

                        // wipe old division assignments
                        $updates['position_id'] = 1;
                        $updates['squad_id'] = 0;
                        $updates['platoon_id'] = 0;

                        // notify division of transfer
                        $division = Division::find($newData[$key]);
                        if ($division->settings()->get('slack_alert_member_transferred') === 'on') {
                            $division->notify(new \App\Notifications\MemberTransferred($member, $division));
                        }
                    }

                    if ($key === 'name' && $user = $member->user) {
                        \Log::debug(sprintf('Saw a username change for %s to %s', $oldData['name'], $newData[$key]));
                        $user->name = $newData[$key];
                        $user->save();
                    }
                }

                $member->update($updates);
            }
        }

        // handle new members not in the tracker
        $syncTable = \DB::connection('sqlite')->table('aod_member_sync');
        $activeIds = Member::where('division_id', '!=', 0)->pluck('clan_id');

        $membersToAdd = $syncTable->where('aoddivision', '!=', 'None')
            ->whereNotIn('userid', $activeIds)->get();

        foreach ($membersToAdd as $member) {
            Member::updateOrCreate([
                'clan_id' => $member->userid,
            ], [
                'allow_pm' => $member->allow_pm,
                'discord' => $member->discordtag,
                'discord_id' => $member->discordid,
                'division_id' => $divisionIds[$member->aoddivision],
                'name' => str_replace('AOD_', '', $member->username),
                'posts' => $member->postcount,
                'privacy_flag' => $member->allow_export !== 'yes' ? 0 : 1,
                'rank_id' => ($member->aodrankval - 2 <= 0) ? 1 : $member->aodrankval - 2,
                'ts_unique_id' => $member->tsid,

                // these can be null, and they piss me off
                'last_activity' => $member->lastactivity !== ''
                    ? \Carbon::createFromTimeString("{$member->lastactivity} {$member->lastactivity_time}")
                        ->format('Y-m-d')
                    : '',
                'last_ts_activity' => $member->lastts_connect !== ''
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
     * @param  mixed  $user_ids
     */
    private static function processMemberRequests($user_ids)
    {
        $requestsToProcess = MemberRequest::approved()->get();

        $requestsToProcess->each(function ($request) use ($user_ids) {
            if ($user_ids->contains($request->member_id)) {
                $request->process();
            }
        });
    }

    private static function isBadDate($newData): bool
    {
        return ! empty($newData->lastdiscord_connect) && $newData->lastdiscord_connect !== '1970';
    }
}

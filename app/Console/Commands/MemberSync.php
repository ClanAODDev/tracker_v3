<?php

namespace App\Console\Commands;

use App\AOD\MemberSync\GetDivisionInfo;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Schema;

class MemberSync extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:membersync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs member sync with AOD forums';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $syncData = collect((new GetDivisionInfo)->data)) {
            \Log::critical(date('Y-m-d H:i:s') . ' - MEMBER SYNC - No data available');

            exit;
        }

        $divisionIds = cache()->remember('division_ids', 60 * 60, function () {
            return Division::active()->pluck('name', 'id')->flip();
        });

        $requestIds = MemberRequest::pending()->pluck('member_id');

        $this->buildSyncTable();

        $syncTable = \DB::connection('sqlite')->table('aod_member_sync');

        $syncTable->truncate();

        foreach ($syncData->chunk(50) as $chunk) {
            $syncTable->insert($chunk->toArray());
        }

        // complete any outstanding member requests
        $this->processMemberRequests($syncTable->pluck('userid'));

        // iterating over members we know exist in the tracker
        $members = Member::whereNotIn('division_id', [0])
            // skip pending member requests
            ->whereNotIn('clan_id', $requestIds)
            ->get();

        $syncTable = collect(\DB::connection('sqlite')->table('aod_member_sync')->get());

        $syncTableMap = $syncTable->keyBy('userid');

        foreach ($members as $member) {

            $newData = $syncTableMap->get($member->clan_id);

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
                'rank' => $oldData['rank'],
                'ts_unique_id' => $oldData['ts_unique_id'],
                'last_voice_status' => $oldData['last_voice_status'],

                'last_activity' => Carbon::parse($oldData['last_activity'])->timestamp,
                'last_voice_activity' => Carbon::parse($oldData['last_voice_activity'])->timestamp,
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
                    'rank' => convertRankToForum($newData->aodrankval),
                    'ts_unique_id' => $newData->tsid,
                    'last_voice_status' => $newData->lastdiscord_status,
                    'last_activity' => $newData->lastactivity,
                    'last_voice_activity' => $newData->lastdiscord_connect,
                ]);

            } catch (\Exception $exception) {
                if ($newData->aoddivision === 'None') {
                    // ignore these because they're dumb
                    continue;
                }

                \Log::error('Error syncing member', [
                    'clan_id' => $member->clan_id,
                    'division_id' => $newData->aoddivision,
                    'error' => $exception->getMessage(),
                ]);

                continue;
            }

            $differences = $newData->diffAssoc($oldData)->filter()->all();

            if (\count($differences) > 0) {
                echo sprintf(
                    'Found updates for %s (%s) %s',
                    $oldData['name'],
                    implode(',', array_keys($differences)),
                    PHP_EOL
                );

                $updates = [];

                // only update things that have changed
                foreach ($differences as $key => $value) {
                    $updates[$key] = $newData[$key];

                    //                    if ($key === 'rank') {
                    //                        $newRank = Rank::from($newData[$key]);
                    //                        $oldRank = Rank::from($oldData[$key]);
                    //
                    //                        if ($member->division->settings()->get('chat_alerts.member_promoted')) {
                    //                            if ($newRank->isPromotion(previousRank: $oldRank)) {
                    //                                $member->division->notify(new Promotion($member->name, $newRank->getLabel()));
                    //                            }
                    //                        }
                    //
                    //                        $updates['last_promoted_at'] = now();
                    //                        RankAction::create([
                    //                            'member_id' => $member->id,
                    //                            'rank' => $newRank,
                    //                        ]);
                    //                    }

                    if ($key === 'division_id') {
                        \Log::debug(sprintf('Saw a division change for %s to %s', $oldData['name'], $newData[$key]));
                        Transfer::create([
                            'member_id' => $member->id,
                            'division_id' => $newData[$key],
                        ]);

                        // wipe old division assignments
                        $updates['position'] = Position::MEMBER;
                        $updates['squad_id'] = 0;
                        $updates['platoon_id'] = 0;

                        // notify old and new divisions of outgoing, incoming transfer
                        $divisionIds = [$newData[$key], $oldData[$key]];
                        $divisions = Division::whereIn('id', $divisionIds)->get()->keyBy('id');

                        $newDivision = $divisions[$newData[$key]];
                        $oldDivision = $divisions[$oldData[$key]];

                        $newDivision->notify(new \App\Notifications\MemberTransferred($member, $newDivision->name));
                        $oldDivision->notify(new \App\Notifications\MemberTransferred($member, $newDivision->name));

                    }

                    if ($key === 'name' && $user = $member->user) {
                        \Log::debug(sprintf('Saw a username change for %s to %s', $oldData[$key], $newData[$key]));
                        $user->update(['name' => $newData[$key]]);
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
            try {
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
                    'rank' => ($member->aodrankval - 2 <= 0) ? 1 : $member->aodrankval - 2,
                    'ts_unique_id' => $member->tsid,
                    'last_activity' => $member->lastactivity,
                    'last_voice_activity' => $member->lastdiscord_connect,
                ]);
            } catch (\Exception $exception) {
                \Log::error('Exception thrown when creating member - %d - %s', [
                    'user' => $member->userid,
                    'exception' => $exception->getMessage(),
                ]);
            }

            \Log::info(sprintf('Added %s - %s', $member->username, $member->userid));
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
    private function processMemberRequests($user_ids)
    {
        $requestsToProcess = MemberRequest::approved()->get();

        $requestsToProcess->each(function ($request) use ($user_ids) {
            if ($user_ids->contains($request->member_id)) {
                $request->process();
            }
        });
    }

    private function buildSyncTable()
    {
        Schema::connection('sqlite')->dropIfExists('aod_member_sync');
        Schema::connection('sqlite')->create('aod_member_sync', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userid');
            $table->string('username');
            $table->date('joindate');
            $table->string('lastvisit');
            $table->string('lastvisit_time');
            $table->string('lastactivity');
            $table->string('lastactivity_time');
            $table->string('lastpost');
            $table->string('lastpost_time');
            $table->integer('postcount');
            $table->string('tsid');
            $table->string('lastts_connect');
            $table->string('lastts_connect_time');
            $table->string('lastdiscord_connect'); // last day in a voice channel
            $table->string('lastdiscord_connect_time'); // last time in a voice channel
            $table->string('lastdiscord_status'); // discord connection
            $table->string('aodrank');
            $table->integer('aodrankval');
            $table->string('aoddivision');
            $table->string('aodstatus');
            $table->string('discordtag');
            $table->string('discordid');
            $table->boolean('allow_export');
            $table->boolean('allow_pm');
        });
    }

    private function parseTimestamp($timestamp)
    {
        return $timestamp !== null ? Carbon::createFromTimestamp($timestamp)->toDateTimeString() : null;
    }
}

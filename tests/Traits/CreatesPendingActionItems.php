<?php

namespace Tests\Traits;

use App\Enums\DiscordStatus;
use App\Enums\Position;
use App\Models\Award;
use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberAward;
use App\Models\Ticket;
use App\Models\Transfer;

trait CreatesPendingActionItems
{
    protected function createInactiveMembers(Division $division, int $count = 2): void
    {
        $maxDays = config('aod.maximum_days_inactive', 14);

        Member::factory()->count($count)->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays($maxDays + 5),
        ]);
    }

    protected function createDivisionAwardRequests(Division $division, int $count = 2): void
    {
        $award   = Award::factory()->create(['division_id' => $division->id]);
        $members = Member::factory()->count($count)->create(['division_id' => $division->id]);

        foreach ($members as $member) {
            MemberAward::factory()->pending()->create([
                'award_id'  => $award->id,
                'member_id' => $member->clan_id,
            ]);
        }
    }

    protected function createClanAwardRequests(int $count = 2): void
    {
        $award = Award::factory()->global()->create();
        MemberAward::factory()->pending()->count($count)->create(['award_id' => $award->id]);
    }

    protected function createPendingTransfers(Division $division, int $count = 2): void
    {
        Transfer::factory()->pending()->count($count)->create([
            'division_id' => $division->id,
        ]);
    }

    protected function createPendingLeaves(Division $division, int $count = 2): void
    {
        $members = Member::where('division_id', $division->id)->take($count)->get();

        if ($members->count() < $count) {
            $needed     = $count - $members->count();
            $newMembers = Member::factory()->count($needed)->create(['division_id' => $division->id]);
            $members    = $members->merge($newMembers);
        }

        foreach ($members as $member) {
            Leave::factory()->create([
                'member_id'   => $member->clan_id,
                'approver_id' => null,
            ]);
        }
    }

    protected function createVoiceIssues(Division $division, int $count = 2): void
    {
        Member::factory()->count($count)->create([
            'division_id'       => $division->id,
            'last_voice_status' => DiscordStatus::NEVER_CONNECTED,
        ]);
    }

    protected function createUnassignedMembers(Division $division, int $count = 2): void
    {
        Member::factory()->count($count)->create([
            'division_id' => $division->id,
            'platoon_id'  => 0,
        ]);
    }

    protected function createMembersWithoutSquad(Division $division, int $count = 2): void
    {
        $platoon = $division->platoons()->first();

        if (! $platoon) {
            $platoon = \App\Models\Platoon::factory()->create(['division_id' => $division->id]);
        }

        Member::factory()->count($count)->create([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => 0,
            'position'    => Position::MEMBER,
        ]);
    }

    protected function createOpenTickets(int $count = 2): void
    {
        Ticket::factory()->count($count)->create(['state' => 'new']);
    }

    protected function createAllPendingActionItems(Division $division): void
    {
        $this->createInactiveMembers($division);
        $this->createDivisionAwardRequests($division);
        $this->createClanAwardRequests();
        $this->createPendingTransfers($division);
        $this->createPendingLeaves($division);
        $this->createVoiceIssues($division);
        $this->createUnassignedMembers($division);
        $this->createMembersWithoutSquad($division);
        $this->createOpenTickets();
    }
}

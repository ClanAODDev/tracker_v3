<?php

namespace Tests\Unit\Services;

use App\AOD\MemberSync\GetDivisionInfo;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\Squad;
use App\Services\MemberSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sync_returns_false_when_no_data(): void
    {
        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = null;

        $service = new MemberSyncService($mockInfo);

        $this->assertFalse($service->sync());
    }

    #[Test]
    public function sync_returns_true_with_valid_data(): void
    {
        $division = Division::factory()->create();

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 12345,
                'username'            => 'AOD_TestUser',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'testuser#1234',
                'discordid'           => '123456789',
                'postcount'           => 10,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'abc123',
                'lastdiscord_status'  => 'connected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);

        $this->assertTrue($service->sync());
    }

    #[Test]
    public function sync_creates_new_members(): void
    {
        $division = Division::factory()->create();

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 99999,
                'username'            => 'AOD_NewMember',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'newmember#1234',
                'discordid'           => '999999999',
                'postcount'           => 5,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'xyz789',
                'lastdiscord_status'  => 'connected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', [
            'clan_id' => 99999,
            'name'    => 'NewMember',
        ]);

        $stats = $service->getStats();
        $this->assertEquals(1, $stats['added']);
    }

    #[Test]
    public function sync_updates_existing_members(): void
    {
        $division = Division::factory()->create();
        $member   = Member::factory()->create([
            'clan_id'     => 55555,
            'name'        => 'OldName',
            'division_id' => $division->id,
            'posts'       => 10,
        ]);

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 55555,
                'username'            => 'AOD_NewName',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'user#1234',
                'discordid'           => '555555555',
                'postcount'           => 20,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'abc123',
                'lastdiscord_status'  => 'connected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $member->refresh();
        $this->assertEquals('NewName', $member->name);
        $this->assertEquals(20, $member->posts);

        $stats = $service->getStats();
        $this->assertEquals(1, $stats['updated']);
    }

    #[Test]
    public function callbacks_are_invoked(): void
    {
        $division = Division::factory()->create();

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 77777,
                'username'            => 'AOD_CallbackTest',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'callback#1234',
                'discordid'           => '777777777',
                'postcount'           => 5,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'def456',
                'lastdiscord_status'  => 'connected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $addedMembers = [];

        $service = new MemberSyncService($mockInfo);
        $service->onAdd(function ($name, $id) use (&$addedMembers) {
            $addedMembers[] = ['name' => $name, 'id' => $id];
        });

        $service->sync();

        $this->assertCount(1, $addedMembers);
        $this->assertEquals('AOD_CallbackTest', $addedMembers[0]['name']);
    }

    #[Test]
    public function get_stats_returns_sync_statistics(): void
    {
        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [];

        $service = new MemberSyncService($mockInfo);

        $stats = $service->getStats();

        $this->assertArrayHasKey('added', $stats);
        $this->assertArrayHasKey('updated', $stats);
        $this->assertArrayHasKey('removed', $stats);
        $this->assertArrayHasKey('errors', $stats);
    }

    #[Test]
    public function skips_members_with_none_division(): void
    {
        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 88888,
                'username'            => 'AOD_NoDivision',
                'joindate'            => '2024-01-01',
                'aoddivision'         => 'None',
                'aodrankval'          => 3,
                'discordtag'          => 'none#1234',
                'discordid'           => '888888888',
                'postcount'           => 5,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'ghi789',
                'lastdiscord_status'  => 'disconnected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseMissing('members', [
            'clan_id' => 88888,
        ]);
    }

    #[Test]
    public function members_with_pending_requests_are_not_removed(): void
    {
        $division = Division::factory()->create();
        $member   = Member::factory()->create([
            'clan_id'     => 22222,
            'division_id' => $division->id,
        ]);

        MemberRequest::factory()->create([
            'member_id'      => $member->id,
            'requester_id'   => $member->id,
            'division_id'    => $division->id,
            'approved_at'    => null,
            'hold_placed_at' => null,
        ]);

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', ['clan_id' => 22222, 'division_id' => $division->id]);
        $this->assertEquals(0, $service->getStats()['removed']);
    }

    #[Test]
    public function members_with_on_hold_requests_are_not_removed(): void
    {
        $division = Division::factory()->create();
        $member   = Member::factory()->create([
            'clan_id'     => 33333,
            'division_id' => $division->id,
        ]);

        MemberRequest::factory()->create([
            'member_id'      => $member->id,
            'requester_id'   => $member->id,
            'division_id'    => $division->id,
            'approved_at'    => null,
            'hold_placed_at' => now(),
        ]);

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', ['clan_id' => 33333, 'division_id' => $division->id]);
        $this->assertEquals(0, $service->getStats()['removed']);
    }

    #[Test]
    public function members_with_approved_requests_are_removed_when_absent_from_forum(): void
    {
        $division = Division::factory()->create();
        $member   = Member::factory()->create([
            'clan_id'     => 44444,
            'division_id' => $division->id,
        ]);

        MemberRequest::factory()->create([
            'member_id'    => $member->id,
            'requester_id' => $member->id,
            'division_id'  => $division->id,
            'approved_at'  => now(),
        ]);

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 99991,
                'username'            => 'AOD_OtherMember',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'other#1234',
                'discordid'           => '999910000',
                'postcount'           => 5,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'abc000',
                'lastdiscord_status'  => 'connected',
                'lastactivity'        => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertEquals(1, $service->getStats()['removed']);
    }

    #[Test]
    public function handles_zero_date_values(): void
    {
        $division = Division::factory()->create();

        $mockInfo       = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid'              => 11111,
                'username'            => 'AOD_ZeroDate',
                'joindate'            => '2024-01-01',
                'aoddivision'         => $division->name,
                'aodrankval'          => 3,
                'discordtag'          => 'zerodate#1234',
                'discordid'           => '111111111',
                'postcount'           => 5,
                'allow_pm'            => 1,
                'allow_export'        => 'yes',
                'tsid'                => 'zero123',
                'lastdiscord_status'  => 'never_configured',
                'lastactivity'        => '0000-00-00 00:00:00',
                'lastdiscord_connect' => '0000-00-00 00:00:00',
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', [
            'clan_id'             => 11111,
            'name'                => 'ZeroDate',
            'last_activity'       => null,
            'last_voice_activity' => null,
        ]);
    }

    #[Test]
    public function a_forum_division_change_resets_platoon_and_squad(): void
    {
        [$from, $to] = [Division::factory()->create(), Division::factory()->create()];
        $platoon     = Platoon::factory()->create(['division_id' => $from->id]);
        $squad       = Squad::factory()->create(['platoon_id' => $platoon->id]);
        $member      = Member::factory()->create([
            'clan_id'     => 66666,
            'division_id' => $from->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
            'position'    => Position::MEMBER,
        ]);

        (new MemberSyncService($this->forumInfo(66666, $to->name)))->sync();

        $member->refresh();
        $this->assertSame($to->id, $member->division_id);
        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
    }

    #[Test]
    public function a_forum_division_change_vacates_the_platoon_the_member_led(): void
    {
        [$from, $to] = [Division::factory()->create(), Division::factory()->create()];
        $platoon     = Platoon::factory()->create(['division_id' => $from->id]);
        $member      = Member::factory()->create([
            'clan_id'     => 77777,
            'division_id' => $from->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => 0,
            'position'    => Position::PLATOON_LEADER,
        ]);
        $platoon->update(['leader_id' => $member->clan_id]);

        (new MemberSyncService($this->forumInfo(77777, $to->name)))->sync();

        $this->assertSame(Position::MEMBER, $member->fresh()->position);
        $this->assertEquals(0, $platoon->fresh()->leader_id);
    }

    #[Test]
    public function a_sync_without_a_division_change_keeps_platoon_and_squad(): void
    {
        $division = Division::factory()->create();
        $platoon  = Platoon::factory()->create(['division_id' => $division->id]);
        $squad    = Squad::factory()->create(['platoon_id' => $platoon->id]);
        $member   = Member::factory()->create([
            'clan_id'     => 88888,
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
            'posts'       => 1,
        ]);

        (new MemberSyncService($this->forumInfo(88888, $division->name)))->sync();

        $member->refresh();
        $this->assertSame([$platoon->id, $squad->id], [$member->platoon_id, $member->squad_id]);
    }

    private function forumInfo(int $userId, string $divisionName): GetDivisionInfo
    {
        $info       = Mockery::mock(GetDivisionInfo::class);
        $info->data = [[
            'userid'              => $userId,
            'username'            => 'AOD_Mover' . $userId,
            'joindate'            => '2024-01-01',
            'aoddivision'         => $divisionName,
            'aodrankval'          => 3,
            'discordtag'          => 'mover',
            'discordid'           => (string) $userId,
            'postcount'           => 50,
            'allow_pm'            => 1,
            'allow_export'        => 'yes',
            'tsid'                => '',
            'lastdiscord_status'  => 'connected',
            'lastactivity'        => time(),
            'lastdiscord_connect' => time(),
        ]];

        return $info;
    }
}

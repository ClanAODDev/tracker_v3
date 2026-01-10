<?php

namespace Tests\Unit\Services;

use App\AOD\MemberSync\GetDivisionInfo;
use App\Models\Division;
use App\Models\Member;
use App\Services\MemberSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MemberSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_returns_false_when_no_data(): void
    {
        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = null;

        $service = new MemberSyncService($mockInfo);

        $this->assertFalse($service->sync());
    }

    public function test_sync_returns_true_with_valid_data(): void
    {
        $division = Division::factory()->create();

        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 12345,
                'username' => 'AOD_TestUser',
                'joindate' => '2024-01-01',
                'aoddivision' => $division->name,
                'aodrankval' => 3,
                'discordtag' => 'testuser#1234',
                'discordid' => '123456789',
                'postcount' => 10,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'abc123',
                'lastdiscord_status' => 'connected',
                'lastactivity' => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);

        $this->assertTrue($service->sync());
    }

    public function test_sync_creates_new_members(): void
    {
        $division = Division::factory()->create();

        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 99999,
                'username' => 'AOD_NewMember',
                'joindate' => '2024-01-01',
                'aoddivision' => $division->name,
                'aodrankval' => 3,
                'discordtag' => 'newmember#1234',
                'discordid' => '999999999',
                'postcount' => 5,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'xyz789',
                'lastdiscord_status' => 'connected',
                'lastactivity' => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', [
            'clan_id' => 99999,
            'name' => 'NewMember',
        ]);

        $stats = $service->getStats();
        $this->assertEquals(1, $stats['added']);
    }

    public function test_sync_updates_existing_members(): void
    {
        $division = Division::factory()->create();
        $member = Member::factory()->create([
            'clan_id' => 55555,
            'name' => 'OldName',
            'division_id' => $division->id,
            'posts' => 10,
        ]);

        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 55555,
                'username' => 'AOD_NewName',
                'joindate' => '2024-01-01',
                'aoddivision' => $division->name,
                'aodrankval' => 3,
                'discordtag' => 'user#1234',
                'discordid' => '555555555',
                'postcount' => 20,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'abc123',
                'lastdiscord_status' => 'connected',
                'lastactivity' => time(),
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

    public function test_callbacks_are_invoked(): void
    {
        $division = Division::factory()->create();

        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 77777,
                'username' => 'AOD_CallbackTest',
                'joindate' => '2024-01-01',
                'aoddivision' => $division->name,
                'aodrankval' => 3,
                'discordtag' => 'callback#1234',
                'discordid' => '777777777',
                'postcount' => 5,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'def456',
                'lastdiscord_status' => 'connected',
                'lastactivity' => time(),
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

    public function test_get_stats_returns_sync_statistics(): void
    {
        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [];

        $service = new MemberSyncService($mockInfo);

        $stats = $service->getStats();

        $this->assertArrayHasKey('added', $stats);
        $this->assertArrayHasKey('updated', $stats);
        $this->assertArrayHasKey('removed', $stats);
        $this->assertArrayHasKey('errors', $stats);
    }

    public function test_skips_members_with_none_division(): void
    {
        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 88888,
                'username' => 'AOD_NoDivision',
                'joindate' => '2024-01-01',
                'aoddivision' => 'None',
                'aodrankval' => 3,
                'discordtag' => 'none#1234',
                'discordid' => '888888888',
                'postcount' => 5,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'ghi789',
                'lastdiscord_status' => 'disconnected',
                'lastactivity' => time(),
                'lastdiscord_connect' => time(),
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseMissing('members', [
            'clan_id' => 88888,
        ]);
    }

    public function test_handles_zero_date_values(): void
    {
        $division = Division::factory()->create();

        $mockInfo = Mockery::mock(GetDivisionInfo::class);
        $mockInfo->data = [
            [
                'userid' => 11111,
                'username' => 'AOD_ZeroDate',
                'joindate' => '2024-01-01',
                'aoddivision' => $division->name,
                'aodrankval' => 3,
                'discordtag' => 'zerodate#1234',
                'discordid' => '111111111',
                'postcount' => 5,
                'allow_pm' => 1,
                'allow_export' => 'yes',
                'tsid' => 'zero123',
                'lastdiscord_status' => 'never_configured',
                'lastactivity' => '0000-00-00 00:00:00',
                'lastdiscord_connect' => '0000-00-00 00:00:00',
            ],
        ];

        $service = new MemberSyncService($mockInfo);
        $service->sync();

        $this->assertDatabaseHas('members', [
            'clan_id' => 11111,
            'name' => 'ZeroDate',
            'last_activity' => null,
            'last_voice_activity' => null,
        ]);
    }
}

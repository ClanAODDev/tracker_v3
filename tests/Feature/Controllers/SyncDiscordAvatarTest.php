<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Services\AODBotService;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class SyncDiscordAvatarTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function member_can_sync_their_own_avatar(): void
    {
        $user = $this->createMemberWithUser(['discord_id' => '123456789012345678']);

        $this->mock(AODBotService::class)
            ->shouldReceive('getMemberAvatar')
            ->with('123456789012345678')
            ->andReturn('newhash123');

        $response = $this->actingAs($user)
            ->postJson(route('settings.sync-avatar'));

        $response->assertOk();
        $response->assertJsonStructure(['avatarUrl']);

        $this->assertEquals('newhash123', $user->member->fresh()->discord_avatar);
    }

    #[Test]
    public function member_without_discord_id_is_forbidden(): void
    {
        $user = $this->createMemberWithUser(['discord_id' => null]);

        $response = $this->actingAs($user)
            ->postJson(route('settings.sync-avatar'));

        $response->assertForbidden();
    }

    #[Test]
    public function user_without_member_is_blocked_by_middleware(): void
    {
        $user = User::factory()->create(['member_id' => null]);

        $response = $this->actingAs($user)
            ->postJson(route('settings.sync-avatar'));

        $response->assertStatus(408);
    }

    #[Test]
    public function unauthenticated_user_is_redirected(): void
    {
        $response = $this->postJson(route('settings.sync-avatar'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function bot_api_failure_returns_503(): void
    {
        $user = $this->createMemberWithUser(['discord_id' => '123456789012345678']);

        $this->mock(AODBotService::class)
            ->shouldReceive('getMemberAvatar')
            ->andThrow(new TransferException('Connection failed'));

        $response = $this->actingAs($user)
            ->postJson(route('settings.sync-avatar'));

        $response->assertStatus(503);
        $response->assertJson(['message' => 'Failed to reach Discord bot']);
    }
}

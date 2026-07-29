<?php

namespace Tests\Unit\Services;

use App\Services\AODBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class AODBotServiceTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);
    }

    #[Test]
    public function get_forum_member_makes_request_to_correct_url()
    {
        Http::fake([
            '*' => Http::response(['id' => 12345, 'name' => 'TestUser'], 200),
        ]);

        $response = (new AODBotService)->getForumMember(12345);

        $this->assertTrue($response->successful());
        Http::assertSent(fn ($request) => $request->url() === 'https://bot.example.com/forum_member/12345');
    }

    #[Test]
    public function get_forum_member_returns_response_instance()
    {
        Http::fake([
            '*' => Http::response(['id' => 12345, 'name' => 'TestUser'], 200),
        ]);

        $response = (new AODBotService)->getForumMember(12345);

        $this->assertInstanceOf(Response::class, $response);
    }

    #[Test]
    public function get_forum_member_response_body_contains_member_data()
    {
        $expectedData = ['id' => 12345, 'name' => 'TestUser', 'rank' => 'CPL'];

        Http::fake([
            '*' => Http::response($expectedData, 200),
        ]);

        $response = (new AODBotService)->getForumMember(12345);

        $this->assertEquals($expectedData, $response->json());
    }

    #[Test]
    public function update_discord_member_makes_request()
    {
        Http::fake([
            '*' => Http::response(['status' => 'updated'], 200),
        ]);

        $response = (new AODBotService)->updateDiscordMember('123456789012345678');

        $this->assertTrue($response->successful());
        Http::assertSent(fn ($request) => $request->url() === 'https://bot.example.com/members/123456789012345678/update');
    }

    #[Test]
    public function update_discord_member_returns_response_with_status()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'updated' => true], 200),
        ]);

        $response = (new AODBotService)->updateDiscordMember('123456789012345678');

        $this->assertEquals('success', $response->json('status'));
        $this->assertTrue($response->json('updated'));
    }

    #[Test]
    public function service_includes_authorization_header()
    {
        Http::fake(['*' => Http::response([], 200)]);

        (new AODBotService)->getForumMember(12345);

        Http::assertSent(fn ($request) => str_contains($request->header('Authorization')[0] ?? '', 'Bearer'));
    }

    #[Test]
    public function service_includes_content_type_header()
    {
        Http::fake(['*' => Http::response([], 200)]);

        (new AODBotService)->getForumMember(12345);

        Http::assertSent(fn ($request) => ($request->header('Content-Type')[0] ?? '') === 'application/json');
    }

    #[Test]
    public function service_includes_requested_by_header_for_authenticated_user()
    {
        $user = $this->createMemberWithUser(['discord_id' => '999888777']);
        $this->actingAs($user);

        Http::fake(['*' => Http::response([], 200)]);

        (new AODBotService)->getForumMember(12345);

        Http::assertSent(fn ($request) => ($request->header('X-Requested-By')[0] ?? null) === '999888777');
    }

    #[Test]
    public function get_member_avatar_returns_avatar_hash()
    {
        Http::fake([
            '*' => Http::response(['avatarHash' => 'abc123def456'], 200),
        ]);

        $hash = (new AODBotService)->getMemberAvatar('123456789012345678');

        $this->assertEquals('abc123def456', $hash);
    }

    #[Test]
    public function get_member_avatar_returns_null_when_no_avatar()
    {
        Http::fake([
            '*' => Http::response(['avatarHash' => null], 200),
        ]);

        $hash = (new AODBotService)->getMemberAvatar('123456789012345678');

        $this->assertNull($hash);
    }

    #[Test]
    public function send_throws_on_server_error_response()
    {
        Http::fake(['*' => Http::response('Internal Server Error', 500)]);

        $this->expectException(RequestException::class);

        (new AODBotService)->getForumMember(12345);
    }

    #[Test]
    public function send_retries_after_a_transient_connection_failure()
    {
        Http::fake([
            '*' => Http::sequence()
                ->pushFailedConnection()
                ->push(['id' => 12345], 200),
        ]);

        $response = (new AODBotService)->getForumMember(12345);

        $this->assertTrue($response->successful());
    }
}

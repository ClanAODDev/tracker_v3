<?php

namespace Tests\Unit\Services;

use App\Services\AODForumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AODForumServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.token' => 'test-token']);
    }

    public function test_request_makes_http_get_with_auth_token()
    {
        Http::fake([
            '*' => Http::response('success', 200),
        ]);

        AODForumService::request('https://example.com/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'authcode2=');
        });
    }

    public function test_request_uses_custom_token_param()
    {
        Http::fake([
            '*' => Http::response('success', 200),
        ]);

        AODForumService::request('https://example.com/test', ['_token_param' => 'custom_auth']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'custom_auth=');
        });
    }

    public function test_request_returns_json_when_response_is_json()
    {
        Http::fake([
            '*' => Http::response(['status' => 'ok', 'data' => 'test'], 200),
        ]);

        $result = AODForumService::request('https://example.com/test');

        $this->assertIsArray($result);
        $this->assertEquals('ok', $result['status']);
    }

    public function test_request_returns_last_word_when_response_is_not_json()
    {
        Http::fake([
            '*' => Http::response('<html>Some text result</html>', 200),
        ]);

        $result = AODForumService::request('https://example.com/test');

        $this->assertEquals('result', $result);
    }

    public function test_request_returns_error_message_on_exception()
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        Http::fake(function () {
            throw new \Exception('Connection failed');
        });

        $result = AODForumService::request('https://example.com/test');

        $this->assertEquals('Error: Invalid user context', $result);
    }

    public function test_add_forum_member_sends_correct_parameters()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        AODForumService::addForumMember(
            impersonatingMemberId: 12345,
            memberIdBeingAdded: 67890,
            rank: 'PFC',
            name: 'TestMember',
            division: 'Test Division'
        );

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'aod_userid=12345')
                && str_contains($url, 'do=addaod')
                && str_contains($url, 'aodname=TestMember')
                && str_contains($url, 'rank=PFC')
                && str_contains($url, 'division=Test')
                && str_contains($url, 'u=67890');
        });
    }

    public function test_add_forum_member_does_not_throw_on_success()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $result = AODForumService::addForumMember(12345, 67890, 'PFC', 'TestMember', 'Test');

        $this->assertNotNull($result);
    }

    public function test_add_forum_member_throws_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_invalid_user', 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to add member to AOD - TestMember');

        AODForumService::addForumMember(12345, 67890, 'PFC', 'TestMember', 'Test');
    }

    public function test_remove_forum_member_sends_correct_parameters()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        AODForumService::removeForumMember(
            memberIdBeingRemoved: 67890,
            impersonatingMemberId: 12345
        );

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'aod_userid=12345')
                && str_contains($url, 'do=remaod')
                && str_contains($url, 'u=67890');
        });
    }

    public function test_remove_forum_member_does_not_throw_on_success()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $result = AODForumService::removeForumMember(67890, 12345);

        $this->assertNotNull($result);
    }

    public function test_remove_forum_member_throws_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_user_not_found', 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to remove member 67890');

        AODForumService::removeForumMember(67890, 12345);
    }

    public function test_fetch_info_calls_info_url()
    {
        Http::fake([
            'https://www.clanaod.net/forums/aodinfo.php*' => Http::response(['status' => 'ok'], 200),
        ]);

        $result = AODForumService::fetchInfo(['user' => 123]);

        $this->assertIsArray($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'aodinfo.php');
        });
    }

    public function test_fetch_info_passes_params()
    {
        Http::fake([
            '*' => Http::response(['name' => 'TestUser'], 200),
        ]);

        AODForumService::fetchInfo(['user_id' => 12345, 'include_ranks' => true]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'user_id=12345')
                && str_contains($request->url(), 'include_ranks=1');
        });
    }
}

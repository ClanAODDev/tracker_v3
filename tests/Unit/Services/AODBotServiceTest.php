<?php

namespace Tests\Unit\Services;

use App\Services\AODBotService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class AODBotServiceTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    private function createServiceWithMockedClient(array $responses): AODBotService
    {
        $mock         = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client       = new Client(['handler' => $handlerStack]);

        $service = new AODBotService;

        $reflection     = new \ReflectionClass($service);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($service, $client);

        return $service;
    }

    public function test_get_forum_member_makes_request_to_correct_url()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);

        $service = $this->createServiceWithMockedClient([
            new Response(200, [], json_encode(['id' => 12345, 'name' => 'TestUser'])),
        ]);

        $response = $service->getForumMember(12345);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_get_forum_member_returns_response_interface()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);

        $service = $this->createServiceWithMockedClient([
            new Response(200, [], json_encode(['id' => 12345, 'name' => 'TestUser'])),
        ]);

        $response = $service->getForumMember(12345);

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }

    public function test_get_forum_member_response_body_contains_member_data()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);

        $expectedData = ['id' => 12345, 'name' => 'TestUser', 'rank' => 'CPL'];

        $service = $this->createServiceWithMockedClient([
            new Response(200, [], json_encode($expectedData)),
        ]);

        $response = $service->getForumMember(12345);
        $body     = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($expectedData, $body);
    }

    public function test_update_discord_member_makes_request()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);

        $service = $this->createServiceWithMockedClient([
            new Response(200, [], json_encode(['status' => 'updated'])),
        ]);

        $response = $service->updateDiscordMember('123456789012345678');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_discord_member_returns_response_with_status()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-token']);

        $service = $this->createServiceWithMockedClient([
            new Response(200, [], json_encode(['status' => 'success', 'updated' => true])),
        ]);

        $response = $service->updateDiscordMember('123456789012345678');
        $body     = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('success', $body['status']);
        $this->assertTrue($body['updated']);
    }

    public function test_service_includes_authorization_header()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-bot-token']);

        $requestedHeaders = null;

        $mock = new MockHandler([
            function ($request) use (&$requestedHeaders) {
                $requestedHeaders = $request->getHeaders();

                return new Response(200, [], '{}');
            },
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client       = new Client(['handler' => $handlerStack]);

        $service        = new AODBotService;
        $reflection     = new \ReflectionClass($service);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($service, $client);

        $service->getForumMember(12345);

        $this->assertArrayHasKey('Authorization', $requestedHeaders);
        $this->assertStringContainsString('Bearer', $requestedHeaders['Authorization'][0]);
    }

    public function test_service_includes_content_type_header()
    {
        config(['aod.bot_api_base_url' => 'https://bot.example.com']);
        config(['aod.discord_bot_token' => 'test-bot-token']);

        $requestedHeaders = null;

        $mock = new MockHandler([
            function ($request) use (&$requestedHeaders) {
                $requestedHeaders = $request->getHeaders();

                return new Response(200, [], '{}');
            },
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client       = new Client(['handler' => $handlerStack]);

        $service        = new AODBotService;
        $reflection     = new \ReflectionClass($service);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($service, $client);

        $service->getForumMember(12345);

        $this->assertArrayHasKey('Content-Type', $requestedHeaders);
        $this->assertEquals('application/json', $requestedHeaders['Content-Type'][0]);
    }
}

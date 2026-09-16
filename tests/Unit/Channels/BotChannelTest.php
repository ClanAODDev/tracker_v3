<?php

namespace Tests\Unit\Channels;

use App\Channels\BotChannel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class BotChannelTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    private function channelWithResponse(ClientException|ServerException $exception, Logger $logger): BotChannel
    {
        $mock   = new MockHandler([$exception]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        return new BotChannel($client, $logger);
    }

    private function memberNotification(): Notification
    {
        return new class extends Notification
        {
            public function toBot($notifiable)
            {
                return ['api_uri' => 'members/123456', 'body' => ['embeds' => [['description' => 'test']]]];
            }
        };
    }

    private function channelNotification(): Notification
    {
        return new class extends Notification
        {
            public function toBot($notifiable)
            {
                return ['api_uri' => 'channels/999', 'body' => ['embeds' => [['description' => 'test']]]];
            }
        };
    }

    #[Test]
    public function unknown_member_404_is_swallowed_without_throwing()
    {
        $logger = Mockery::mock(Logger::class);
        $logger->shouldReceive('warning')->once()->with(
            'BotChannel: member not found on Discord, skipping',
            Mockery::type('array')
        );
        $logger->shouldNotReceive('error');

        $exception = new ClientException(
            'Not Found',
            new Request('POST', 'members/123456'),
            new Response(404, [], json_encode(['message' => 'Unknown Member']))
        );

        $channel = $this->channelWithResponse($exception, $logger);

        $channel->send(null, $this->memberNotification());
    }

    #[Test]
    public function non_member_404_still_throws_and_logs_error()
    {
        $logger = Mockery::mock(Logger::class);
        $logger->shouldReceive('error')->once()->with('BotChannel request failed', Mockery::type('array'));
        $logger->shouldNotReceive('warning');

        $exception = new ClientException(
            'Not Found',
            new Request('POST', 'channels/999'),
            new Response(404, [], json_encode(['message' => 'Unknown Channel']))
        );

        $channel = $this->channelWithResponse($exception, $logger);

        $this->expectException(ClientException::class);

        $channel->send(null, $this->channelNotification());
    }

    #[Test]
    public function non_404_member_error_still_throws_and_logs_error()
    {
        $logger = Mockery::mock(Logger::class);
        $logger->shouldReceive('error')->once()->with('BotChannel request failed', Mockery::type('array'));
        $logger->shouldNotReceive('warning');

        $exception = new ClientException(
            'Server Error',
            new Request('POST', 'members/123456'),
            new Response(500, [], 'boom')
        );

        $channel = $this->channelWithResponse($exception, $logger);

        $this->expectException(ClientException::class);

        $channel->send(null, $this->memberNotification());
    }

    #[Test]
    public function bot_down_response_posts_alert_to_webhook_and_still_throws()
    {
        config(['aod.exception_alerts_webhook' => 'https://discord.com/api/webhooks/it-team']);
        Http::fake();

        $exception = new ServerException(
            'Service Unavailable',
            new Request('POST', 'channels/999'),
            new Response(503, [], json_encode(['error' => 'Discord client not ready']))
        );

        $channel = $this->channelWithResponse($exception, Mockery::mock(Logger::class)->shouldIgnoreMissing());

        $this->expectException(ServerException::class);

        try {
            $channel->send(null, $this->channelNotification());
        } finally {
            Http::assertSent(fn ($request) => $request->url() === 'https://discord.com/api/webhooks/it-team'
                && str_contains($request['content'], 'Discord bot is down'));
        }
    }

    #[Test]
    public function bot_down_alert_is_throttled_to_one_per_hour()
    {
        config(['aod.exception_alerts_webhook' => 'https://discord.com/api/webhooks/it-team']);
        Http::fake();

        $makeChannel = fn () => $this->channelWithResponse(
            new ServerException(
                'Service Unavailable',
                new Request('POST', 'channels/999'),
                new Response(503, [], json_encode(['error' => 'Discord client not ready']))
            ),
            Mockery::mock(Logger::class)->shouldIgnoreMissing()
        );

        foreach (range(1, 2) as $attempt) {
            try {
                $makeChannel()->send(null, $this->channelNotification());
            } catch (ServerException) {
                // expected on every attempt
            }
        }

        Http::assertSentCount(1);
    }

    #[Test]
    public function bot_down_alert_is_skipped_when_webhook_not_configured()
    {
        config(['aod.exception_alerts_webhook' => null]);
        Http::fake();

        $exception = new ServerException(
            'Service Unavailable',
            new Request('POST', 'channels/999'),
            new Response(503, [], json_encode(['error' => 'Discord client not ready']))
        );

        $channel = $this->channelWithResponse($exception, Mockery::mock(Logger::class)->shouldIgnoreMissing());

        try {
            $channel->send(null, $this->channelNotification());
        } catch (ServerException) {
            // expected
        }

        Http::assertNothingSent();
    }

    #[Test]
    public function x_requested_by_header_is_set_for_authenticated_user_with_discord_id()
    {
        $user = $this->createMemberWithUser();
        $user->member->update(['discord_id' => '999888777']);

        $this->actingAs($user);

        $history      = [];
        $mock         = new MockHandler([new Response(200, [], json_encode(['id' => 1]))]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));
        $client = new Client(['handler' => $handlerStack]);

        $channel = new BotChannel($client, Mockery::mock(Logger::class)->shouldIgnoreMissing());
        $channel->send(null, $this->memberNotification());

        $this->assertSame('999888777', $history[0]['request']->getHeaderLine('X-Requested-By'));
    }

    #[Test]
    public function x_requested_by_header_is_omitted_when_unauthenticated()
    {
        $history      = [];
        $mock         = new MockHandler([new Response(200, [], json_encode(['id' => 1]))]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));
        $client = new Client(['handler' => $handlerStack]);

        $channel = new BotChannel($client, Mockery::mock(Logger::class)->shouldIgnoreMissing());
        $channel->send(null, $this->memberNotification());

        $this->assertFalse($history[0]['request']->hasHeader('X-Requested-By'));
    }
}

<?php

namespace Tests\Unit\Channels;

use App\Channels\BotChannel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notification;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BotChannelTest extends TestCase
{
    private function channelWithResponse(ClientException $exception, Logger $logger): BotChannel
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
}

<?php

namespace App\Channels;

use App\Notifications\Channel\NotifyAdminTicketCreated;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notification;

class BotChannel
{
    private Client $client;

    private Logger $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toBot')) {
            $message = (array) $notification->toBot($notifiable);
        } else {
            $message = $notification->toArray($notifiable);
        }

        if (! $message) {
            return;
        }

        $url = sprintf('%s/%s', config('aod.bot_api_base_url'), $message['api_uri']);

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', config('aod.discord_bot_token')),
        ];

        if (auth()->check()) {
            array_merge($headers, ['X-Requested-By' => auth()->user()->member->discord_id]);
        }

        $request = new Request('POST', $url, $headers, json_encode($message['body']));

        try {
            $response = $this->client->send($request, ['verify' => false]);
        } catch (GuzzleException $e) {
            $this->logger->error('BotChannel request failed', [
                'url'          => $url,
                'notification' => get_class($notification),
                'error'        => $e->getMessage(),
            ]);

            return;
        }

        if ($notification instanceof NotifyAdminTicketCreated) {
            $response = json_decode($response->getBody());
            $notifiable->update(['external_message_id' => $response->id]);
        }
    }
}

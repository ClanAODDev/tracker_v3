<?php

namespace App\Channels;

use App\Exceptions\WebHookFailedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notifiable;
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

    /**
     * @param  Notifiable  $notifiable
     *
     * @throws WebHookFailedException
     * @throws GuzzleException
     */
    public function send($notifiable, Notification $notification)
    {
        if (is_string($notifiable)) {
            // support null object notifications
            $url = $notifiable;
        } else {
            $url = $notifiable->routeNotificationFor('bot', $notification);

            if (! $url) {
                return;
            }
        }

        if (method_exists($notification, 'toBot')) {
            $body = (array) $notification->toBot($notifiable);
        } else {
            $body = $notification->toArray($notifiable);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', config('app.aod.discord_bot_token')),
        ];

        if (auth()->check()) {
            array_merge($headers, ['X-Requested-By' => auth()->user()->member->discord_id]);
        }

        $request = new Request('POST', $url, $headers, json_encode($body));

        try {
            $this->client->send($request, ['verify' => false]);
        } catch (ServerException $exception) {
            \Log::error($exception->getMessage());
            \Log::error("Failing payload: " . json_encode($body));
        }
    }
}

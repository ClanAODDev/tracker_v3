<?php

namespace App\Channels;

use App\Exceptions\WebHookFailedException;
use App\Notifications\NotifyAdminTicketCreated;
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
        if (method_exists($notification, 'toBot')) {
            $message = (array) $notification->toBot($notifiable);
        } else {
            $message = $notification->toArray($notifiable);
        }

        $target = $notifiable->routeNotificationFor('bot', $notification);

        if (! $target) {
            return;
        }

        $url = sprintf('%s/%s', config('app.aod.bot_api_base_url'), str_replace(':target', $target, $message['api']));

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', config('app.aod.discord_bot_token')),
        ];

        if (auth()->check()) {
            array_merge($headers, ['X-Requested-By' => auth()->user()->member->discord_id]);
        }

        $request = new Request('POST', $url, $headers, json_encode($message['body']));

        try {
            $response = $this->client->send($request, ['verify' => false]);

            // kinda gross, let's refactor at some point
            if ($notification instanceof NotifyAdminTicketCreated) {
                // we need the resulting message id for additional actions
                $response = json_decode($response->getBody());
                $notifiable->update(['message_id' => $response->id]);
            }

        } catch (ServerException $exception) {
            \Log::error($exception->getMessage());
            \Log::error('Failing payload: ' . json_encode($message['body']));
        }
    }
}

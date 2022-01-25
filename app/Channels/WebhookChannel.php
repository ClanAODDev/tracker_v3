<?php

namespace App\Channels;

use App\Exceptions\WebHookFailedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Log\Logger;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class WebhookChannel
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param Notifiable $notifiable
     *
     * @throws WebHookFailedException
     * @throws GuzzleException
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$url = $notifiable->routeNotificationFor('webhook', $notification)) {
            return;
        }

        if (method_exists($notification, 'toWebhook')) {
            $body = (array) $notification->toWebhook($notifiable);
        } else {
            $body = $notification->toArray($notifiable);
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $request = new Request('POST', $url, $headers, json_encode(array_merge(
            $body,
            [
                'username'   => 'AOD Tracker',
                'avatar_url' => 'https://tracker.clanaod.net/images/logo_v2.png',
            ]
        )));

        $this->client->send($request);
    }
}

<?php

namespace App\Channels;

use App\Notifications\Channel\NotifyAdminTicketCreated;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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
            $context = [
                'url'          => $url,
                'notification' => get_class($notification),
                'error'        => $e->getMessage(),
            ];

            if ($e instanceof RequestException && $e->hasResponse()) {
                $context['response_status'] = $e->getResponse()->getStatusCode();
                $context['response_body']   = (string) $e->getResponse()->getBody();
            }

            if ($this->isUnknownMember($message['api_uri'], $context['response_status'] ?? null)) {
                $this->logger->warning('BotChannel: member not found on Discord, skipping', $context);

                return;
            }

            $this->logger->error('BotChannel request failed', $context);

            throw $e;
        }

        if ($notification instanceof NotifyAdminTicketCreated) {
            $response = json_decode($response->getBody());
            $notifiable->update(['external_message_id' => $response->id]);
        }
    }

    /**
     * A 404 on a members/{id} DM means Discord doesn't recognize that
     * member (they've left the guild, or their discord_id is stale) —
     * expected and not worth retrying, unlike a 404 on any other
     * endpoint, which likely signals a real configuration problem.
     */
    private function isUnknownMember(string $apiUri, ?int $responseStatus): bool
    {
        return $responseStatus === 404 && str_starts_with($apiUri, 'members/');
    }
}

<?php

namespace App\Channels\Messages;

use Exception;

class BotReactMessage
{
    public $states = [
        'resolved' => '✅',
        'rejected' => '❌',
        'assigned' => '⏳',
    ];

    private string $emote;

    public function __construct(private $notifiable) {}

    public function to(string $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function status(string $status)
    {
        if (! isset($this->states[$status])) {
            throw new Exception('Invalid status provided to BotReactMessage');
        }

        $this->emote = $this->states[$status];

        return $this;
    }

    /**
     * @throws Exception
     */
    public function send(): array
    {
        if (! $this->emote) {
            throw new Exception('A status {assigned, resolved, rejected} must be defined');
        }

        $routeTarget = $this->notifiable->routeNotificationFor('help');
        if (! isset($routeTarget)) {
            throw new Exception('A channel target must be defined');
        }

        return [
            'api_uri' => sprintf('channels/%s/messages/%s/react', $routeTarget, $this->notifiable->external_message_id),
            'body' => [
                'emoji' => $this->emote,
                'exclusive' => true,
            ],
        ];
    }
}

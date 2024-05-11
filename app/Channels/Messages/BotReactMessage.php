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

    private string $messageId;

    public function to(string $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function messageId(string $messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function status(string $status)
    {
        if (! isset($this->states[$status])) {
            throw new \Exception('Invalid status provided to BotReactMessage');
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

        if (! $this->messageId) {
            throw new Exception('A message id must be defined');
        }

        return [
            'api' => sprintf('channel/:target/%s/react', $this->messageId),
            'body'=> [
                'emoji' => $this->emote,
            ]
        ];
    }
}

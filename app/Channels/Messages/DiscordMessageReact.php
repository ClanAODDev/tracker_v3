<?php

namespace App\Channels\Messages;

class DiscordMessageReact
{
    public const RESOLVED = '✅';
    public const REJECTED = '❌';
    public const ASSIGNED = '⏳';
    private string $emote;
    private string $messageId;
    private string $channel;

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
        $statuses = [
            'assigned' => self::ASSIGNED,
            'rejected' => self::REJECTED,
            'resolved' => self::RESOLVED,
        ];

        if (!isset($statuses[$status])) {
            throw new \Exception("Invalid status provided to DiscordMessageReact");
        }

        $this->emote = $statuses[$status];

        return $this;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function send()
    {
        if (!$this->channel) {
            throw new Exception('A channel must be defined');
        }

        if (!$this->emote) {
            throw new Exception('A status {assigned, resolved, rejected} must be defined');
        }

        if (!isset($this->messageId)) {
            throw new Exception('A message id must be defined');
        }

        return [
            'content' => "!react {$this->channel} relayed {$this->messageId} {$this->emote}",
        ];
    }
}




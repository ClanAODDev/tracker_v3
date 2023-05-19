<?php

namespace App\Channels\Messages;

use Exception;

/**
 * Class DiscordMessage.
 */
class DiscordMessage
{
    /**
     * Color codes.
     */
    public const SUCCESS = 3066993;

    public const ERROR = 15158332;

    public const INFO = 10181046;

    private $fields = [];
    private $messageId;

    /**
     * Use info-coded color.
     *
     * @return $this
     */
    public function info()
    {
        $this->color = self::INFO;

        return $this;
    }

    /**
     * Use success-code color.
     */
    public function success()
    {
        $this->color = self::SUCCESS;

        return $this;
    }

    /**
     * Use danger-code color.
     */
    public function error()
    {
        $this->color = self::ERROR;

        return $this;
    }

    /**
     * @param $channel
     * @return DiscordMessage
     */
    public function to($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function messageId($messageId)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function fields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

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

        if (!isset($this->message) && empty($this->fields)) {
            throw new Exception('A message must be defined');
        }

        if (empty($this->fields)) {
            return [
                'content' => "!relay {$this->channel} {$this->message}",
            ];
        }

        $body = empty($this->fields)
            ? $this->message
            : "'" . json_encode([
                'embed' => [
                    'color' => $this->color ?? 0,
                    'author' => [
                        'name' => 'AOD Tracker',
                    ],
                    'fields' => $this->fields,
                ],
                'id' => $this->messageId
            ]) . "'";

        return [
            'content' => "!relay {$this->channel}" . $body,
        ];
    }
}
